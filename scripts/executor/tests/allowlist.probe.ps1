# Which allow-rule forms admit the executor scripts in a headless dontAsk step (EXECUTOR_OPERATING_MODEL section 13, C-02).
# Runs in a disposable repo under %TEMP%, with the project hooks and settings copied in. Nothing leaves that repo:
# status.ps1 is read-only, stage.ps1 stages a seed file, and merge-verified.ps1 is a dry run that must refuse.
# Usage: powershell -NoProfile -ExecutionPolicy Bypass -File scripts\executor\tests\allowlist.probe.ps1 [-Cases 1,2] [-Model haiku]
param([string[]]$Cases, [string]$Model = 'haiku')
$ErrorActionPreference = 'Stop'
$Cases = @($Cases | ForEach-Object { $_ -split ',' } | Where-Object { $_ })
foreach ($item in @(Get-ChildItem Env: | Where-Object { $_.Name -match '^(CLAUDE|ANTHROPIC_BASE_URL)' })) { Remove-Item -LiteralPath ("Env:" + $item.Name) }
$repo = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
. (Join-Path $PSScriptRoot '..\lib\state.ps1')
$box = Join-Path $env:TEMP ("pranata-sandbox-" + [guid]::NewGuid().ToString('N').Substring(0, 8))
$runnerRules = Get-PranataRunnerAllowedTools

# Each row: id, tool, allow rule, command, expected denials (0 = admitted). Rows 1-9 record why the runner uses Bash.
$matrix = @(
    @('1', 'PowerShell', 'PowerShell(powershell -NoProfile -File scripts\executor\status.ps1*)', 'powershell -NoProfile -File scripts\executor\status.ps1 -Offline', 1),
    @('2', 'PowerShell', 'PowerShell(powershell -NoProfile -File scripts/executor/status.ps1*)', 'powershell -NoProfile -File scripts/executor/status.ps1 -Offline', 1),
    @('3', 'PowerShell', 'PowerShell(powershell -NoProfile -File scripts\\executor\\status.ps1*)', 'powershell -NoProfile -File scripts\executor\status.ps1 -Offline', 1),
    @('4', 'PowerShell', 'PowerShell(powershell *)', 'powershell -NoProfile -File scripts\executor\status.ps1 -Offline', 1),
    @('5', 'PowerShell', 'Read', 'powershell -NoProfile -ExecutionPolicy Bypass -File scripts\executor\status.ps1 -Offline', 1),
    @('6', 'PowerShell', 'PowerShell(.\scripts\executor\status.ps1*)', '.\scripts\executor\status.ps1 -Offline', 1),
    @('7', 'PowerShell', 'PowerShell(scripts/executor/status.ps1*)', 'scripts/executor/status.ps1 -Offline', 1),
    @('8', 'Bash', 'Bash(powershell -NoProfile -File scripts/executor/status.ps1*)', 'powershell -NoProfile -File scripts/executor/status.ps1 -Offline', 0),
    @('10', 'Bash', $runnerRules, 'powershell -NoProfile -File scripts/executor/status.ps1 -Offline', 0),
    @('11', 'Bash', $runnerRules, 'powershell -NoProfile -File scripts/executor/stage.ps1 -Paths seed.txt', 0),
    @('12', 'Bash', $runnerRules, 'powershell -NoProfile -File scripts/executor/merge-verified.ps1 -Pr 1 -VerifiedSha 0000000000000000000000000000000000000000 -DryRun', 0),
    @('13', 'Bash', $runnerRules, 'git diff --cached --stat', 0),
    @('14', 'Bash', $runnerRules, 'npm --version', 0),
    @('15', 'Bash', $runnerRules, 'powershell -NoProfile -File scripts/executor/hold.ps1 -Mode SHUTDOWN_AFTER_CURRENT_TASK -Reason x', 1),
    @('16', 'Bash', $runnerRules, 'powershell -NoProfile -File scripts/executor/runner.ps1 -Start', 1)
)

$failed = 0
[void](New-Item -ItemType Directory -Path $box)
try {
    # Git writes warnings to stderr; PS 5.1 would turn them into terminating errors here.
    $ErrorActionPreference = 'Continue'
    & git init --quiet --initial-branch=main $box
    Copy-Item -Recurse (Join-Path $repo '.claude') (Join-Path $box '.claude')
    [void](New-Item -ItemType Directory -Path (Join-Path $box 'scripts') -Force)
    Copy-Item -Recurse (Join-Path $repo 'scripts\executor') (Join-Path $box 'scripts\executor')
    Set-Content -LiteralPath (Join-Path $box 'seed.txt') -Value 'seed' -Encoding ASCII
    & git -C $box add -A 2>$null
    & git -C $box commit --quiet -m 'chore: seed' 2>$null
    Add-Content -LiteralPath (Join-Path $box 'seed.txt') -Value 'changed' -Encoding ASCII

    Push-Location $box
    $env:PRANATA_EXECUTOR_MODE = 'AUTONOMOUS'
    foreach ($row in $matrix) {
        if ($Cases.Count -gt 0 -and $Cases -notcontains $row[0]) { continue }
        $prompt = "Use the $($row[1]) tool to run exactly this one command, once, with nothing added: $($row[3]) . The command may fail; that does not matter. Then reply with the single word DONE if it ran, or DENIED if it was refused. Do not try alternatives."
        $out = $prompt | & claude -p --model $Model --output-format json --setting-sources project --strict-mcp-config --permission-mode dontAsk --max-turns 3 --allowedTools $row[2] 2>$null | Out-String
        $json = $null
        try { $json = $out | ConvertFrom-Json } catch { }
        $denied = if ($json) { @($json.permission_denials).Count } else { -1 }
        $ok = ($denied -eq $row[4])
        if (-not $ok) { $failed++ }
        $text = if ($json) { (([string]$json.result) -replace '\s+', ' ') } else { 'unparsable' }
        Write-Output ("case {0} {1}: {2} denials={3} expected={4} result={5} command={6}" -f $row[0], $row[1], $(if ($ok) { 'PASS' } else { 'FAIL' }), $denied, $row[4], $text.Substring(0, [Math]::Min(20, $text.Length)), $row[3])
    }
    Remove-Item Env:PRANATA_EXECUTOR_MODE
    Pop-Location
} finally {
    Set-Location $repo
    if ($box -match 'pranata-sandbox-[0-9a-f]{8}$') { Remove-Item -LiteralPath $box -Recurse -Force -ErrorAction SilentlyContinue }
}
Write-Output "ALLOWLIST PROBE: $(if ($failed -eq 0) { 'PASS' } else { "FAILED ($failed)" })"
if ($failed -gt 0) { exit 1 }
