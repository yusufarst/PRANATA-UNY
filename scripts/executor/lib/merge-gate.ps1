# Merge gate of EXECUTOR_OPERATING_MODEL section 8.1, as a pure function over collected facts,
# so every negative path is testable without GitHub and without touching main.

# Minimum job set per Task (QUALITY_GATES 4.1). A Task not listed here cannot use bootstrap evidence.
$script:BootstrapRequiredJobs = @{
    'TASK-002' = @('app-boot', 'bootstrap-gate')
    'TASK-003' = @('app-boot', 'db-integration', 'bootstrap-gate')
    'TASK-004' = @('app-boot', 'db-integration', 'quality', 'bootstrap-gate')
}

# Ordinal comparison, character for character. PowerShell -ceq and -cin compare by culture and treat some invisible
# characters (U+FEFF, U+00AD) as absent, so a byte order mark or a disguised path would compare equal.
function Test-PranataSame([string]$A, [string]$B) { return [string]::Equals($A, $B, [StringComparison]::Ordinal) }
function Test-PranataAmong([string]$Value, [string[]]$Set) {
    foreach ($item in @($Set)) { if ([string]::Equals($Value, [string]$item, [StringComparison]::Ordinal)) { return $true } }
    return $false
}

function Get-PranataMergeFailures {
    param([hashtable]$Facts)
    $failures = New-Object System.Collections.ArrayList

    if (-not $Facts.TaskId) { [void]$failures.Add('task: PR branch does not name a Task') }
    if ($Facts.RiskClass -notin @('LOW', 'STANDARD', 'HIGH')) { [void]$failures.Add("task: risk class '$($Facts.RiskClass)' could not be read from the contract on main") }
    # A branch name is chosen by the agent; a finished or stopped Task cannot be merged under it. TODO stays valid
    # because section 2 records READY in TASK_PLAN, so the contract on main still says TODO during the Task.
    # Section 8.5 lifts this one check (8.1 item 7) for a DONE Task, and only when the whole remediation lane holds.
    if ($Facts.TaskStatus -notin @('TODO', 'READY', 'IN_PROGRESS', 'VERIFYING')) {
        $lifted = $false
        if ((Test-PranataSame $Facts.TaskStatus 'DONE') -and $Facts.Remediation) {
            if ($null -eq $Facts.Remediation.Failures) {
                [void]$failures.Add('remediation 8.5: the lane was not evaluated')
            } else {
                $laneFailures = @(@($Facts.Remediation.Failures) | Where-Object { $_ })
                foreach ($item in $laneFailures) { [void]$failures.Add($item) }
                $lifted = ($laneFailures.Count -eq 0)
            }
        }
        if (-not $lifted) { [void]$failures.Add("task: contract status on main is '$($Facts.TaskStatus)'") }
    }
    if ($Facts.CheckpointTaskId -and $Facts.CheckpointTaskId -ne $Facts.TaskId) { [void]$failures.Add("task: PR names $($Facts.TaskId), checkpoint names $($Facts.CheckpointTaskId)") }
    if ($Facts.Bootstrap) {
        if ($Facts.Mode -ne 'SUPERVISED') { [void]$failures.Add('bootstrap: only allowed in a SUPERVISED session') }
        if (-not $script:BootstrapRequiredJobs.ContainsKey([string]$Facts.TaskId)) { [void]$failures.Add("bootstrap: not allowed for $($Facts.TaskId) (only TASK-002 to TASK-004)") }
        # Fail closed: bootstrap mode without collected evidence is never a pass.
        if ($null -eq $Facts.BootstrapFailures) { [void]$failures.Add('bootstrap 4.1: evidence was not collected') }
        foreach ($item in @($Facts.BootstrapFailures)) { if ($item) { [void]$failures.Add($item) } }
    }
    if ($Facts.PrState -ne 'OPEN') { [void]$failures.Add("8.1.5 PR state is $($Facts.PrState), expected OPEN") }
    if ($Facts.PrBase -ne 'main') { [void]$failures.Add("8.1.5 PR base is $($Facts.PrBase), expected main") }
    if ($Facts.IsDraft) { [void]$failures.Add('8.1.5 PR is a draft') }
    if ($Facts.PrMergeable -ne 'MERGEABLE') { [void]$failures.Add("8.1.5 PR mergeable state is '$($Facts.PrMergeable)'") }
    if (-not $Facts.Authorized) { [void]$failures.Add('8.1.5 no active authorization on main covers this Task') }
    if (-not $Facts.ReconcileScopeOk) { [void]$failures.Add('8.3 a reconciliation PR may change documents only') }
    if ($Facts.PrTextProblem) { [void]$failures.Add('GOV-08 PR title or body carries attribution') }

    if (-not $Facts.PrHeadSha -or $Facts.PrHeadSha -ne $Facts.VerifiedSha) {
        [void]$failures.Add("8.1.1 PR HEAD $($Facts.PrHeadSha) differs from verified SHA $($Facts.VerifiedSha)")
    }
    if (-not $Facts.Bootstrap) {
        if ($Facts.CiGateConclusion -ne 'success') { [void]$failures.Add("8.1.2 ci-gate on PR HEAD is '$($Facts.CiGateConclusion)', expected success") }
    }
    if ($Facts.ReviewSha -ne $Facts.PrHeadSha) { [void]$failures.Add('8.1.3 review disposition is missing or for another SHA') }
    if ($Facts.ReviewStatus -ne 'PASS') { [void]$failures.Add("8.1.3 review status is '$($Facts.ReviewStatus)', expected PASS") }
    if ($Facts.OpenBlockingFindings -gt 0) { [void]$failures.Add("8.1.3 $($Facts.OpenBlockingFindings) blocking finding(s) open") }
    if ($Facts.RiskClass -eq 'HIGH' -and -not $Facts.IndependentReviewDone) { [void]$failures.Add('8.1.3 independent review missing for a HIGH Task') }
    # Section 8.3: reconciliation PRs (LOW) carry no Codex review.
    if ($Facts.RiskClass -ne 'LOW' -and $Facts.CodexStatus -notin @('COMPLETED_NO_FINDINGS', 'COMPLETED_WITH_FINDINGS') -and -not $Facts.SubstituteReviewDone) {
        [void]$failures.Add("8.1.3 Codex status '$($Facts.CodexStatus)' is not final and no substitute review is recorded")
    }
    if (-not $Facts.MainIsAncestor) { [void]$failures.Add('8.1.4 main is not an ancestor of PR HEAD; a fast-forward is impossible') }
    if ($Facts.RemoteMainSha -ne $Facts.CheckedMainSha) { [void]$failures.Add('8.1.4 main moved since the check') }
    if ($Facts.GuardPathsTouched.Count -gt 0 -and $Facts.Mode -ne 'SUPERVISED') {
        [void]$failures.Add("8.1.6 guardrail paths touched, merge is SUPERVISED only: $($Facts.GuardPathsTouched -join ', ')")
    }
    if ($Facts.CommitIdentityProblems.Count -gt 0) {
        [void]$failures.Add("GOV-08 commit identity or attribution problem: $($Facts.CommitIdentityProblems -join '; ')")
    }
    return ,$failures
}

