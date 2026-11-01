# Shared durable-state helpers for the executor scripts. ASCII only (PS 5.1).

$script:OwnerName = 'Yusuf Arif Setiawan'
$script:OwnerEmail = '134618411+yusufarst@users.noreply.github.com'
$script:GuardPathRegex = '^(\.github/workflows/|\.claude/|\.githooks/|scripts/executor/)'

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

# Single-instance lock: the file holds PID and process start time, so a reused PID cannot pin it.
function Get-PranataLockHolder([string]$LockPath) {
    $lock = Read-PranataJson $LockPath
    if (-not $lock) { return $null }
    $process = Get-Process -Id $lock.pid -ErrorAction SilentlyContinue
    if (-not $process) { return $null }
    if ($process.StartTime.ToUniversalTime().ToString('o') -ne $lock.started) { return $null }
    return $lock
}

function Enter-PranataLock([string]$LockPath) {
    # Creating with CreateNew makes acquisition atomic; a stale file is removed only after proving the holder is dead.
    for ($attempt = 0; $attempt -lt 2; $attempt++) {
        if (Test-Path -LiteralPath $LockPath) {
            if (Get-PranataLockHolder $LockPath) { return $false }
            Remove-Item -LiteralPath $LockPath -Force -ErrorAction SilentlyContinue
        }
        $self = Get-Process -Id $PID
        $body = @{ pid = $PID; started = $self.StartTime.ToUniversalTime().ToString('o') } | ConvertTo-Json -Compress
        try {
            $stream = [IO.File]::Open($LockPath, [IO.FileMode]::CreateNew, [IO.FileAccess]::Write, [IO.FileShare]::None)
            try {
                $bytes = [Text.Encoding]::UTF8.GetBytes($body)
                $stream.Write($bytes, 0, $bytes.Length)
            } finally { $stream.Close() }
            return $true
        } catch [IO.IOException] { continue }
    }
    return $false
}

function Exit-PranataLock([string]$LockPath) {
    $lock = Read-PranataJson $LockPath
    if ($lock -and $lock.pid -eq $PID) { Remove-Item -LiteralPath $LockPath -Force -ErrorAction SilentlyContinue }
}

function Test-PranataIdentity([string]$Root) {
    $name = (& git -C $Root config user.name)
    $email = (& git -C $Root config user.email)
    return ($name -ceq $script:OwnerName -and $email -ceq $script:OwnerEmail)
}
