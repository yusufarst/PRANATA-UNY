# Offline tests for merge gate, commit-msg hook, runner, watcher and hold (TAC-02, TAC-03, TAC-05, TAC-06).
# Everything runs in a throwaway folder %TEMP%\pranata-test-<id>: a local repo plus a local bare origin.
# No network, no GitHub, no model call: the runner drives a stub command.
$ErrorActionPreference = 'Stop'
$repo = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
. (Join-Path $repo 'scripts\executor\lib\state.ps1')
. (Join-Path $repo '.claude\hooks\lib\policy.ps1')
. (Join-Path $repo 'scripts\executor\lib\merge-gate.ps1')

$script:fail = 0
$script:passed = 0
function Assert([bool]$Condition, [string]$Name) {
    if ($Condition) { $script:passed++ } else { $script:fail++; Write-Output "FAIL: $Name" }
}
function Invoke-Script([string]$Path, [string[]]$Arguments) {
    $ErrorActionPreference = 'Continue'
    $output = & powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -File $Path @Arguments 2>&1
    return @{ Out = ($output | Out-String); Code = $LASTEXITCODE }
}

$work = Join-Path $env:TEMP ("pranata-test-" + [guid]::NewGuid().ToString('N').Substring(0, 8))
[void](New-Item -ItemType Directory -Path $work)
try {
    # --- Merge gate: every section 8.1 check fails on its own.
    $good = @{
        Bootstrap = $false; Mode = 'AUTONOMOUS'; TaskId = 'TASK-010'; RiskClass = 'HIGH'; Authorized = $true
        PrState = 'OPEN'; PrBase = 'main'; IsDraft = $false; PrHeadSha = 'aaa'; VerifiedSha = 'aaa'
        CiGateConclusion = 'success'; ReviewSha = 'aaa'; ReviewStatus = 'PASS'; OpenBlockingFindings = 0
        IndependentReviewDone = $true; SubstituteReviewDone = $false; CodexStatus = 'COMPLETED_NO_FINDINGS'
        MainIsAncestor = $true; RemoteMainSha = 'mmm'; CheckedMainSha = 'mmm'; GuardPathsTouched = @(); CommitIdentityProblems = @()
        PrMergeable = 'MERGEABLE'; PrTextProblem = $false; ReconcileScopeOk = $true; TaskStatus = 'READY'
    }
    Assert ((Get-PranataMergeFailures -Facts $good).Count -eq 0) 'gate: all checks passing gives no failure'
    $negatives = @(
        @('PrState', 'MERGED', '8.1.5'), @('PrBase', 'develop', '8.1.5'), @('IsDraft', $true, '8.1.5'), @('Authorized', $false, '8.1.5'),
        @('PrHeadSha', 'bbb', '8.1.1'), @('VerifiedSha', 'ccc', '8.1.1'), @('CiGateConclusion', 'failure', '8.1.2'), @('CiGateConclusion', 'missing', '8.1.2'),
        @('ReviewSha', 'old', '8.1.3'), @('ReviewStatus', 'PENDING', '8.1.3'), @('OpenBlockingFindings', 1, '8.1.3'), @('IndependentReviewDone', $false, '8.1.3'),
        @('CodexStatus', 'TIMEOUT', '8.1.3'), @('CodexStatus', 'FAILED', '8.1.3'), @('MainIsAncestor', $false, '8.1.4'), @('CheckedMainSha', 'old', '8.1.4'),
        @('GuardPathsTouched', @('.claude/settings.json'), '8.1.6'), @('CommitIdentityProblems', @('aaa author is x'), 'GOV-08'),
        @('PrMergeable', 'CONFLICTING', '8.1.5'), @('PrTextProblem', $true, 'GOV-08'), @('ReconcileScopeOk', $false, '8.3'), @('TaskId', $null, 'task:'), @('RiskClass', $null, 'task:'), @('TaskStatus', 'DONE', 'task:'), @('TaskStatus', 'BLOCKED', 'task:'), @('CheckpointTaskId', 'TASK-999', 'task:'), @('TaskStatus', $null, 'task:')
    )
    foreach ($row in $negatives) {
        $facts = $good.Clone()
        $facts[$row[0]] = $row[1]
        $failures = Get-PranataMergeFailures -Facts $facts
        Assert (($failures | Where-Object { $_ -like "*$($row[2])*" }).Count -ge 1) "gate: $($row[0])=$($row[1]) must fail $($row[2])"
    }
    $sup = $good.Clone(); $sup.Mode = 'SUPERVISED'; $sup.GuardPathsTouched = @('.claude/x')
    Assert ((Get-PranataMergeFailures -Facts $sup).Count -eq 0) 'gate: guard paths pass in SUPERVISED'
    $sub = $good.Clone(); $sub.CodexStatus = 'TIMEOUT'; $sub.SubstituteReviewDone = $true
    Assert ((Get-PranataMergeFailures -Facts $sub).Count -eq 0) 'gate: substitute review satisfies Codex timeout'
    $boot = $good.Clone(); $boot.Bootstrap = $true; $boot.BootstrapFailures = @(); $boot.Mode = 'SUPERVISED'; $boot.TaskId = 'TASK-002'; $boot.CiGateConclusion = 'missing'
    Assert ((Get-PranataMergeFailures -Facts $boot).Count -eq 0) 'gate: bootstrap TASK-002 SUPERVISED without ci-gate passes'
    $bootAuto = $boot.Clone(); $bootAuto.Mode = 'AUTONOMOUS'
    Assert ((Get-PranataMergeFailures -Facts $bootAuto).Count -ge 1) 'gate: bootstrap refused in AUTONOMOUS'
    $bootLate = $boot.Clone(); $bootLate.TaskId = 'TASK-005'
    Assert ((Get-PranataMergeFailures -Facts $bootLate).Count -ge 1) 'gate: bootstrap refused for TASK-005'
    $bootEarly = $boot.Clone(); $bootEarly.TaskId = 'TASK-001'
    Assert ((Get-PranataMergeFailures -Facts $bootEarly).Count -ge 1) 'gate: bootstrap refused for TASK-001, which has no bootstrap job set'
    $bootNoEvidence = $boot.Clone(); $bootNoEvidence.BootstrapFailures = $null
    Assert (((Get-PranataMergeFailures -Facts $bootNoEvidence) -join ';') -match 'not collected') 'gate: bootstrap without collected evidence refused'
    $bootBad = $boot.Clone(); $bootBad.BootstrapFailures = @('bootstrap 4.1.3 x')
    Assert (((Get-PranataMergeFailures -Facts $bootBad) -join ';') -match '4\.1\.3 x') 'gate: bootstrap evidence failure reaches the refusal'

    # --- Bootstrap evidence (QUALITY_GATES 4.1): every step fails on its own.
    $headS = 'a' * 40; $mainS = 'b' * 40; $treeS = 'c' * 40; $mergeS = 'd' * 40
    function New-BootRun {
        return @{
            LookupError = $null; RunId = 77; RunAttempt = 2; RunStatus = 'completed'; RunConclusion = 'success'
            Jobs = @(@{ name = 'app-boot'; conclusion = 'success' }, @{ name = 'bootstrap-gate'; conclusion = 'success' })
            JobsTotal = 2; JobError = $null; EvidenceError = $null
            Evidence = [pscustomobject]@{ pr = 5; head_sha = $headS; base_sha = $mainS; tree_sha = $treeS; merge_sha = $mergeS; run_id = 77; run_attempt = 2 }
        }
    }
    function Get-BootFailures([hashtable]$Run, [string]$TaskId = 'TASK-002') {
        return (Get-PranataBootstrapFailures -Run $Run -TaskId $TaskId -Pr 5 -HeadSha $headS -MainSha $mainS -Tree $treeS)
    }
    Assert ((Get-BootFailures (New-BootRun)).Count -eq 0) 'bootstrap: green run on the latest HEAD accepted'
    Assert (((Get-BootFailures (New-BootRun) 'TASK-003') -join ';') -match "'db-integration' is missing") 'bootstrap: TASK-003 requires db-integration'
    Assert (((Get-BootFailures (New-BootRun) 'TASK-004') -join ';') -match "'quality' is missing") 'bootstrap: TASK-004 requires quality'
    foreach ($row in @(
        @('RunId', $null, '4\.1\.2'), @('LookupError', 'boom', '4\.1\.2'), @('RunStatus', 'in_progress', '4\.1\.3'), @('RunConclusion', 'failure', '4\.1\.3'),
        @('RunConclusion', 'cancelled', '4\.1\.3'), @('RunConclusion', $null, '4\.1\.3'), @('JobError', 'boom', '4\.1\.3'), @('JobsTotal', 3, '4\.1\.3'), @('Evidence', $null, '4\.1\.4')
    )) {
        $run = New-BootRun; $run[$row[0]] = $row[1]
        Assert (((Get-BootFailures $run) -join ';') -match $row[2]) "bootstrap: $($row[0])=$($row[1]) refused"
    }
    foreach ($conclusion in @('failure', 'cancelled', 'skipped', 'timed_out', 'action_required', 'neutral', $null)) {
        $run = New-BootRun; $run.Jobs = @(@{ name = 'app-boot'; conclusion = $conclusion }, @{ name = 'bootstrap-gate'; conclusion = 'success' })
        Assert (((Get-BootFailures $run) -join ';') -match "job 'app-boot'") "bootstrap: job conclusion '$conclusion' refused"
    }
    $run = New-BootRun; $run.Jobs = @(@{ name = 'bootstrap-gate'; conclusion = 'success' }); $run.JobsTotal = 1
    Assert (((Get-BootFailures $run) -join ';') -match "'app-boot' is missing") 'bootstrap: missing required job refused'
    $run = New-BootRun; $run.Jobs += @{ name = 'extra'; conclusion = 'skipped' }; $run.JobsTotal = 3
    Assert (((Get-BootFailures $run) -join ';') -match "job 'extra'") 'bootstrap: a skipped job outside the required set refused'
    foreach ($field in @('pr', 'head_sha', 'base_sha', 'tree_sha', 'run_id', 'run_attempt', 'merge_sha')) {
        $run = New-BootRun; $run.Evidence.$field = 'other'
        Assert (((Get-BootFailures $run) -join ';') -match "4\.1\.4 evidence $field") "bootstrap: evidence $field mismatch refused"
    }
    $run = New-BootRun; $run.Evidence = [pscustomobject]@{ pr = 5 }
    Assert (((Get-BootFailures $run) -join ';') -match 'evidence head_sha') 'bootstrap: incomplete evidence refused'
    Assert ((Get-BootFailures (New-BootRun) 'TASK-010').Count -eq 0) 'bootstrap: a Task outside the map is refused by the gate, not here'

    # --- Run collection against a stubbed gh: only a run proven to belong to this PR and HEAD counts.
    $script:ghRuns = '{"workflow_runs":[]}'; $script:ghJobs = '{"total_count":0,"jobs":[]}'; $script:ghEvidence = $null; $script:ghExit = 0
    function gh {
        $global:LASTEXITCODE = $script:ghExit
        if ($script:ghExit -ne 0) { return }
        if ($args[0] -eq 'api') { if ($args[1] -match '/attempts/') { return $script:ghJobs } else { return $script:ghRuns } }
        if (-not $script:ghEvidence) { $global:LASTEXITCODE = 1; return }
        $target = $args[$args.Count - 1]
        [void](New-Item -ItemType Directory -Path $target -Force)
        [IO.File]::WriteAllText((Join-Path $target 'bootstrap-evidence.json'), $script:ghEvidence)
    }
    function New-RunJson([long]$Id, [int]$Attempt, [string]$Head, [int]$Number, [string]$Event = 'pull_request') {
        return '{"id":' + $Id + ',"run_attempt":' + $Attempt + ',"status":"completed","conclusion":"success","head_sha":"' + $Head + '","event":"' + $Event + '","pull_requests":[{"number":' + $Number + '}]}'
    }
    $bootDir = Join-Path $work 'bootrun'
    [void](New-Item -ItemType Directory -Path $bootDir)
    $collect = { Get-PranataBootstrapRun -Repo 'o/r' -Pr 5 -HeadSha $headS -WorkDir $bootDir }

    $collected = & $collect
    Assert ($null -eq $collected.RunId -and -not $collected.LookupError) 'collect: no run gives no run id'
    Assert (((Get-PranataBootstrapFailures -Run $collected -TaskId 'TASK-002' -Pr 5 -HeadSha $headS -MainSha $mainS -Tree $treeS) -join ';') -match '4\.1\.2 no bootstrap run') 'collect: a missing run is refused'

    $script:ghRuns = '{"workflow_runs":[' + (New-RunJson 90 1 ('e' * 40) 5) + ',' + (New-RunJson 91 1 $headS 6) + ',' + (New-RunJson 92 1 $headS 5 'push') + ',' + (New-RunJson 70 1 $headS 5) + ',' + (New-RunJson 77 2 $headS 5) + ']}'
    $script:ghJobs = '{"total_count":2,"jobs":[{"name":"app-boot","conclusion":"success"},{"name":"bootstrap-gate","conclusion":"success"}]}'
    $script:ghEvidence = '{"pr":5,"head_sha":"' + $headS + '","base_sha":"' + $mainS + '","tree_sha":"' + $treeS + '","merge_sha":"' + $mergeS + '","run_id":77,"run_attempt":2}'
    $collected = & $collect
    Assert ($collected.RunId -eq 77 -and $collected.RunAttempt -eq 2) "collect: another HEAD, another PR, another event and older runs are ignored ($($collected.RunId))"
    Assert ((Get-PranataBootstrapFailures -Run $collected -TaskId 'TASK-002' -Pr 5 -HeadSha $headS -MainSha $mainS -Tree $treeS).Count -eq 0) 'collect: the collected green run passes'
    Assert (@(Get-ChildItem -LiteralPath $bootDir).Count -eq 0) 'collect: the downloaded evidence is removed'

    $script:ghEvidence = $null
    $collected = & $collect
    Assert (((Get-PranataBootstrapFailures -Run $collected -TaskId 'TASK-002' -Pr 5 -HeadSha $headS -MainSha $mainS -Tree $treeS) -join ';') -match '4\.1\.4 evidence artifact unavailable') 'collect: a missing artifact is refused'
    $script:ghEvidence = 'not json'
    $collected = & $collect
    Assert (((Get-PranataBootstrapFailures -Run $collected -TaskId 'TASK-002' -Pr 5 -HeadSha $headS -MainSha $mainS -Tree $treeS) -join ';') -match '4\.1\.4 evidence artifact unavailable') 'collect: an unreadable artifact is refused'
    $script:ghExit = 1
    $collected = & $collect
    Assert (((Get-PranataBootstrapFailures -Run $collected -TaskId 'TASK-002' -Pr 5 -HeadSha $headS -MainSha $mainS -Tree $treeS) -join ';') -match '4\.1\.2 run lookup failed') 'collect: a failing API call is refused'
    Remove-Item -LiteralPath Function:\gh

    $low = $good.Clone(); $low.RiskClass = 'LOW'; $low.CodexStatus = 'NOT_REQUESTED'; $low.IndependentReviewDone = $false
    Assert ((Get-PranataMergeFailures -Facts $low).Count -eq 0) 'gate: reconciliation (LOW) needs no Codex review'
    Assert ((Get-PranataTaskFromBranch 'chore/task-001-x').TaskId -eq 'TASK-001') 'gate: Task read from branch'
    Assert (Test-PranataReconcileScope @('docs/11-tasks/TASK-020.md', 'docs/11-tasks/TASK_PLAN.md', 'docs/handoff/CURRENT_HANDOFF.md') 'TASK-020') 'gate: reconciliation of the three documents allowed'
    Assert (-not (Test-PranataReconcileScope @('docs/11-tasks/TASK-030.md') 'TASK-020')) 'gate: reconciliation cannot touch another Task contract'
    Assert (-not (Test-PranataReconcileScope @('docs/00-governance/APPROVAL_RECORDS.md') 'TASK-020')) 'gate: reconciliation cannot touch authorization records'
    Assert ((Get-PranataTaskFromBranch 'docs/reconcile-task-012').Reconcile) 'gate: reconciliation branch recognised'
    Assert (-not (Get-PranataTaskFromBranch 'feature/other').TaskId) 'gate: unrelated branch names no Task'
    Assert (Test-PranataAuthorizationText "## AUTH-013`n- State: **ACTIVE** from x`n## AUTH-012`n" 'TASK-078') 'gate: active authorization read'
    Assert (-not (Test-PranataAuthorizationText "## AUTH-013`n- State: **REVOKED**`n## AUTH-012`n- State: **ACTIVE**`n" 'TASK-010')) 'gate: revoked authorization refused'
    Assert (-not (Test-PranataAuthorizationText "## AUTH-013`n- State: **ACTIVE**`n" 'TASK-079')) 'gate: Task outside the 78 refused'

    # --- Post-DONE remediation lane (EXECUTOR_OPERATING_MODEL 8.5, M1-AC-01 to M1-AC-10), pure judgement.
    $srcBase = '351ba2f17c1764283e66215f6069ed64872fb97a'
    $srcHeads = @{ 1 = 'd055d763cb236b71296a2fff5bc06f1218a681c7'; 2 = '0b2ac7b2c8bad1a3c9d96c2816624651c0ca2e6f'; 3 = '161ff74b7c83dd6878c37a9516d26fa70fd8830d'; 4 = 'd1c609fbeb65054cdfb42abe075793fb1811c617' }
    $remPaths = @{
        0 = @('scripts/executor/lib/merge-gate.ps1', 'scripts/executor/merge-verified.ps1', 'scripts/executor/tests/scripts.tests.ps1')
        1 = @('tests/Feature/Access/ProcessAssignmentTest.php')
        2 = @('resources/js/components/app/files.test.tsx', 'tests/Feature/Files/PrivateFilesTest.php')
        3 = @('docs/11-tasks/TASK-015.md')
        4 = @('docs/11-tasks/TASK-016.md', 'docs/handoff/CURRENT_HANDOFF.md')
    }
    $remTasks = @{ 0 = 'TASK-001'; 1 = 'TASK-015'; 2 = 'TASK-016'; 3 = 'TASK-015'; 4 = 'TASK-016' }
    $remFamilies = @{ 0 = 'fix/task-001-post-done-remediation-gate'; 1 = 'fix/task-015-test-integrity'; 2 = 'fix/task-016-test-integrity'; 3 = 'docs/reconcile-task-015'; 4 = 'docs/reconcile-task-016' }
    $remBranches = @{ 0 = 'fix/task-001-post-done-remediation-gate'; 1 = 'fix/task-015-test-integrity-r2'; 2 = 'fix/task-016-test-integrity-r2'; 3 = 'docs/reconcile-task-015-r3'; 4 = 'docs/reconcile-task-016-r3' }
    function New-RemBlock([string]$Id = 'REM-001', [string]$State = 'ACTIVE', [string[]]$Skip = @(), [string]$Extra = '') {
        $lines = @("REMEDIATION: $Id", "STATE: $State", 'MODE: SUPERVISED', 'ORDER: 0, 1, 2, 3, 4')
        $lines += "ENTRY 0 | TASK-001 | $($remFamilies[0]) | PR: new | SOURCE: none | EQUIVALENCE: none | PATHS: $($remPaths[0] -join ', ') | PURPOSE: gate"
        $lines += "ENTRY 1 | TASK-015 | $($remFamilies[1]) | PR: #53 | SOURCE: $srcBase..$($srcHeads[1]) | EQUIVALENCE: IDENTICAL | PATHS: $($remPaths[1] -join ', ') | PURPOSE: tests"
        $lines += "ENTRY 2 | TASK-016 | $($remFamilies[2]) | PR: #52 | SOURCE: $srcBase..$($srcHeads[2]) | EQUIVALENCE: IDENTICAL | PATHS: $($remPaths[2] -join ', ') | PURPOSE: tests"
        $lines += "ENTRY 3 | TASK-015 | $($remFamilies[3]) | PR: #54 | SOURCE: $srcBase..$($srcHeads[3]) | EQUIVALENCE: IDENTICAL or DELTA | PATHS: $($remPaths[3] -join ', ') | PURPOSE: evidence"
        $lines += "ENTRY 4 | TASK-016 | $($remFamilies[4]) | PR: #55 | SOURCE: $srcBase..$($srcHeads[4]) | EQUIVALENCE: IDENTICAL or DELTA | PATHS: $($remPaths[4] -join ', ') | PURPOSE: evidence"
        $lines = @($lines | Where-Object { $line = $_; @($Skip | Where-Object { $line.StartsWith($_) }).Count -eq 0 })
        if ($Extra) { $lines += $Extra }
        return "## $Id`n`n- State: **$State**`n`n" + '```text' + "`n" + ($lines -join "`n") + "`n" + '```' + "`n"
    }
    $authText = "## AUTH-013`n- State: **ACTIVE** from x`n`n"
    function New-RemPr([int]$K, [string]$Branch = $remBranches[$K], [hashtable]$Change = @{}) {
        $pr = @{ Number = 60 + $K; Branch = $Branch; Body = "Ringkasan`r`nRemediation: REM-001 entry $K`r`n"; HeadSha = ('e' * 39) + $K
            DescendsFromActivation = $true; AncestorOfMain = $true; ChangedFiles = $remPaths[$K] }
        foreach ($key in $Change.Keys) { $pr[$key] = $Change[$key] }
        return $pr
    }
    $taskText = "# TASK`n`n" + '```text' + "`nSTATUS: DONE`nRISK CLASS: HIGH`n" + '```' + "`n`n## OBJECTIVE`n`nGoal.`n`n## COMPLETION EVIDENCE`n`n- Old evidence.`n`n## SAFE NEXT TASK`n`nNext.`n"
    $taskEdited = $taskText.Replace('- Old evidence.', "- Old evidence.`n- Correction 2026-11-07 (REM-001).")
    function New-RemFacts([int]$N) {
        $facts = @{
            Records = $authText + (New-RemBlock); PrBody = "Ringkasan`r`n`r`nRemediation: REM-001 entry $N`r`n"; TaskId = $remTasks[$N]; Branch = $remBranches[$N]
            Mode = 'SUPERVISED'; Bootstrap = $false; CheckpointTaskId = $remTasks[$N]; CheckpointRemediation = "REM-001/$N"
            ChangedFiles = $remPaths[$N]; TaskFiles = @{}; Blobs = @{ Main = @{}; SourceBase = @{}; SourceHead = @{}; Head = @{} }
            Review = $null; DeltaSha256 = $null
            Publication = @{ Error = $null; ActivationSha = ('a' * 40); CandidateDescends = $true; Prs = @(0..($N - 1) | Where-Object { $_ -ge 0 -and $_ -lt $N } | ForEach-Object { New-RemPr $_ }) }
        }
        if ($N -ge 1) {
            foreach ($path in $remPaths[$N]) {
                $facts.Blobs.Main[$path] = 'base-blob'; $facts.Blobs.SourceBase[$path] = 'base-blob'
                $facts.Blobs.SourceHead[$path] = "head-blob-$path"; $facts.Blobs.Head[$path] = "head-blob-$path"
            }
            $facts.Review = [pscustomobject]@{ sha = 'x'; source_sha = $srcHeads[$N]; equivalence = 'IDENTICAL' }
        }
        if ($N -ge 3) {
            $facts.TaskFiles[$remPaths[$N][0]] = @{ Main = $taskText; Head = $taskEdited }
            $facts.Review = [pscustomobject]@{ sha = 'x'; source_sha = $srcHeads[$N]; equivalence = 'DELTA'; delta_sha256 = ('f' * 64); delta_verified = $true }
            $facts.DeltaSha256 = 'f' * 64
            foreach ($path in $remPaths[$N]) { $facts.Blobs.Head[$path] = 'delta-blob' }
        }
        return $facts
    }
    function Get-RemFailures([hashtable]$Facts) { return (@((Get-PranataRemediationFailures -Facts $Facts).Failures) -join ' ;; ') }
    function Assert-RemRefused([hashtable]$Facts, [string]$Pattern, [string]$Name) {
        $text = Get-RemFailures $Facts
        Assert ($text -match $Pattern) "remediation: $Name refused ($text)"
    }

    foreach ($n in 0..4) {
        $result = Get-PranataRemediationFailures -Facts (New-RemFacts $n)
        Assert ($result.Failures.Count -eq 0 -and $result.Label -eq "REM-001 entry $n") "remediation: entry $n with every condition met passes ($(@($result.Failures) -join ' ;; '))"
    }
    $records = Get-PranataRemediationRecords ($authText + (New-RemBlock))
    Assert ($records.Count -eq 1 -and $records[0].Entries.Count -eq 5 -and $records[0].Entries[3].Equivalence -eq 'IDENTICAL or DELTA' -and $records[0].Entries[4].Paths.Count -eq 2) 'remediation: the machine block is read entry by entry'

    # M1-AC-01: authorization read from main, exactly one ACTIVE record.
    $f = New-RemFacts 1; $f.Records = $authText
    Assert-RemRefused $f 'REM-001 is not recorded on main' 'a missing REM record'
    foreach ($state in @('PROPOSED', 'CLOSED')) {
        $f = New-RemFacts 1; $f.Records = $authText + (New-RemBlock -State $state)
        Assert-RemRefused $f "REM-001 is $state, not ACTIVE" "a $state record"
    }
    $f = New-RemFacts 1; $f.Records = $authText + (New-RemBlock) + (New-RemBlock)
    Assert-RemRefused $f 'REM-001 is recorded 2 times' 'a record written twice'
    $f = New-RemFacts 1; $f.Records = $authText + (New-RemBlock) + (New-RemBlock -Id 'REM-002')
    Assert-RemRefused $f '2 ACTIVE remediation records cover TASK-015' 'two ACTIVE records covering the same entry'
    $f = New-RemFacts 1; $f.Records = $authText + (New-RemBlock) + (New-RemBlock -Id 'REM-002' -State 'CLOSED')
    Assert ((Get-RemFailures $f) -eq '') 'remediation: a CLOSED record next to the ACTIVE one is ignored'
    foreach ($case in @(
        @(@('STATE:'), '', 'STATE missing'), @(@('MODE:'), '', 'MODE missing'), @(@('ORDER:'), '', 'ORDER'), @(@('ENTRY 2'), '', 'entry 2 missing'),
        @(@(), 'STATE: CLOSED', 'STATE repeated'), @(@(), 'MODE: AUTONOMOUS', 'unexpected line'), @(@(), 'free text', 'unexpected line'),
        @(@('ENTRY 4'), 'ENTRY 4 | TASK-016 | docs/reconcile-task-016 | PR: #55 | SOURCE: 351ba2f..d1c609f | EQUIVALENCE: DELTA | PATHS: docs/11-tasks/TASK-016.md | PURPOSE: x', 'bad SOURCE'),
        @(@('ENTRY 4'), 'ENTRY 4 | TASK-016 | docs/reconcile-task-016 | PR: #55 | SOURCE: none | EQUIVALENCE: DELTA | PATHS: docs/11-tasks/TASK-016.md | PURPOSE: x', 'disagree'),
        @(@('ENTRY 4'), 'ENTRY 4 | TASK-016 | docs/reconcile-task-016 | PR: #55 | SOURCE: none | EQUIVALENCE: none | PATHS: ../x.md | PURPOSE: x', "bad path"),
        @(@('ENTRY 4'), 'ENTRY 4 | TASK-016 | docs/reconcile-task-016 | PR: #55 | SOURCE: none | EQUIVALENCE: none | PURPOSE: x', 'fields'),
        @(@(), 'ENTRY 1 | TASK-015 | fix/task-015-test-integrity | PR: #53 | SOURCE: none | EQUIVALENCE: none | PATHS: a.txt | PURPOSE: x', 'entry 1 repeated')
    )) {
        $f = New-RemFacts 1; $f.Records = $authText + (New-RemBlock -Skip $case[0] -Extra $case[1])
        Assert-RemRefused $f "malformed: .*$([regex]::Escape($case[2]))" "a malformed block ($($case[2]))"
    }
    $f = New-RemFacts 1; $f.Records = $authText + (New-RemBlock) + (New-RemBlock -Id 'REM-002' -State 'CLOSED' -Skip @('MODE:'))
    Assert-RemRefused $f 'REM-002 is malformed' 'any malformed record, even a closed one'
    $f = New-RemFacts 1; $f.Records = $authText + (New-RemBlock).Replace('REMEDIATION: REM-001', 'REMEDIATION: REM-1')
    Assert-RemRefused $f 'malformed' 'a header without a three-digit id'

    # M1-AC-02: Task, branch family, exact paths, TASK_PLAN, and COMPLETION EVIDENCE only with STATUS DONE kept.
    $f = New-RemFacts 1; $f.TaskId = 'TASK-016'; $f.Branch = 'fix/task-016-test-integrity-r2'
    Assert-RemRefused $f 'the entry is for TASK-015' 'a PR for another Task'
    $f = New-RemFacts 1; $f.PrBody = 'Remediation: REM-001 entry 9'
    Assert-RemRefused $f 'has no entry 9' 'an entry that is not recorded'
    foreach ($branch in @('fix/task-015-other', 'fix/task-015-test-integrity-r1', 'fix/task-015-test-integrity-r', 'fix/task-015-test-integrity-r02', 'fix/task-015-test-integrity-r2a', 'fix/task-015-test-integrity-v2', 'fix/task-015-test-integrity-r2-r3')) {
        $f = New-RemFacts 1; $f.Branch = $branch
        Assert-RemRefused $f 'is neither' "branch $branch"
    }
    foreach ($branch in @('fix/task-015-test-integrity', 'fix/task-015-test-integrity-r2', 'fix/task-015-test-integrity-r10')) {
        $f = New-RemFacts 1; $f.Branch = $branch
        Assert ((Get-RemFailures $f) -eq '') "remediation: branch $branch of the family accepted"
    }
    $f = New-RemFacts 1; $f.ChangedFiles = @($remPaths[1] + 'app/Other.php')
    Assert-RemRefused $f 'app/Other.php is outside the entry paths' 'a file outside the entry paths'
    $f = New-RemFacts 3; $f.ChangedFiles = @($remPaths[3] + 'docs/11-tasks/TASK_PLAN.md')
    Assert-RemRefused $f 'TASK_PLAN.md must not change' 'a TASK_PLAN change'
    $f = New-RemFacts 1; $f.ChangedFiles = @()
    Assert-RemRefused $f 'changes no file' 'a PR without changes'
    $f = New-RemFacts 3; $f.TaskFiles[$remPaths[3][0]].Head = $taskEdited.Replace('Goal.', 'Another goal.')
    Assert-RemRefused $f 'changed outside ## COMPLETION EVIDENCE: ## OBJECTIVE' 'a change outside COMPLETION EVIDENCE'
    $f = New-RemFacts 3; $f.TaskFiles[$remPaths[3][0]].Head = $taskEdited.Replace('Next.', "Next.`r")
    Assert-RemRefused $f 'changed outside ## COMPLETION EVIDENCE: ## SAFE NEXT TASK' 'a line-ending change outside COMPLETION EVIDENCE'
    $f = New-RemFacts 3; $f.TaskFiles[$remPaths[3][0]].Head = $taskEdited.Replace('STATUS: DONE', 'STATUS: VERIFYING')
    Assert-RemRefused $f 'STATUS: DONE is missing at PR HEAD' 'a STATUS change'
    $f = New-RemFacts 3; $f.TaskFiles[$remPaths[3][0]].Head = $taskEdited.Replace('- Correction', "## NEW`n- Correction")
    Assert-RemRefused $f 'the section headings changed' 'a new section'
    $f = New-RemFacts 3; $f.TaskFiles = @{}
    Assert-RemRefused $f 'was not compared' 'a Task file that was not compared'

    # M1-AC-04 and M1-AC-09: SUPERVISED only, no -Bootstrap, checkpoint Task and entry.
    $f = New-RemFacts 1; $f.Mode = 'AUTONOMOUS'
    Assert-RemRefused $f 'only in SUPERVISED mode' 'AUTONOMOUS mode'
    $f = New-RemFacts 1; $f.Bootstrap = $true
    Assert-RemRefused $f 'together with -Bootstrap' '-Bootstrap'
    $f = New-RemFacts 1; $f.CheckpointTaskId = 'TASK-016'
    Assert-RemRefused $f "checkpoint task_id is 'TASK-016', expected 'TASK-015'" 'a checkpoint for another Task'
    $f = New-RemFacts 1; $f.CheckpointTaskId = $null
    Assert-RemRefused $f 'checkpoint task_id' 'a checkpoint without a Task'
    foreach ($value in @($null, '', 'REM-001/2', 'REM-002/1', 'rem-001/1')) {
        $f = New-RemFacts 1; $f.CheckpointRemediation = $value
        Assert-RemRefused $f "checkpoint remediation is '$value', expected 'REM-001/1'" "checkpoint remediation '$value'"
    }

    # M1-AC-08: SOURCE base on main, IDENTICAL blobs, DELTA digest, disposition bound to SOURCE HEAD.
    $f = New-RemFacts 1; $f.Blobs.Main[$remPaths[1][0]] = 'moved-blob'
    Assert-RemRefused $f 'on main is not the blob of SOURCE base' 'main no longer at the SOURCE base blob'
    $f = New-RemFacts 4; $f.Blobs.Main['docs/handoff/CURRENT_HANDOFF.md'] = 'moved-blob'
    Assert-RemRefused $f 'CURRENT_HANDOFF.md on main is not the blob of SOURCE base' 'one of two paths moved on main'
    $f = New-RemFacts 1; $f.Blobs.Head[$remPaths[1][0]] = 'other-blob'
    Assert-RemRefused $f 'IDENTICAL: .* at PR HEAD is not the blob of SOURCE HEAD' 'an IDENTICAL claim with another HEAD blob'
    $f = New-RemFacts 2; $f.Blobs.Head[$remPaths[2][1]] = 'other-blob'
    Assert-RemRefused $f 'IDENTICAL: tests/Feature/Files/PrivateFilesTest.php' 'one of two IDENTICAL paths changed'
    $f = New-RemFacts 1; $f.Blobs = @{ Main = @{}; SourceBase = @{}; SourceHead = @{}; Head = @{} }
    Assert-RemRefused $f 'were not collected' 'blobs that were not collected'
    $f = New-RemFacts 1; $f.Review = [pscustomobject]@{ source_sha = $srcHeads[1]; equivalence = 'DELTA'; delta_sha256 = ('f' * 64); delta_verified = $true }; $f.DeltaSha256 = 'f' * 64
    Assert-RemRefused $f "equivalence is 'DELTA', the entry allows IDENTICAL" 'a DELTA claim on an IDENTICAL-only entry'
    $f = New-RemFacts 1; $f.Review = [pscustomobject]@{ source_sha = $srcHeads[1] }
    Assert-RemRefused $f "equivalence is ''" 'a disposition without equivalence'
    $f = New-RemFacts 1; $f.Review = $null
    Assert-RemRefused $f 'no review disposition records the equivalence' 'a missing disposition'
    $f = New-RemFacts 1; $f.Review.source_sha = $srcHeads[2]
    Assert-RemRefused $f 'source_sha is .*, expected SOURCE HEAD' 'a disposition for another source_sha'
    $f = New-RemFacts 3; $f.DeltaSha256 = 'e' * 64
    Assert-RemRefused $f 'DELTA: SHA-256 recorded f+, recomputed e+' 'a DELTA digest that differs'
    $f = New-RemFacts 3; $f.DeltaSha256 = $null
    Assert-RemRefused $f 'could not be recomputed' 'a DELTA digest that could not be recomputed'
    $f = New-RemFacts 3; $f.Review.delta_verified = $false
    Assert-RemRefused $f 'not verified independently' 'a DELTA without independent verification'
    $f = New-RemFacts 3; $f.Review.delta_verified = 'true'
    Assert-RemRefused $f 'not verified independently' 'a DELTA verification flag that is not a boolean'
    $f = New-RemFacts 3; $f.Review.delta_sha256 = 'abc'
    Assert-RemRefused $f 'is not a SHA-256' 'a malformed DELTA digest'
    $f = New-RemFacts 3; $f.Review = [pscustomobject]@{ source_sha = $srcHeads[3]; equivalence = 'IDENTICAL' }
    Assert-RemRefused $f 'IDENTICAL: docs/11-tasks/TASK-015.md at PR HEAD' 'an IDENTICAL claim on a changed delta entry'

    # M1-AC-10: activation, order, single use and the publication evidence of earlier entries.
    $f = New-RemFacts 1; $f.PrBody = 'Ringkasan'
    Assert-RemRefused $f 'exactly one line' 'a candidate PR without its remediation line'
    $f = New-RemFacts 1; $f.PrBody = "Remediation: REM-001 entry 1`nRemediation: REM-001 entry 2"
    Assert-RemRefused $f 'found 2' 'a candidate PR with two remediation lines'
    $f = New-RemFacts 1; $f.Publication.Prs = @()
    Assert-RemRefused $f 'entry 0 is not published yet' 'entry 1 before entry 0'
    $f = New-RemFacts 3; $f.Publication.Prs = @((New-RemPr 0), (New-RemPr 1))
    Assert-RemRefused $f 'entry 2 is not published yet' 'entry 3 before entry 2'
    $f = New-RemFacts 0; $f.Publication.Prs = @(New-RemPr 0)
    Assert-RemRefused $f 'entry 0 is already published \(#60\)' 'entry 0 used again'
    $f = New-RemFacts 2; $f.Publication.Prs += New-RemPr 2 -Branch 'fix/task-016-test-integrity-r3'
    Assert-RemRefused $f 'entry 2 is already published' 'entry 2 used again through another successor'
    $f = New-RemFacts 2; $f.Publication.Prs += New-RemPr 0 -Branch 'fix/task-001-post-done-remediation-gate-r2'
    Assert-RemRefused $f 'entry 0 has 2 publication records' 'duplicate publication evidence'
    foreach ($change in @(
        @{ AncestorOfMain = $false }, @{ DescendsFromActivation = $false }, @{ Body = 'Ringkasan' }, @{ Body = 'Remediation: REM-001 entry 1' },
        @{ Body = 'Remediation: REM-002 entry 0' }, @{ Body = "Remediation: REM-001 entry 0`nRemediation: REM-001 entry 1" },
        @{ ChangedFiles = @('scripts/executor/lib/merge-gate.ps1', 'docs/11-tasks/TASK_PLAN.md') }, @{ ChangedFiles = @() }, @{ Branch = 'fix/task-001-other' }
    )) {
        $f = New-RemFacts 1; $f.Publication.Prs = @(New-RemPr 0 -Change $change)
        Assert-RemRefused $f 'entry 0 is not published yet' "evidence not counted: $(($change.Keys | ForEach-Object { "$_=$($change[$_])" }) -join ',')"
    }
    $f = New-RemFacts 1; $f.Publication.CandidateDescends = $false
    Assert-RemRefused $f 'does not descend from the activation commit' 'a candidate that does not descend from the activation commit'
    $f = New-RemFacts 1; $f.Publication.ActivationSha = $null
    Assert-RemRefused $f 'no activation commit' 'a record never activated on main'
    $f = New-RemFacts 1; $f.Publication = @{ Error = 'gh pr list exited 1' }
    Assert-RemRefused $f 'could not be read: gh pr list exited 1' 'unreadable publication evidence'
    $f = New-RemFacts 1; $f.Publication = $null
    Assert-RemRefused $f 'publication evidence was not collected' 'publication evidence that was not collected'
    # Real history: #49 and #51 were merged before the activation commit and never count.
    $pr49 = @{ Number = 49; Branch = 'docs/reconcile-task-015'; Body = 'Rekonsiliasi TASK-015'; HeadSha = '06d83a7e2ce0683905c791c7dbad0f1364b252ff'
        DescendsFromActivation = $false; AncestorOfMain = $true; ChangedFiles = @('docs/11-tasks/TASK-015.md', 'docs/11-tasks/TASK_PLAN.md', 'docs/handoff/CURRENT_HANDOFF.md') }
    $pr51 = @{ Number = 51; Branch = 'docs/reconcile-task-016'; Body = 'Rekonsiliasi TASK-016'; HeadSha = $srcBase
        DescendsFromActivation = $false; AncestorOfMain = $true; ChangedFiles = @('docs/11-tasks/TASK-016.md') }
    $f = New-RemFacts 4; $f.Publication.Prs = @((New-RemPr 0), (New-RemPr 1), (New-RemPr 2), $pr49, $pr51)
    Assert-RemRefused $f 'entry 3 is not published yet' 'entry 4 without the genuine entry 3, although #49 exists'
    $f = New-RemFacts 3; $f.Publication.Prs = @((New-RemPr 0), (New-RemPr 1), (New-RemPr 2), $pr49)
    Assert ((Get-RemFailures $f) -eq '') 'remediation: #49 does not count as an earlier publication of entry 3'
    $f = New-RemFacts 4; $f.Publication.Prs += $pr51
    Assert ((Get-RemFailures $f) -eq '') 'remediation: #51 does not count as an earlier publication of entry 4'
    $forged = $pr49.Clone(); $forged.Body = 'Remediation: REM-001 entry 3'; $forged.ChangedFiles = @('docs/11-tasks/TASK-015.md')
    $f = New-RemFacts 4; $f.Publication.Prs = @((New-RemPr 0), (New-RemPr 1), (New-RemPr 2), $forged)
    Assert-RemRefused $f 'entry 3 is not published yet' 'a pre-activation PR carrying a remediation line'

    # The gate: only the DONE check is lifted, and every other 8.1 check still fails on its own (M1-AC-03, M1-AC-05).
    $goodRem = $good.Clone(); $goodRem.TaskStatus = 'DONE'; $goodRem.Mode = 'SUPERVISED'; $goodRem.Remediation = @{ Label = 'REM-001 entry 1'; Failures = @() }
    Assert ((Get-PranataMergeFailures -Facts $goodRem).Count -eq 0) 'gate: a DONE Task passes through a satisfied remediation lane'
    $doneOnly = $goodRem.Clone(); $doneOnly.Remediation = $null
    Assert (((Get-PranataMergeFailures -Facts $doneOnly) -join ';') -match "status on main is 'DONE'") 'gate: a DONE Task without the lane is refused'
    $laneFails = $goodRem.Clone(); $laneFails.Remediation = @{ Label = 'REM-001 entry 1'; Failures = @('REM-001 entry 1: 8.5.7 x') }
    $text = (Get-PranataMergeFailures -Facts $laneFails) -join ';'
    Assert ($text -match 'REM-001 entry 1: 8\.5\.7 x' -and $text -match "status on main is 'DONE'") 'gate: a lane refusal is reported with its entry and keeps the DONE refusal'
    $laneMissing = $goodRem.Clone(); $laneMissing.Remediation = @{ Label = 'REM-001 entry 1'; Failures = $null }
    Assert (((Get-PranataMergeFailures -Facts $laneMissing) -join ';') -match 'not evaluated') 'gate: a lane that was not evaluated is refused'
    foreach ($status in @('BLOCKED', 'SUPERSEDED', 'done', $null)) {
        $other = $goodRem.Clone(); $other.TaskStatus = $status
        Assert (((Get-PranataMergeFailures -Facts $other) -join ';') -match 'task: contract status') "gate: status '$status' stays refused even with a satisfied lane"
    }
    # The lane is SUPERVISED only, where guardrail paths pass; their AUTONOMOUS refusal is checked just below.
    foreach ($row in @($negatives | Where-Object { $_[0] -notin @('TaskStatus', 'GuardPathsTouched') })) {
        $facts = $goodRem.Clone()
        $facts[$row[0]] = $row[1]
        $failures = Get-PranataMergeFailures -Facts $facts
        Assert (($failures | Where-Object { $_ -like "*$($row[2])*" }).Count -ge 1) "gate: on the remediation lane $($row[0])=$($row[1]) must still fail $($row[2])"
    }
    $remAuto = $goodRem.Clone(); $remAuto.Mode = 'AUTONOMOUS'; $remAuto.GuardPathsTouched = @('scripts/executor/merge-verified.ps1')
    Assert (((Get-PranataMergeFailures -Facts $remAuto) -join ';') -match '8\.1\.6') 'gate: guardrail paths on the remediation lane stay SUPERVISED only'
    # --- Disposable repo with the real commit-msg hook, under the machine autocrlf setting.
    $origin = Join-Path $work 'origin.git'
    $clone = Join-Path $work 'clone'
    & git init --bare --quiet --initial-branch=main $origin
    & git init --quiet --initial-branch=main $clone
    & git -C $clone remote add origin $origin
    Copy-Item -Recurse (Join-Path $repo '.githooks') (Join-Path $clone '.githooks')
    & git -C $clone config core.hooksPath .githooks
    # Same as the real .gitignore: runtime state is never committed.
    Add-Content -LiteralPath (Join-Path $clone '.git\info\exclude') -Value '/.pranata/'

    function Test-Commit([string]$Message) {
        $file = Join-Path $work 'msg.txt'
        [IO.File]::WriteAllText($file, $Message, (New-Object Text.UTF8Encoding($false)))
        Add-Content -LiteralPath (Join-Path $clone 'f.txt') -Value ([guid]::NewGuid().ToString())
        & git -C $clone add f.txt 2>$null
        $previous = $ErrorActionPreference; $ErrorActionPreference = 'Continue'
        & git -C $clone commit --quiet -F $file 2>$null
        $code = $LASTEXITCODE
        $ErrorActionPreference = $previous
        return $code
    }
    Assert ((Test-Commit "chore(task-001): initial`n") -eq 0) 'commit-msg: clean message accepted'
    $robot = [string][char]0xD83E + [string][char]0xDD16
    foreach ($bad in @(
        "chore: x`n`nCo-Authored-By: Claude <noreply@anthropic.com>`n",
        "chore: x`n`nco-authored-by: Someone <a@b.test>`n",
        "chore: x`n`nGenerated with Claude Code`n",
        "chore: x`n`nWritten by an AI`n",
        "chore: x $robot`n",
        "chore: x`n`nClaude-Session: abc`n",
        "chore: x`n`nhttps://claude.ai/code/session_1`n"
    )) { Assert ((Test-Commit $bad) -ne 0) "commit-msg: must reject [$($bad.Trim())]" }
    Assert ((Test-Commit "docs(task-001): describe the Claude Code adapter and Codex review status`n") -eq 0) 'commit-msg: plain mention of a tool name accepted'

    # CRLF resilience: machine has core.autocrlf=true, so a fresh checkout must still run the hook.
    $hookText = [IO.File]::ReadAllText((Join-Path $clone '.githooks\commit-msg'))
    [IO.File]::WriteAllText((Join-Path $clone '.githooks\commit-msg'), $hookText.Replace("`n", "`r`n"), (New-Object Text.UTF8Encoding($false)))
    Assert ((Test-Commit "chore: crlf hook accepts clean message`n") -eq 0) 'commit-msg: CRLF copy accepts a clean message'
    Assert ((Test-Commit "chore: x`n`nCo-Authored-By: Claude <noreply@anthropic.com>`n") -ne 0) 'commit-msg: CRLF copy still rejects attribution'
    [IO.File]::WriteAllText((Join-Path $clone '.githooks\commit-msg'), $hookText, (New-Object Text.UTF8Encoding($false)))

    # --- Raw metadata check.
    $owner = "$($script:OwnerName)"
    $range = "HEAD~1..HEAD"
    Assert ((Get-PranataCommitProblems -Root $clone -Range $range -OwnerName (& git -C $clone config user.name) -OwnerEmail (& git -C $clone config user.email)).Count -eq 0) 'metadata: configured identity passes'
    Assert ((Get-PranataCommitProblems -Root $clone -Range $range -OwnerName 'Someone Else' -OwnerEmail 'x@y.test').Count -ge 2) 'metadata: other identity fails author and committer'

    # --- Runner with a stub command; state lives in the disposable clone.
    $stub = Join-Path $work 'claude-stub.ps1'
    @'
$prompt = [Console]::In.ReadToEnd()
Set-Content -LiteralPath (Join-Path $PWD '.pranata\stub-prompt.txt') -Value $prompt -Encoding UTF8
$log = Join-Path $PWD '.pranata\stub-calls.log'
Add-Content -LiteralPath $log -Value (($args -join ' ') + ' MODE=' + $env:PRANATA_EXECUTOR_MODE + ' TOKEN=' + [bool]$env:GH_TOKEN + ' GITCFG=' + $env:GIT_CONFIG_COUNT)
if (Test-Path (Join-Path $PWD '.pranata\stub-auth-error')) { Write-Output '{"type":"result","subtype":"success","is_error":true,"session_id":"s-auth","result":"Failed to authenticate"}'; exit 1 }
if (Test-Path (Join-Path $PWD '.pranata\stub-rate-limit')) { Write-Output '{"type":"result","is_error":true,"session_id":"s-limit","result":"usage limit reached"}'; exit 1 }
if (Test-Path (Join-Path $PWD '.pranata\stub-max-turns')) { Write-Output '{"type":"result","subtype":"error_max_turns","is_error":true,"session_id":"s-turns","num_turns":41,"terminal_reason":"max_turns","permission_denials":[{"tool_input":{"command":"echo usage limit"}}]}'; exit 1 }
if (Test-Path (Join-Path $PWD '.pranata\stub-critical')) { Start-Process -FilePath 'cmd.exe' -ArgumentList '/c ping -n 14 127.0.0.1 >nul & rem merge-verified' -NoNewWindow; Start-Sleep -Seconds 14 }
if (Test-Path (Join-Path $PWD '.pranata\stub-sleep')) { Start-Sleep -Seconds 120 }
if (Test-Path (Join-Path $PWD '.pranata\stub-raise-hold')) { Set-Content -LiteralPath (Join-Path $PWD 'late.txt') -Value x -Encoding ASCII; Set-Content -LiteralPath (Join-Path $PWD '.pranata\executor\hold.json') -Value '{"mode":"IMMEDIATE_SAFE_SHUTDOWN"}' -Encoding ASCII }
$status = '{\"task\":\"TASK-999\",\"stage\":\"IN_PROGRESS\",\"result\":\"progressed\",\"next\":\"lanjut langkah berikut\"}'
Write-Output ('{"type":"result","is_error":false,"session_id":"s-1","num_turns":1,"result":"ok\n' + $status + '"}')
'@ | Set-Content -LiteralPath $stub -Encoding ASCII

    $scriptsDir = Join-Path $repo 'scripts\executor'
    $state = Initialize-PranataStateDirs $clone
    $cp = New-PranataCheckpoint
    $cp.mode = 'AUTONOMOUS'; $cp.task_id = 'TASK-999'; $cp.stage = 'IN_PROGRESS'; $cp.branch = 'chore/x'; $cp.head_sha = (& git -C $clone rev-parse HEAD); $cp.next_action = 'mulai'
    Save-PranataCheckpoint $clone $cp

    $tokenFile = Join-Path $work 'fake-token.dpapi'
    [IO.File]::WriteAllText($tokenFile, (ConvertFrom-SecureString (ConvertTo-SecureString 'fake-test-value' -AsPlainText -Force)))
    $noToken = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', (Join-Path $work 'missing.dpapi'))
    Assert ($noToken.Code -eq 5 -and $noToken.Out -match 'D-27') 'runner: AUTONOMOUS without the restricted token refused'

    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Code -eq 0) "runner: one step exits 0 ($($r.Out))"
    $after = Get-PranataCheckpoint $clone
    Assert ($after.session_id -eq 's-1') 'runner: session id recorded in checkpoint'
    Assert ($after.next_action -eq 'lanjut langkah berikut') 'runner: next action recorded in checkpoint'
    Assert ($after.task_id -eq 'TASK-999' -and $after.stage -eq 'IN_PROGRESS') 'runner: task state survives a step'
    Assert (Test-Path -LiteralPath $state.Context) 'runner: context pack generated'
    Assert (-not (Test-Path -LiteralPath $state.RunnerLock)) 'runner: lock released'
    $calls = Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log') -Raw
    Assert ($calls -match 'dontAsk' -and $calls -match '--max-turns' -and $calls -match 'MODE=AUTONOMOUS') 'runner: restrictive flags and AUTONOMOUS env passed'
    Assert ($calls -notmatch 'bypass|skip-permissions|--bare') 'runner: no bypass flags'

    Assert ($calls -match 'TOKEN=True' -and $calls -match 'GITCFG=2') 'runner: restricted token and per-process Git credential reach the child'
    Assert ($calls -notmatch 'fake-test-value') 'runner: token value never appears in arguments'
    Assert (-not (Get-ChildItem -LiteralPath $state.Logs -Recurse -File | Select-String -SimpleMatch 'fake-test-value')) 'runner: token value never reaches a log'
    Assert ($calls -match '--setting-sources project' -and $calls -match '--strict-mcp-config') 'runner: project settings only, no MCP'
    Assert ($calls -match 'Bash\(powershell -NoProfile -File scripts/executor/stage\.ps1\*\)' -and $calls -match 'Bash\(powershell -NoProfile -File scripts/executor/merge-verified\.ps1\*\)') 'runner: stage and merge scripts allowed through Bash'
    Assert ($calls -match '--disallowedTools "?PowerShell,' -and $calls -notmatch 'PowerShell\(') 'runner: the PowerShell tool is off in headless steps'
    Assert ($calls -notmatch 'hold\.ps1|runner\.ps1|watcher\.ps1') 'runner: hold, runner and watcher scripts are not allowed in a step'
    $openRules = @((Get-PranataRunnerAllowedTools) -split ',' | Where-Object { $_ -eq 'Bash' -or $_ -match '\\' -or $_ -match '^Bash\((cd|bash|sh|pwsh|cmd|powershell \*|powershell -NoProfile -File \*|\*)' })
    Assert ($openRules.Count -eq 0) "runner: no bare Bash, shell, cd or backslash rule ($($openRules -join ' '))"
    $stepPrompt = Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-prompt.txt') -Raw
    Assert ($stepPrompt -match 'Jangan awali perintah dengan cd' -and $stepPrompt -match 'powershell -NoProfile -File scripts/executor/stage\.ps1 -Paths' -and $stepPrompt -match 'result blocked') 'runner: the step prompt names the cd rule, the one script form and stops on repeated denials'
    Assert ($stepPrompt -match 'scripts/executor/fetch-source\.ps1 -Package' -and $stepPrompt -notmatch 'dibaca dengan gh api') 'runner: the step prompt sends dependency sources to fetch-source, not to gh api'
    Assert ($calls -match 'Bash\(powershell -NoProfile -File scripts/executor/fetch-source\.ps1\*\)') 'runner: fetch-source allowed through Bash'

    # A failed step reported as subtype=success must stop the loop, not spin on the model.
    Set-Content -LiteralPath (Join-Path $clone '.pranata\stub-auth-error') -Value x -Encoding ASCII
    $before = @(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile, '-MaxSteps', '5')
    Assert ($r.Out -match 'status=error') 'runner: is_error with subtype=success is an error'
    Assert ((@(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count - $before) -eq 1) 'runner: exactly one call on a failed step, no retry loop'
    Remove-Item -LiteralPath (Join-Path $clone '.pranata\stub-auth-error')

    # The turn budget ending a step continues from the checkpoint, inside the no-progress budget.
    Set-Content -LiteralPath (Join-Path $clone '.pranata\stub-max-turns') -Value x -Encoding ASCII
    $before = @(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count
    $nextBefore = (Get-PranataCheckpoint $clone).next_action
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile, '-MaxSteps', '2')
    Assert ($r.Code -eq 0 -and $r.Out -match 'status=max_turns') 'runner: error_max_turns is reported as max_turns'
    Assert ($r.Out -match 'rate_limited=False') 'runner: a turn limit is not mistaken for a usage limit'
    Assert ($r.Out -notmatch 'without a continuable status') 'runner: a turn limit does not stop the loop'
    Assert ((@(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count - $before) -eq 2) 'runner: the next step starts after a turn limit'
    $afterTurns = Get-PranataCheckpoint $clone
    Assert ($afterTurns.stage -eq 'IN_PROGRESS' -and $afterTurns.next_action -eq $nextBefore) 'runner: a turn limit keeps the checkpoint'
    $before = @(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Out -match 'BLOCKED: no progress') 'runner: repeated turn limits without progress end in BLOCKED'
    Assert ((@(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count - $before) -eq 3) 'runner: the no-progress budget bounds turn-limit steps'
    Remove-Item -LiteralPath (Join-Path $clone '.pranata\stub-max-turns')
    $cpT = Get-PranataCheckpoint $clone; $cpT.stage = 'IN_PROGRESS'; $cpT.last_action = $null; Save-PranataCheckpoint $clone $cpT

    # IMMEDIATE hold keeps unfinished work as a local WIP commit on the Task branch.
    & git -C $clone switch --quiet -c chore/task-999-x
    $cpW = Get-PranataCheckpoint $clone; $cpW.branch = 'chore/task-999-x'; Save-PranataCheckpoint $clone $cpW
    Set-Content -LiteralPath (Join-Path $clone 'unfinished.txt') -Value x -Encoding ASCII
    $originBefore = (& git -C $origin for-each-ref | Measure-Object).Count
    [void](Invoke-Script (Join-Path $scriptsDir 'hold.ps1') @('-Mode', 'IMMEDIATE_SAFE_SHUTDOWN', '-Reason', 'uji', '-Root', $clone))
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ((& git -C $clone log -1 --format=%s) -eq 'wip(task-999): checkpoint IN_PROGRESS') 'hold: WIP commit recorded'
    Assert (-not (& git -C $clone status --porcelain)) 'hold: working tree clean after WIP commit'
    Assert ((Get-PranataCheckpoint $clone).head_sha -eq (& git -C $clone rev-parse HEAD)) 'hold: checkpoint head_sha matches the WIP commit'
    Assert ((& git -C $origin for-each-ref | Measure-Object).Count -eq $originBefore) 'hold: WIP commit not pushed'
    [void](Invoke-Script (Join-Path $scriptsDir 'release-hold.ps1') @('-Root', $clone))
    # A hold raised during the last step, with -Once: the unfinished work is still kept.
    Set-Content -LiteralPath (Join-Path $clone '.pranata\stub-raise-hold') -Value x -Encoding ASCII
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Remove-Item -LiteralPath (Join-Path $clone '.pranata\stub-raise-hold')
    Assert ((& git -C $clone log -1 --format=%s) -match '^wip\(task-999\)' -and (Test-Path -LiteralPath (Join-Path $clone 'late.txt'))) 'hold: raised during a step still gives a WIP commit'
    [void](Invoke-Script (Join-Path $scriptsDir 'release-hold.ps1') @('-Root', $clone))

    # WIP commit never lands on a branch the checkpoint does not name.
    & git -C $clone switch --quiet -c chore/task-999-other
    Set-Content -LiteralPath (Join-Path $clone 'stray.txt') -Value x -Encoding ASCII
    [void](Invoke-Script (Join-Path $scriptsDir 'hold.ps1') @('-Mode', 'IMMEDIATE_SAFE_SHUTDOWN', '-Reason', 'uji', '-Root', $clone))
    $head = (& git -C $clone rev-parse HEAD)
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Out -match 'WIP commit skipped' -and (& git -C $clone rev-parse HEAD) -eq $head) 'hold: no WIP commit on an unrelated branch'
    [void](Invoke-Script (Join-Path $scriptsDir 'release-hold.ps1') @('-Root', $clone))
    Remove-Item -LiteralPath (Join-Path $clone 'stray.txt')
    & git -C $clone switch --quiet chore/task-999-x
    $cpH = Get-PranataCheckpoint $clone; $cpH.head_sha = (& git -C $clone rev-parse HEAD); $cpH.next_action = 'lanjut langkah berikut'; Save-PranataCheckpoint $clone $cpH

    # Hold: both modes stop the runner; resume continues the same Task from the checkpoint.
    $h = Invoke-Script (Join-Path $scriptsDir 'hold.ps1') @('-Mode', 'IMMEDIATE_SAFE_SHUTDOWN', '-Reason', 'uji', '-Root', $clone)
    Assert ($h.Code -eq 0 -and (Test-Path -LiteralPath $state.Hold)) 'hold: IMMEDIATE written'
    $before = @(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Out -match 'HOLD IMMEDIATE_SAFE_SHUTDOWN') 'hold: IMMEDIATE stops the runner'
    Assert (@(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count -eq $before) 'hold: no model step during IMMEDIATE'
    $w = Invoke-Script (Join-Path $scriptsDir 'watcher.ps1') @('-Once', '-Root', $clone)
    Assert ($w.Out -match 'hold active') 'hold: watcher does not restart during hold'
    $savedMode = $env:PRANATA_EXECUTOR_MODE
    $env:PRANATA_EXECUTOR_MODE = 'AUTONOMOUS'
    $rel = Invoke-Script (Join-Path $scriptsDir 'release-hold.ps1') @('-Root', $clone)
    $env:PRANATA_EXECUTOR_MODE = $savedMode
    Assert (($rel.Code -ne 0) -and (Test-Path -LiteralPath $state.Hold)) 'hold: AUTONOMOUS step cannot release'
    $rel = Invoke-Script (Join-Path $scriptsDir 'release-hold.ps1') @('-Root', $clone)
    Assert (($rel.Code -eq 0) -and -not (Test-Path -LiteralPath $state.Hold)) 'hold: Owner release works'
    $resumed = Get-PranataCheckpoint $clone
    Assert ($resumed.task_id -eq 'TASK-999' -and $resumed.next_action -eq 'lanjut langkah berikut') 'resume: Task status unchanged after hold'
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Code -eq 0 -and $r.Out -match 'status=progressed') 'resume: runner continues the Task after release'

    $h = Invoke-Script (Join-Path $scriptsDir 'hold.ps1') @('-Mode', 'SHUTDOWN_AFTER_CURRENT_TASK', '-Reason', 'uji', '-Root', $clone)
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Out -match 'status=progressed') 'hold: SHUTDOWN_AFTER_CURRENT_TASK lets the active Task continue'
    $doneCp = Get-PranataCheckpoint $clone; $doneCp.stage = 'DONE'; Save-PranataCheckpoint $clone $doneCp
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Out -match 'SHUTDOWN_AFTER_CURRENT_TASK: Task done, stopping') 'hold: SHUTDOWN_AFTER_CURRENT_TASK stops once done'
    [void](Invoke-Script (Join-Path $scriptsDir 'release-hold.ps1') @('-Root', $clone))
    $doneCp = Get-PranataCheckpoint $clone; $doneCp.stage = 'IN_PROGRESS'; Save-PranataCheckpoint $clone $doneCp

    # Runner exclusivity with a live foreign holder (this process), then a stale lock from a dead PID.
    [void](Enter-PranataLock $state.RunnerLock)
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Code -eq 3 -and $r.Out -match 'REFUSED') 'runner: second instance refused'
    Exit-PranataLock $state.RunnerLock
    Set-Content -LiteralPath $state.RunnerLock -Value '{"pid":999999,"started":"2000-01-01T00:00:00.0000000Z"}' -Encoding ASCII
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Code -eq 0) 'runner: stale lock of a dead PID recovered'

    # Usage limit: recorded, wait set, no busy loop (-Once returns immediately after).
    Set-Content -LiteralPath (Join-Path $clone '.pranata\stub-rate-limit') -Value x -Encoding ASCII
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Out -match 'rate_limited=True') 'runner: usage limit detected'
    $limited = Get-PranataCheckpoint $clone
    Assert ([bool]$limited.wait_until) 'runner: wait_until set after usage limit'
    $before = @(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert (@(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count -eq $before) 'runner: no model call while waiting for reset'
    Remove-Item -LiteralPath (Join-Path $clone '.pranata\stub-rate-limit')
    $cp2 = Get-PranataCheckpoint $clone; $cp2.wait_until = $null; Save-PranataCheckpoint $clone $cp2

    # Runner refuses model steps outside AUTONOMOUS.
    $cp3 = Get-PranataCheckpoint $clone; $cp3.mode = 'SUPERVISED'; Save-PranataCheckpoint $clone $cp3
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Out -match 'starts no model step') 'runner: SUPERVISED checkpoint starts no model step'

    $watcherText = Get-Content -LiteralPath (Join-Path $scriptsDir 'watcher.ps1') -Raw
    Assert ($watcherText -match 'New-ScheduledTaskSettingsSet [^\r\n]*-Priority 4') 'watcher: registered at normal priority'
    # Watcher: single instance, evidence based, -NoStart keeps the test from spawning anything.
    $w = Invoke-Script (Join-Path $scriptsDir 'watcher.ps1') @('-Once', '-NoStart', '-Root', $clone)
    Assert ($w.Code -eq 0 -and $w.Out -match 'runner=False') 'watcher: reports evidence'
    [void](Enter-PranataLock $state.WatcherLock)
    $w = Invoke-Script (Join-Path $scriptsDir 'watcher.ps1') @('-Once', '-NoStart', '-Root', $clone)
    Assert ($w.Code -eq 3 -and $w.Out -match 'REFUSED') 'watcher: second instance refused'
    Exit-PranataLock $state.WatcherLock
    (Get-Item -LiteralPath $state.Heartbeat -ErrorAction SilentlyContinue) | Out-Null
    [IO.File]::WriteAllText($state.Heartbeat, 'x')
    (Get-Item -LiteralPath $state.Heartbeat).LastWriteTime = (Get-Date).AddMinutes(-60)
    $w = Invoke-Script (Join-Path $scriptsDir 'watcher.ps1') @('-Once', '-NoStart', '-Root', $clone)
    Assert ($w.Out -match 'idle=True') 'watcher: stale heartbeat with no activity is idle'
    [IO.File]::WriteAllText($state.Heartbeat, 'x')
    $w = Invoke-Script (Join-Path $scriptsDir 'watcher.ps1') @('-Once', '-NoStart', '-Root', $clone)
    Assert ($w.Out -match 'idle=False') 'watcher: fresh heartbeat is not idle'
    $cp4 = Get-PranataCheckpoint $clone; $cp4.mode = 'AUTONOMOUS'; Save-PranataCheckpoint $clone $cp4
    (Get-Item -LiteralPath $state.Heartbeat).LastWriteTime = (Get-Date).AddMinutes(-60)
    $w = Invoke-Script (Join-Path $scriptsDir 'watcher.ps1') @('-Once', '-NoStart', '-Root', $clone)
    Assert ($w.Out -match 'runner is down; -NoStart given') 'watcher: detects a dead runner with eligible work'

    # Stop guard: blocks once when the checkpoint lags, never when stop_hook_active.
    $stop = Join-Path $repo '.claude\hooks\stop-guard.ps1'
    function Invoke-Stop([string]$Json) {
        $saved = $env:CLAUDE_PROJECT_DIR; $env:CLAUDE_PROJECT_DIR = $clone
        try { return ($Json | & powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -File $stop | Out-String) } finally { $env:CLAUDE_PROJECT_DIR = $saved }
    }
    $cp5 = Get-PranataCheckpoint $clone; $cp5.head_sha = '0000000'; Save-PranataCheckpoint $clone $cp5
    Assert (-not (Invoke-Stop '{"stop_hook_active":true,"session_id":"s-a"}').Trim()) 'stop: stop_hook_active never blocks'
    Assert ((Invoke-Stop '{"stop_hook_active":false,"session_id":"s-a"}') -match '"decision":\s*"block"') 'stop: lagging checkpoint blocks'
    Assert (-not (Invoke-Stop '{"stop_hook_active":false,"session_id":"s-a"}').Trim()) 'stop: same session and HEAD never blocks twice, even without stop_hook_active'
    Assert ((Invoke-Stop '{"stop_hook_active":false,"session_id":"s-b"}') -match '"decision":\s*"block"') 'stop: a new session is checked again'
    $cp5.head_sha = (& git -C $clone rev-parse HEAD); Save-PranataCheckpoint $clone $cp5
    Assert (-not (Invoke-Stop '{"stop_hook_active":false}').Trim()) 'stop: current checkpoint passes'

    # Runner timeout: the whole step tree is stopped, the lock released, and no second step starts.
    $cpT = Get-PranataCheckpoint $clone; $cpT.mode = 'AUTONOMOUS'; $cpT.stage = 'IN_PROGRESS'; $cpT.wait_until = $null; Save-PranataCheckpoint $clone $cpT
    Set-Content -LiteralPath (Join-Path $clone '.pranata\stub-sleep') -Value x -Encoding ASCII
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile, '-StepTimeoutMinutes', '0.1')
    Assert ($r.Out -match 'status=timeout' -and $r.Out -match 'stopping PID') 'runner: over-limit step stopped and reported'
    Assert (-not (Test-Path -LiteralPath $state.RunnerLock) -and -not (Test-Path -LiteralPath $state.StepLock)) 'runner: locks released after timeout'
    Assert (@(Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -match 'claude-stub' }).Count -eq 0) 'runner: step process tree is gone after timeout'
    Remove-Item -LiteralPath (Join-Path $clone '.pranata\stub-sleep')

    # A step stuck inside commit, push or merge: left alone, hold raised, and no WIP commit while it lives.
    $cpC = Get-PranataCheckpoint $clone; $cpC.mode = 'AUTONOMOUS'; $cpC.stage = 'IN_PROGRESS'; $cpC.wait_until = $null; $cpC.branch = (& git -C $clone rev-parse --abbrev-ref HEAD); Save-PranataCheckpoint $clone $cpC
    Set-Content -LiteralPath (Join-Path $clone '.pranata\stub-critical') -Value x -Encoding ASCII
    Set-Content -LiteralPath (Join-Path $clone 'dirty-during-step.txt') -Value x -Encoding ASCII
    $headBefore = (& git -C $clone rev-parse HEAD)
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile, '-StepTimeoutMinutes', '0.05')
    Assert ($r.Out -match 'HOLD written') "runner: stuck critical step raises a hold ($($r.Out))"
    Assert ($r.Out -match 'WIP commit skipped: the step is still alive' -and (& git -C $clone rev-parse HEAD) -eq $headBefore) 'runner: no WIP commit while the step lives'
    # The harness returns only once the step closed its output, so the live check above is the proof; the file must remain.
    Assert (Test-Path -LiteralPath $state.StepLock) 'runner: step.lock is left in place for a stuck step'
    Remove-Item -LiteralPath (Join-Path $clone '.pranata\stub-critical')
    Remove-Item -LiteralPath (Join-Path $clone 'dirty-during-step.txt')
    Remove-Item -LiteralPath $state.StepLock -ErrorAction SilentlyContinue
    [void](Invoke-Script (Join-Path $scriptsDir 'release-hold.ps1') @('-Root', $clone))

    # A live step outliving a dead runner blocks both a new runner and the watcher. This process stands in for the step.
    Write-PranataFileAtomic $state.StepLock (New-PranataProcessStamp $PID)
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Code -eq 3 -and $r.Out -match 'previous step') 'runner: refuses while a previous step is alive'
    (Get-Item -LiteralPath $state.Heartbeat).LastWriteTime = (Get-Date).AddMinutes(-60)
    $w = Invoke-Script (Join-Path $scriptsDir 'watcher.ps1') @('-Once', '-NoStart', '-Root', $clone)
    Assert ($w.Out -match 'step=True' -and $w.Out -match 'idle=False' -and $w.Out -notmatch 'runner is down') 'watcher: a live step means no restart, even with a stale heartbeat'
    Remove-Item -LiteralPath $state.StepLock

    # No progress: three identical steps stop the loop instead of burning usage.
    $cpN = Get-PranataCheckpoint $clone; $cpN.stage = 'IN_PROGRESS'; $cpN.next_action = 'lanjut langkah berikut'; Save-PranataCheckpoint $clone $cpN
    $before = @(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Out -match 'BLOCKED: no progress') 'runner: no progress stops the loop'
    Assert ((@(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count - $before) -eq 3) 'runner: exactly three calls before giving up'
    Assert ((Get-PranataCheckpoint $clone).stage -eq 'BLOCKED') 'runner: no progress recorded as BLOCKED'

    # A Task already DONE: steps that add nothing stop quietly and the stage stays DONE.
    $cpD = Get-PranataCheckpoint $clone; $cpD.stage = 'DONE'; $cpD.next_action = 'lanjut langkah berikut'; Save-PranataCheckpoint $clone $cpD
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    Assert ($r.Out -match 'Task is DONE' -and (Get-PranataCheckpoint $clone).stage -eq 'DONE') 'runner: DONE is not rewritten to BLOCKED'

    # A step that keeps rewriting next_action has a finite budget.
    $talk = Join-Path $work 'claude-talk.ps1'
    @(
        '$null = [Console]::In.ReadToEnd()',
        '$n = [guid]::NewGuid().ToString("N")',
        'Write-Output (''{"type":"result","is_error":false,"session_id":"s-t","result":"ok\n{\"task\":\"TASK-999\",\"stage\":\"IN_PROGRESS\",\"result\":\"progressed\",\"next\":\"'' + $n + ''\"}"}'')'
    ) | Set-Content -LiteralPath $talk -Encoding ASCII
    $cpK = Get-PranataCheckpoint $clone; $cpK.stage = 'IN_PROGRESS'; Save-PranataCheckpoint $clone $cpK
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $talk, '-TokenFile', $tokenFile, '-MaxUnmovedSteps', '4')
    Assert ($r.Out -match 'BLOCKED: no progress after 4 steps') 'runner: endlessly rewriting next_action is bounded'

    # A healthy step that merely mentions a limit is not a usage limit.
    $mention = @'
