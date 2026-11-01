# Stop guard: a session with an active Task may not end while the checkpoint is behind reality.
# Blocks at most once per session and HEAD. The marker file keeps that true even if the platform
# does not report stop_hook_active, so this hook can never loop without progress.
$ErrorActionPreference = 'Stop'
try {
    # Raw stream: PS 5.1 Console.In would decode with the ANSI code page.
    $payload = (New-Object IO.StreamReader([Console]::OpenStandardInput(), [Text.Encoding]::UTF8)).ReadToEnd() | ConvertFrom-Json
    if ($payload.stop_hook_active) { exit 0 }

    $projectDir = $env:CLAUDE_PROJECT_DIR
    if (-not $projectDir) { $projectDir = [string]$payload.cwd }
    $stateDir = Join-Path $projectDir '.pranata\executor'
    $checkpointPath = Join-Path $stateDir 'checkpoint.json'
    if (-not (Test-Path -LiteralPath $checkpointPath)) { exit 0 }
    if (Test-Path -LiteralPath (Join-Path $stateDir 'hold.json')) { exit 0 }

    $checkpoint = Get-Content -LiteralPath $checkpointPath -Raw | ConvertFrom-Json
    if (-not $checkpoint.task_id -or $checkpoint.stage -in @('DONE', 'NONE')) { exit 0 }

    $head = (& git -C $projectDir rev-parse HEAD 2>$null)
    $reasons = @()
    if ($checkpoint.head_sha -and $head -and $head -ne $checkpoint.head_sha) { $reasons += "HEAD $head differs from checkpoint head_sha" }
    if (-not $checkpoint.next_action) { $reasons += 'next_action is empty' }
    if ($reasons.Count -eq 0) { exit 0 }

    $markerPath = Join-Path $stateDir 'stop-guard-marker'
    $marker = "$($payload.session_id)|$head"
    if ((Test-Path -LiteralPath $markerPath) -and ((Get-Content -LiteralPath $markerPath -Raw).Trim() -eq $marker)) { exit 0 }
    [IO.File]::WriteAllText($markerPath, $marker)

    $output = @{ decision = 'block'; reason = "Record the checkpoint before stopping: $($reasons -join '; ')." }
    [Console]::Out.Write(($output | ConvertTo-Json -Compress))
    exit 0
} catch {
    # A broken Stop guard must not trap the session; the runner re-checks the checkpoint after each step.
    exit 0
}