# QUALITY_GATES 4.1 steps 2-4 as a pure function over what GitHub reported. A pull_request run tests the synthetic
# merge commit, not the PR HEAD, so the link to HEAD, base and tree comes from run metadata and the evidence artifact.
function Get-PranataBootstrapFailures {
    param([hashtable]$Run, [string]$TaskId, [int]$Pr, [string]$HeadSha, [string]$MainSha, [string]$Tree)
    $failures = New-Object System.Collections.ArrayList
    $required = $script:BootstrapRequiredJobs[$TaskId]
    if (-not $required) { return ,$failures }
    if ($Run.LookupError) { [void]$failures.Add("bootstrap 4.1.2 run lookup failed: $($Run.LookupError)"); return ,$failures }
    if (-not $Run.RunId) { [void]$failures.Add("bootstrap 4.1.2 no bootstrap run for PR $Pr on HEAD $HeadSha"); return ,$failures }

    if ($Run.RunStatus -cne 'completed') { [void]$failures.Add("bootstrap 4.1.3 run $($Run.RunId) status is '$($Run.RunStatus)', expected completed") }
    if ($Run.RunConclusion -cne 'success') { [void]$failures.Add("bootstrap 4.1.3 run $($Run.RunId) conclusion is '$($Run.RunConclusion)', expected success") }
    if ($Run.JobError) { [void]$failures.Add("bootstrap 4.1.3 job list failed: $($Run.JobError)") }
    $jobs = @($Run.Jobs)
    if ($Run.JobsTotal -ne $jobs.Count) { [void]$failures.Add("bootstrap 4.1.3 job list is incomplete: $($jobs.Count) of $($Run.JobsTotal)") }
    foreach ($name in $required) {
        if (@($jobs | Where-Object { $_.name -ceq $name }).Count -eq 0) { [void]$failures.Add("bootstrap 4.1.3 required job '$name' is missing") }
    }
    foreach ($job in $jobs) {
        if ($job.conclusion -cne 'success') { [void]$failures.Add("bootstrap 4.1.3 job '$($job.name)' is '$($job.conclusion)', expected success") }
    }

    $evidence = $Run.Evidence
    if (-not $evidence) {
        [void]$failures.Add("bootstrap 4.1.4 evidence artifact unavailable: $($Run.EvidenceError)")
        return ,$failures
    }
    $checks = @(
        @('pr', [string]$Pr), @('head_sha', $HeadSha), @('base_sha', $MainSha), @('tree_sha', $Tree),
        @('run_id', [string]$Run.RunId), @('run_attempt', [string]$Run.RunAttempt)
    )
    foreach ($check in $checks) {
        $actual = [string]$evidence.($check[0])
        if (-not $check[1] -or $actual -cne $check[1]) { [void]$failures.Add("bootstrap 4.1.4 evidence $($check[0]) is '$actual', expected '$($check[1])'") }
    }
    if ([string]$evidence.merge_sha -cnotmatch '^[0-9a-f]{40}$') { [void]$failures.Add("bootstrap 4.1.4 evidence merge_sha is '$($evidence.merge_sha)'") }
    return ,$failures
}

# Reads a GitHub API GET as an object; native stderr never decides, the exit code does.
function Invoke-PranataGhJson([string]$Path) {
    $ErrorActionPreference = 'Continue'
    $output = & gh api $Path 2>$null
    if ($LASTEXITCODE -ne 0) { throw "gh api $Path exited $LASTEXITCODE" }
    return (($output -join "`n") | ConvertFrom-Json)
}

# Collects what Get-PranataBootstrapFailures judges. Any error is recorded, so it can only lead to a refusal.
function Get-PranataBootstrapRun {
    param([string]$Repo, [int]$Pr, [string]$HeadSha, [string]$WorkDir)
    $result = @{
        LookupError = $null; RunId = $null; RunAttempt = $null; RunStatus = $null; RunConclusion = $null
        Jobs = @(); JobsTotal = -1; JobError = $null; Evidence = $null; EvidenceError = 'not read'
    }
    try {
        $listing = Invoke-PranataGhJson "repos/$Repo/actions/workflows/bootstrap.yml/runs?event=pull_request&head_sha=$HeadSha&per_page=100"
        # The filter is rechecked here: only a run proven to belong to this PR and HEAD counts.
        $runs = @($listing.workflow_runs | Where-Object {
            $_.head_sha -ceq $HeadSha -and $_.event -ceq 'pull_request' -and
            @($_.pull_requests | Where-Object { $_.number -eq $Pr }).Count -gt 0
        } | Sort-Object { [long]$_.id }, { [int]$_.run_attempt })
        if ($runs.Count -eq 0) { return $result }
        $latest = $runs[-1]
        $result.RunId = $latest.id; $result.RunAttempt = $latest.run_attempt
        $result.RunStatus = $latest.status; $result.RunConclusion = $latest.conclusion
    } catch {
        $result.LookupError = $_.Exception.Message
        return $result
    }
    try {
        $jobs = Invoke-PranataGhJson "repos/$Repo/actions/runs/$($result.RunId)/attempts/$($result.RunAttempt)/jobs?per_page=100"
        $result.JobsTotal = [int]$jobs.total_count
        $result.Jobs = @($jobs.jobs | ForEach-Object { @{ name = $_.name; conclusion = $_.conclusion } })
    } catch { $result.JobError = $_.Exception.Message }

    $dir = Join-Path $WorkDir ("bootstrap-evidence-" + [guid]::NewGuid().ToString('N').Substring(0, 8))
    try {
        $ErrorActionPreference = 'Continue'
        & gh run download $result.RunId -R $Repo -n bootstrap-evidence -D $dir 2>$null | Out-Null
        if ($LASTEXITCODE -ne 0) { throw "gh run download exited $LASTEXITCODE" }
        $result.Evidence = [IO.File]::ReadAllText((Join-Path $dir 'bootstrap-evidence.json')) | ConvertFrom-Json
    } catch {
        $result.EvidenceError = $_.Exception.Message
    } finally {
        if (Test-Path -LiteralPath $dir) { Remove-Item -LiteralPath $dir -Recurse -Force }
    }
    return $result
}

# Reads the PR branch name; the Task is never taken from a caller-supplied argument.
function Get-PranataTaskFromBranch([string]$Branch) {
    if ($Branch -match '^(feature|chore|fix)/task-(\d{3})-') { return @{ TaskId = "TASK-$($Matches[2])"; Reconcile = $false } }
    if ($Branch -match '^docs/reconcile-task-(\d{3})(-r\d+)?$') { return @{ TaskId = "TASK-$($Matches[1])"; Reconcile = $true } }
    return @{ TaskId = $null; Reconcile = $false }
}

