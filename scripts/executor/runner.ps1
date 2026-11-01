# Project-scoped headless runner (EXECUTOR_OPERATING_MODEL section 9). One instance, one Task at a time.
# It never uses the bypass permission mode: tools outside the allowlist are refused by dontAsk.
# Outside AUTONOMOUS mode it refuses to spawn model steps unless -AllowSupervisedStep is given by a test.
param(
    [switch]$Start,
    [switch]$Once,
    [int]$MaxSteps = 0,
    [int]$MaxTurns = 40,
    [double]$StepTimeoutMinutes = 90,
    [int]$WaitPollSeconds = 300,
    [int]$MaxNoProgressSteps = 3,
    [int]$MaxUnmovedSteps = 10,
    [string]$Root,
    [string]$ClaudeCommand = 'claude',
    [string]$Model,
    [string]$TokenFile,
    [switch]$AllowSupervisedStep
)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
if (-not $Root) { $Root = Get-PranataRepoRoot }
$paths = Initialize-PranataStateDirs $Root

$allowedTools = @(
    'Read', 'Edit', 'Write', 'Glob', 'Grep', 'Agent',
    'PowerShell(git *)', 'PowerShell(gh *)',
    'PowerShell(php artisan *)', 'PowerShell(composer *)', 'PowerShell(npm *)', 'PowerShell(npx *)',
    'PowerShell(powershell -NoProfile -File scripts\executor\status.ps1*)',
    'PowerShell(powershell -NoProfile -File scripts\executor\stage.ps1*)',
    'PowerShell(powershell -NoProfile -File scripts\executor\merge-verified.ps1*)',
    'Bash(git *)', 'Bash(gh *)', 'Bash(php artisan *)', 'Bash(composer *)', 'Bash(npm *)', 'Bash(npx *)'
) -join ','

$guardGlobs = @('.github/workflows/**', '.claude/**', '.githooks/**', 'scripts/executor/**', '.pranata/executor/hold.json')
$disallowedTools = (@($guardGlobs | ForEach-Object { "Edit($_)"; "Write($_)" })) -join ','

function Write-RunnerLog([string]$Message) {
    $line = "{0} {1}" -f (Get-Date).ToUniversalTime().ToString('o'), $Message
    Add-Content -LiteralPath (Join-Path $paths.Logs 'runner.log') -Value $line
    # Not Write-Output: inside a function that would become part of its return value.
    [Console]::Out.WriteLine($line)
}

function Write-ContextPack($Checkpoint) {
    $text = @(
        '# Paket konteks eksekusi (dibangkitkan runner)',
        "Otorisasi: $($Checkpoint.authorization_id) | Mode: $($Checkpoint.mode)",
        "Task: $($Checkpoint.task_id) | Tahap: $($Checkpoint.stage)",
        "Kontrak: docs/11-tasks/$($Checkpoint.task_id).md",
        "Cabang: $($Checkpoint.branch) | HEAD: $($Checkpoint.head_sha)",
        "PR: $($Checkpoint.pr_number) | CI: $($Checkpoint.ci_run_ids -join ',') | Codex: $($Checkpoint.codex_status) | Review: $($Checkpoint.review_status)",
        "Aksi terakhir: $($Checkpoint.last_action) (idempoten: $($Checkpoint.last_action_idempotent))",
        "Tindakan berikutnya: $($Checkpoint.next_action)"
    ) -join "`r`n"
    Write-PranataFileAtomic $paths.Context $text
}

function New-StepPrompt($Checkpoint) {
    return @"
Peran: EXECUTION AGENT - AUTONOMOUS. Otorisasi: $($Checkpoint.authorization_id).
Baca .pranata/executor/context.md, lalu verifikasi keadaan nyata (cabang, HEAD, PR, CI, status merge) sebelum bertindak.
Task: $($Checkpoint.task_id), tahap: $($Checkpoint.stage). Tindakan berikutnya: $($Checkpoint.next_action).
Kerjakan sampai tahap berikutnya atau sampai menunggu hasil eksternal; perbarui checkpoint pada setiap transisi; patuhi hold.
Jangan mengulang perintah yang tidak idempoten tanpa memverifikasi hasil sebelumnya. Keluarkan baris status JSON akhir:
{"task":"$($Checkpoint.task_id)","stage":"$($Checkpoint.stage)","result":"progressed|waiting|blocked|done","next":"<...>"}
"@
}

