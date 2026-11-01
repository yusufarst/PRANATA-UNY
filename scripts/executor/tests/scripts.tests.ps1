# Offline tests for merge gate, commit-msg hook, runner, watcher and hold (TAC-02, TAC-03, TAC-05, TAC-06).
# Everything runs in a throwaway folder %TEMP%\pranata-test-<id>: a local repo plus a local bare origin.
# No network, no GitHub, no model call: the runner drives a stub command.
$ErrorActionPreference = 'Stop'
$repo = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
. (Join-Path $repo 'scripts\executor\lib\state.ps1')
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
    }
    Assert ((Get-PranataMergeFailures -Facts $good).Count -eq 0) 'gate: all checks passing gives no failure'
    $negatives = @(
        @('PrState', 'MERGED', '8.1.5'), @('PrBase', 'develop', '8.1.5'), @('IsDraft', $true, '8.1.5'), @('Authorized', $false, '8.1.5'),
        @('PrHeadSha', 'bbb', '8.1.1'), @('VerifiedSha', 'ccc', '8.1.1'), @('CiGateConclusion', 'failure', '8.1.2'), @('CiGateConclusion', 'missing', '8.1.2'),
        @('ReviewSha', 'old', '8.1.3'), @('ReviewStatus', 'PENDING', '8.1.3'), @('OpenBlockingFindings', 1, '8.1.3'), @('IndependentReviewDone', $false, '8.1.3'),
        @('CodexStatus', 'TIMEOUT', '8.1.3'), @('CodexStatus', 'FAILED', '8.1.3'), @('MainIsAncestor', $false, '8.1.4'), @('CheckedMainSha', 'old', '8.1.4'),
        @('GuardPathsTouched', @('.claude/settings.json'), '8.1.6'), @('CommitIdentityProblems', @('aaa author is x'), 'GOV-08')
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

    # --- Disposable repo with the real commit-msg hook, under the machine autocrlf setting.
    $origin = Join-Path $work 'origin.git'
    $clone = Join-Path $work 'clone'
    & git init --bare --quiet --initial-branch=main $origin
    & git init --quiet --initial-branch=main $clone
    & git -C $clone remote add origin $origin
    Copy-Item -Recurse (Join-Path $repo '.githooks') (Join-Path $clone '.githooks')
    & git -C $clone config core.hooksPath .githooks

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
Add-Content -LiteralPath $log -Value (($args -join ' ') + ' MODE=' + $env:PRANATA_EXECUTOR_MODE)
if (Test-Path (Join-Path $PWD '.pranata\stub-rate-limit')) { Write-Output '{"type":"result","is_error":true,"session_id":"s-limit","result":"usage limit reached"}'; exit 1 }
if (Test-Path (Join-Path $PWD '.pranata\stub-sleep')) { Start-Sleep -Seconds 120 }
$status = '{\"task\":\"TASK-999\",\"stage\":\"IN_PROGRESS\",\"result\":\"progressed\",\"next\":\"lanjut langkah berikut\"}'
Write-Output ('{"type":"result","is_error":false,"session_id":"s-1","num_turns":1,"result":"ok\n' + $status + '"}')
'@ | Set-Content -LiteralPath $stub -Encoding ASCII

    $scriptsDir = Join-Path $repo 'scripts\executor'
    $state = Initialize-PranataStateDirs $clone
    $cp = New-PranataCheckpoint
    $cp.mode = 'AUTONOMOUS'; $cp.task_id = 'TASK-999'; $cp.stage = 'IN_PROGRESS'; $cp.branch = 'chore/x'; $cp.head_sha = (& git -C $clone rev-parse HEAD); $cp.next_action = 'mulai'
    Save-PranataCheckpoint $clone $cp

    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub)
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

    # Hold: both modes stop the runner; resume continues the same Task from the checkpoint.
    $h = Invoke-Script (Join-Path $scriptsDir 'hold.ps1') @('-Mode', 'IMMEDIATE_SAFE_SHUTDOWN', '-Reason', 'uji', '-Root', $clone)
    Assert ($h.Code -eq 0 -and (Test-Path -LiteralPath $state.Hold)) 'hold: IMMEDIATE written'
    $before = @(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub)
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
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub)
    Assert ($r.Code -eq 0 -and $r.Out -match 'status=progressed') 'resume: runner continues the Task after release'

    $h = Invoke-Script (Join-Path $scriptsDir 'hold.ps1') @('-Mode', 'SHUTDOWN_AFTER_CURRENT_TASK', '-Reason', 'uji', '-Root', $clone)
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub)
    Assert ($r.Out -match 'status=progressed') 'hold: SHUTDOWN_AFTER_CURRENT_TASK lets the active Task continue'
    $doneCp = Get-PranataCheckpoint $clone; $doneCp.stage = 'DONE'; Save-PranataCheckpoint $clone $doneCp
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Root', $clone, '-ClaudeCommand', $stub)
    Assert ($r.Out -match 'SHUTDOWN_AFTER_CURRENT_TASK: Task done, stopping') 'hold: SHUTDOWN_AFTER_CURRENT_TASK stops once done'
    [void](Invoke-Script (Join-Path $scriptsDir 'release-hold.ps1') @('-Root', $clone))
    $doneCp = Get-PranataCheckpoint $clone; $doneCp.stage = 'IN_PROGRESS'; Save-PranataCheckpoint $clone $doneCp

    # Runner exclusivity with a live foreign holder (this process), then a stale lock from a dead PID.
    [void](Enter-PranataLock $state.RunnerLock)
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub)
    Assert ($r.Code -eq 3 -and $r.Out -match 'REFUSED') 'runner: second instance refused'
    Exit-PranataLock $state.RunnerLock
    Set-Content -LiteralPath $state.RunnerLock -Value '{"pid":999999,"started":"2000-01-01T00:00:00.0000000Z"}' -Encoding ASCII
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub)
    Assert ($r.Code -eq 0) 'runner: stale lock of a dead PID recovered'

    # Usage limit: recorded, wait set, no busy loop (-Once returns immediately after).
    Set-Content -LiteralPath (Join-Path $clone '.pranata\stub-rate-limit') -Value x -Encoding ASCII
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub)
    Assert ($r.Out -match 'rate_limited=True') 'runner: usage limit detected'
    $limited = Get-PranataCheckpoint $clone
    Assert ([bool]$limited.wait_until) 'runner: wait_until set after usage limit'
    $before = @(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub)
    Assert (@(Get-Content -LiteralPath (Join-Path $clone '.pranata\stub-calls.log')).Count -eq $before) 'runner: no model call while waiting for reset'
    Remove-Item -LiteralPath (Join-Path $clone '.pranata\stub-rate-limit')
    $cp2 = Get-PranataCheckpoint $clone; $cp2.wait_until = $null; Save-PranataCheckpoint $clone $cp2

    # Runner refuses model steps outside AUTONOMOUS.
    $cp3 = Get-PranataCheckpoint $clone; $cp3.mode = 'SUPERVISED'; Save-PranataCheckpoint $clone $cp3
    $r = Invoke-Script (Join-Path $scriptsDir 'runner.ps1') @('-Start', '-Once', '-Root', $clone, '-ClaudeCommand', $stub)
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