# Section 8.3: exactly these three documents. Anything wider could rewrite a risk class or an authorization.
function Test-PranataReconcileScope([string[]]$Changed, [string]$TaskId) {
    $allowed = @("docs/11-tasks/$TaskId.md", 'docs/11-tasks/TASK_PLAN.md', 'docs/handoff/CURRENT_HANDOFF.md')
    return (@($Changed | Where-Object { -not (Test-PranataAmong $_ $allowed) }).Count -eq 0)
}

function Test-PranataAuthorizationText([string]$Records, [string]$TaskId) {
    if ($TaskId -notmatch '^TASK-(\d{3})$') { return $false }
    $number = [int]$Matches[1]
    if ($number -lt 1 -or $number -gt 78) { return $false }
    if ($Records -notmatch '(?ms)^## AUTH-013\s*$(.*?)(^## |\z)') { return $false }
    return ($Matches[1] -match 'State: \*\*ACTIVE\*\*')
}

# Raw metadata check over every commit the merge would publish.
function Get-PranataCommitProblems {
    param([string]$Root, [string]$Range, [string]$OwnerName, [string]$OwnerEmail)
    $problems = New-Object System.Collections.ArrayList
    $expected = "$OwnerName <$OwnerEmail>"
    foreach ($sha in @(& git -C $Root rev-list $Range)) {
        $raw = (& git -C $Root cat-file -p $sha) -join "`n"
        foreach ($field in @('author', 'committer')) {
            if ($raw -notmatch "(?m)^$field (.+) \d+ [+-]\d{4}$") { [void]$problems.Add("$sha has no $field"); continue }
            if ($Matches[1] -cne $expected) { [void]$problems.Add("$sha $field is '$($Matches[1])'") }
        }
        if (Test-PranataAttributionInCommand $raw) { [void]$problems.Add("$sha carries attribution text") }
    }
    return ,$problems
}

# --- Post-DONE remediation lane (EXECUTOR_OPERATING_MODEL 8.5, PCR-004).
# Pure parts first (records, entry, Task file sections, judgement), then the collectors that read Git and GitHub.
# A missing or unreadable fact always ends in a refusal, never in a pass.

$script:RemediationMarker = '(?m)^Remediation: (REM-[0-9]{3}) entry ([0-9]{1,3})\r?$'
$script:RemediationRecordsPath = 'docs/00-governance/APPROVAL_RECORDS.md'
$script:AbsentBlob = '(absent)'
# Canonical diff for the DELTA digest of 8.5 item 5, pinned against local diff settings. An independent verifier
# reproduces the digest byte for byte with GIT_ATTR_NOSYSTEM=1 and an empty file <empty>:
#   git --attr-source=<PR HEAD> -c core.attributesFile= -c diff.orderFile=<empty> -c diff.suppressBlankEmpty=false
#       -c core.bigFileThreshold=512m -c core.quotePath=true diff <the options below> <SOURCE HEAD> <PR HEAD> -- <entry paths>
# piped to sha256sum. Settings that remain (the repository's info/attributes) can only change the digest, which
# then fails to match: a refusal, never a pass.
$script:RemediationDiffConfig = @('-c', 'core.attributesFile=', '-c', 'diff.suppressBlankEmpty=false', '-c', 'core.bigFileThreshold=512m', '-c', 'core.quotePath=true')
$script:RemediationDiffOptions = @(
    '--no-color', '--no-ext-diff', '--no-textconv', '--no-renames', '--full-index', '--binary',
    '--diff-algorithm=myers', '--indent-heuristic', '-U3', '--inter-hunk-context=0', '--no-relative',
    '--src-prefix=a/', '--dst-prefix=b/'
)

function ConvertFrom-PranataRemediationEntry([string]$Line) {
    $parts = @($Line -split ' \| ')
    if ($parts.Count -ne 8) { return @{ Problem = "entry line has $($parts.Count) fields, expected 8: '$Line'" } }
    $entry = @{ Problem = $null; Number = $null; Task = $null; Branch = $null; SourceBase = $null; SourceHead = $null; Equivalence = $null; Paths = @() }
    $bad = New-Object System.Collections.ArrayList
    if ($parts[0] -cmatch '^ENTRY ([0-9]{1,3})$') { $entry.Number = [int]$Matches[1] } else { [void]$bad.Add('number') }
    if ($parts[1] -cmatch '^TASK-[0-9]{3}$') { $entry.Task = $parts[1] } else { [void]$bad.Add('Task') }
    if ($parts[2] -cmatch '^[a-z0-9][a-z0-9._-]*(/[a-z0-9._-]+)+$' -and $parts[2] -cnotmatch '-r[0-9]+$') { $entry.Branch = $parts[2] } else { [void]$bad.Add('branch') }
    if ($parts[3] -cnotmatch '^PR: (new|#[0-9]+)$') { [void]$bad.Add('PR') }
    if ($parts[4] -cmatch '^SOURCE: ([0-9a-f]{40})\.\.([0-9a-f]{40})$') { $entry.SourceBase = $Matches[1]; $entry.SourceHead = $Matches[2] }
    elseif ($parts[4] -cnotmatch '^SOURCE: none$') { [void]$bad.Add('SOURCE') }
    if ($parts[5] -cmatch '^EQUIVALENCE: (none|IDENTICAL|DELTA|IDENTICAL or DELTA)$') { $entry.Equivalence = $Matches[1] } else { [void]$bad.Add('EQUIVALENCE') }
    if ($entry.Equivalence -and (($entry.Equivalence -ceq 'none') -ne (-not $entry.SourceHead))) { [void]$bad.Add('SOURCE and EQUIVALENCE disagree') }
    if ($parts[6] -cmatch '^PATHS: (.+)$') {
        $entry.Paths = @($Matches[1] -split ', ')
        foreach ($path in $entry.Paths) {
            if ($path -cnotmatch '^[A-Za-z0-9_.-]+(/[A-Za-z0-9_.-]+)*$' -or $path -match '(^|/)\.\.?(/|$)') { [void]$bad.Add("path '$path'") }
        }
        if (@($entry.Paths | Select-Object -Unique).Count -ne $entry.Paths.Count) { [void]$bad.Add('duplicate path') }
    } else { [void]$bad.Add('PATHS') }
    if ($parts[7] -cnotmatch '^PURPOSE: \S') { [void]$bad.Add('PURPOSE') }
    if ($bad.Count -gt 0) { $entry.Problem = "entry line has a bad $($bad -join ', '): '$Line'" }
    return $entry
}