$null = [Console]::In.ReadToEnd()
$status = '{\"task\":\"TASK-999\",\"stage\":\"IN_PROGRESS\",\"result\":\"blocked\",\"next\":\"baca rate limit\"}'
Write-Output ('{"type":"result","is_error":false,"session_id":"s-m","result":"rate limit docs\n' + $status + '"}')
'@
    $stub2 = Join-Path $work 'claude-mention.ps1'
    $mention | Set-Content -LiteralPath $stub2 -Encoding ASCII
    $cpM = Get-PranataCheckpoint $clone; $cpM.stage = 'IN_PROGRESS'; Save-PranataCheckpoint $clone $cpM
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub2, '-TokenFile', $tokenFile)
    Assert ($r.Out -match 'rate_limited=False' -and (Get-PranataCheckpoint $clone).stage -eq 'BLOCKED') 'runner: mention of a limit keeps the real status'

    # SHUTDOWN_AFTER_CURRENT_TASK: a dead runner is still restarted so the Task can finish.
    $cpS = Get-PranataCheckpoint $clone; $cpS.stage = 'IN_PROGRESS'; $cpS.wait_until = $null; Save-PranataCheckpoint $clone $cpS
    [void](Invoke-Script (Join-Path $scriptsDir 'hold.ps1') @('-Mode', 'SHUTDOWN_AFTER_CURRENT_TASK', '-Reason', 'uji', '-Root', $clone))
    (Get-Item -LiteralPath $state.Heartbeat).LastWriteTime = (Get-Date).AddMinutes(-60)
    $w = Invoke-Script (Join-Path $scriptsDir 'watcher.ps1') @('-Once', '-NoStart', '-Root', $clone)
    Assert ($w.Out -match 'runner is down; -NoStart given') 'watcher: restarts during SHUTDOWN_AFTER_CURRENT_TASK while the Task is unfinished'
    [void](Invoke-Script (Join-Path $scriptsDir 'release-hold.ps1') @('-Root', $clone))

    # --- merge-verified.ps1 end to end: real Git against a local origin, gh replaced by a stub on PATH for child processes only.
    $originM = Join-Path $work 'origin-m.git'
    $cloneM = Join-Path $work 'clone-m'
    & git init --bare --quiet --initial-branch=main $originM
    & git init --quiet --initial-branch=main $cloneM
    & git -C $cloneM remote add origin $originM
    Add-Content -LiteralPath (Join-Path $cloneM '.git\info\exclude') -Value '/.pranata/'
    [void](New-Item -ItemType Directory -Path (Join-Path $cloneM 'docs\00-governance') -Force)
    [void](New-Item -ItemType Directory -Path (Join-Path $cloneM 'docs\11-tasks') -Force)
    Set-Content -LiteralPath (Join-Path $cloneM 'docs\00-governance\APPROVAL_RECORDS.md') -Value "## AUTH-013`n- State: **ACTIVE** from x`n## AUTH-012" -Encoding ASCII
    Set-Content -LiteralPath (Join-Path $cloneM 'docs\11-tasks\TASK-010.md') -Value "STATUS: READY`nRISK CLASS: HIGH" -Encoding ASCII
    & git -c core.autocrlf=false -C $cloneM add -A 2>$null
    & git -c core.autocrlf=false -C $cloneM commit --quiet -m 'chore: seed'
    & git -C $cloneM push --quiet origin main 2>$null
    & git -C $cloneM switch --quiet -c chore/task-010-x
    Set-Content -LiteralPath (Join-Path $cloneM 'work.txt') -Value x -Encoding ASCII
    & git -c core.autocrlf=false -C $cloneM add -A 2>$null
    & git -c core.autocrlf=false -C $cloneM commit --quiet -m 'chore(task-010): work'
    $shaM = (& git -C $cloneM rev-parse HEAD)
    $mainM = (& git -C $originM rev-parse refs/heads/main)

    $stubDir = Join-Path $work 'stubbin'
    [void](New-Item -ItemType Directory -Path $stubDir)
    Set-Content -LiteralPath (Join-Path $stubDir 'gh.cmd') -Value '@powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0gh-stub.ps1" %*' -Encoding ASCII
    Set-Content -LiteralPath (Join-Path $stubDir 'gh-stub.ps1') -Encoding ASCII -Value @'