function Get-DescendantProcesses([int]$ParentId) {
    $all = @(Get-CimInstance Win32_Process -ErrorAction SilentlyContinue)
    $found = New-Object System.Collections.ArrayList
    $queue = New-Object System.Collections.Queue
    $queue.Enqueue($ParentId)
    while ($queue.Count -gt 0) {
        $current = $queue.Dequeue()
        foreach ($child in @($all | Where-Object { $_.ParentProcessId -eq $current })) {
            [void]$found.Add($child)
            $queue.Enqueue([int]$child.ProcessId)
        }
    }
    return ,$found
}

function Test-CriticalChild([int]$ParentId) {
    foreach ($child in (Get-DescendantProcesses $ParentId)) {
        if ([string]$child.CommandLine -match '(?i)\bgit(\.exe)?"?\s.*\b(push|commit)\b|merge-verified') { return $true }
    }
    return $false
}

# Runs one headless step with a wall-clock limit. The runner keeps its lock until the step is really gone,
# and step.lock lets the watcher see a step that outlived a dead runner, so two sessions can never overlap.
function Invoke-HeadlessStep($Checkpoint) {
    $stamp = (Get-Date).ToUniversalTime().ToString('yyyyMMddTHHmmssZ')
    $outFile = Join-Path $paths.Logs "step-$stamp.json"
    $errFile = Join-Path $paths.Logs "step-$stamp.err"

    # Project settings only and no MCP: same guardrails every step, and no extra server processes on the Owner's PC.
    $argLine = @('-p', '--output-format', 'json', '--permission-mode', 'dontAsk', '--max-turns', $MaxTurns,
        '--setting-sources', 'project', '--strict-mcp-config', '--allowedTools', "`"$allowedTools`"", '--disallowedTools', "`"$disallowedTools`"")
    if ($Model) { $argLine += @('--model', $Model) }
    if ($Checkpoint.session_id -and $Checkpoint.resume_session) { $argLine += @('--resume', $Checkpoint.session_id) }

    $resolved = (Get-Command $ClaudeCommand -ErrorAction Stop).Source
    $psi = New-Object Diagnostics.ProcessStartInfo
    if ($resolved -match '\.(ps1)$') {
        $psi.FileName = 'powershell.exe'
        $psi.Arguments = "-NoProfile -ExecutionPolicy Bypass -File `"$resolved`" " + ($argLine -join ' ')
    } elseif ($resolved -match '\.(cmd|bat)$') {
        $psi.FileName = 'cmd.exe'
        $psi.Arguments = "/c `"`"$resolved`" " + ($argLine -join ' ') + '"'
    } else {
        $psi.FileName = $resolved
        $psi.Arguments = ($argLine -join ' ')
    }
    $psi.WorkingDirectory = $Root
    $psi.UseShellExecute = $false
    $psi.RedirectStandardInput = $true
    $psi.RedirectStandardOutput = $true
    $psi.RedirectStandardError = $true
    # A runner started from another Claude session must not inherit that session's host wiring.
    foreach ($name in @($psi.EnvironmentVariables.Keys)) {
        if ($name -match '^(CLAUDE|ANTHROPIC_BASE_URL)' ) { $psi.EnvironmentVariables.Remove($name) }
    }
    if ($Checkpoint.mode -eq 'AUTONOMOUS') { $psi.EnvironmentVariables['PRANATA_EXECUTOR_MODE'] = 'AUTONOMOUS' }
    # D-27: the restricted token reaches this child only; the Owner's login and global Git config stay untouched.
    if ($runnerToken) {
        $psi.EnvironmentVariables['GH_TOKEN'] = $runnerToken
        $psi.EnvironmentVariables['GIT_CONFIG_COUNT'] = '2'
        $psi.EnvironmentVariables['GIT_CONFIG_KEY_0'] = 'credential.https://github.com.helper'
        $psi.EnvironmentVariables['GIT_CONFIG_VALUE_0'] = ''
        $psi.EnvironmentVariables['GIT_CONFIG_KEY_1'] = 'credential.https://github.com.helper'
        $psi.EnvironmentVariables['GIT_CONFIG_VALUE_1'] = '!gh auth git-credential'
    }

    $process = [Diagnostics.Process]::Start($psi)
    Write-PranataFileAtomic $paths.StepLock (New-PranataProcessStamp $process.Id)
    $bytes = (New-Object Text.UTF8Encoding($false)).GetBytes((New-StepPrompt $Checkpoint))
    $process.StandardInput.BaseStream.Write($bytes, 0, $bytes.Length)
    $process.StandardInput.Close()
    $stdoutTask = $process.StandardOutput.ReadToEndAsync()
    $stderrTask = $process.StandardError.ReadToEndAsync()

    $limitMs = [int]($StepTimeoutMinutes * 60 * 1000)
    if (-not $process.WaitForExit($limitMs)) {
        if (Test-CriticalChild $process.Id) {
            # Section 9.2: never stop a step inside commit, push or merge. Give it one more window, then stop safely.
            Write-RunnerLog "step passed the limit inside commit/push/merge; waiting one more window, PID $($process.Id) left alone"
            if (-not $process.WaitForExit($limitMs)) {
                $hold = [ordered]@{ mode = 'IMMEDIATE_SAFE_SHUTDOWN'; reason = "step PID $($process.Id) stuck inside commit/push/merge; verify state by hand"; requested_at = (Get-Date).ToUniversalTime().ToString('o'); requested_by = 'Runner' }
                Write-PranataFileAtomic $paths.Hold ($hold | ConvertTo-Json)
                Write-RunnerLog "HOLD written: step PID $($process.Id) stuck inside commit/push/merge"
                return @{ Result = 'stuck_critical'; Raw = $null; Err = $null; ExitCode = -1 }
            }
        } else {
            Write-RunnerLog "step exceeded $StepTimeoutMinutes min; stopping PID $($process.Id) and its children only"
            $previous = $ErrorActionPreference
            $ErrorActionPreference = 'Continue'
            & taskkill.exe /T /F /PID $process.Id 2>&1 | Out-Null
            $ErrorActionPreference = $previous
            [void]$process.WaitForExit(30000)
            Remove-Item -LiteralPath $paths.StepLock -Force -ErrorAction SilentlyContinue
            return @{ Result = 'timeout'; Raw = $null; Err = $null; ExitCode = -1 }
        }
    }
    $process.WaitForExit()
    Remove-Item -LiteralPath $paths.StepLock -Force -ErrorAction SilentlyContinue
    $stdout = $stdoutTask.Result
    [IO.File]::WriteAllText($outFile, $stdout, (New-Object Text.UTF8Encoding($false)))
    [IO.File]::WriteAllText($errFile, $stderrTask.Result, (New-Object Text.UTF8Encoding($false)))
    return @{ Result = 'finished'; Raw = $stdout; Err = $stderrTask.Result; ExitCode = $process.ExitCode }
}

function Get-StepOutcome($Step) {
    $outcome = @{ Status = 'unknown'; SessionId = $null; IsError = $true; Next = $null; RateLimited = $false; ResetAt = $null }
    if ($Step.Result -in @('timeout', 'stuck_critical')) { $outcome.Status = $Step.Result; return $outcome }
    $json = $null
    try { $json = $Step.Raw | ConvertFrom-Json } catch { $outcome.Status = 'unparsable' }
    if ($json) {
        if ($json.PSObject.Properties['session_id']) { $outcome.SessionId = $json.session_id }
        if ($json.PSObject.Properties['is_error']) { $outcome.IsError = [bool]$json.is_error }
    }
    # Only a failed step can be a usage limit: a healthy step may simply mention the words.
    if ($outcome.IsError) {
        $text = "$($Step.Raw)`n$($Step.Err)"
        if ($text -match '(?i)usage limit|rate[_ ]limit|limit reached|too many requests|overloaded') { $outcome.RateLimited = $true }
        if ($text -match '(?i)resets?( at)?\s+"?([0-9T:\-\.Z+]+)') { $outcome.ResetAt = $Matches[2] }
    }
    if (-not $json) { return $outcome }

    $resultText = ''
    if ($json.PSObject.Properties['result']) { $resultText = [string]$json.result }
    $statusLines = @($resultText -split '\r?\n' | Where-Object { $_ -match '^\s*\{.*"result"\s*:' })
    if ($statusLines.Count -gt 0) {
        try {
            $status = $statusLines[-1] | ConvertFrom-Json
            $outcome.Status = [string]$status.result
            $outcome.Next = [string]$status.next
        } catch { $outcome.Status = 'unparsable' }
    } elseif ($outcome.IsError) {
        # The CLI can report is_error=true with subtype=success (an expired login does), so is_error decides.
        $outcome.Status = 'error'
    } else {
        $outcome.Status = 'no_status'
    }
    return $outcome
}

# IMMEDIATE_SAFE_SHUTDOWN (section 10): keep unfinished work as a local WIP commit on the Task branch, never pushed.
function Save-WipCheckpoint($Checkpoint) {
    $branch = (& git -C $Root rev-parse --abbrev-ref HEAD)
    if ($branch -in @('main', 'HEAD') -or -not $Checkpoint.task_id) { return }
    if (-not (& git -C $Root status --porcelain)) { return }
    if ($Checkpoint.branch -and $branch -ne $Checkpoint.branch) {
        Write-RunnerLog "WIP commit skipped: on $branch, checkpoint names $($Checkpoint.branch); changes remain in the working tree"
        return
    }
    if (-not (Test-PranataIdentity $Root)) { Write-RunnerLog 'WIP commit skipped: Git identity mismatch (GOV-08)'; return }
    $taskSlug = ([string]$Checkpoint.task_id).ToLowerInvariant()
    # No autocrlf override here: with a bulk add it would rewrite every line of every CRLF working copy.
    & git -C $Root add -A
    & git -C $Root commit --quiet -m "wip(${taskSlug}): checkpoint $($Checkpoint.stage)"
    if ($LASTEXITCODE -ne 0) { Write-RunnerLog 'WIP commit failed; changes remain in the working tree'; return }
    $Checkpoint.head_sha = (& git -C $Root rev-parse HEAD)
    $Checkpoint.last_action = 'wip checkpoint commit during hold'
    Save-PranataCheckpoint $Root $Checkpoint
    Write-RunnerLog "WIP commit $($Checkpoint.head_sha) on $branch (not pushed)"
}

if (-not $Start) { Write-Output 'Use -Start. See docs/11-tasks/EXECUTOR_BOOTSTRAP.md section 5.6.'; exit 2 }
if (-not (Enter-PranataLock $paths.RunnerLock)) {
    Write-Output "REFUSED: another runner holds $($paths.RunnerLock)"
    exit 3
}
try {
    if (Get-PranataLockHolder $paths.StepLock) {
        Write-RunnerLog 'REFUSED: a previous step is still running; not starting another'
        exit 3
    }
    if (-not (Test-PranataIdentity $Root)) { Write-RunnerLog 'STOP: Git identity mismatch (GOV-08)'; exit 4 }
    if (-not $TokenFile) { $TokenFile = Get-PranataTokenPath }
    $runnerToken = Read-PranataRunnerToken $TokenFile
    if (-not $runnerToken -and (Get-PranataCheckpoint $Root).mode -eq 'AUTONOMOUS') {
        Write-RunnerLog "STOP: AUTONOMOUS needs the restricted runner token (D-27); run scripts\executor\install-runner-token.ps1"
        exit 5
    }
    $steps = 0
    $noProgress = 0
    $notMoved = 0
    while ($true) {
        $hold = Read-PranataJson $paths.Hold
        $checkpoint = Get-PranataCheckpoint $Root
        if ($hold -and $hold.mode -eq 'IMMEDIATE_SAFE_SHUTDOWN') {
            Write-RunnerLog "HOLD $($hold.mode): stopping"
            break
        }
        if (-not $checkpoint.task_id -or $checkpoint.stage -in @('NONE', 'BLOCKED')) {
            Write-RunnerLog "nothing runnable (task=$($checkpoint.task_id) stage=$($checkpoint.stage))"
            break
        }
        if ($checkpoint.stage -eq 'DONE') {
            if ($hold -and $hold.mode -eq 'SHUTDOWN_AFTER_CURRENT_TASK') { Write-RunnerLog 'HOLD SHUTDOWN_AFTER_CURRENT_TASK: Task done, stopping'; break }
        }
        if ($checkpoint.mode -ne 'AUTONOMOUS' -and -not $AllowSupervisedStep) {
            Write-RunnerLog "mode is $($checkpoint.mode); runner starts no model step outside AUTONOMOUS"
            break
        }
        if ($checkpoint.wait_until -and ([datetime]$checkpoint.wait_until) -gt (Get-Date)) {
            if ($Once) { Write-RunnerLog "waiting until $($checkpoint.wait_until)"; break }
            Start-Sleep -Seconds $WaitPollSeconds
            continue
        }
        if ($MaxSteps -gt 0 -and $steps -ge $MaxSteps) { Write-RunnerLog "max steps $MaxSteps reached"; break }

        $headBefore = (& git -C $Root rev-parse HEAD)
        $stageBefore = $checkpoint.stage
        $nextBefore = $checkpoint.next_action
        Write-ContextPack $checkpoint
        $steps++
        Write-RunnerLog "step $steps start task=$($checkpoint.task_id) stage=$($checkpoint.stage)"
        $step = Invoke-HeadlessStep $checkpoint
        $outcome = Get-StepOutcome $step
        Write-RunnerLog "step $steps end status=$($outcome.Status) exit=$($step.ExitCode) error=$($outcome.IsError) rate_limited=$($outcome.RateLimited)"

        # The step may have rewritten the checkpoint; keep its progress and add only what the runner observed.
        $checkpoint = Get-PranataCheckpoint $Root
        if ($outcome.SessionId) { $checkpoint.session_id = $outcome.SessionId }
        if ($outcome.RateLimited) {
            $until = (Get-Date).AddMinutes(30)
            if ($outcome.ResetAt) { try { $until = [datetime]$outcome.ResetAt } catch { } }
            $checkpoint.wait_until = $until.ToUniversalTime().ToString('o')
            $checkpoint.last_action = 'usage limit reached; waiting for reset without calling the model'
        } elseif ($outcome.Status -eq 'waiting') {
            $checkpoint.wait_until = (Get-Date).AddSeconds($WaitPollSeconds).ToUniversalTime().ToString('o')
            if ($outcome.Next) { $checkpoint.next_action = $outcome.Next }
        } elseif ($outcome.Status -eq 'blocked') {
            $checkpoint.stage = 'BLOCKED'
            if ($outcome.Next) { $checkpoint.next_action = $outcome.Next }
        } elseif ($outcome.Status -in @('progressed', 'done')) {
            $checkpoint.wait_until = $null
            if ($outcome.Next) { $checkpoint.next_action = $outcome.Next }
            $headAfter = (& git -C $Root rev-parse HEAD)
            $moved = ($headAfter -ne $headBefore) -or ($checkpoint.stage -ne $stageBefore)
            if ($moved -or $checkpoint.next_action -ne $nextBefore) { $noProgress = 0 } else { $noProgress++ }
            if ($moved) { $notMoved = 0 } else { $notMoved++ }
            # A step that only rewrites next_action is talking, not working; it gets a wider but finite budget.
            if ($noProgress -ge $MaxNoProgressSteps -or $notMoved -ge $MaxUnmovedSteps) {
                if ($checkpoint.stage -eq 'DONE') {
                    Save-PranataCheckpoint $Root $checkpoint
                    Write-RunnerLog 'Task is DONE and steps add nothing; stopping'
                    break
                }
                $checkpoint.stage = 'BLOCKED'
                $checkpoint.last_action = "no progress after $notMoved steps; stopped to protect usage"
                Save-PranataCheckpoint $Root $checkpoint
                Write-RunnerLog "BLOCKED: no progress after $notMoved steps"
                break
            }
        }
        Save-PranataCheckpoint $Root $checkpoint

        if ($outcome.Status -in @('timeout', 'stuck_critical', 'unparsable', 'unknown', 'error', 'no_status', 'blocked') -and -not $outcome.RateLimited) {
            Write-RunnerLog "step ended without a continuable status ($($outcome.Status)); stopping for the watcher to re-evaluate"
            break
        }
        if ($Once) { break }
    }
    # Every exit path passes here, so a hold raised during the last step still keeps the unfinished work.
    $holdAtExit = Read-PranataJson $paths.Hold
    if ($holdAtExit -and $holdAtExit.mode -eq 'IMMEDIATE_SAFE_SHUTDOWN') { Save-WipCheckpoint (Get-PranataCheckpoint $Root) }
} finally {
    Exit-PranataLock $paths.RunnerLock
}