# Every machine block 'REMEDIATION: REM-nnn' of APPROVAL_RECORDS. Any unexpected or repeated line makes the block malformed.
function Get-PranataRemediationRecords([string]$Records) {
    $result = New-Object System.Collections.ArrayList
    $lines = @(([string]$Records) -split "\r?\n")
    $i = 0
    while ($i -lt $lines.Count) {
        if ($lines[$i] -cnotmatch '^\s*REMEDIATION:') { $i++; continue }
        $record = @{ Id = $null; State = $null; Mode = $null; Order = $null; Entries = @{}; Problems = (New-Object System.Collections.ArrayList) }
        if ($lines[$i] -cmatch '^REMEDIATION: (REM-[0-9]{3})$') { $record.Id = $Matches[1] } else { [void]$record.Problems.Add("header '$($lines[$i])'") }
        $i++
        while ($i -lt $lines.Count -and $lines[$i] -notmatch '^\s*(```|$)') {
            $line = $lines[$i]
            $i++
            if ($line -cmatch '^STATE: (PROPOSED|ACTIVE|CLOSED)$') {
                if ($record.State) { [void]$record.Problems.Add('STATE repeated') }
                $record.State = $Matches[1]
            } elseif ($line -cmatch '^MODE: SUPERVISED$') {
                if ($record.Mode) { [void]$record.Problems.Add('MODE repeated') }
                $record.Mode = 'SUPERVISED'
            } elseif ($line -cmatch '^ORDER: ([0-9]{1,3}(, [0-9]{1,3})*)$') {
                if ($null -ne $record.Order) { [void]$record.Problems.Add('ORDER repeated') }
                $record.Order = @($Matches[1] -split ', ' | ForEach-Object { [int]$_ })
            } elseif ($line -cmatch '^ENTRY ') {
                $entry = ConvertFrom-PranataRemediationEntry $line
                if ($entry.Problem) { [void]$record.Problems.Add($entry.Problem) }
                elseif ($record.Entries.ContainsKey($entry.Number)) { [void]$record.Problems.Add("entry $($entry.Number) repeated") }
                else { $record.Entries[$entry.Number] = $entry }
            } else {
                [void]$record.Problems.Add("unexpected line '$line'")
            }
        }
        if (-not $record.State) { [void]$record.Problems.Add('STATE missing') }
        if (-not $record.Mode) { [void]$record.Problems.Add('MODE missing or not SUPERVISED') }
        if ($record.Entries.Count -eq 0) { [void]$record.Problems.Add('no entry') }
        # Entries run from 0 in the recorded order, one line each (8.5 item 7).
        $expected = @(0..([Math]::Max($record.Entries.Count, 1) - 1))
        if ($null -eq $record.Order -or (@($record.Order) -join ',') -cne ($expected -join ',')) { [void]$record.Problems.Add("ORDER is not 0..$($record.Entries.Count - 1)") }
        foreach ($number in $expected) { if ($record.Entries.Count -gt 0 -and -not $record.Entries.ContainsKey($number)) { [void]$record.Problems.Add("entry $number missing") } }
        [void]$result.Add($record)
    }
    return ,$result
}

# The exact family, or a successor -rN with N >= 2 (8.5 item 2).
function Test-PranataRemediationBranch([string]$Branch, [string]$Family) {
    if (-not $Branch -or -not $Family) { return $false }
    if (Test-PranataSame $Branch $Family) { return $true }
    if (-not $Branch.StartsWith("$Family-r", [StringComparison]::Ordinal)) { return $false }
    $suffix = $Branch.Substring($Family.Length + 2)
    return ($suffix -cmatch '^[1-9][0-9]{0,3}$' -and [int]$suffix -ge 2)
}

# Reads the PR body line and finds the one ACTIVE entry it names; the record comes from main only.
function Resolve-PranataRemediationEntry {
    param([string]$Records, [string]$PrBody, [string]$TaskId, [string]$Branch)
    $out = @{ Label = 'remediation'; Id = $null; Number = $null; Record = $null; Entry = $null; Failures = (New-Object System.Collections.ArrayList) }
    $markers = [regex]::Matches([string]$PrBody, $script:RemediationMarker)
    if ($markers.Count -ne 1) {
        [void]$out.Failures.Add("8.5.7 the PR body must carry exactly one line 'Remediation: REM-nnn entry N', found $($markers.Count)")
        return $out
    }
    $out.Id = $markers[0].Groups[1].Value
    $out.Number = [int]$markers[0].Groups[2].Value
    $out.Label = "$($out.Id) entry $($out.Number)"
    $all = Get-PranataRemediationRecords $Records
    foreach ($record in $all) {
        foreach ($problem in $record.Problems) { [void]$out.Failures.Add("8.5.1 remediation record $($record.Id) is malformed: $problem") }
    }
    if ($out.Failures.Count -gt 0) { return $out }
    $named = @($all | Where-Object { $_.Id -ceq $out.Id })
    if ($named.Count -eq 0) { [void]$out.Failures.Add("8.5.1 $($out.Id) is not recorded on main"); return $out }
    if ($named.Count -gt 1) { [void]$out.Failures.Add("8.5.1 $($out.Id) is recorded $($named.Count) times on main"); return $out }
    $record = $named[0]
    if ($record.State -cne 'ACTIVE') { [void]$out.Failures.Add("8.5.1 $($out.Id) is $($record.State), not ACTIVE"); return $out }
    $covering = @($all | Where-Object {
        $_.State -ceq 'ACTIVE' -and @($_.Entries.Values | Where-Object { $_.Task -ceq $TaskId -and (Test-PranataRemediationBranch $Branch $_.Branch) }).Count -gt 0
    })
    if ($covering.Count -gt 1) { [void]$out.Failures.Add("8.5.1 $($covering.Count) ACTIVE remediation records cover $TaskId on $Branch, exactly one may"); return $out }
    if (-not $record.Entries.ContainsKey($out.Number)) { [void]$out.Failures.Add("8.5.2 $($out.Id) has no entry $($out.Number)"); return $out }
    $entry = $record.Entries[$out.Number]
    if (-not (Test-PranataSame $entry.Task $TaskId)) { [void]$out.Failures.Add("8.5.2 the entry is for $($entry.Task), the PR branch names '$TaskId'"); return $out }
    if (-not (Test-PranataRemediationBranch $Branch $entry.Branch)) {
        [void]$out.Failures.Add("8.5.2 branch '$Branch' is neither $($entry.Branch) nor a successor $($entry.Branch)-rN (N >= 2)")
        return $out
    }
    $out.Record = $record
    $out.Entry = $entry
    return $out
}

function Split-PranataSections([string]$Text) {
    $sections = New-Object System.Collections.ArrayList
    $heading = '(before the first section)'
    $body = New-Object System.Collections.ArrayList
    # Split on LF only: a CR that appears or disappears is a change too. Get-PranataBlobText keeps every byte.
    foreach ($line in ([string]$Text).Split([char]10)) {
        if ($line -cmatch '^## ') {
            [void]$sections.Add(@{ Heading = $heading; Body = ($body -join "`n") })
            $heading = $line
            $body = New-Object System.Collections.ArrayList
            continue
        }
        [void]$body.Add($line)
    }
    [void]$sections.Add(@{ Heading = $heading; Body = ($body -join "`n") })
    return ,$sections
}