$dir = Split-Path -Parent $MyInvocation.MyCommand.Path
if ($args[0] -eq 'api') { Get-Content -LiteralPath (Join-Path $dir 'checks.json') -Raw; exit 0 }
if ($args -contains '--jq') { Get-Content -LiteralPath (Join-Path $dir 'after.txt') -Raw; exit 0 }
if ($args[1] -eq 'list') { Get-Content -LiteralPath (Join-Path $dir 'merged.json') -Raw; exit 0 }
if ($args -contains 'commits') { Get-Content -LiteralPath (Join-Path $dir ('commits-' + $args[2] + '.json')) -Raw; exit 0 }
Get-Content -LiteralPath (Join-Path $dir 'pr.json') -Raw
'@
    Set-Content -LiteralPath (Join-Path $stubDir 'merged.json') -Encoding ASCII -Value '[]'
    function Set-GhFacts([string]$Ci, [string]$Branch = 'chore/task-010-x', [string]$Body = 'Ringkasan') {
        Set-Content -LiteralPath (Join-Path $stubDir 'checks.json') -Encoding ASCII -Value ('{"check_runs":[{"name":"ci-gate","conclusion":"' + $Ci + '"}]}')
        Set-Content -LiteralPath (Join-Path $stubDir 'after.txt') -Encoding ASCII -Value 'MERGED'
        Set-Content -LiteralPath (Join-Path $stubDir 'pr.json') -Encoding ASCII -Value ('{"state":"OPEN","baseRefName":"main","headRefName":"' + $Branch + '","headRefOid":"' + $shaM + '","isDraft":false,"mergeable":"MERGEABLE","title":"TASK-010","body":"' + $Body + '"}')
    }
    $stateM = Initialize-PranataStateDirs $cloneM
    $reviewFile = Join-Path $stateM.Dir "review-$shaM.json"
    function Set-Review([bool]$Independent = $true) {
        Set-Content -LiteralPath $reviewFile -Encoding ASCII -Value ('{"sha":"' + $shaM + '","status":"PASS","open_blocking_findings":0,"independent_review_done":' + $Independent.ToString().ToLowerInvariant() + ',"substitute_review_done":false,"codex_status":"COMPLETED_NO_FINDINGS"}')
    }
    $merge = Join-Path $scriptsDir 'merge-verified.ps1'
    $savedPath = $env:PATH
    $env:PATH = "$stubDir;$env:PATH"
    try {
        $mergeArgs = @('-Pr', '10', '-VerifiedSha', $shaM, '-Root', $cloneM)
        Set-GhFacts 'success'; Set-Review
        $m = Invoke-Script $merge ($mergeArgs + '-DryRun')
        Assert ($m.Code -eq 0 -and $m.Out -match 'Gate PASS: TASK-010 \(HIGH\)') "merge: all gates pass in a dry run ($($m.Out))"
        Set-GhFacts 'failure'
        $m = Invoke-Script $merge $mergeArgs
        Assert ($m.Code -ne 0 -and $m.Out -match '8\.1\.2') 'merge: failing ci-gate refused'
        Set-GhFacts 'success'; Remove-Item -LiteralPath $reviewFile
        $m = Invoke-Script $merge $mergeArgs
        Assert ($m.Code -ne 0 -and $m.Out -match '8\.1\.3') 'merge: missing review refused'
        Set-Review $false
        $m = Invoke-Script $merge $mergeArgs
        Assert ($m.Code -ne 0 -and $m.Out -match 'independent review missing') 'merge: risk read from the contract requires the independent review'
        Set-Review
        Set-GhFacts 'success'
        $m = Invoke-Script $merge ($mergeArgs + '-Bootstrap')
        Assert ($m.Code -ne 0 -and $m.Out -match 'bootstrap: not allowed for TASK-010') 'merge: bootstrap refused for a later Task'
        Set-GhFacts 'success' 'chore/no-task-here'
        $m = Invoke-Script $merge $mergeArgs
        Assert ($m.Code -ne 0 -and $m.Out -match 'does not name a Task') 'merge: branch without a Task refused'
        Set-GhFacts 'success' 'chore/task-010-x' 'Generated with Claude Code'
        $m = Invoke-Script $merge $mergeArgs
        Assert ($m.Code -ne 0 -and $m.Out -match 'PR title or body') 'merge: PR body attribution refused'
        Set-GhFacts 'success' 'docs/reconcile-task-010'
        $m = Invoke-Script $merge $mergeArgs
        Assert ($m.Code -ne 0 -and $m.Out -match '8\.3') 'merge: reconciliation PR touching code refused'
        Set-GhFacts 'success'
        Set-Content -LiteralPath $stateM.Hold -Encoding ASCII -Value '{"mode":"IMMEDIATE_SAFE_SHUTDOWN"}'
        $m = Invoke-Script $merge $mergeArgs
        Assert ($m.Code -ne 0 -and $m.Out -match 'IMMEDIATE_SAFE_SHUTDOWN') 'merge: hold refuses the merge'
        Remove-Item -LiteralPath $stateM.Hold
        Assert ((& git -C $originM rev-parse refs/heads/main) -eq $mainM) 'merge: every refusal left main unchanged'

        $m = Invoke-Script $merge $mergeArgs
        Assert ($m.Code -eq 0 -and $m.Out -match 'MERGED: remote main') "merge: fast-forward performed ($($m.Out))"
        Assert ((& git -C $originM rev-parse refs/heads/main) -eq $shaM) 'merge: remote main equals the verified SHA'
    } finally { $env:PATH = $savedPath }

    # --- merge-verified.ps1 on the post-DONE remediation lane (8.5): real Git history and blobs, gh stubbed.
    $originR = Join-Path $work 'origin-r.git'
    $cloneR = Join-Path $work 'clone-r'
    & git init --bare --quiet --initial-branch=main $originR
    & git init --quiet --initial-branch=main $cloneR
    & git -C $cloneR remote add origin $originR
    Add-Content -LiteralPath (Join-Path $cloneR '.git\info\exclude') -Value '/.pranata/'
    function Save-RFile([string]$Path, [string]$Text) {
        $full = Join-Path $cloneR $Path
        [void](New-Item -ItemType Directory -Path (Split-Path -Parent $full) -Force)
        [IO.File]::WriteAllText($full, $Text)
    }
    function New-RCommit([string]$Message) {
        & git -c core.autocrlf=false -C $cloneR add -A 2>$null
        & git -c core.autocrlf=false -C $cloneR commit --quiet -m $Message
        return (& git -C $cloneR rev-parse HEAD)
    }
    $authR = "## AUTH-013`n- State: **ACTIVE** from x`n`n"
    Save-RFile 'docs/00-governance/APPROVAL_RECORDS.md' $authR
    Save-RFile 'docs/11-tasks/TASK-001.md' "STATUS: DONE`nRISK CLASS: HIGH`n"
    Save-RFile 'docs/11-tasks/TASK-015.md' "STATUS: DONE`nRISK CLASS: HIGH`n"
    Save-RFile 'tests/a.txt' "old`n"
    $seedR = New-RCommit 'chore: seed'
    & git -c core.autocrlf=false -C $cloneR switch --quiet -c src
    Save-RFile 'tests/a.txt' "new`n"
    $srcHeadR = New-RCommit 'test(task-015): reviewed source'
    & git -c core.autocrlf=false -C $cloneR switch --quiet main
    function Save-RRecords([string]$State) {
        $block = @("REMEDIATION: REM-001", "STATE: $State", 'MODE: SUPERVISED', 'ORDER: 0, 1',
            'ENTRY 0 | TASK-001 | fix/task-001-post-done-remediation-gate | PR: new | SOURCE: none | EQUIVALENCE: none | PATHS: scripts/executor/lib/merge-gate.ps1 | PURPOSE: gate',
            "ENTRY 1 | TASK-015 | fix/task-015-test-integrity | PR: #53 | SOURCE: $seedR..$srcHeadR | EQUIVALENCE: IDENTICAL or DELTA | PATHS: tests/a.txt | PURPOSE: tests")
        Save-RFile 'docs/00-governance/APPROVAL_RECORDS.md' ($authR + "## REM-001`n`n" + '```text' + "`n" + ($block -join "`n") + "`n" + '```' + "`n")
    }
    Save-RRecords 'PROPOSED'
    [void](New-RCommit 'docs: propose REM-001')
    Save-RRecords 'ACTIVE'
    $activationR = New-RCommit 'docs: activate REM-001'
    & git -C $cloneR push --quiet origin main 2>$null
    & git -c core.autocrlf=false -C $cloneR switch --quiet -c fix/task-001-post-done-remediation-gate
    Save-RFile 'scripts/executor/lib/merge-gate.ps1' "gate`n"
    $e0 = New-RCommit 'fix(task-001): remediation gate'

    $stateR = Initialize-PranataStateDirs $cloneR
    function Set-RCheckpoint([string]$Task, $Remediation) {
        $cp = New-PranataCheckpoint
        $cp.task_id = $Task
        if ($null -ne $Remediation) { $cp['remediation'] = $Remediation }
        Save-PranataCheckpoint $cloneR $cp
    }
    function Set-RReview([string]$Sha, [string]$Extra = '') {
        Set-Content -LiteralPath (Join-Path $stateR.Dir "review-$Sha.json") -Encoding ASCII -Value ('{"sha":"' + $Sha + '","status":"PASS","open_blocking_findings":0,"independent_review_done":true,"substitute_review_done":false,"codex_status":"COMPLETED_NO_FINDINGS"' + $Extra + '}')
    }
    function Set-RGhFacts([string]$Head, [string]$Branch, [string]$Body) {
        Set-Content -LiteralPath (Join-Path $stubDir 'checks.json') -Encoding ASCII -Value '{"check_runs":[{"name":"ci-gate","conclusion":"success"}]}'
        Set-Content -LiteralPath (Join-Path $stubDir 'after.txt') -Encoding ASCII -Value 'MERGED'
        Set-Content -LiteralPath (Join-Path $stubDir 'pr.json') -Encoding ASCII -Value ('{"state":"OPEN","baseRefName":"main","headRefName":"' + $Branch + '","headRefOid":"' + $Head + '","isDraft":false,"mergeable":"MERGEABLE","title":"Remediasi","body":"' + $Body + '"}')
    }
    # A PR merged before activation, with the right branch and line, must never count (the #49 and #51 situation).
    $historical = '{"number":9,"headRefName":"fix/task-001-post-done-remediation-gate","headRefOid":"' + $seedR + '","body":"Remediation: REM-001 entry 0"}'
    Set-Content -LiteralPath (Join-Path $stubDir 'merged.json') -Encoding ASCII -Value ('[' + $historical + ']')
    Set-Content -LiteralPath (Join-Path $stubDir 'commits-9.json') -Encoding ASCII -Value ('{"commits":[{"oid":"' + $seedR + '"}]}')

    $env:PATH = "$stubDir;$env:PATH"
    try {
        $argsR = @('-Pr', '10', '-VerifiedSha', $e0, '-Root', $cloneR)
        Set-RCheckpoint 'TASK-001' 'REM-001/0'; Set-RReview $e0
        Set-RGhFacts $e0 'fix/task-001-post-done-remediation-gate' 'Ringkasan\nRemediation: REM-001 entry 0'
        $m = Invoke-Script $merge ($argsR + '-DryRun')
        Assert ($m.Code -eq 0 -and $m.Out -match 'Remediation lane: REM-001 entry 0' -and $m.Out -match 'Gate PASS: TASK-001 \(HIGH\) via REM-001 entry 0') "remediation e2e: entry 0 passes in a dry run, the pre-activation PR not counted ($($m.Out))"

        Set-RGhFacts $e0 'fix/task-001-post-done-remediation-gate' 'Ringkasan'
        $m = Invoke-Script $merge $argsR
        Assert ($m.Code -ne 0 -and $m.Out -match 'exactly one line' -and $m.Out -match "status on main is 'DONE'") "remediation e2e: a PR body without the line keeps the DONE refusal ($($m.Out))"
        Set-RGhFacts $e0 'fix/task-001-post-done-remediation-gate' 'Ringkasan\nRemediation: REM-001 entry 0'
        Set-RCheckpoint 'TASK-001' 'REM-001/1'
        $m = Invoke-Script $merge $argsR
        Assert ($m.Code -ne 0 -and $m.Out -match "REM-001 entry 0: 8\.5\.3 checkpoint remediation is 'REM-001/1'") "remediation e2e: a checkpoint for another entry refused ($($m.Out))"
        Set-RCheckpoint 'TASK-001' $null
        $m = Invoke-Script $merge $argsR
        Assert ($m.Code -ne 0 -and $m.Out -match "checkpoint remediation is ''") 'remediation e2e: a checkpoint without remediation refused'
        Set-RCheckpoint 'TASK-015' 'REM-001/0'
        $m = Invoke-Script $merge $argsR
        Assert ($m.Code -ne 0 -and $m.Out -match 'checkpoint names TASK-015' -and $m.Out -match "checkpoint task_id is 'TASK-015'") 'remediation e2e: a checkpoint for another Task refused'
        Set-RCheckpoint 'TASK-001' 'REM-001/0'
        $savedModeR = $env:PRANATA_EXECUTOR_MODE
        $env:PRANATA_EXECUTOR_MODE = 'AUTONOMOUS'
        $m = Invoke-Script $merge $argsR
        $env:PRANATA_EXECUTOR_MODE = $savedModeR
        Assert ($m.Code -ne 0 -and $m.Out -match 'only in SUPERVISED mode' -and $m.Out -match '8\.1\.6') 'remediation e2e: AUTONOMOUS refused'
        $m = Invoke-Script $merge ($argsR + '-Bootstrap')
        Assert ($m.Code -ne 0 -and $m.Out -match 'together with -Bootstrap') 'remediation e2e: -Bootstrap refused'
        Set-Content -LiteralPath (Join-Path $stubDir 'checks.json') -Encoding ASCII -Value '{"check_runs":[{"name":"ci-gate","conclusion":"failure"}]}'
        $m = Invoke-Script $merge $argsR
        Assert ($m.Code -ne 0 -and $m.Out -match '8\.1\.2' -and $m.Out -notmatch "status on main is 'DONE'") 'remediation e2e: a satisfied lane does not hide a failing ci-gate'
        Set-RGhFacts $e0 'fix/task-001-post-done-remediation-gate' 'Ringkasan\nRemediation: REM-001 entry 0'
        Assert ((& git -C $originR rev-parse refs/heads/main) -eq $activationR) 'remediation e2e: every refusal left main unchanged'
        $m = Invoke-Script $merge $argsR
        Assert ($m.Code -eq 0 -and $m.Out -match 'MERGED: remote main' -and (& git -C $originR rev-parse refs/heads/main) -eq $e0) "remediation e2e: entry 0 merged by fast-forward ($($m.Out))"

        # Entry 1 before entry 0 is recorded as published: refused.
        & git -c core.autocrlf=false -C $cloneR switch --quiet -c fix/task-015-test-integrity-r2 $e0
        & git -c core.autocrlf=false -C $cloneR cherry-pick $srcHeadR 2>$null | Out-Null
        $e1 = (& git -C $cloneR rev-parse HEAD)
        $argsR1 = @('-Pr', '11', '-VerifiedSha', $e1, '-Root', $cloneR)
        Set-RCheckpoint 'TASK-015' 'REM-001/1'; Set-RReview $e1 (',"source_sha":"' + $srcHeadR + '","equivalence":"IDENTICAL"')
        Set-RGhFacts $e1 'fix/task-015-test-integrity-r2' 'Ringkasan\nRemediation: REM-001 entry 1'
        $m = Invoke-Script $merge ($argsR1 + '-DryRun')
        Assert ($m.Code -ne 0 -and $m.Out -match 'entry 0 is not published yet') "remediation e2e: entry 1 before the publication of entry 0 refused ($($m.Out))"
        $published0 = '{"number":10,"headRefName":"fix/task-001-post-done-remediation-gate","headRefOid":"' + $e0 + '","body":"Ringkasan\nRemediation: REM-001 entry 0"}'
        Set-Content -LiteralPath (Join-Path $stubDir 'merged.json') -Encoding ASCII -Value ('[' + $historical + ',' + $published0 + ']')
        Set-Content -LiteralPath (Join-Path $stubDir 'commits-10.json') -Encoding ASCII -Value ('{"commits":[{"oid":"' + $e0 + '"}]}')
        $m = Invoke-Script $merge ($argsR1 + '-DryRun')
        Assert ($m.Code -eq 0 -and $m.Out -match 'Gate PASS: TASK-015 \(HIGH\) via REM-001 entry 1') "remediation e2e: entry 1 IDENTICAL to its source passes after entry 0 ($($m.Out))"
        Set-RReview $e1 (',"source_sha":"' + $e0 + '","equivalence":"IDENTICAL"')
        $m = Invoke-Script $merge ($argsR1 + '-DryRun')
        Assert ($m.Code -ne 0 -and $m.Out -match 'source_sha is') 'remediation e2e: a disposition for another source refused'

        # The same entry 0 again, on a successor branch: single use.
        & git -c core.autocrlf=false -C $cloneR switch --quiet -c fix/task-001-post-done-remediation-gate-r2 $e0
        Save-RFile 'scripts/executor/lib/merge-gate.ps1' "gate again`n"
        $e0b = New-RCommit 'fix(task-001): remediation gate again'
        Set-RCheckpoint 'TASK-001' 'REM-001/0'; Set-RReview $e0b
        Set-RGhFacts $e0b 'fix/task-001-post-done-remediation-gate-r2' 'Ringkasan\nRemediation: REM-001 entry 0'
        $m = Invoke-Script $merge @('-Pr', '12', '-VerifiedSha', $e0b, '-Root', $cloneR, '-DryRun')
        Assert ($m.Code -ne 0 -and $m.Out -match 'entry 0 is already published \(#10\)') "remediation e2e: a used entry refused ($($m.Out))"

        # A change on top of the source: IDENTICAL refused, DELTA accepted only with the independently computed digest.
        & git -c core.autocrlf=false -C $cloneR switch --quiet fix/task-015-test-integrity-r2
        Save-RFile 'tests/a.txt' "new`nmore`n"
        $e1d = New-RCommit 'test(task-015): one more line'
        $argsR1d = @('-Pr', '11', '-VerifiedSha', $e1d, '-Root', $cloneR, '-DryRun')
        Set-RCheckpoint 'TASK-015' 'REM-001/1'
        Set-RGhFacts $e1d 'fix/task-015-test-integrity-r2' 'Ringkasan\nRemediation: REM-001 entry 1'
        Set-RReview $e1d (',"source_sha":"' + $srcHeadR + '","equivalence":"IDENTICAL"')
        $m = Invoke-Script $merge $argsR1d
        Assert ($m.Code -ne 0 -and $m.Out -match 'IDENTICAL: tests/a.txt at PR HEAD') 'remediation e2e: IDENTICAL with another blob refused'
        # Raw bytes through cmd.exe, outside PowerShell and outside the script under test.
        $diffFile = Join-Path $work 'delta.diff'
        & cmd.exe /c "git -C `"$cloneR`" diff --no-color --no-ext-diff --no-textconv --no-renames --full-index --binary --diff-algorithm=myers --indent-heuristic -U3 --inter-hunk-context=0 --no-relative --src-prefix=a/ --dst-prefix=b/ $srcHeadR $e1d -- tests/a.txt > `"$diffFile`""
        $digest = (Get-FileHash -LiteralPath $diffFile -Algorithm SHA256).Hash.ToLowerInvariant()
        Assert ((Get-Item -LiteralPath $diffFile).Length -gt 0) 'remediation e2e: the independent diff is not empty'
        Set-RReview $e1d (',"source_sha":"' + $srcHeadR + '","equivalence":"DELTA","delta_sha256":"' + $digest + '","delta_verified":true')
        $m = Invoke-Script $merge $argsR1d
        Assert ($m.Code -eq 0 -and $m.Out -match 'via REM-001 entry 1') "remediation e2e: DELTA with the recomputed digest passes ($($m.Out))"
        Set-RReview $e1d (',"source_sha":"' + $srcHeadR + '","equivalence":"DELTA","delta_sha256":"' + ('0' * 64) + '","delta_verified":true')
        $m = Invoke-Script $merge $argsR1d
        Assert ($m.Code -ne 0 -and $m.Out -match "DELTA: SHA-256 recorded 0{64}, recomputed $digest") 'remediation e2e: DELTA with another digest refused'
        Assert ((& git -C $originR rev-parse refs/heads/main) -eq $e0) 'remediation e2e: dry runs and refusals left main at entry 0'
    } finally { $env:PATH = $savedPath }
    # GOV-09 on this machine: a CRLF working copy of an LF blob must not rewrite the blob.
    $eolRepo = Join-Path $work 'eol'
    & git init --quiet --initial-branch=main $eolRepo
    & git -C $eolRepo config core.autocrlf true
    [IO.File]::WriteAllText((Join-Path $eolRepo 'lf.txt'), "one`ntwo`n")
    [IO.File]::WriteAllText((Join-Path $eolRepo 'other.txt'), "alpha`nbeta`n")
    & git -c core.autocrlf=false -C $eolRepo add lf.txt other.txt 2>$null
    & git -c core.autocrlf=false -C $eolRepo commit --quiet -m 'chore: seed'
    [IO.File]::WriteAllText((Join-Path $eolRepo 'lf.txt'), "one`r`ntwo`r`nthree`r`n")
    [IO.File]::WriteAllText((Join-Path $eolRepo 'other.txt'), "alpha`r`nbeta`r`n")
    [IO.File]::WriteAllText((Join-Path $eolRepo 'new.txt'), "fresh`r`n")
    $s = Invoke-Script (Join-Path $scriptsDir 'stage.ps1') @('-Paths', 'lf.txt,new.txt', '-Root', $eolRepo)
    Assert ($s.Code -eq 0) "stage: explicit paths accepted ($($s.Out))"
    $num = (& git -C $eolRepo diff --cached --numstat) -join '|'
    Assert ($num -match "^1\s+0\s+lf\.txt" -and $num -match "1\s+0\s+new\.txt" -and $num -notmatch 'other\.txt') "stage: only the real line changes are staged ($num)"
    Assert (((& git -C $eolRepo ls-files --eol lf.txt) -match '^i/lf') -and ((& git -C $eolRepo ls-files --eol new.txt) -match '^i/lf')) 'stage: blobs stay LF'
    $s = Invoke-Script (Join-Path $scriptsDir 'stage.ps1') @('-Paths', '.', '-Root', $eolRepo)
    Assert ($s.Code -ne 0) 'stage: a folder is refused'

    $s = Invoke-Script (Join-Path $scriptsDir 'stage.ps1') @('-Paths', '*.txt', '-Root', $eolRepo)
    Assert ($s.Code -ne 0) 'stage: a wildcard is refused'
    $s = Invoke-Script (Join-Path $scriptsDir 'stage.ps1') @('-Paths', '../outside.txt', '-Root', $eolRepo)
    Assert ($s.Code -ne 0) 'stage: a path outside the repository is refused'
    [void](New-Item -ItemType Directory -Path (Join-Path $eolRepo '.claude') -Force)
    Set-Content -LiteralPath (Join-Path $eolRepo '.claude\settings.json') -Value '{}' -Encoding ASCII
    $savedStageMode = $env:PRANATA_EXECUTOR_MODE
    $env:PRANATA_EXECUTOR_MODE = 'AUTONOMOUS'
    $s = Invoke-Script (Join-Path $scriptsDir 'stage.ps1') @('-Paths', '.claude/settings.json', '-Root', $eolRepo)
    $env:PRANATA_EXECUTOR_MODE = $savedStageMode
    Assert ($s.Code -ne 0 -and -not ((& git -C $eolRepo diff --cached --name-only) -contains '.claude/settings.json')) 'stage: guardrail path refused in AUTONOMOUS mode'
    Remove-Item -LiteralPath (Join-Path $eolRepo '.claude') -Recurse -Force
    Assert (((& git -C $eolRepo diff --cached --name-only --diff-filter=D) | Measure-Object).Count -eq 0) 'stage: a wildcard deletes nothing from the index'
    [IO.File]::WriteAllText((Join-Path $eolRepo 'gone.txt'), "x`n")
    & git -c core.autocrlf=false -C $eolRepo add gone.txt 2>$null
    Remove-Item -LiteralPath (Join-Path $eolRepo 'gone.txt')
    $s = Invoke-Script (Join-Path $scriptsDir 'stage.ps1') @('-Paths', 'gone.txt', '-Root', $eolRepo)
    Assert ($s.Code -eq 0 -and -not ((& git -C $eolRepo diff --cached --name-only) -contains 'gone.txt')) 'stage: a really deleted file is staged as such'

    # fetch-source refuses before any download: only locked registry or commit-pinned dists, never a name it cannot place.
    [IO.File]::WriteAllText((Join-Path $eolRepo 'package-lock.json'), '{"packages":{"":{},"node_modules/evil":{"version":"1.0.0","resolved":"http://example.test/evil-1.0.0.tgz","integrity":"sha512-AA=="},"node_modules/weak":{"version":"1.0.0","resolved":"https://registry.npmjs.org/weak/-/weak-1.0.0.tgz","integrity":"sha1-AA=="},"node_modules/climb":{"version":"1.0.0/../../../../.claude","resolved":"https://registry.npmjs.org/climb/-/climb-1.0.0.tgz","integrity":"sha512-AA=="}}}')
    $sha = 'd52c3b3129e4c8e213c97cdc68f94b0b7d27f181'
    [IO.File]::WriteAllText((Join-Path $eolRepo 'composer.lock'), ('{"packages":[{"name":"acme/unpinned","version":"v1.0.0","dist":{"type":"zip","url":"https://example.test/acme.zip","reference":"abc123"}},{"name":"acme/star","version":"v1.0.0","dist":{"type":"zip","url":"https://example.test/any","reference":"*"}},{"name":"acme/host","version":"v1.0.0","dist":{"type":"zip","url":"https://example.test/repos/acme/host/zipball/' + $sha + '","reference":"' + $sha + '"}},{"name":"acme/slug","version":"dev-main","dist":{"type":"zip","url":"https://api.github.com/repos/Acme/AcmeSlug/zipball/' + $sha + '","reference":"' + $sha + '"}}],"packages-dev":[]}'))
    $env:PRANATA_FETCH_SOURCE_TEST = '1'
    # A GitHub slug unlike the package name passes the pin check; dev-main then stops it, still offline.
    $f = Invoke-Script (Join-Path $scriptsDir 'fetch-source.ps1') @('-Package', 'acme/slug', '-Root', $eolRepo)
    Assert ($f.Code -ne 0 -and $f.Out -match 'Not a release version') "fetch-source: a pinned zipball under another slug passes the pin check ($($f.Out))"
    foreach ($case in @(@('missing', 'not-locked'), @('evil', 'a non-registry URL'), @('weak', 'a tarball without sha512'), @('climb', 'a version that climbs out of .pranata/tools/src'), @('acme/unpinned', 'a dist without its commit'), @('acme/star', 'a wildcard reference'), @('acme/host', 'a pinned dist on another host'), @('../x', 'a path'), @('a b', 'a name with a space'))) {
        $f = Invoke-Script (Join-Path $scriptsDir 'fetch-source.ps1') @('-Package', $case[0], '-Root', $eolRepo)
        Assert ($f.Code -ne 0 -and -not (Test-Path -LiteralPath (Join-Path $eolRepo '.pranata\tools\src'))) "fetch-source: refuses $($case[1]) before downloading"
    }
    Remove-Item Env:PRANATA_FETCH_SOURCE_TEST
    $f = Invoke-Script (Join-Path $scriptsDir 'fetch-source.ps1') @('-Package', 'acme/slug', '-Root', $eolRepo)
    Assert ($f.Code -ne 0 -and $f.Out -match 'repository root') 'fetch-source: a root other than the repository root is refused'
    Remove-Item -LiteralPath (Join-Path $eolRepo 'package-lock.json'), (Join-Path $eolRepo 'composer.lock')

    # WIP commit on a CRLF working copy touches only the real change.
    $state2 = Initialize-PranataStateDirs $eolRepo
    Add-Content -LiteralPath (Join-Path $eolRepo '.git\info\exclude') -Value '/.pranata/'
    & git -C $eolRepo reset --quiet
    & git -C $eolRepo switch --quiet -c chore/task-999-eol 2>$null
    $cpE = New-PranataCheckpoint; $cpE.mode = 'AUTONOMOUS'; $cpE.task_id = 'TASK-999'; $cpE.stage = 'IN_PROGRESS'; $cpE.branch = 'chore/task-999-eol'; $cpE.next_action = 'x'
    Save-PranataCheckpoint $eolRepo $cpE
    [void](Invoke-Script (Join-Path $scriptsDir 'hold.ps1') @('-Mode', 'IMMEDIATE_SAFE_SHUTDOWN', '-Reason', 'uji', '-Root', $eolRepo))
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $eolRepo, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile)
    $wip = (& git -C $eolRepo show --numstat --format= HEAD) -join '|'
    Assert ($wip -notmatch 'other\.txt' -and $wip -match 'lf\.txt') "hold: WIP commit does not rewrite line endings ($wip)"

    # Worktree in the disposable repo (C-13).
    $worktree = Join-Path $work 'pranata-uny-worktrees\task-011'
    & git -C $clone worktree add --quiet $worktree -b feature/task-011-x 2>$null
    Assert (Test-Path -LiteralPath (Join-Path $worktree 'f.txt')) 'worktree: created on this Windows machine'
    & git -C $clone worktree remove --force $worktree 2>$null
} finally {
    Set-Location $repo
    if ($work -match 'pranata-test-[0-9a-f]{8}$') { Remove-Item -LiteralPath $work -Recurse -Force -ErrorAction SilentlyContinue }
}
Write-Output "script tests: passed=$($script:passed) failed=$($script:fail)"
if ($script:fail -gt 0) { Write-Output 'RESULT: FAIL'; exit 1 }
Write-Output 'RESULT: PASS'
exit 0
