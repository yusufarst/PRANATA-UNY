# PRANATA executor guard policy (EXECUTOR_OPERATING_MODEL section 11).
# Pure decisions: no side effects, no network. Facts that need the machine are injected
# through $Facts so tests never touch real processes, containers or other projects.
# ASCII only: Windows PowerShell 5.1 reads BOM-less files with the ANSI code page.

$script:OtherProjects = 'multiplecorp|bantukasir|elligble'
$script:OwnRepo = 'yusufarst/PRANATA-UNY'
$script:SandboxRepo = 'yusufarst/pranata-uny-sandbox-[a-z0-9-]+'
$script:GuardPaths = '(^|[\\/\s"''])(\.github[\\/]workflows|\.claude|\.githooks|scripts[\\/]executor)([\\/\s"'']|$)|\.pranata[\\/]executor[\\/]hold\.json'
$script:WriteVerbs = '(?i)\b(Set-Content|Add-Content|Out-File|New-Item|Remove-Item|Move-Item|Copy-Item|Rename-Item|Clear-Content|Expand-Archive|sc|ac|ni|ri|mi|cpi|rni|del|erase|rm|rmdir|mv|cp|ren|tee|touch|sed\s+-i|git\s+(rm|mv|checkout|restore|apply|add|update-index|stash))\b|>|\.WriteAll'

function New-PranataVerdict([string]$Decision, [string]$Category, [string]$Reason) {
    return @{ Decision = $Decision; Category = $Category; Reason = $Reason }
}

function Deny([string]$Category, [string]$Reason) { return (New-PranataVerdict 'deny' $Category $Reason) }

function Get-PranataAllowedRoots([string]$ProjectDir) {
    $roots = New-Object System.Collections.ArrayList
    [void]$roots.Add((ConvertTo-PranataPath $ProjectDir))
    $parent = Split-Path -Parent $ProjectDir
    if ($parent) { [void]$roots.Add((ConvertTo-PranataPath (Join-Path $parent 'pranata-uny-worktrees'))) }
    foreach ($temp in @($env:TEMP, $(if ($env:LOCALAPPDATA) { Join-Path $env:LOCALAPPDATA 'Temp' }))) {
        if ($temp) {
            [void]$roots.Add((ConvertTo-PranataPath (Join-Path $temp 'claude')))
            [void]$roots.Add((ConvertTo-PranataPath $temp) + '\pranata-*')
        }
    }
    if ($env:USERPROFILE) {
        $slug = ($ProjectDir -replace '[^A-Za-z0-9]', '-')
        [void]$roots.Add((ConvertTo-PranataPath (Join-Path $env:USERPROFILE ".claude\projects\$slug")))
    }
    return $roots
}