# 8.5 item 2: a Task contract may change only inside its COMPLETION EVIDENCE section, and stays DONE.
function Get-PranataTaskFileProblems([string]$Main, [string]$Head) {
    $problems = New-Object System.Collections.ArrayList
    if ($Head -cnotmatch '(?m)^STATUS: DONE\r?$') { [void]$problems.Add('STATUS: DONE is missing at PR HEAD') }
    $before = Split-PranataSections $Main
    $after = Split-PranataSections $Head
    if (-not (Test-PranataSame (@($before | ForEach-Object { $_.Heading }) -join "`n") (@($after | ForEach-Object { $_.Heading }) -join "`n"))) {
        [void]$problems.Add('the section headings changed')
        return ,$problems
    }
    for ($i = 0; $i -lt $before.Count; $i++) {
        if (Test-PranataSame $before[$i].Heading '## COMPLETION EVIDENCE') { continue }
        if (-not (Test-PranataSame $before[$i].Body $after[$i].Body)) { [void]$problems.Add("changed outside ## COMPLETION EVIDENCE: $($before[$i].Heading)") }
    }
    return ,$problems
}

# 8.5 item 7: a merged PR counts as the publication of entry k only when every condition holds.
function Test-PranataRemediationEvidence($Pr, [string]$Id, [int]$Number, $Entry) {
    if (-not $Pr -or -not $Entry) { return $false }
    $markers = [regex]::Matches([string]$Pr.Body, $script:RemediationMarker)
    if ($markers.Count -ne 1) { return $false }
    if (-not (Test-PranataSame $markers[0].Groups[1].Value $Id) -or [int]$markers[0].Groups[2].Value -ne $Number) { return $false }
    if (-not (Test-PranataRemediationBranch ([string]$Pr.Branch) $Entry.Branch)) { return $false }
    if ($Pr.DescendsFromActivation -ne $true -or $Pr.AncestorOfMain -ne $true) { return $false }
    $changed = @(@($Pr.ChangedFiles) | Where-Object { $_ })
    if ($changed.Count -eq 0) { return $false }
    return (@($changed | Where-Object { -not (Test-PranataAmong $_ $Entry.Paths) }).Count -eq 0)
}

function Get-PranataBlobFact($Map, [string]$Path) {
    if ($Map -and $Map.ContainsKey($Path) -and $Map[$Path]) { return [string]$Map[$Path] }
    return $null
}

