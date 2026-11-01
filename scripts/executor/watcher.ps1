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
    if ($hold) { Write-WatcherLog "hold active ($($hold.mode)); not restarting anything"; exit 0 }

    $checkpoint = Get-PranataCheckpoint $Root
    $runner = $null
    if (Test-Path -LiteralPath $paths.RunnerLock) { $runner = Get-PranataLockHolder $paths.RunnerLock }

    $heartbeatAge = [double]::PositiveInfinity
    if (Test-Path -LiteralPath $paths.Heartbeat) { $heartbeatAge = ((Get-Date) - (Get-Item -LiteralPath $paths.Heartbeat).LastWriteTime).TotalMinutes }

    # PRANATA-owned activity only: the command line must name this repository.
    $rootPattern = [regex]::Escape($Root)
    $active = @(Get-CimInstance Win32_Process -ErrorAction SilentlyContinue | Where-Object {
        $_.ProcessId -ne $PID -and $_.CommandLine -and $_.CommandLine -match $rootPattern -and $_.CommandLine -notmatch 'watcher\.ps1'
    })
    $waiting = $checkpoint.wait_until -and ([datetime]$checkpoint.wait_until) -gt (Get-Date)
    $idle = (-not $runner) -and ($active.Count -eq 0) -and (-not $waiting) -and ($heartbeatAge -ge $IdleMinutes)

    Write-WatcherLog ("runner={0} active={1} waiting={2} heartbeat_min={3:N1} idle={4} stage={5}" -f [bool]$runner, $active.Count, [bool]$waiting, $heartbeatAge, $idle, $checkpoint.stage)

    if ($runner) { exit 0 }
    if ($checkpoint.mode -ne 'AUTONOMOUS') { exit 0 }
    if (-not $checkpoint.task_id -or $checkpoint.stage -in @('NONE', 'BLOCKED', 'DONE')) { exit 0 }
    if ($waiting -or $active.Count -gt 0) { exit 0 }
    if ($NoStart) { Write-WatcherLog 'runner is down; -NoStart given'; exit 0 }

    Write-WatcherLog 'runner is down with eligible work and no hold; starting one runner'
    $script = Join-Path $PSScriptRoot 'runner.ps1'
    Start-Process -FilePath 'powershell.exe' -ArgumentList "-NoProfile -NonInteractive -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$script`" -Start" -WorkingDirectory $Root -WindowStyle Hidden
} finally {
    Exit-PranataLock $paths.WatcherLock
}