function ConvertTo-PranataPath([string]$Path) {
    $p = $Path -replace '/', '\'
    if ($p -match '^\\([a-zA-Z])\\(.*)$') { $p = $Matches[1] + ':\' + $Matches[2] }
    elseif ($p -match '^\\([a-zA-Z])$') { $p = $Matches[1] + ':\' }
    $p = ($p -split '[*?]')[0]
    return $p.TrimEnd('\').ToLowerInvariant()
}

function Resolve-PranataPath([string]$Path, [string]$Cwd) {
    $p = ConvertTo-PranataPath $Path
    if ($p -notmatch '^[a-z]:' -and $p -notmatch '^\\\\') { $p = (ConvertTo-PranataPath $Cwd) + '\' + $p }
    try { return ([IO.Path]::GetFullPath($p)).TrimEnd('\').ToLowerInvariant() } catch { return $p }
}

function Test-PranataPathAllowed([string]$Path, [string]$ProjectDir, [string]$Cwd) {
    $full = Resolve-PranataPath $Path $Cwd
    foreach ($root in (Get-PranataAllowedRoots $ProjectDir)) {
        if ($root.EndsWith('*')) {
            if ($full.StartsWith($root.TrimEnd('*'))) { return $true }
        } elseif ($full -eq $root -or $full.StartsWith($root + '\')) { return $true }
    }
    return $false
}

function Test-PranataFileTool {
    param([string]$ToolName, [string]$Path, [string]$ProjectDir, [string]$Cwd, [string]$Mode)
    if (-not $Path) { return (New-PranataVerdict 'allow' '' '') }
    if ($Path -match "(?i)$script:OtherProjects") {
        return (Deny 'file-other-project' 'Path belongs to another protected project.')
    }
    if (-not (Test-PranataPathAllowed $Path $ProjectDir $Cwd)) {
        return (Deny 'file-outside-scope' "Path is outside the PRANATA scope: $Path")
    }
    $isWrite = $ToolName -in @('Write', 'Edit', 'NotebookEdit', 'MultiEdit')
    if ($isWrite) {
        $full = Resolve-PranataPath $Path $Cwd
        if ($full -match '\\\.git(\\|$)') { return (Deny 'git-internal-write' 'Direct writes into .git are forbidden.') }
        if ($Mode -eq 'AUTONOMOUS') {
            $rel = $full
            $root = ConvertTo-PranataPath $ProjectDir
            if ($full.StartsWith($root)) { $rel = $full.Substring($root.Length).TrimStart('\') }
            if ($rel -match $script:GuardPaths) {
                return (Deny 'guard-path-autonomous' 'Guardrail paths are read-only in AUTONOMOUS mode.')
            }
        }
    }
    return (New-PranataVerdict 'allow' '' '')
}

function Test-PranataAttribution([string]$Text) {
    if (-not $Text) { return $false }
    $patterns = @(
        '(?im)^\s*co-authored-by\s*:',
        '(?i)(generated|created|written|authored|assisted)\s+(with|by|using)\s+\[?(claude|anthropic|chatgpt|openai|codex|copilot|gemini|cursor|an?\s+ai\b|ai\b)',
        '(?i)noreply@anthropic\.com',
        '(?i)claude\.(ai|com)/code',
        ([string][char]0xD83E + [string][char]0xDD16),
        '(?im)^\s*(ai|tool|assistant|claude|codex|copilot|chatgpt|gemini)[- ]?(assisted|generated|author|session|by)[a-z-]*\s*:'
    )
    foreach ($pattern in $patterns) { if ($Text -match $pattern) { return $true } }
    return $false
}

function Get-PranataSegments([string]$Command) {
    $flat = ($Command -replace '`|"|''|\\\r?\n', '')
    $parts = [regex]::Split($flat, '\r?\n|;|&&|\|\||\||\(|\)|(?<![\w:])&(?!\w)')
    return @($parts | ForEach-Object { $_.Trim() } | Where-Object { $_ })
}

function Get-PranataTokens([string]$Segment) { return @($Segment -split '\s+' | Where-Object { $_ }) }

# Returns the argument list after each occurrence of an executable anywhere in a segment.
# Searching mid-segment also catches wrappers such as: powershell -c "git push --force".
function Get-PranataInvocations([string]$Segment, [string]$Name) {
    $tokens = Get-PranataTokens $Segment
    $result = New-Object System.Collections.ArrayList
    for ($i = 0; $i -lt $tokens.Count; $i++) {
        if ($tokens[$i] -match "(?i)(^|[\\/&])$Name(\.exe|\.cmd)?$") {
            $rest = @()
            if ($i + 1 -lt $tokens.Count) { $rest = @($tokens[($i + 1)..($tokens.Count - 1)]) }
            [void]$result.Add([object[]]$rest)
        }
    }
    return ,$result
}

function Test-PranataGit {
    param([string[]]$GitArgs, [string]$CurrentBranch, [string]$Original, [hashtable]$Facts)
    $i = 0
    $configOverrides = @()
    while ($i -lt $GitArgs.Count -and $GitArgs[$i] -like '-*') {
        $flag = $GitArgs[$i]
        if ($flag -in @('-C', '-c', '--git-dir', '--work-tree', '--namespace', '--exec-path')) {
            if ($flag -eq '-c' -and $i + 1 -lt $GitArgs.Count) { $configOverrides += $GitArgs[$i + 1] }
            if ($flag -in @('--git-dir', '--work-tree', '--exec-path')) {
                return (Deny 'git-repo-redirect' "git $flag is forbidden.")
            }
            $i += 2
        } else {
            if ($flag -match '^--(git-dir|work-tree|exec-path)=') { return (Deny 'git-repo-redirect' "git $flag is forbidden.") }
            if ($flag -match '^--config-env') { return (Deny 'git-config-override' "git $flag is forbidden.") }
            $i++
        }
    }
    foreach ($override in $configOverrides) {
        if ($override -match '(?i)^(user\.|author\.|committer\.|core\.hookspath|alias\.|credential|core\.sshcommand|core\.fsmonitor|url\.|remote\.)') {
            return (Deny 'git-config-override' "Per-command override of $override is forbidden.")
        }
    }
    if ($i -ge $GitArgs.Count) { return (New-PranataVerdict 'allow' '' '') }
    $sub = $GitArgs[$i].ToLowerInvariant()
    $rest = @()
    if ($i + 1 -lt $GitArgs.Count) { $rest = @($GitArgs[($i + 1)..($GitArgs.Count - 1)]) }
    $positional = @($rest | Where-Object { $_ -notlike '-*' })

    switch ($sub) {
        'push' {
            foreach ($a in $rest) {
                if ($a -match '^(--force.*|--mirror|--all|--tags|--delete|--prune|--no-verify|--follow-tags)$' -or $a -match '^-[a-zA-Z]*[fd][a-zA-Z]*$') {
                    return (Deny 'git-push-forbidden' "git push $a is forbidden.")
                }
            }
            if ($positional.Count -ge 1 -and $positional[0] -ne 'origin') {
                return (Deny 'git-push-remote' 'Pushing to anything but origin is forbidden.')
            }
            $refspecs = @($positional | Select-Object -Skip 1)
            if ($refspecs.Count -eq 0 -and $CurrentBranch -eq 'main') {
                return (Deny 'git-push-main' 'Direct push to main outside merge-verified.ps1 is forbidden.')
            }
            foreach ($spec in $refspecs) {
                if ($spec -match '^\+') { return (Deny 'git-push-forbidden' 'Forced refspecs are forbidden.') }
                if ($spec -match '^:') { return (Deny 'git-push-delete' 'Deleting remote branches is forbidden.') }
                $dest = ($spec -split ':')[-1]
                if ($dest -in @('main', 'refs/heads/main') -or ($dest -eq 'HEAD' -and $CurrentBranch -eq 'main')) {
                    return (Deny 'git-push-main' 'Direct push to main outside merge-verified.ps1 is forbidden.')
                }
            }
        }
        'config' {
            $writeFlags = @($rest | Where-Object { $_ -match '^(--unset.*|--add|--replace-all|--rename-section|--remove-section|--edit|-e|set|unset)$' })
            $values = @($rest | Where-Object { $_ -notlike '-*' })
            if ($writeFlags.Count -gt 0 -or $values.Count -ge 2) {
                return (Deny 'git-config-write' 'Changing Git configuration is forbidden; identity and hooks are Owner-only.')
            }
        }
        'commit' {
            foreach ($a in $rest) {
                if ($a -match '^(--no-verify|--amend|--author.*|--reset-author)$' -or $a -match '^-[a-zA-Z]*n[a-zA-Z]*$') {
                    return (Deny 'git-commit-forbidden' "git commit $a is forbidden.")
                }
            }
        }
        'reset' { if ($rest -contains '--hard') { return (Deny 'git-destructive' 'git reset --hard is forbidden.') } }
        'clean' { return (Deny 'git-destructive' 'git clean is forbidden.') }
        'clone' { return (Deny 'git-clone' 'Cloning repositories is forbidden; only this repository is in scope.') }
        'remote' {
            if ($positional.Count -ge 1 -and $positional[0] -in @('add', 'remove', 'rm', 'rename', 'set-url', 'set-head', 'prune', 'update')) {
                return (Deny 'git-remote-write' 'Changing remotes is forbidden.')
            }
        }
        'branch' {
            foreach ($a in $rest) {
                if ($a -match '^(--delete|-d|-D|--force|-f|-M|--move|-m|--set-upstream-to.*)$') {
                    if ($a -notmatch 'set-upstream') { return (Deny 'git-branch-destructive' "git branch $a is forbidden; branches are never deleted automatically.") }
                }
            }
        }
        { $_ -in @('rebase', 'filter-branch', 'filter-repo', 'replace', 'update-ref', 'credential', 'credential-manager', 'daemon', 'send-email') } {
            return (Deny 'git-history-or-credential' "git $sub is forbidden.")
        }
    }
    return (New-PranataVerdict 'allow' '' '')
}

# Owner decision 2026-11-01: no local Docker for PRANATA, not even read-only calls.
# Heavy verification runs on GitHub-hosted runners, whose service containers never touch this PC.
function Test-PranataDocker {
    return (Deny 'docker-local-forbidden' 'Local Docker is forbidden for PRANATA (Owner decision); use GitHub-hosted CI.')
}

function Test-PranataDatabase([string]$Segment) {
    if ($Segment -match '(?i)(^|[\s\\/])(mysql|mysqldump|mariadb|sqlcmd|mongosh|mongo|redis-cli|sqlite3)(\.exe)?(\s|$)') {
        return (Deny 'db-foreign-tool' 'Only the pranata-postgres database is in scope.')
    }
    foreach ($m in [regex]::Matches($Segment, '(?i)(postgres(ql)?|mysql|mongodb|redis)://[^\s]+')) {
        if ($m.Value -notmatch '(?i)^postgres(ql)?://([^@/]+@)?(localhost|127\.0\.0\.1|pranata-postgres)(:(55432|5432))?/pranata_(dev|test\w*|e2e_\w+|perf)(\?|$)') {
            return (Deny 'db-foreign-host' 'Database URL is outside pranata-postgres.')
        }
    }
    foreach ($m in [regex]::Matches($Segment, '(?i)\b(DB_HOST|PGHOST)\s*=\s*([^\s]+)')) {
        if ($m.Groups[2].Value -notmatch '(?i)^(localhost|127\.0\.0\.1|pranata-postgres)$') {
            return (Deny 'db-foreign-host' 'Database host is outside pranata-postgres.')
        }
    }
    foreach ($m in [regex]::Matches($Segment, '(?i)\b(DB_DATABASE|PGDATABASE)\s*=\s*([^\s]+)')) {
        if ($m.Groups[2].Value -notmatch '(?i)^pranata_(dev|test\w*|e2e_\w+|perf)$') {
            return (Deny 'db-foreign-name' 'Database name is outside the PRANATA set.')
        }
    }
    if ($Segment -match '(?i)artisan\b.*--env[= ](production|prod|staging)\b') {
        return (Deny 'db-production' 'Artisan against staging or production is forbidden.')
    }
    if ($Segment -match '(?i)artisan\b.*--database[= ]') {
        if ($Segment -notmatch '(?i)--database[= ](pgsql|pgsql_\w+|pranata_\w+)(\s|$)') {
            return (Deny 'db-foreign-name' 'Artisan database connection is outside the PRANATA set.')
        }
    }
    if ($Segment -match '(?i)(^|[\s\\/])(psql|pg_dump|pg_dumpall|pg_restore|createdb|dropdb)(\.exe)?(\s|$)') {
        if ($Segment -match '(?i)pg_dumpall') { return (Deny 'db-foreign-name' 'pg_dumpall reaches every database.') }
        $hostValue = $null; $port = $null
        if ($Segment -match '(?i)(\s-h\s*|\s--host[= ])([^\s]+)') { $hostValue = $Matches[2] }
        if ($Segment -match '(?i)(\s-p\s*|\s--port[= ])([^\s]+)') { $port = $Matches[2] }
        $insideContainer = $Segment -match '(?i)\bexec\b.*\bpranata-postgres\b'
        if (-not $insideContainer -and -not $hostValue) {
            return (Deny 'db-foreign-host' 'Name the PRANATA host explicitly (-h localhost -p 55432) or use docker exec pranata-postgres.')
        }
        if ($hostValue -and $hostValue -notmatch '(?i)^(localhost|127\.0\.0\.1|pranata-postgres)$') {
            return (Deny 'db-foreign-host' 'Database host is outside pranata-postgres.')
        }
        if (-not $insideContainer -and $port -ne '55432') { return (Deny 'db-foreign-host' 'Only port 55432 reaches pranata-postgres.') }
        if ($Segment -notmatch '(?i)\bpranata_(dev|test\w*|e2e_\w+|perf)\b') {
            return (Deny 'db-foreign-name' 'Database name is outside the PRANATA set.')
        }
    }
    return (New-PranataVerdict 'allow' '' '')
}

function Test-PranataProcess {
    param([string]$Segment, [hashtable]$Facts)
    if ($Segment -match '(?i)(^|[\s\\/])(pkill|killall|tskill)(\.exe)?(\s|$)') { return (Deny 'process-by-name' 'Killing processes by name is forbidden.') }
    if ($Segment -match '(?i)\bwmic\b.*\b(delete|terminate|call)\b') { return (Deny 'process-by-name' 'wmic process control is forbidden.') }

    $pidsToCheck = @()
    if ($Segment -match '(?i)(^|[\s\\/])taskkill(\.exe)?(\s|$)') {
        if ($Segment -notmatch '(?i)/pid\s+\d+') { return (Deny 'process-by-name' 'taskkill needs /PID of a verified PRANATA process.') }
        if ($Segment -match '(?i)/im\b|/fi\b') { return (Deny 'process-by-name' 'taskkill /IM and /FI are forbidden.') }
        foreach ($m in [regex]::Matches($Segment, '(?i)/pid\s+(\d+)')) { $pidsToCheck += [int]$m.Groups[1].Value }
    }
    if ($Segment -match '(?i)(^|\s)(Stop-Process|spps|kill)(\s|$)') {
        if ($Segment -match '(?i)\s-(Name|n|ProcessName|InputObject)\b') { return (Deny 'process-by-name' 'Stop-Process by name is forbidden.') }
        $found = [regex]::Matches($Segment, '(?i)(Stop-Process|spps|kill)(\s+-Id|\s+-9|\s+-Force|\s+-s\s+\w+)*\s+(\d+)')
        if ($found.Count -eq 0) { return (Deny 'process-by-name' 'Stopping a process needs a literal, verified PRANATA PID.') }
        foreach ($m in $found) { $pidsToCheck += [int]$m.Groups[3].Value }
    }
    foreach ($id in $pidsToCheck) {
        $line = $null
        if ($Facts -and $Facts.ContainsKey('ProcessCommandLine')) { $line = & $Facts['ProcessCommandLine'] $id }
        if (-not $line -or $line -notmatch '(?i)pranata') {
            return (Deny 'process-foreign' "PID $id is not a verified PRANATA process.")
        }
    }
    return (New-PranataVerdict 'allow' '' '')
}

function Test-PranataSystem([string]$Segment) {
    $rules = @(
        @('process-by-name', '(?i)\.Kill\(|\.Terminate\(|Invoke-CimMethod.*Terminate'),
        @('system-env', '(?i)(^|[\s\\/])setx(\.exe)?(\s|$)|SetEnvironmentVariable\s*\(.*(Machine|User)'),
        @('system-network', '(?i)(^|[\s\\/])netsh(\.exe)?(\s|$)|\b(New|Set|Remove|Disable|Enable)-NetFirewall'),
        @('system-registry', '(?i)(^|[\s\\/])reg(\.exe)?\s+(add|delete|import|load|restore)\b|\b(Set|New|Remove)-ItemProperty\s+.*(HKLM|HKCU)'),
        @('system-service', '(?i)\b(Stop|Restart|Set|Remove|Suspend)-Service\b|(^|[\s\\/])sc(\.exe)?\s+(stop|config|delete|create)\b|(^|\s)net\s+(stop|start)\b'),
        @('system-policy', '(?i)\bSet-ExecutionPolicy\b|\bdisable-computerrestore\b'),
        @('system-power', '(?i)\b(Stop|Restart)-Computer\b|(^|[\s\\/])shutdown(\.exe)?\s+/|\bpowercfg\b'),
        @('system-wsl', '(?i)(^|[\s\\/])wsl(\.exe)?(\s|$)'),
        @('system-docker-desktop', '(?i)docker\s+desktop\b|Docker Desktop\.exe|com\.docker\.service'),
        @('system-global-package', '(?i)\bnpm(\.cmd)?\s+(i|install|add|uninstall|update)\s+(.*\s)?(-g|--global)\b|\bnpm(\.cmd)?\s+(.*\s)?(-g|--global)\s+(i|install|add)\b|\bcomposer(\.bat)?\s+global\b|\b(winget|choco|scoop)(\.exe)?\s+(install|upgrade|uninstall|update|remove)\b|\bInstall-(Module|Package|Script)\b|\bpip3?(\.exe)?\s+install\b(?!.*(--target|-t\s))'),
        @('system-scheduler', '(?i)\b(Register|Unregister|Set|Disable|Enable|Start|Stop)-ScheduledTask\b(?!.*PRANATA-UNY)|(^|[\s\\/])schtasks(\.exe)?\s+/(create|delete|change|run|end)\b(?!.*PRANATA-UNY)'),
        @('permission-bypass', '(?i)--dangerously-skip-permissions|--allow-dangerously-skip-permissions|bypassPermissions|disableAllHooks|(^|[\s\\/])claude(\.exe|\.cmd)?\s+.*--bare\b'),
        @('indirect-execution', '(?i)(^|\s)-(EncodedCommand|enc|ec)(\s|$)|\b(Invoke-Expression|iex)\b|-Verb\s+RunAs|\brunas(\.exe)?\s'),
        @('secret-exposure', '(?i)\$env:(GH_TOKEN|GITHUB_TOKEN|ANTHROPIC_API_KEY)|\$(GH_TOKEN|GITHUB_TOKEN|ANTHROPIC_API_KEY)\b|\bgit-credential\b|\.git-credentials|cmdkey(\.exe)?\s')
    )
    foreach ($rule in $rules) { if ($Segment -match $rule[1]) { return (Deny $rule[0] "Forbidden system or safety operation ($($rule[0])).") } }
    return (New-PranataVerdict 'allow' '' '')
}

function Test-PranataGh {
    param([string[]]$GhArgs, [string]$Segment)
    if ($GhArgs.Count -eq 0) { return (New-PranataVerdict 'allow' '' '') }
    $sub = $GhArgs[0].ToLowerInvariant()
    $action = ''
    if ($GhArgs.Count -gt 1) { $action = $GhArgs[1].ToLowerInvariant() }
    $repoPattern = "(?i)^($script:OwnRepo|$script:SandboxRepo)$"

    for ($i = 0; $i -lt $GhArgs.Count; $i++) {
        $repoValue = $null
        if ($GhArgs[$i] -in @('-R', '--repo') -and $i + 1 -lt $GhArgs.Count) { $repoValue = $GhArgs[$i + 1] }
        elseif ($GhArgs[$i] -match '^--repo=(.+)$') { $repoValue = $Matches[1] }
        if ($repoValue -and ($repoValue -replace '^https://github\.com/', '') -notmatch $repoPattern) {
            return (Deny 'gh-foreign-repo' "gh is limited to this repository, not $repoValue.")
        }
    }
    switch ($sub) {
        { $_ -in @('auth', 'secret', 'config', 'extension', 'alias', 'ssh-key', 'gpg-key', 'codespace', 'gist', 'org', 'project', 'cache', 'label', 'release', 'attestation') } {
            return (Deny 'gh-admin' "gh $sub is forbidden for the executor.")
        }
        'repo' {
            if ($action -in @('view', 'list')) { return (New-PranataVerdict 'allow' '' '') }
            return (Deny 'gh-admin' "gh repo $action is Owner-only.")
        }
        'variable' {
            if ($action -in @('list', 'get')) { return (New-PranataVerdict 'allow' '' '') }
            return (Deny 'gh-admin' "gh variable $action is Owner-only.")
        }
        'ruleset' { return (New-PranataVerdict 'allow' '' '') }
        'workflow' {
            if ($action -in @('list', 'view')) { return (New-PranataVerdict 'allow' '' '') }
            return (Deny 'gh-admin' "gh workflow $action is Owner-only.")
        }
        'pr' {
            if ($action -eq 'merge') { return (Deny 'gh-pr-merge' 'gh pr merge is forbidden; merging goes through merge-verified.ps1.') }
            return (New-PranataVerdict 'allow' '' '')
        }
        { $_ -in @('run', 'issue', 'status', 'search', 'browse', '--version', 'help') } { return (New-PranataVerdict 'allow' '' '') }
        'api' { return (Test-PranataGhApi @($GhArgs | Select-Object -Skip 1)) }
    }
    return (Deny 'gh-admin' "gh $sub is not allowed for the executor.")
}

function Test-PranataGhApi([string[]]$ApiArgs) {
    $method = 'GET'
    $endpoint = $null
    $hasFields = $false
    for ($i = 0; $i -lt $ApiArgs.Count; $i++) {
        $a = $ApiArgs[$i]
        if ($a -in @('-X', '--method') -and $i + 1 -lt $ApiArgs.Count) { $method = $ApiArgs[$i + 1].ToUpperInvariant(); $i++; continue }
        if ($a -match '^--method=(.+)$') { $method = $Matches[1].ToUpperInvariant(); continue }
        if ($a -match '^-X(.+)$') { $method = $Matches[1].ToUpperInvariant(); continue }
        if ($a -in @('-f', '-F', '--field', '--raw-field', '--input')) { $hasFields = $true; $i++; continue }
        if ($a -match '^(--field|--raw-field|--input)=') { $hasFields = $true; continue }
        if ($a -in @('-H', '--header', '-q', '--jq', '-t', '--template', '--hostname', '--cache', '-p', '--preview')) { $i++; continue }
        if ($a -like '-*') { continue }
        if (-not $endpoint) { $endpoint = $a }
    }
    if (-not $endpoint) { return (Deny 'gh-api-admin' 'gh api without an endpoint is not allowed.') }
    if ($method -eq 'GET' -and $hasFields -and $endpoint -ne 'graphql') { $method = 'POST' }
    if ($endpoint -eq 'graphql') {
        $joined = $ApiArgs -join ' '
        if ($joined -match '(?i)\bmutation\b' -or $joined -match '--input') { return (Deny 'gh-api-admin' 'GraphQL mutations are forbidden.') }
        return (New-PranataVerdict 'allow' '' '')
    }
    $path = $endpoint.TrimStart('/')
    if ($path -match '(?i)^repos/([^/]+/[^/?]+)') {
        if ($Matches[1] -notmatch "(?i)^($script:OwnRepo|$script:SandboxRepo)$") {
            return (Deny 'gh-foreign-repo' "gh api is limited to this repository, not $($Matches[1]).")
        }
    }
    if ($method -eq 'GET') { return (New-PranataVerdict 'allow' '' '') }
    if ($path -match '(?i)^repos/[^/]+/[^/]+/(issues/\d+/comments|issues/comments/\d+(/reactions)?|issues/\d+/reactions|pulls/\d+/(comments|reviews|requested_reviewers)|pulls/\d+/comments/\d+/replies)(\?|$)') {
        return (New-PranataVerdict 'allow' '' '')
    }
    return (Deny 'gh-api-admin' "gh api $method $path is forbidden for the executor.")
}

function Test-PranataShellPaths {
    param([string]$Command, [string]$ProjectDir, [string]$Cwd)
    $expanded = $Command
    $map = @{
        'CLAUDE_PROJECT_DIR' = $ProjectDir; 'USERPROFILE' = $env:USERPROFILE; 'HOME' = $env:USERPROFILE
        'LOCALAPPDATA' = $env:LOCALAPPDATA; 'APPDATA' = $env:APPDATA; 'TEMP' = $env:TEMP; 'TMP' = $env:TEMP
    }
    foreach ($name in $map.Keys) {
        if ($map[$name]) {
            $value = $map[$name].Replace(' ', [string][char]1)
            $expanded = [regex]::Replace($expanded, "(?i)\`$env:$name\b|\`$\{$name\}|\`$$name\b|%$name%", { param($m) $value }.GetNewClosure())
        }
    }
    if ($env:USERPROFILE) { $expanded = [regex]::Replace($expanded, '(^|[\s"''=])~(?=[\\/])', { param($m) $m.Groups[1].Value + $env:USERPROFILE.Replace(' ', [string][char]1) }) }

    foreach ($m in [regex]::Matches($expanded, '"[^"]*"|''[^'']*''|[^\s"'';|&()]+')) {
        $token = $m.Value.Trim('"', "'")
        if ($token -match '^--?[\w-]+=(.+)$') { $token = $Matches[1] }
        $isAbsolute = $token -match '^[A-Za-z]:[\\/]' -or $token -match '^/[a-zA-Z](/|$)' -or $token -match '^\\\\'
        $hasParent = ($token -notmatch '\s') -and ($token -match '(^|[\\/])\.\.([\\/]|$)') -and ($token -notmatch '^(https?:|refs/|[\w./-]*\.\.\.?[\w./-]*$)')
        if (-not ($isAbsolute -or $hasParent)) { continue }
        if ($isAbsolute -and $token -match '(?i)^([a-z]:[\\/]|/[a-z]/)(windows|program files( \(x86\))?|programdata)([\\/]|$)') { continue }
        $token = $token.Replace([string][char]1, ' ')
        if (-not (Test-PranataPathAllowed $token $ProjectDir $Cwd)) {
            return (Deny 'file-outside-scope' "Command touches a path outside the PRANATA scope: $token")
        }
    }
    return (New-PranataVerdict 'allow' '' '')
}

function Test-PranataBodyFiles {
    param([string]$Segment, [string]$Original, [string]$Cwd)
    foreach ($m in [regex]::Matches($Original, '(?i)(--body-file|--file|-F)[= ]\s*("[^"]+"|''[^'']+''|[^\s;|&]+)')) {
        $file = $m.Groups[2].Value.Trim('"', "'")
        $full = Resolve-PranataPath $file $Cwd
        if (Test-Path -LiteralPath $full -PathType Leaf) {
            if (Test-PranataAttribution ([IO.File]::ReadAllText($full))) {
                return (Deny 'attribution' 'Message file carries AI/tool attribution, which GOV-08 forbids.')
            }
        }
    }
    return (New-PranataVerdict 'allow' '' '')
}

function Test-PranataCommand {
    param(
        [string]$Command,
        [string]$ProjectDir,
        [string]$Cwd,
        [string]$Mode = 'SUPERVISED',
        [hashtable]$Facts = @{}
    )
    if (-not $Command) { return (New-PranataVerdict 'allow' '' '') }
    if (-not $Cwd) { $Cwd = $ProjectDir }
    if ($Command -match "(?i)$script:OtherProjects") {
        return (Deny 'file-other-project' 'Command references another protected project.')
    }
    $verdict = Test-PranataSystem ($Command -replace '`|"|''', '')
    if ($verdict.Decision -eq 'deny') { return $verdict }
    $verdict = Test-PranataShellPaths -Command $Command -ProjectDir $ProjectDir -Cwd $Cwd
    if ($verdict.Decision -eq 'deny') { return $verdict }

    if ($Mode -eq 'AUTONOMOUS') {
        if ($Command -match '(?i)release-hold\.ps1') { return (Deny 'hold-owner-only' 'Releasing a hold is Owner-only.') }
        if ($Command -match '(?i)(^|[\s\\/])merge-verified\.ps1\b.*-Bootstrap') { return (Deny 'merge-bootstrap' 'Bootstrap merge is SUPERVISED only.') }
        foreach ($segment in (Get-PranataSegments $Command)) {
            if ($segment -match $script:GuardPaths -and ($segment -match $script:WriteVerbs)) {
                return (Deny 'guard-path-autonomous' 'Guardrail paths are read-only in AUTONOMOUS mode.')
            }
        }
    }

    foreach ($segment in (Get-PranataSegments $Command)) {
        if ($segment -match '(?i)\bGIT_(AUTHOR|COMMITTER)_(NAME|EMAIL|DATE)\b|\bGIT_CONFIG_(GLOBAL|SYSTEM|COUNT|PARAMETERS|KEY|VALUE)|\bGIT_(DIR|WORK_TREE|SSH_COMMAND|ASKPASS)\b|\bGH_(HOST|REPO|ENTERPRISE_TOKEN)\b') {
            return (Deny 'git-env-override' 'Overriding Git or gh identity and location through the environment is forbidden.')
        }
        foreach ($check in @(
            (Test-PranataSystem $segment),
            (Test-PranataDatabase $segment),
            (Test-PranataProcess -Segment $segment -Facts $Facts)
        )) { if ($check.Decision -eq 'deny') { return $check } }

        if ($segment -match '(?i)(curl|wget|Invoke-WebRequest|Invoke-RestMethod|iwr|irm)(\.exe)?\s.*api\.github\.com') {
            return (Deny 'gh-api-admin' 'Calling the GitHub API outside gh is forbidden.')
        }
        if ($segment -match '(?i)(^|[\s\\/])docker-compose(\.exe)?(\s|$)') {
            $check = Test-PranataDocker
            if ($check.Decision -eq 'deny') { return $check }
        }
        foreach ($invocation in (Get-PranataInvocations $segment 'git')) {
            $check = Test-PranataGit -GitArgs $invocation -CurrentBranch $Facts['CurrentBranch'] -Original $Command -Facts $Facts
            if ($check.Decision -eq 'deny') { return $check }
        }
        foreach ($invocation in (Get-PranataInvocations $segment 'docker')) {
            $check = Test-PranataDocker
            if ($check.Decision -eq 'deny') { return $check }
        }
        foreach ($invocation in (Get-PranataInvocations $segment 'gh')) {
            $check = Test-PranataGh -GhArgs $invocation -Segment $segment
            if ($check.Decision -eq 'deny') { return $check }
        }
    }

    $wantsMessage = $Command -match '(?i)\bgit(\.exe)?\b.*\bcommit\b|\bgh(\.exe)?\b.*\b(pr|issue|api)\b'
    if ($wantsMessage) {
        if (Test-PranataAttribution $Command) {
            return (Deny 'attribution' 'Commit or PR text carries AI/tool attribution, which GOV-08 forbids.')
        }
        $check = Test-PranataBodyFiles -Segment '' -Original $Command -Cwd $Cwd
        if ($check.Decision -eq 'deny') { return $check }
    }
    return (New-PranataVerdict 'allow' '' '')
}

# During IMMEDIATE_SAFE_SHUTDOWN only the steps needed to reach the safe stop may run.
function Test-PranataHoldAllowsCommand([string]$Command) {
    foreach ($segment in (Get-PranataSegments $Command)) {
        if ($segment -notmatch '(?i)^(git(\.exe)?\s+((-c\s+\S+\s+)*)(status|log|rev-parse|diff|show|add|commit|ls-remote|branch\s+--show-current)\b|(\.[\\/])?scripts[\\/]executor[\\/](status|hold)\.ps1\b|powershell(\.exe)?\s.*scripts[\\/]executor[\\/](status|hold)\.ps1\b)') {
            return $false
        }
    }
    return $true
}
