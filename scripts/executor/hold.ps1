# Request a safe stop (EXECUTOR_OPERATING_MODEL section 10). Hooks and the runner honour the file.
param(
    [Parameter(Mandatory = $true)][ValidateSet('IMMEDIATE_SAFE_SHUTDOWN', 'SHUTDOWN_AFTER_CURRENT_TASK')][string]$Mode,
    [Parameter(Mandatory = $true)][string]$Reason,
    [string]$Root
)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
if (-not $Root) { $Root = Get-PranataRepoRoot }
$paths = Initialize-PranataStateDirs $Root
$hold = [ordered]@{ mode = $Mode; reason = $Reason; requested_at = (Get-Date).ToUniversalTime().ToString('o'); requested_by = 'Owner' }
Write-PranataFileAtomic $paths.Hold ($hold | ConvertTo-Json)
# Section 10: the scheduled watcher is disabled during a full stop. -Root marks a disposable test repo.
if ($Mode -eq 'IMMEDIATE_SAFE_SHUTDOWN' -and -not $PSBoundParameters.ContainsKey('Root')) { Set-PranataWatcherEnabled $false }
Write-Output "HOLD ACTIVE: $Mode"
