# Shared durable-state helpers for the executor scripts. ASCII only (PS 5.1).

$script:OwnerName = 'Yusuf Arif Setiawan'
$script:OwnerEmail = '134618411+yusufarst@users.noreply.github.com'
$script:GuardPathRegex = '^(\.github/workflows/|\.claude/|\.githooks/|scripts/executor/)'
$script:HeldLocks = @{}

function Get-PranataRepoRoot { return (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path }

function Get-PranataStatePaths([string]$Root) {
    $dir = Join-Path $Root '.pranata\executor'
    return @{
        Dir = $dir
        Checkpoint = Join-Path $dir 'checkpoint.json'
        Hold = Join-Path $dir 'hold.json'
        Heartbeat = Join-Path $dir 'heartbeat'
        Context = Join-Path $dir 'context.md'
        RunnerLock = Join-Path $dir 'runner.lock'
        WatcherLock = Join-Path $dir 'watcher.lock'
        StepLock = Join-Path $dir 'step.lock'
        Logs = Join-Path $Root '.pranata\logs'
    }
}

function Initialize-PranataStateDirs([string]$Root) {
    $paths = Get-PranataStatePaths $Root
    foreach ($dir in @($paths.Dir, $paths.Logs)) {
        if (-not (Test-Path -LiteralPath $dir)) { [void](New-Item -ItemType Directory -Path $dir -Force) }
    }
    return $paths
}

function Write-PranataFileAtomic([string]$Path, [string]$Text) {
    $temp = "$Path.$PID.tmp"
    [IO.File]::WriteAllText($temp, $Text, (New-Object Text.UTF8Encoding($false)))
    if (Test-Path -LiteralPath $Path) { [IO.File]::Replace($temp, $Path, [NullString]::Value) } else { [IO.File]::Move($temp, $Path) }
}

function Read-PranataJson([string]$Path) {
    if (-not (Test-Path -LiteralPath $Path)) { return $null }
    return ([IO.File]::ReadAllText($Path) | ConvertFrom-Json)
}

function New-PranataCheckpoint {
    return [ordered]@{
        authorization_id = 'AUTH-013'; mode = 'SUPERVISED'; task_id = $null; stage = 'NONE'; branch = $null
        head_sha = $null; pr_number = $null; ci_run_ids = @(); codex_status = 'NOT_REQUESTED'; review_status = $null
        last_action = $null; last_action_idempotent = $true; next_action = $null; session_id = $null
        wait_until = $null; updated_at = $null
    }
}

function Save-PranataCheckpoint([string]$Root, $Checkpoint) {
    $paths = Initialize-PranataStateDirs $Root
    $Checkpoint.updated_at = (Get-Date).ToUniversalTime().ToString('o')
    Write-PranataFileAtomic $paths.Checkpoint ($Checkpoint | ConvertTo-Json -Depth 5)
}

function Get-PranataCheckpoint([string]$Root) {
    $paths = Get-PranataStatePaths $Root
    $loaded = Read-PranataJson $paths.Checkpoint
    $checkpoint = New-PranataCheckpoint
    if ($loaded) {
        foreach ($property in $loaded.PSObject.Properties) { $checkpoint[$property.Name] = $property.Value }
    }
    return $checkpoint
}

# AUTONOMOUS wins if either signal says so; same rule as the guard hook.
function Get-PranataEffectiveMode([string]$Root) {
    if ($env:PRANATA_EXECUTOR_MODE -eq 'AUTONOMOUS') { return 'AUTONOMOUS' }
    if ((Get-PranataCheckpoint $Root).mode -eq 'AUTONOMOUS') { return 'AUTONOMOUS' }
    return 'SUPERVISED'
}

function New-PranataProcessStamp([int]$ProcessId) {
    $process = Get-Process -Id $ProcessId
    return (@{ pid = $ProcessId; started = $process.StartTime.ToUniversalTime().ToString('o') } | ConvertTo-Json -Compress)
}

# The body is informational (status, watcher). The lock itself is the open handle below.
function Get-PranataLockHolder([string]$LockPath) {
    if (-not (Test-Path -LiteralPath $LockPath)) { return $null }
    try {
        $stream = [IO.File]::Open($LockPath, [IO.FileMode]::Open, [IO.FileAccess]::Read, [IO.FileShare]::ReadWrite -bor [IO.FileShare]::Delete)
        try { $text = (New-Object IO.StreamReader($stream)).ReadToEnd() } finally { $stream.Close() }
    } catch { return $null }
    try { $lock = $text | ConvertFrom-Json } catch { return $null }
    if (-not $lock) { return $null }
    $process = Get-Process -Id $lock.pid -ErrorAction SilentlyContinue
    if (-not $process) { return $null }
    if ($process.StartTime.ToUniversalTime().ToString('o') -ne $lock.started) { return $null }
    return $lock
}

# Single instance through an exclusive handle kept for the life of the process: the OS releases it on any
# death, so a stale lock can never be confused with a live one and no holder can delete another's lock.
function Enter-PranataLock([string]$LockPath) {
    try {
        $stream = [IO.File]::Open($LockPath, [IO.FileMode]::OpenOrCreate, [IO.FileAccess]::ReadWrite, [IO.FileShare]::Read)
    } catch [IO.IOException] { return $false }
    $stream.SetLength(0)
    $bytes = [Text.Encoding]::UTF8.GetBytes((New-PranataProcessStamp $PID))
    $stream.Write($bytes, 0, $bytes.Length)
    $stream.Flush()
    $script:HeldLocks[$LockPath] = $stream
    return $true
}

function Exit-PranataLock([string]$LockPath) {
    if ($script:HeldLocks.ContainsKey($LockPath)) {
        $script:HeldLocks[$LockPath].Close()
        $script:HeldLocks.Remove($LockPath)
        Remove-Item -LiteralPath $LockPath -Force -ErrorAction SilentlyContinue
    }
}

# Restricted runner token (D-27): DPAPI-encrypted for this Windows user, outside the repository.
function Get-PranataTokenPath { return (Join-Path $env:LOCALAPPDATA 'PRANATA-UNY\runner-token.dpapi') }

function Read-PranataRunnerToken([string]$Path) {
    if (-not (Test-Path -LiteralPath $Path)) { return $null }
    $secure = ConvertTo-SecureString ([IO.File]::ReadAllText($Path).Trim())
    $pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
    try { return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer) }
    finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer) }
}

function Set-PranataWatcherEnabled([bool]$Enabled) {
    $task = Get-ScheduledTask -TaskName 'PRANATA-UNY-Watcher' -ErrorAction SilentlyContinue
    if (-not $task) { return }
    if ($Enabled) { [void](Enable-ScheduledTask -TaskName 'PRANATA-UNY-Watcher') } else { [void](Disable-ScheduledTask -TaskName 'PRANATA-UNY-Watcher') }
}

function Test-PranataIdentity([string]$Root) {
    $name = (& git -C $Root config user.name)
    $email = (& git -C $Root config user.email)
    return ($name -ceq $script:OwnerName -and $email -ceq $script:OwnerEmail)
}
