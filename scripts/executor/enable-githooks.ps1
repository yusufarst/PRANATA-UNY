# Owner-approved, repository-level activation of .githooks (TASK-001). Never touches global configuration.
param([string]$Root)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
if (-not $Root) { $Root = Get-PranataRepoRoot }
if ($env:PRANATA_EXECUTOR_MODE -eq 'AUTONOMOUS') { throw 'Hook activation is SUPERVISED only.' }
& git -C $Root config --local core.hooksPath .githooks
$value = (& git -C $Root config --local --get core.hooksPath)
if ($value -ne '.githooks') { throw "core.hooksPath is '$value'" }
Write-Output 'core.hooksPath=.githooks (local)'