# Judges the collected facts of 8.5 items 1-7. Returns the label 'REM-nnn entry N' and the refusals, each labelled.
function Get-PranataRemediationFailures {
    param([hashtable]$Facts)
    $failures = New-Object System.Collections.ArrayList
    $resolved = Resolve-PranataRemediationEntry -Records $Facts.Records -PrBody $Facts.PrBody -TaskId $Facts.TaskId -Branch $Facts.Branch
    $prefix = "$($resolved.Label): "
    foreach ($item in $resolved.Failures) { [void]$failures.Add("$prefix$item") }
    if (-not $resolved.Entry) { return @{ Label = $resolved.Label; Failures = $failures } }
    $entry = $resolved.Entry
    $number = $resolved.Number

    # Item 3: SUPERVISED only, never with -Bootstrap, and a checkpoint that names this Task and this entry.
    if (-not (Test-PranataSame $Facts.Mode 'SUPERVISED')) { [void]$failures.Add("${prefix}8.5.3 the lane runs only in SUPERVISED mode, mode is '$($Facts.Mode)'") }
    if ($Facts.Bootstrap) { [void]$failures.Add("${prefix}8.5.3 the lane is refused together with -Bootstrap") }
    if (-not (Test-PranataSame ([string]$Facts.CheckpointTaskId) $entry.Task)) { [void]$failures.Add("${prefix}8.5.3 checkpoint task_id is '$($Facts.CheckpointTaskId)', expected '$($entry.Task)'") }
    $expectedCheckpoint = "$($resolved.Id)/$number"
    if (-not (Test-PranataSame ([string]$Facts.CheckpointRemediation) $expectedCheckpoint)) {
        [void]$failures.Add("${prefix}8.5.3 checkpoint remediation is '$($Facts.CheckpointRemediation)', expected '$expectedCheckpoint'")
    }

    # Item 2: exact paths, no TASK_PLAN, and a Task contract changed only in COMPLETION EVIDENCE with STATUS DONE kept.
    $changed = @(@($Facts.ChangedFiles) | Where-Object { $_ })
    if ($changed.Count -eq 0) { [void]$failures.Add("${prefix}8.5.2 the PR changes no file") }
    foreach ($file in $changed) {
        if (-not (Test-PranataAmong $file $entry.Paths)) { [void]$failures.Add("${prefix}8.5.2 $file is outside the entry paths") }
    }
    if (@($changed | Where-Object { Test-PranataSame $_ 'docs/11-tasks/TASK_PLAN.md' }).Count -gt 0) { [void]$failures.Add("${prefix}8.5.2 TASK_PLAN.md must not change") }
    foreach ($file in @($changed | Where-Object { $_ -cmatch '^docs/11-tasks/TASK-[0-9]{3}\.md$' })) {
        $pair = $null
        if ($Facts.TaskFiles -and $Facts.TaskFiles.ContainsKey($file)) { $pair = $Facts.TaskFiles[$file] }
        if (-not $pair -or $null -eq $pair.Main -or $null -eq $pair.Head) { [void]$failures.Add("${prefix}8.5.2 $file was not compared"); continue }
        foreach ($problem in (Get-PranataTaskFileProblems $pair.Main $pair.Head)) { [void]$failures.Add("${prefix}8.5.2 ${file}: $problem") }
    }

    # Item 5: the reviewed SOURCE, proven from blobs (IDENTICAL) or from the recomputed diff digest (DELTA).
    if ($entry.SourceHead) {
        $blobs = $Facts.Blobs
        foreach ($path in $entry.Paths) {
            $onMain = Get-PranataBlobFact $blobs.Main $path
            $atBase = Get-PranataBlobFact $blobs.SourceBase $path
            if (-not $onMain -or -not $atBase) { [void]$failures.Add("${prefix}8.5.5 blobs of $path on main and at SOURCE base were not collected"); continue }
            if (-not (Test-PranataSame $onMain $atBase)) { [void]$failures.Add("${prefix}8.5.5 $path on main is not the blob of SOURCE base $($entry.SourceBase)") }
        }
        $review = $Facts.Review
        $allowed = @{ 'IDENTICAL' = @('IDENTICAL'); 'DELTA' = @('DELTA'); 'IDENTICAL or DELTA' = @('IDENTICAL', 'DELTA') }[$entry.Equivalence]
        $claimed = $null
        if (-not $review) {
            [void]$failures.Add("${prefix}8.5.5 no review disposition records the equivalence")
        } else {
            $claimed = [string]$review.equivalence
            if (-not (Test-PranataSame ([string]$review.source_sha) $entry.SourceHead)) {
                [void]$failures.Add("${prefix}8.5.5 review disposition source_sha is '$($review.source_sha)', expected SOURCE HEAD $($entry.SourceHead)")
            }
            if (-not (Test-PranataAmong $claimed $allowed)) { [void]$failures.Add("${prefix}8.5.5 review disposition equivalence is '$claimed', the entry allows $($entry.Equivalence)") }
        }
        if ((Test-PranataSame $claimed 'IDENTICAL') -and (Test-PranataAmong $claimed $allowed)) {
            foreach ($path in $entry.Paths) {
                $atHead = Get-PranataBlobFact $blobs.Head $path
                $atSource = Get-PranataBlobFact $blobs.SourceHead $path
                if (-not $atHead -or -not $atSource) { [void]$failures.Add("${prefix}8.5.5 blobs of $path at PR HEAD and SOURCE HEAD were not collected"); continue }
                if (-not (Test-PranataSame $atHead $atSource)) { [void]$failures.Add("${prefix}8.5.5 IDENTICAL: $path at PR HEAD is not the blob of SOURCE HEAD $($entry.SourceHead)") }
            }
        } elseif ((Test-PranataSame $claimed 'DELTA') -and (Test-PranataAmong $claimed $allowed)) {
            if (-not ($review.delta_verified -is [bool] -and $review.delta_verified)) { [void]$failures.Add("${prefix}8.5.5 DELTA: the diff was not verified independently (delta_verified)") }
            $recorded = [string]$review.delta_sha256
            if ($recorded -cnotmatch '^[0-9a-f]{64}$') { [void]$failures.Add("${prefix}8.5.5 DELTA: review disposition delta_sha256 '$recorded' is not a SHA-256") }
            elseif (-not $Facts.DeltaSha256) { [void]$failures.Add("${prefix}8.5.5 DELTA: the diff digest could not be recomputed") }
            elseif (-not (Test-PranataSame $recorded $Facts.DeltaSha256)) { [void]$failures.Add("${prefix}8.5.5 DELTA: SHA-256 recorded $recorded, recomputed $($Facts.DeltaSha256)") }
        }
    }

    # Item 7: order and single use, from publication evidence after the activation commit only.
    $publication = $Facts.Publication
    if (-not $publication) { [void]$failures.Add("${prefix}8.5.7 publication evidence was not collected") }
    elseif ($publication.Error) { [void]$failures.Add("${prefix}8.5.7 publication evidence could not be read: $($publication.Error)") }
    elseif (-not $publication.ActivationSha) { [void]$failures.Add("${prefix}8.5.7 no activation commit of $($resolved.Id) in the history of main") }
    elseif ($null -eq $publication.PathCommits -or $null -eq $publication.FamilyPrs) { [void]$failures.Add("${prefix}8.5.7 publication evidence is incomplete (Git history or branch family not checked)") }
    else {
        if ($publication.CandidateDescends -ne $true) { [void]$failures.Add("${prefix}8.5.7 PR HEAD does not descend from the activation commit $($publication.ActivationSha)") }
        # Single use without trusting PR bodies: Git history of the entry paths, and merged PRs of the branch family.
        $pathCommits = @(@($publication.PathCommits) | Where-Object { $_ })
        if ($pathCommits.Count -gt 0) {
            [void]$failures.Add("${prefix}8.5.7 entry $number is already used: its paths changed on main after the activation commit ($((@($pathCommits | Select-Object -First 3)) -join ', '))")
        }
        $familyPrs = @(@($publication.FamilyPrs) | Where-Object { $_ })
        if ($familyPrs.Count -gt 0) {
            [void]$failures.Add("${prefix}8.5.7 entry $number is already used: merged PR $((@($familyPrs | ForEach-Object { "#$($_.Number)" })) -join ', ') of branch family $($entry.Branch) descends from the activation commit")
        }
        for ($k = 0; $k -le $number; $k++) {
            $entryK = $resolved.Record.Entries[$k]
            $valid = @(@($publication.Prs) | Where-Object { Test-PranataRemediationEvidence $_ $resolved.Id $k $entryK })
            $numbers = (@($valid | ForEach-Object { "#$($_.Number)" }) -join ', ')
            if ($k -lt $number -and $valid.Count -eq 0) { [void]$failures.Add("${prefix}8.5.7 entry $k is not published yet; entries run in order") }
            elseif ($k -lt $number -and $valid.Count -gt 1) { [void]$failures.Add("${prefix}8.5.7 entry $k has $($valid.Count) publication records ($numbers), expected exactly one") }
            elseif ($k -eq $number -and $valid.Count -gt 0) { [void]$failures.Add("${prefix}8.5.7 entry $number is already published ($numbers); each entry is used once") }
        }
    }
    return @{ Label = $resolved.Label; Failures = $failures }
}

# $true or $false from Git. Any other outcome (an unknown object, a bad SHA) throws: a fact that could not be read
# is never taken as 'not an ancestor'.
function Test-PranataAncestor([string]$Root, [string]$Ancestor, [string]$Descendant) {
    if ($Ancestor -cnotmatch '^[0-9a-f]{40}$' -or $Descendant -cnotmatch '^[0-9a-f]{40}$') { throw "ancestry of '$Ancestor' and '$Descendant' cannot be checked" }
    $ErrorActionPreference = 'Continue'
    & git -C $Root merge-base --is-ancestor $Ancestor $Descendant 2>$null
    if ($LASTEXITCODE -eq 0) { return $true }
    if ($LASTEXITCODE -eq 1) { return $false }
    throw "git merge-base --is-ancestor $Ancestor $Descendant exited $LASTEXITCODE"
}

# The files a diff touches, one path per element: --no-renames lists a moved file under its old and its new path,
# and -z prints every path unquoted, so a guardrail path is never hidden by rename pairing or by quoting.
# The NUL-separated output is read as raw bytes and strict UTF-8, independent of the console encoding.
function Get-PranataChangedFiles([string]$Root, [string[]]$Revisions) {
    $bytes = Invoke-PranataGitBytes $Root (@('diff', '--name-only', '--no-renames', '--no-relative', '-z') + @($Revisions))
    if ($null -eq $bytes) { throw "git diff --name-only $($Revisions -join ' ') failed" }
    try { $text = (New-Object System.Text.UTF8Encoding($false, $true)).GetString($bytes) } catch { throw 'git diff --name-only printed a path that is not valid UTF-8' }
    # Callers wrap the result in @(): one path comes back as a string, none as nothing.
    return @(@($text.Split([char]0)) | Where-Object { $_ })
}

# A blob id, '(absent)' when the commit exists without the path, or $null when even the commit is unknown.
function Get-PranataBlobId([string]$Root, [string]$Sha, [string]$Path) {
    $ErrorActionPreference = 'Continue'
    $id = (& git -C $Root rev-parse --verify --quiet "${Sha}:${Path}" 2>$null)
    if ($LASTEXITCODE -eq 0 -and [string]$id -cmatch '^[0-9a-f]{40}$') { return [string]$id }
    & git -C $Root cat-file -e "${Sha}^{commit}" 2>$null
    if ($LASTEXITCODE -eq 0) { return $script:AbsentBlob }
    return $null
}

# The raw standard output of a git command, or $null when it fails. Read from the process stream, never through
# the PowerShell pipeline, which splits on CR and LF and re-encodes the text. Arguments carry no space or quote.
function Invoke-PranataGitBytes([string]$Root, [string[]]$Arguments) {
    foreach ($argument in $Arguments) { if (-not $argument -or $argument -match '[\s"]') { return $null } }
    $info = New-Object System.Diagnostics.ProcessStartInfo
    $info.FileName = 'git'
    $info.Arguments = ($Arguments -join ' ')
    $info.WorkingDirectory = $Root
    $info.UseShellExecute = $false
    $info.CreateNoWindow = $true
    $info.RedirectStandardOutput = $true
    $info.RedirectStandardError = $true
    $process = $null
    try {
        $process = [System.Diagnostics.Process]::Start($info)
        $errors = $process.StandardError.ReadToEndAsync()
        $buffer = New-Object System.IO.MemoryStream
        $process.StandardOutput.BaseStream.CopyTo($buffer)
        $process.WaitForExit()
        [void]$errors.Result
        if ($process.ExitCode -ne 0) { return $null }
        return ,$buffer.ToArray()
    } catch {
        return $null
    } finally {
        if ($process) { $process.Dispose() }
    }
}

# The raw bytes of a blob, or $null when Git cannot give them.
function Get-PranataBlobBytes([string]$Root, [string]$Sha, [string]$Path) {
    if ($Sha -cnotmatch '^[0-9a-f]{40}$' -or $Path -cnotmatch '^[A-Za-z0-9_./-]+$') { return $null }
    $bytes = Invoke-PranataGitBytes $Root @('cat-file', 'blob', "${Sha}:${Path}")
    if ($null -eq $bytes) { return $null }
    return ,$bytes
}

# A blob as text, byte for byte: strict UTF-8 that refuses invalid bytes, CR and final newline kept. $null when
# the blob cannot be read or is not valid UTF-8, so the caller refuses.
function Get-PranataBlobText([string]$Root, [string]$Sha, [string]$Path) {
    $bytes = Get-PranataBlobBytes $Root $Sha $Path
    if ($null -eq $bytes) { return $null }
    try { return (New-Object System.Text.UTF8Encoding($false, $true)).GetString($bytes) } catch { return $null }
}

# SHA-256 of the canonical diff SOURCE HEAD..PR HEAD on the entry paths. --output keeps the raw bytes away from
# the PowerShell pipeline, which would re-encode them.
function Get-PranataRemediationDeltaSha256 {
    param([string]$Root, [string]$From, [string]$To, [string[]]$Paths, [string]$WorkDir)
    $stem = Join-Path $WorkDir ('remediation-delta-' + [guid]::NewGuid().ToString('N').Substring(0, 8))
    $file = "$stem.diff"
    $order = "$stem.order"
    $ErrorActionPreference = 'Continue'
    $options = $script:RemediationDiffOptions
    $config = @("--attr-source=$To") + $script:RemediationDiffConfig + @('-c', "diff.orderFile=$order")
    $savedNoSystem = $env:GIT_ATTR_NOSYSTEM
    try {
        [IO.File]::WriteAllBytes($order, [byte[]]@())
        $env:GIT_ATTR_NOSYSTEM = '1'
        & git -C $Root @config diff @options "--output=$file" $From $To -- @Paths 2>$null
        if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $file)) { throw "git diff exited $LASTEXITCODE" }
        return (Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash.ToLowerInvariant()
    } finally {
        $env:GIT_ATTR_NOSYSTEM = $savedNoSystem
        foreach ($item in @($file, $order)) { if (Test-Path -LiteralPath $item) { Remove-Item -LiteralPath $item -Force } }
    }
}

