# PreToolUse guard for shell and file tools. Fails closed: any internal error denies the call.
# Allow is expressed by silence, so the normal permission flow still applies afterwards.
$ErrorActionPreference = 'Stop'

function Write-PranataDenial([string]$Reason, [string]$Category, [string]$ProjectDir, [string]$ToolName) {
    if ($ProjectDir) {
        try {
            $logDir = Join-Path $ProjectDir '.pranata\logs'
            if (-not (Test-Path -LiteralPath $logDir)) { [void](New-Item -ItemType Directory -Path $logDir -Force) }
            # Category only: command text may carry values that must not reach a log.
            Add-Content -LiteralPath (Join-Path $logDir 'guard-denials.log') -Value ("{0} {1} {2}" -f (Get-Date).ToUniversalTime().ToString('o'), $ToolName, $Category)
        } catch { }
    }
    $output = @{ hookSpecificOutput = @{ hookEventName = 'PreToolUse'; permissionDecision = 'deny'; permissionDecisionReason = "PRANATA guard: $Reason" } }
    [Console]::Out.Write(($output | ConvertTo-Json -Depth 4 -Compress))
    exit 0
}

$projectDir = $env:CLAUDE_PROJECT_DIR
$toolName = ''
try {
    # Raw stream: PS 5.1 Console.In would decode with the ANSI code page.
    $payload = (New-Object IO.StreamReader([Console]::OpenStandardInput(), [Text.Encoding]::UTF8)).ReadToEnd() | ConvertFrom-Json
    $toolName = [string]$payload.tool_name
    $cwd = [string]$payload.cwd
    if (-not $projectDir) { $projectDir = $cwd }
    if (-not $projectDir) { throw 'project directory is unknown' }
    if (-not $cwd) { $cwd = $projectDir }

    . (Join-Path $PSScriptRoot 'lib\policy.ps1')

    $mode = 'SUPERVISED'
    if ($env:PRANATA_EXECUTOR_MODE -eq 'AUTONOMOUS') { $mode = 'AUTONOMOUS' }
    $checkpointPath = Join-Path $projectDir '.pranata\executor\checkpoint.json'
    if ($mode -ne 'AUTONOMOUS' -and (Test-Path -LiteralPath $checkpointPath)) {
        $checkpoint = Get-Content -LiteralPath $checkpointPath -Raw | ConvertFrom-Json
        if ($checkpoint.mode -eq 'AUTONOMOUS') { $mode = 'AUTONOMOUS' }
    }

    $holdMode = $null
    $holdPath = Join-Path $projectDir '.pranata\executor\hold.json'
    if (Test-Path -LiteralPath $holdPath) { $holdMode = (Get-Content -LiteralPath $holdPath -Raw | ConvertFrom-Json).mode }

    $toolInput = $payload.tool_input
    if ($toolName -in @('Bash', 'PowerShell')) {
        if ($toolInput.PSObject.Properties['dangerouslyDisableSandbox'] -and $toolInput.dangerouslyDisableSandbox) {
            Write-PranataDenial 'Disabling the sandbox is forbidden.' 'permission-bypass' $projectDir $toolName
        }
        $command = [string]$toolInput.command
        $facts = @{
            CurrentBranch = $(if ($command -match '\bpush\b') { (& git -C $projectDir rev-parse --abbrev-ref HEAD 2>$null) } else { $null })
            ProcessCommandLine = {
                param($id)
                $process = Get-CimInstance Win32_Process -Filter "ProcessId = $id" -ErrorAction SilentlyContinue
                if ($process) { return [string]$process.CommandLine }
                return $null
            }
        }
        $verdict = Test-PranataCommand -Command $command -ProjectDir $projectDir -Cwd $cwd -Mode $mode -Facts $facts
        if ($verdict.Decision -eq 'deny') { Write-PranataDenial $verdict.Reason $verdict.Category $projectDir $toolName }
        if ($holdMode -eq 'IMMEDIATE_SAFE_SHUTDOWN' -and -not (Test-PranataHoldAllowsCommand $command)) {
            Write-PranataDenial 'IMMEDIATE_SAFE_SHUTDOWN is active; only the safe-stop steps may run.' 'hold-active' $projectDir $toolName
        }
    } else {
        $path = $null
        foreach ($field in @('file_path', 'notebook_path', 'path')) {
            if ($toolInput.PSObject.Properties[$field] -and $toolInput.$field) { $path = [string]$toolInput.$field; break }
        }
        $verdict = Test-PranataFileTool -ToolName $toolName -Path $path -ProjectDir $projectDir -Cwd $cwd -Mode $mode
        if ($verdict.Decision -eq 'deny') { Write-PranataDenial $verdict.Reason $verdict.Category $projectDir $toolName }
        if ($holdMode -eq 'IMMEDIATE_SAFE_SHUTDOWN' -and $toolName -in @('Write', 'Edit', 'NotebookEdit', 'MultiEdit')) {
            if ([string]$path -notmatch '(?i)[\\/]\.pranata[\\/]executor[\\/]') {
                Write-PranataDenial 'IMMEDIATE_SAFE_SHUTDOWN is active; only checkpoint writes may run.' 'hold-active' $projectDir $toolName
            }
        }
    }
    exit 0
} catch {
    Write-PranataDenial "guard failed closed: $($_.Exception.Message)" 'guard-error' $projectDir $toolName
}
