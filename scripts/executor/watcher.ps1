# Single scheduled watcher (EXECUTOR_OPERATING_MODEL sections 9.1-9.3, D-11). Cheap signals only: no model calls.
# Register/Unregister touch only the task named PRANATA-UNY-Watcher.
param(
    [switch]$Once,
    [switch]$NoStart,
    [switch]$Register,
    [switch]$Unregister,
    [int]$IdleMinutes = 45,
    [int]$IntervalMinutes = 10,
    [string]$Root
)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
if (-not $Root) { $Root = Get-PranataRepoRoot }
$paths = Initialize-PranataStateDirs $Root
$taskName = 'PRANATA-UNY-Watcher'

function Write-WatcherLog([string]$Message) {
    $line = "{0} {1}" -f (Get-Date).ToUniversalTime().ToString('o'), $Message
    Add-Content -LiteralPath (Join-Path $paths.Logs 'watcher.log') -Value $line
    Write-Output $line
}

if ($Register) {
    if (Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue) { Write-Output "ALREADY REGISTERED: $taskName"; exit 0 }
    $script = Join-Path $PSScriptRoot 'watcher.ps1'
    $action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument "-NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$script`" -Once" -WorkingDirectory $Root
    $trigger = New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) -RepetitionInterval (New-TimeSpan -Minutes $IntervalMinutes)
    $settings = New-ScheduledTaskSettingsSet -MultipleInstances IgnoreNew -StartWhenAvailable -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -ExecutionTimeLimit (New-TimeSpan -Minutes 5)
    $principal = New-ScheduledTaskPrincipal -UserId "$env:USERDOMAIN\$env:USERNAME" -LogonType Interactive -RunLevel Limited
    [void](Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Settings $settings -Principal $principal)
    Write-Output "REGISTERED: $taskName every $IntervalMinutes min"
    exit 0
}
if ($Unregister) {
    if (Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue) { Unregister-ScheduledTask -TaskName $taskName -Confirm:$false; Write-Output "UNREGISTERED: $taskName" }
    exit 0
}
if (-not $Once) { Write-Output 'Use -Once, -Register or -Unregister.'; exit 2 }
if (-not (Enter-PranataLock $paths.WatcherLock)) { Write-Output 'REFUSED: another watcher is running'; exit 3 }

try {
    $hold = Read-PranataJson $paths.Hold
    # SHUTDOWN_AFTER_CURRENT_TASK still finishes the active Task, so a dead runner is restarted until it is DONE.
    if ($hold -and $hold.mode -eq 'IMMEDIATE_SAFE_SHUTDOWN') { Write-WatcherLog "hold active ($($hold.mode)); not restarting anything"; exit 0 }

    $checkpoint = Get-PranataCheckpoint $Root
    $runner = Get-PranataLockHolder $paths.RunnerLock
    $step = Get-PranataLockHolder $paths.StepLock

    $heartbeatAge = [double]::PositiveInfinity
    if (Test-Path -LiteralPath $paths.Heartbeat) { $heartbeatAge = ((Get-Date) - (Get-Item -LiteralPath $paths.Heartbeat).LastWriteTime).TotalMinutes }

    # PRANATA-owned activity only: tests, migrations and builds name this repository on their command line.
    $rootPattern = [regex]::Escape($Root)
    $active = @(Get-CimInstance Win32_Process -ErrorAction SilentlyContinue | Where-Object {
        $_.ProcessId -ne $PID -and $_.CommandLine -and $_.CommandLine -match $rootPattern -and $_.CommandLine -notmatch 'watcher\.ps1'
    })
    $waiting = $checkpoint.wait_until -and ([datetime]$checkpoint.wait_until) -gt (Get-Date)
    # Section 9.2: idle needs all of these. No code change is not evidence, and neither is a quiet step.
    $idle = (-not $runner) -and (-not $step) -and ($active.Count -eq 0) -and (-not $waiting) -and ($heartbeatAge -ge $IdleMinutes)

    Write-WatcherLog ("runner={0} step={1} active={2} waiting={3} heartbeat_min={4:N1} idle={5} stage={6}" -f [bool]$runner, [bool]$step, $active.Count, [bool]$waiting, $heartbeatAge, $idle, $checkpoint.stage)

    if (-not $idle) { exit 0 }
    if ($checkpoint.mode -ne 'AUTONOMOUS') { exit 0 }
    if (-not $checkpoint.task_id -or $checkpoint.stage -in @('NONE', 'BLOCKED')) { exit 0 }
    if ($checkpoint.stage -eq 'DONE' -and $hold) { exit 0 }
    if ($NoStart) { Write-WatcherLog 'runner is down; -NoStart given'; exit 0 }

    Write-WatcherLog 'runner is down with eligible work and no hold; starting one runner'
    $script = Join-Path $PSScriptRoot 'runner.ps1'
    Start-Process -FilePath 'powershell.exe' -ArgumentList "-NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$script`" -Start" -WorkingDirectory $Root -WindowStyle Hidden
} finally {
    Exit-PranataLock $paths.WatcherLock
}