# The activation commit of 8.5 item 7: the earliest commit of main whose APPROVAL_RECORDS holds the record ACTIVE.
function Get-PranataRemediationActivation([string]$Root, [string]$MainSha, [string]$Id) {
    $ErrorActionPreference = 'Continue'
    $commits = @(& git -C $Root log --reverse --format=%H $MainSha -- $script:RemediationRecordsPath 2>$null)
    if ($LASTEXITCODE -ne 0) { throw 'git log of APPROVAL_RECORDS failed' }
    foreach ($sha in $commits) {
        # An unreadable version could hide an earlier activation, so it stops the search instead of being skipped.
        $text = Get-PranataBlobText $Root $sha $script:RemediationRecordsPath
        if ($null -eq $text) { throw "APPROVAL_RECORDS at $sha could not be read as UTF-8" }
        $records = Get-PranataRemediationRecords $text
        if (@($records | Where-Object { $_.Id -ceq $Id -and $_.State -ceq 'ACTIVE' }).Count -gt 0) { return $sha }
    }
    return $null
}

function Invoke-PranataGhCommandJson([string[]]$Arguments) {
    $ErrorActionPreference = 'Continue'
    $output = & gh @Arguments 2>$null
    if ($LASTEXITCODE -ne 0) { throw "gh $($Arguments[0]) $($Arguments[1]) exited $LASTEXITCODE" }
    return (($output -join "`n") | ConvertFrom-Json)
}

# A listing or commit list that reaches its cap may be truncated; both are refused rather than read as complete.
$script:RemediationListLimit = 1000
$script:RemediationCommitLimit = 250

