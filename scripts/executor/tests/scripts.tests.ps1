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
    $boot = $good.Clone(); $boot.Bootstrap = $true; $boot.Mode = 'SUPERVISED'; $boot.TaskId = 'TASK-001'; $boot.CiGateConclusion = 'missing'
    Assert ((Get-PranataMergeFailures -Facts $boot).Count -eq 0) 'gate: bootstrap TASK-001 SUPERVISED without ci-gate passes'
    $bootAuto = $boot.Clone(); $bootAuto.Mode = 'AUTONOMOUS'
    Assert ((Get-PranataMergeFailures -Facts $bootAuto).Count -ge 1) 'gate: bootstrap refused in AUTONOMOUS'
    $bootLate = $boot.Clone(); $bootLate.TaskId = 'TASK-005'
    Assert ((Get-PranataMergeFailures -Facts $bootLate).Count -ge 1) 'gate: bootstrap refused for TASK-005'

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
$log = Join-Path $PWD '.pranata\stub-calls.log'
Add-Content -LiteralPath $log -Value (($args -join ' ') + ' MODE=' + $env:PRANATA_EXECUTOR_MODE + ' TOKEN=' + [bool]$env:GH_TOKEN + ' GITCFG=' + $env:GIT_CONFIG_COUNT)
if (Test-Path (Join-Path $PWD '.pranata\stub-auth-error')) { Write-Output '{"type":"result","subtype":"success","is_error":true,"session_id":"s-auth","result":"Failed to authenticate"}'; exit 1 }
if (Test-Path (Join-Path $PWD '.pranata\stub-rate-limit')) { Write-Output '{"type":"result","is_error":true,"session_id":"s-limit","result":"usage limit reached"}'; exit 1 }
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

    # A failed step reported as subtype=success must stop the loop, not spin on the model.
    Set-Content -LiteralPath (Join-Path $clone '.pranata\stub-auth-error') -Value x -Encoding ASCII
    $before = @(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub, '-TokenFile', $tokenFile, '-MaxSteps', '5')
    Assert ($r.Out -match 'status=error') 'runner: is_error with subtype=success is an error'
    Assert ((@(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count - $before) -eq 1) 'runner: exactly one call on a failed step, no retry loop'
    Remove-Item -LiteralPath (Join-Path $clone '.pranata\stub-auth-error')

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
Get-Content -LiteralPath (Join-Path $dir 'pr.json') -Raw
'@
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
