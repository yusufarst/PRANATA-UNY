# Read-only snapshot for the Owner: checkpoint, hold, processes, branch and PR state.
param([string]$Root, [switch]$Offline)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
if (-not $Root) { $Root = Get-PranataRepoRoot }
$paths = Get-PranataStatePaths $Root
$checkpoint = Get-PranataCheckpoint $Root
$hold = Read-PranataJson $paths.Hold
$runner = if (Test-Path -LiteralPath $paths.RunnerLock) { Get-PranataLockHolder $paths.RunnerLock } else { $null }
$heartbeatAge = 'none'
if (Test-Path -LiteralPath $paths.Heartbeat) {
    $heartbeatAge = [math]::Round(((Get-Date) - (Get-Item -LiteralPath $paths.Heartbeat).LastWriteTime).TotalMinutes, 1).ToString() + ' min'
}
Write-Output "branch:      $(& git -C $Root rev-parse --abbrev-ref HEAD)"
Write-Output "head:        $(& git -C $Root rev-parse HEAD)"
Write-Output "mode:        $($checkpoint.mode)"
Write-Output "task/stage:  $($checkpoint.task_id) / $($checkpoint.stage)"
Write-Output "next action: $($checkpoint.next_action)"
Write-Output "hold:        $(if ($hold) { $hold.mode } else { 'none' })"
Write-Output "runner:      $(if ($runner) { "PID $($runner.pid)" } else { 'not running' })"
Write-Output "heartbeat:   $heartbeatAge"
$plan = Join-Path $Root 'docs\11-tasks\TASK_PLAN.md'
if (Test-Path -LiteralPath $plan) {
    $done = @(Select-String -LiteralPath $plan -Pattern '^- \[x\] \[TASK-').Count
    $total = @(Select-String -LiteralPath $plan -Pattern '^- \[[ x]\] \[TASK-').Count
    Write-Output "tasks done:  $done/$total"
}
if (-not $Offline -and $checkpoint.pr_number) {
    $pr = (& gh pr view $checkpoint.pr_number --json state,headRefOid,url 2>$null) | ConvertFrom-Json
    if ($pr) { Write-Output "pr:          $($pr.state) $($pr.headRefOid) $($pr.url)" }
}
