# Owner-only release of a hold, after verifying state (EXECUTOR_OPERATING_MODEL section 10).
param([string]$Root)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
if (-not $Root) { $Root = Get-PranataRepoRoot }
if ($env:PRANATA_EXECUTOR_MODE -eq 'AUTONOMOUS') { throw 'Releasing a hold is Owner-only and cannot run from an AUTONOMOUS step.' }
$paths = Get-PranataStatePaths $Root
if (-not (Test-Path -LiteralPath $paths.Hold)) { Write-Output 'NO HOLD ACTIVE'; exit 0 }

$checkpoint = Get-PranataCheckpoint $Root
if ($checkpoint.task_id -and $checkpoint.head_sha) {
    $head = (& git -C $Root rev-parse HEAD)
    if ($head -ne $checkpoint.head_sha) {
        Write-Output "WARNING: HEAD $head differs from checkpoint head_sha $($checkpoint.head_sha); resume must reconcile before acting."
    }
}
foreach ($lockPath in @($paths.RunnerLock, $paths.WatcherLock)) {
    if ((Test-Path -LiteralPath $lockPath) -and -not (Get-PranataLockHolder $lockPath)) {
        Remove-Item -LiteralPath $lockPath -Force
        Write-Output "Removed stale lock: $lockPath"
    }
}
Remove-Item -LiteralPath $paths.Hold -Force
Write-Output 'HOLD RELEASED'
