# Project-scoped headless runner (EXECUTOR_OPERATING_MODEL section 9). One instance, one Task at a time.
# It never uses the bypass permission mode: tools outside the allowlist are refused by dontAsk.
# Outside AUTONOMOUS mode it refuses to spawn model steps unless -AllowSupervisedStep is given by a test.
param(
    [switch]$Start,
    [switch]$Once,
    [int]$MaxSteps = 0,
    [int]$MaxTurns = 40,
    [int]$StepTimeoutMinutes = 90,
    [int]$WaitPollSeconds = 300,
    [string]$Root,
    [string]$ClaudeCommand = 'claude',
    [string]$Model,
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
    'PowerShell(powershell -NoProfile -File scripts\executor\merge-verified.ps1*)',
    'Bash(git *)', 'Bash(gh *)', 'Bash(php artisan *)', 'Bash(composer *)', 'Bash(npm *)', 'Bash(npx *)'
) -join ','

$guardGlobs = @('.github/workflows/**', '.claude/**', '.githooks/**', 'scripts/executor/**', '.pranata/executor/hold.json')
$disallowedTools = (@($guardGlobs | ForEach-Object { "Edit($_)"; "Write($_)" })) -join ','

function Write-RunnerLog([string]$Message) {
    $line = "{0} {1}" -f (Get-Date).ToUniversalTime().ToString('o'), $Message
    Add-Content -LiteralPath (Join-Path $paths.Logs 'runner.log') -Value $line
    Write-Output $line
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

# Runs one headless step with a wall-clock limit; only the PID started here is ever stopped.
function Invoke-HeadlessStep($Checkpoint) {
    $stamp = (Get-Date).ToUniversalTime().ToString('yyyyMMddTHHmmssZ')
    $outFile = Join-Path $paths.Logs "step-$stamp.json"
    $errFile = Join-Path $paths.Logs "step-$stamp.err"
    $promptFile = Join-Path $paths.Dir 'step-prompt.txt'
    [IO.File]::WriteAllText($promptFile, (New-StepPrompt $Checkpoint), (New-Object Text.UTF8Encoding($false)))

    # Project settings only and no MCP: same guardrails every step, and no extra server processes on the Owner's PC.
    $argLine = @('-p', '--output-format', 'json', '--permission-mode', 'dontAsk', '--max-turns', $MaxTurns,
        '--setting-sources', 'project', '--strict-mcp-config', '--allowedTools', "`"$allowedTools`"", '--disallowedTools', "`"$disallowedTools`"")
    if ($Model) { $argLine += @('--model', $Model) }
    if ($Checkpoint.session_id -and $Checkpoint.resume_session) { $argLine += @('--resume', $Checkpoint.session_id) }

    $resolved = (Get-Command $ClaudeCommand -ErrorAction Stop).Source
    $saved = $env:PRANATA_EXECUTOR_MODE
    if ($Checkpoint.mode -eq 'AUTONOMOUS') { $env:PRANATA_EXECUTOR_MODE = 'AUTONOMOUS' }
    try {
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
        $process = [Diagnostics.Process]::Start($psi)
        $bytes = (New-Object Text.UTF8Encoding($false)).GetBytes((New-StepPrompt $Checkpoint))
        $process.StandardInput.BaseStream.Write($bytes, 0, $bytes.Length)
        $process.StandardInput.Close()
        $stdoutTask = $process.StandardOutput.ReadToEndAsync()
        $stderrTask = $process.StandardError.ReadToEndAsync()
        $timedOut = -not $process.WaitForExit($StepTimeoutMinutes * 60 * 1000)
        if ($timedOut) {
            Write-RunnerLog "step exceeded $StepTimeoutMinutes min; stopping PID $($process.Id) only"
            if ($Checkpoint.stage -notin @('COMMITTING', 'PUSHING', 'MERGING')) { Stop-Process -Id $process.Id -Force }
            return @{ Result = 'timeout'; Raw = $null; ExitCode = -1 }
        }
        $stdout = $stdoutTask.Result
        [IO.File]::WriteAllText($outFile, $stdout, (New-Object Text.UTF8Encoding($false)))
        [IO.File]::WriteAllText($errFile, $stderrTask.Result, (New-Object Text.UTF8Encoding($false)))
        return @{ Result = 'finished'; Raw = $stdout; Err = $stderrTask.Result; ExitCode = $process.ExitCode }
    } finally { $env:PRANATA_EXECUTOR_MODE = $saved }
}

function Get-StepOutcome($Step) {
    $outcome = @{ Status = 'unknown'; SessionId = $null; IsError = $true; Next = $null; RateLimited = $false; ResetAt = $null }
    if ($Step.Result -eq 'timeout') { $outcome.Status = 'timeout'; return $outcome }
    $text = "$($Step.Raw)`n$($Step.Err)"
    if ($text -match '(?i)usage limit|rate[_ ]limit|limit reached|too many requests|overloaded') { $outcome.RateLimited = $true }
    if ($text -match '(?i)resets?( at)?\s+"?([0-9T:\-\.Z+]+)') { $outcome.ResetAt = $Matches[2] }
    try { $json = $Step.Raw | ConvertFrom-Json } catch { $outcome.Status = 'unparsable'; return $outcome }
    if ($json.PSObject.Properties['session_id']) { $outcome.SessionId = $json.session_id }
    if ($json.PSObject.Properties['is_error']) { $outcome.IsError = [bool]$json.is_error }
    $resultText = ''
    if ($json.PSObject.Properties['result']) { $resultText = [string]$json.result }
    $statusLines = @($resultText -split '\r?\n' | Where-Object { $_ -match '^\s*\{.*"result"\s*:' })
    if ($statusLines.Count -gt 0) {
        try {
            $status = $statusLines[-1] | ConvertFrom-Json
            $outcome.Status = [string]$status.result
            $outcome.Next = [string]$status.next
        } catch { $outcome.Status = 'unparsable' }
    } elseif ($json.PSObject.Properties['subtype']) { $outcome.Status = [string]$json.subtype }
    return $outcome
}

if (-not $Start) { Write-Output 'Use -Start. See docs/11-tasks/EXECUTOR_BOOTSTRAP.md section 5.6.'; exit 2 }
if (-not (Enter-PranataLock $paths.RunnerLock)) {
    Write-Output "REFUSED: another runner holds $($paths.RunnerLock)"
    exit 3
}
try {
    if (-not (Test-PranataIdentity $Root)) { Write-RunnerLog 'STOP: Git identity mismatch (GOV-08)'; exit 4 }
    $steps = 0
    while ($true) {
        $hold = Read-PranataJson $paths.Hold
        $checkpoint = Get-PranataCheckpoint $Root
        if ($hold -and $hold.mode -eq 'IMMEDIATE_SAFE_SHUTDOWN') { Write-RunnerLog "HOLD $($hold.mode): stopping"; break }
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
        }
        Save-PranataCheckpoint $Root $checkpoint

        if ($outcome.Status -in @('timeout', 'unparsable', 'unknown', 'error_during_execution', 'error_max_turns') -and -not $outcome.RateLimited) {
            Write-RunnerLog "step ended without a usable status ($($outcome.Status)); stopping for the watcher to re-evaluate"
            break
        }
        if ($Once) { break }
    }
} finally {
    Exit-PranataLock $paths.RunnerLock
}
