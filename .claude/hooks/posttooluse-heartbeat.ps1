# Cheap activity evidence for the watcher (EXECUTOR_OPERATING_MODEL section 9.1). Never blocks work.
try {
    $dir = Join-Path $env:CLAUDE_PROJECT_DIR '.pranata\executor'
    if (-not (Test-Path -LiteralPath $dir)) { [void](New-Item -ItemType Directory -Path $dir -Force) }
    [IO.File]::WriteAllText((Join-Path $dir 'heartbeat'), (Get-Date).ToUniversalTime().ToString('o'))
} catch { }
exit 0