# Publication facts of 8.5 item 7 for entry $Entry. Git, not GitHub, decides ancestry and the changed files.
# Prs: merged PRs whose body names this record, the evidence of earlier entries. FamilyPrs and PathCommits do not
# depend on any PR body, which stays editable after a merge: a merged PR of this entry's branch family after the
# activation commit, or a commit on main since activation touching this entry's paths, means the entry is used.
# Anything that cannot be read or looks incomplete sets Error, which refuses the merge.
function Get-PranataRemediationPublication {
    param([string]$Root, [string]$Repo, [string]$MainSha, [string]$HeadSha, [string]$Id, $Entry)
    $result = @{ Error = $null; ActivationSha = $null; CandidateDescends = $false; Prs = @(); FamilyPrs = $null; PathCommits = $null }
    $ErrorActionPreference = 'Continue'
    try {
        if (-not $Entry -or @($Entry.Paths).Count -eq 0 -or -not $Entry.Branch) { throw 'the entry to check is unknown' }
        $result.ActivationSha = Get-PranataRemediationActivation $Root $MainSha $Id
        if (-not $result.ActivationSha) { return $result }
        $activation = $result.ActivationSha
        $result.CandidateDescends = Test-PranataAncestor $Root $activation $HeadSha
        $log = @(& git -C $Root log --no-renames --format=%H "$activation..$MainSha" -- @($Entry.Paths) 2>$null)
        if ($LASTEXITCODE -ne 0) { throw 'git log of the entry paths since the activation commit failed' }
        $result.PathCommits = @($log | Where-Object { $_ })
        $limit = $script:RemediationListLimit
        $listing = @(Invoke-PranataGhCommandJson @('pr', 'list', '--repo', $Repo, '--state', 'merged', '--base', 'main', '--limit', [string]$limit, '--json', 'number,headRefName,headRefOid,body'))
        if ($listing.Count -ge $limit) { throw "the merged PR listing reached its limit of $limit and may be incomplete" }
        $seen = @{}
        $prs = New-Object System.Collections.ArrayList
        $family = New-Object System.Collections.ArrayList
        foreach ($pr in $listing) {
            if ($null -eq $pr) { continue }
            $number = [string]$pr.number
            $head = [string]$pr.headRefOid
            if ($number -cnotmatch '^[1-9][0-9]*$' -or $head -cnotmatch '^[0-9a-f]{40}$' -or -not $pr.headRefName) { throw 'the merged PR listing holds an entry without a number, branch or head SHA' }
            if ($seen.ContainsKey($number)) { throw "the merged PR listing names #$number twice" }
            $seen[$number] = $true
            $branch = [string]$pr.headRefName
            $isFamily = Test-PranataRemediationBranch $branch $Entry.Branch
            $hasMarker = ([string]$pr.body -cmatch "(?m)^Remediation: $Id entry ")
            if (-not $isFamily -and -not $hasMarker) { continue }
            # A PR older than the activation commit never counts, whatever its branch or body (the #49 and #51 case).
            $descends = Test-PranataAncestor $Root $activation $head
            $onMain = $false
            if ($descends) { $onMain = Test-PranataAncestor $Root $head $MainSha }
            if ($isFamily -and $descends -and $onMain) { [void]$family.Add(@{ Number = [int]$number; Branch = $branch; HeadSha = $head }) }
            if (-not $hasMarker) { continue }
            $changed = @()
            if ($descends -and $onMain) {
                # The PR base comes from Git: HEAD~<commit count>, and the commits GitHub lists must be exactly that range.
                $view = Invoke-PranataGhCommandJson @('pr', 'view', $number, '--repo', $Repo, '--json', 'commits')
                $oids = @(@($view.commits) | Where-Object { $_ } | ForEach-Object { [string]$_.oid })
                if ($oids.Count -eq 0 -or @($oids | Where-Object { $_ -cnotmatch '^[0-9a-f]{40}$' }).Count -gt 0) { throw "PR #$number lists no usable commits" }
                if ($oids.Count -ge $script:RemediationCommitLimit) { throw "PR #$number reaches the commit listing limit of $($script:RemediationCommitLimit)" }
                $before = [string](& git -C $Root rev-parse --verify --quiet "$head~$($oids.Count)" 2>$null)
                if ($LASTEXITCODE -ne 0 -or $before -cnotmatch '^[0-9a-f]{40}$') { throw "the base of PR #$number could not be resolved in Git" }
                $range = @(& git -C $Root rev-list "$before..$head" 2>$null)
                if ($LASTEXITCODE -ne 0) { throw "git rev-list of PR #$number failed" }
                if ((@($range | Sort-Object -CaseSensitive) -join ',') -cne (@($oids | Sort-Object -CaseSensitive) -join ',')) { throw "the commits of PR #$number on GitHub differ from $before..$head in Git" }
                $changed = @(Get-PranataChangedFiles $Root @($before, $head))
                if (@($changed).Count -eq 0) { throw "PR #$number changes no file in Git" }
            }
            [void]$prs.Add(@{
                Number = [int]$number; Branch = $branch; Body = [string]$pr.body; HeadSha = $head
                DescendsFromActivation = $descends; AncestorOfMain = $onMain; ChangedFiles = @($changed)
            })
        }
        $result.Prs = $prs.ToArray()
        $result.FamilyPrs = $family.ToArray()
    } catch {
        $result.Error = $_.Exception.Message
    }
    return $result
}

# Collects every fact Get-PranataRemediationFailures judges, for the entry the PR body names.
function Get-PranataRemediationFacts {
    param(
        [string]$Root, [string]$Repo, [string]$Records, [string]$PrBody, [string]$TaskId, [string]$Branch, [string]$Mode,
        [bool]$Bootstrap, $CheckpointTaskId, $CheckpointRemediation, [string[]]$Changed, [string]$MainSha, [string]$HeadSha,
        $Review, [string]$WorkDir
    )
    $facts = @{
        Records = $Records; PrBody = $PrBody; TaskId = $TaskId; Branch = $Branch; Mode = $Mode; Bootstrap = $Bootstrap
        CheckpointTaskId = $CheckpointTaskId; CheckpointRemediation = $CheckpointRemediation; ChangedFiles = $Changed
        TaskFiles = @{}; Blobs = @{ Main = @{}; SourceBase = @{}; SourceHead = @{}; Head = @{} }
        Review = $Review; DeltaSha256 = $null; Publication = $null
    }
    foreach ($file in @(@($Changed) | Where-Object { $_ -cmatch '^docs/11-tasks/TASK-[0-9]{3}\.md$' })) {
        $facts.TaskFiles[$file] = @{ Main = (Get-PranataBlobText $Root $MainSha $file); Head = (Get-PranataBlobText $Root $HeadSha $file) }
    }
    $resolved = Resolve-PranataRemediationEntry -Records $Records -PrBody $PrBody -TaskId $TaskId -Branch $Branch
    if (-not $resolved.Entry) { return $facts }
    $entry = $resolved.Entry
    if ($entry.SourceHead) {
        foreach ($path in $entry.Paths) {
            $facts.Blobs.Main[$path] = Get-PranataBlobId $Root $MainSha $path
            $facts.Blobs.SourceBase[$path] = Get-PranataBlobId $Root $entry.SourceBase $path
            $facts.Blobs.SourceHead[$path] = Get-PranataBlobId $Root $entry.SourceHead $path
            $facts.Blobs.Head[$path] = Get-PranataBlobId $Root $HeadSha $path
        }
        if ($Review -and (Test-PranataSame ([string]$Review.equivalence) 'DELTA')) {
            try { $facts.DeltaSha256 = Get-PranataRemediationDeltaSha256 -Root $Root -From $entry.SourceHead -To $HeadSha -Paths $entry.Paths -WorkDir $WorkDir }
            catch { $facts.DeltaSha256 = $null }
        }
    }
    $facts.Publication = Get-PranataRemediationPublication -Root $Root -Repo $Repo -MainSha $MainSha -HeadSha $HeadSha -Id $resolved.Id -Entry $entry
    return $facts
}
