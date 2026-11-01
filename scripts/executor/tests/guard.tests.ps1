# Forbidden-command list for EXECUTOR_OPERATING_MODEL section 11 (TAC-01).
# Only decisions are exercised: nothing here runs the listed commands.
# Usage: powershell -NoProfile -File scripts\executor\tests\guard.tests.ps1
param([switch]$Quiet)
$ErrorActionPreference = 'Stop'
$repo = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
. (Join-Path $repo '.claude\hooks\lib\policy.ps1')

$home2 = $env:USERPROFILE
$parent = Split-Path -Parent $repo
$pranataPid = 4242
$foreignPid = 5151
$facts = @{
    CurrentBranch = 'chore/task-001-x'
    ProcessCommandLine = {
        param($id)
        if ($id -eq 4242) { return 'powershell.exe -File C:\work\PRANATA-UNY\scripts\executor\runner.ps1' }
        if ($id -eq 5151) { return 'node.exe C:\work\unrelated\server.js' }
        return $null
    }
}
$factsOnMain = @{ CurrentBranch = 'main'; ProcessCommandLine = $facts.ProcessCommandLine }

# Each row: category group, expected decision, command, optional mode, optional facts
$commands = @(
    # Git
    @('git', 'deny', 'git push --force origin chore/x'),
    @('git', 'deny', 'git push -f origin chore/x'),
    @('git', 'deny', 'git push -uf origin chore/x'),
    @('git', 'deny', 'git push --force-with-lease origin chore/x'),
    @('git', 'deny', 'git push origin +chore/x'),
    @('git', 'deny', 'git push origin --delete chore/x'),
    @('git', 'deny', 'git push origin :chore/x'),
    @('git', 'deny', 'git push origin main'),
    @('git', 'deny', 'git push origin HEAD:main'),
    @('git', 'deny', 'git push origin abc123:refs/heads/main'),
    @('git', 'deny', 'git push', 'SUPERVISED', $factsOnMain),
    @('git', 'deny', 'git push origin HEAD', 'SUPERVISED', $factsOnMain),
    @('git', 'deny', 'git push https://example.test/x.git chore/x'),
    @('git', 'deny', 'git push --mirror origin'),
    @('git', 'deny', 'git push --no-verify origin chore/x'),
    @('git', 'deny', 'powershell -c "git push origin HEAD:main"'),
    @('git', 'deny', 'g`it push --force origin chore/x'),
    @('git', 'deny', 'git config user.name Someone'),
    @('git', 'deny', 'git config --global user.email a@b.test'),
    @('git', 'deny', 'git config --global core.autocrlf false'),
    @('git', 'deny', 'git config core.hooksPath .nowhere'),
    @('git', 'deny', 'git config --unset core.hooksPath'),
    @('git', 'deny', 'git -c user.name=Someone commit -m x'),
    @('git', 'deny', 'git -c core.hooksPath=/dev/null commit -m x'),
    @('git', 'deny', 'git -c alias.p=push p origin main'),
    @('git', 'deny', 'git commit --no-verify -m x'),
    @('git', 'deny', 'git commit -n -m x'),
    @('git', 'deny', 'git commit --amend -m x'),
    @('git', 'deny', 'git commit --author="A <a@b.test>" -m x'),
    @('git', 'deny', '$env:GIT_AUTHOR_NAME="A"; git commit -m x'),
    @('git', 'deny', 'GIT_COMMITTER_EMAIL=a@b.test git commit -m x'),
    @('git', 'deny', 'git reset --hard HEAD~1'),
    @('git', 'deny', 'git clean -fdx'),
    @('git', 'deny', 'git rebase main'),
    @('git', 'deny', 'git filter-branch --all'),
    @('git', 'deny', 'git remote set-url origin https://example.test/x.git'),
    @('git', 'deny', 'git branch -D chore/x'),
    @('git', 'deny', 'git clone https://github.com/someone/other.git'),
    @('git', 'deny', 'git --git-dir=C:/elsewhere/.git status'),
    # Attribution
    @('attribution', 'deny', 'git commit -m "chore: x`n`nCo-Authored-By: Bot <noreply@anthropic.com>"'),
    @('attribution', 'deny', 'gh pr create --title x --body "Generated with Claude Code"'),
    @('attribution', 'deny', 'gh pr edit 3 --body "Written by an AI"'),
    # Docker
    @('docker', 'deny', 'docker system prune -af'),
    @('docker', 'deny', 'docker volume prune -f'),
    @('docker', 'deny', 'docker network prune'),
    @('docker', 'deny', 'docker builder prune -a'),
    @('docker', 'deny', 'docker image prune -a'),
    @('docker', 'deny', 'docker container prune'),
    @('docker', 'deny', 'docker stop other-app-1'),
    @('docker', 'deny', 'docker rm -f other-db'),
    @('docker', 'deny', 'docker restart other-app-1'),
    @('docker', 'deny', 'docker volume rm other_data'),
    @('docker', 'deny', 'docker network rm other_net'),
    @('docker', 'deny', 'docker stop $(docker ps -q)'),
    @('docker', 'deny', 'docker exec -it other-db psql'),
    @('docker', 'deny', 'docker logs other-app-1'),
    @('docker', 'deny', 'docker compose up -d'),
    @('docker', 'deny', 'docker compose -p other down -v'),
    @('docker', 'deny', 'docker-compose up -d'),
    @('docker', 'deny', 'docker run -d postgres:16'),
    @('docker', 'deny', 'docker desktop restart'),
    @('docker', 'deny', 'docker context use other'),
    @('docker', 'deny', 'docker --context other ps'),
    @('docker', 'deny', 'Restart-Service com.docker.service'),
    @('docker', 'deny', 'wsl --shutdown'),
    @('docker', 'deny', 'wsl --terminate docker-desktop'),
    # Files of other projects and out-of-scope folders
    @('files', 'deny', 'Get-Content C:\work\MULTIPLECORP\.env'),
    @('files', 'deny', 'cat /c/work/BantuKasir/.env'),
    @('files', 'deny', 'ls ../ELLIGBLE'),
    @('files', 'deny', 'git -C C:\work\multiplecorp status'),
    @('files', 'deny', "Get-ChildItem `"$home2\Documents`""),
    @('files', 'deny', 'cat ~/.ssh/id_rsa'),
    @('files', 'deny', 'cat ~/.git-credentials'),
    @('files', 'deny', 'cat $HOME/.config/gh/hosts.yml'),
    @('files', 'deny', 'Get-Content ..\other\.env'),
    @('files', 'deny', 'Get-Content C:\Projects\other\file.txt'),
    @('files', 'deny', 'cat /c/Projects/other/file.txt'),
    # Databases
    @('database', 'deny', 'psql -h db.example.test -p 5432 -d app'),
    @('database', 'deny', 'psql -h localhost -p 5433 -d pranata_dev'),
    @('database', 'deny', 'psql -h localhost -p 55432 -d other_db'),
    @('database', 'deny', 'psql postgres://u@10.0.0.5:5432/pranata_dev'),
    @('database', 'deny', 'php artisan migrate --env=production'),
    @('database', 'deny', 'php artisan migrate --database=other'),
    @('database', 'deny', 'DB_HOST=10.0.0.5 php artisan migrate'),
    @('database', 'deny', 'DB_DATABASE=other php artisan migrate:fresh'),
    @('database', 'deny', 'mysql -h localhost app'),
    @('database', 'deny', 'dropdb -h localhost -p 55432 other_db'),
    @('database', 'deny', 'pg_dumpall -h localhost -p 55432'),
    # Processes
    @('process', 'deny', 'Stop-Process -Name node'),
    @('process', 'deny', 'Get-Process node | Stop-Process'),
    @('process', 'deny', 'taskkill /IM php.exe /F'),
    @('process', 'deny', 'taskkill /F /FI "IMAGENAME eq node.exe"'),
    @('process', 'deny', 'pkill node'),
    @('process', 'deny', 'killall php'),
    @('process', 'deny', "Stop-Process -Id $foreignPid"),
    @('process', 'deny', "taskkill /PID $foreignPid /F"),
    @('process', 'deny', 'Stop-Process -Id 999999'),
    @('process', 'deny', 'Stop-Process -Id $somePid'),
    @('process', 'deny', 'wmic process where name="node.exe" delete'),
    # Windows system settings
    @('system', 'deny', 'setx PATH "C:\x"'),
    @('system', 'deny', "[Environment]::SetEnvironmentVariable('A','1','Machine')"),
    @('system', 'deny', 'netsh advfirewall set allprofiles state off'),
    @('system', 'deny', 'New-NetFirewallRule -DisplayName x'),
    @('system', 'deny', 'Set-ExecutionPolicy Bypass'),
    @('system', 'deny', 'npm install -g pnpm'),
    @('system', 'deny', 'composer global require x/y'),
    @('system', 'deny', 'winget install x'),
    @('system', 'deny', 'Install-Module Foo'),
    @('system', 'deny', 'Register-ScheduledTask -TaskName Other'),
    @('system', 'deny', 'schtasks /delete /tn Other'),
    @('system', 'deny', 'Restart-Computer'),
    # Permission bypass and indirection
    @('bypass', 'deny', 'claude -p x --dangerously-skip-permissions'),
    @('bypass', 'deny', 'claude --permission-mode bypassPermissions -p x'),
    @('bypass', 'deny', 'claude -p x --bare'),
    @('bypass', 'deny', "claude -p x --settings '{`"disableAllHooks`": true}'"),
    @('bypass', 'deny', 'powershell -EncodedCommand ZQBjAGgAbwA='),
    @('bypass', 'deny', 'Invoke-Expression $x'),
    @('bypass', 'deny', 'Start-Process powershell -Verb RunAs'),
    # GitHub administration
    @('github', 'deny', 'gh auth token'),
    @('github', 'deny', 'gh auth login'),
    @('github', 'deny', 'gh secret set X'),
    @('github', 'deny', 'gh repo edit --visibility private'),
    @('github', 'deny', 'gh repo delete yusufarst/PRANATA-UNY'),
    @('github', 'deny', 'gh pr merge 3 --admin'),
    @('github', 'deny', 'gh pr merge 3 --squash'),
    @('github', 'deny', 'gh api repos/yusufarst/PRANATA-UNY/rulesets -X POST --input r.json'),
    @('github', 'deny', 'gh api -X PUT repos/yusufarst/PRANATA-UNY/branches/main/protection'),
    @('github', 'deny', 'gh api -X DELETE repos/yusufarst/PRANATA-UNY/git/refs/heads/x'),
    @('github', 'deny', 'gh api repos/yusufarst/PRANATA-UNY/git/refs -f ref=refs/heads/x -f sha=abc'),
    @('github', 'deny', 'gh api --method PUT repos/yusufarst/PRANATA-UNY/actions/secrets/X'),
    @('github', 'deny', 'gh api -X PATCH repos/yusufarst/PRANATA-UNY -f visibility=private'),
    @('github', 'deny', 'gh api graphql -f query="mutation { x }"'),
    @('github', 'deny', 'gh pr list --repo someone/other'),
    @('github', 'deny', 'gh api repos/someone/other/contents'),
    @('github', 'deny', 'curl -X POST https://api.github.com/repos/yusufarst/PRANATA-UNY/rulesets'),
    @('github', 'deny', 'gh workflow disable ci.yml'),
    # Secrets
    @('secrets', 'deny', 'echo $env:GH_TOKEN'),
    @('secrets', 'deny', 'git credential fill'),
    # Guard paths in AUTONOMOUS mode
    @('guard-paths', 'deny', 'Set-Content .claude\settings.json x', 'AUTONOMOUS'),
    @('guard-paths', 'deny', 'Remove-Item scripts\executor\runner.ps1', 'AUTONOMOUS'),
    @('guard-paths', 'deny', 'echo x > .githooks/commit-msg', 'AUTONOMOUS'),
    @('guard-paths', 'deny', 'git checkout main -- .github/workflows/ci.yml', 'AUTONOMOUS'),
    @('guard-paths', 'deny', 'Remove-Item .pranata\executor\hold.json', 'AUTONOMOUS'),
    @('guard-paths', 'deny', 'powershell -File scripts\executor\release-hold.ps1', 'AUTONOMOUS'),
    @('guard-paths', 'deny', 'powershell -File scripts\executor\merge-verified.ps1 -Pr 3 -Bootstrap', 'AUTONOMOUS'),

    # Normal work that must keep flowing
    @('allowed', 'allow', 'git status --short'),
    @('allowed', 'allow', 'git log -1 --format=%H'),
    @('allowed', 'allow', 'git switch -c chore/task-002-x main'),
    @('allowed', 'allow', 'git -c core.autocrlf=false add docs/x.md'),
    @('allowed', 'allow', 'git -c commit.gpgsign=false -c core.autocrlf=false commit -F msg.txt'),
    @('allowed', 'allow', 'git push -u origin chore/task-001-x'),
    @('allowed', 'allow', 'git push origin HEAD'),
    @('allowed', 'allow', 'git config user.name'),
    @('allowed', 'allow', 'git config --get core.hooksPath'),
    @('allowed', 'allow', 'git config --show-origin --get-regexp user'),
    @('allowed', 'allow', 'git diff main...HEAD --stat'),
    @('allowed', 'allow', 'git ls-remote origin refs/heads/main'),
    @('allowed', 'allow', 'git merge-base --is-ancestor main HEAD'),
    @('allowed', 'allow', 'git worktree add ../pranata-uny-worktrees/task-011 -b feature/task-011-x'),
    @('docker-local', 'deny', 'docker compose -p pranata up -d'),
    @('docker-local', 'deny', 'docker compose --project-name pranata ps'),
    @('docker-local', 'deny', 'docker compose -f compose.yaml -p pranata stop'),
    @('docker-local', 'deny', 'docker ps --format "{{.Names}}"'),
    @('docker-local', 'deny', 'docker stats --no-stream'),
    @('docker-local', 'deny', 'docker logs pranata-web'),
    @('docker-local', 'deny', 'docker exec pranata-postgres psql -U pranata -d pranata_test'),
    @('docker-local', 'deny', 'docker stop pranata-web'),
    @('docker-local', 'deny', 'docker volume inspect pranata_pgdata'),
    @('allowed', 'allow', 'psql -h localhost -p 55432 -d pranata_dev'),
    @('allowed', 'allow', 'php artisan test --filter=Foo'),
    @('allowed', 'allow', 'php artisan migrate --env=testing'),
    @('allowed', 'allow', 'composer install'),
    @('allowed', 'allow', 'npm run build'),
    @('allowed', 'allow', 'npm install --save-dev vite'),
    @('allowed', 'allow', "Stop-Process -Id $pranataPid"),
    @('allowed', 'allow', "taskkill /PID $pranataPid"),
    @('allowed', 'allow', 'gh pr create --title "TASK-001" --body-file pr.md'),
    @('allowed', 'allow', 'gh pr view 3 --json state,mergeCommit'),
    @('allowed', 'allow', 'gh pr checks 3'),
    @('allowed', 'allow', 'gh pr comment 3 --body "ready for review"'),
    @('allowed', 'allow', 'gh run view 123 --log-failed'),
    @('allowed', 'allow', 'gh api repos/yusufarst/PRANATA-UNY/commits/abc/check-runs'),
    @('allowed', 'allow', 'gh api repos/yusufarst/PRANATA-UNY/rulesets'),
    @('allowed', 'allow', 'gh api repos/yusufarst/PRANATA-UNY/issues/3/comments -f body=hello'),
    @('allowed', 'allow', 'Get-Content .env.example'),
    @('allowed', 'allow', "Get-Content `"$repo\docs\PHASE_STATUS.md`""),
    @('allowed', 'allow', "cat `"$($env:TEMP)\claude\x\out.txt`""),
    @('allowed', 'allow', 'Get-Content $env:CLAUDE_PROJECT_DIR\docs\x.md'),
    @('allowed', 'allow', 'powershell -File scripts\executor\status.ps1'),
    @('allowed', 'allow', 'Get-Content .claude\settings.json', 'AUTONOMOUS'),
    @('allowed', 'allow', 'Set-Content docs\x.md y', 'AUTONOMOUS')
)

$files = @(
    @('files', 'deny', 'Read', 'C:\work\MULTIPLECORP\.env', 'SUPERVISED'),
    @('files', 'deny', 'Edit', 'C:/work/BantuKasir/app.php', 'SUPERVISED'),
    @('files', 'deny', 'Read', "$parent\ELLIGBLE\config.php", 'SUPERVISED'),
    @('files', 'deny', 'Read', "$home2\.ssh\id_rsa", 'SUPERVISED'),
    @('files', 'deny', 'Grep', "$parent", 'SUPERVISED'),
    @('files', 'deny', 'Read', "$repo\..\other\file.txt", 'SUPERVISED'),
    @('git-internal', 'deny', 'Write', "$repo\.git\config", 'SUPERVISED'),
    @('guard-paths', 'deny', 'Write', "$repo\.claude\settings.json", 'AUTONOMOUS'),
    @('guard-paths', 'deny', 'Edit', "$repo\.github\workflows\ci.yml", 'AUTONOMOUS'),
    @('guard-paths', 'deny', 'Edit', "$repo\.githooks\commit-msg", 'AUTONOMOUS'),
    @('guard-paths', 'deny', 'Write', "$repo\scripts\executor\runner.ps1", 'AUTONOMOUS'),
    @('guard-paths', 'deny', 'Write', "$repo\.pranata\executor\hold.json", 'AUTONOMOUS'),
    @('allowed', 'allow', 'Read', "$repo\docs\PHASE_STATUS.md", 'SUPERVISED'),
    @('allowed', 'allow', 'Edit', "$repo\app\Foo.php", 'AUTONOMOUS'),
    @('allowed', 'allow', 'Write', "$repo\.pranata\executor\checkpoint.json", 'AUTONOMOUS'),
    @('allowed', 'allow', 'Write', "$repo\.claude\settings.json", 'SUPERVISED'),
    @('allowed', 'allow', 'Read', "$parent\pranata-uny-worktrees\task-011\x.php", 'SUPERVISED'),
    @('allowed', 'allow', 'Read', "$($env:TEMP)\claude\x\out.txt", 'SUPERVISED')
)

$fail = 0
$counts = @{}
foreach ($row in $commands) {
    $mode = 'SUPERVISED'; if ($row.Count -ge 4) { $mode = $row[3] }
    $useFacts = $facts; if ($row.Count -ge 5) { $useFacts = $row[4] }
    $verdict = Test-PranataCommand -Command $row[2] -ProjectDir $repo -Cwd $repo -Mode $mode -Facts $useFacts
    $counts[$row[0]] = 1 + [int]$counts[$row[0]]
    if ($verdict.Decision -ne $row[1]) {
        $fail++
        Write-Output "FAIL [$($row[0])] expected $($row[1]), got $($verdict.Decision) ($($verdict.Category)): $($row[2])"
    }
}
foreach ($row in $files) {
    $verdict = Test-PranataFileTool -ToolName $row[2] -Path $row[3] -ProjectDir $repo -Cwd $repo -Mode $row[4]
    $counts[$row[0]] = 1 + [int]$counts[$row[0]]
    if ($verdict.Decision -ne $row[1]) {
        $fail++
        Write-Output "FAIL [$($row[0])] expected $($row[1]), got $($verdict.Decision) ($($verdict.Category)): $($row[2]) $($row[3])"
    }
}

# Hold boundary: safe-stop steps pass, ordinary work does not.
foreach ($row in @(
    @($true, 'git status --short'),
    @($true, 'git -c core.autocrlf=false add docs/x.md'),
    @($false, 'docker compose -p pranata stop'),
    @($false, 'npm run build'),
    @($false, 'git push -u origin chore/x'),
    @($false, 'php artisan migrate')
)) {
    $counts['hold'] = 1 + [int]$counts['hold']
    if ((Test-PranataHoldAllowsCommand $row[1]) -ne $row[0]) {
        $fail++
        Write-Output "FAIL [hold] expected $($row[0]): $($row[1])"
    }
}

# Hook wire contract, end to end through the real script, no tool ever executes.
$hook = Join-Path $repo '.claude\hooks\pretooluse-guard.ps1'
function Invoke-Hook([string]$Json, [string]$ProjectDir) {
    $saved = $env:CLAUDE_PROJECT_DIR
    $env:CLAUDE_PROJECT_DIR = $ProjectDir
    try {
        $psi = New-Object System.Diagnostics.ProcessStartInfo
        $psi.FileName = 'powershell.exe'
        $psi.Arguments = "-NoProfile -NonInteractive -ExecutionPolicy Bypass -File `"$hook`""
        $psi.RedirectStandardInput = $true; $psi.RedirectStandardOutput = $true; $psi.UseShellExecute = $false
        $process = [Diagnostics.Process]::Start($psi)
        $bytes = (New-Object Text.UTF8Encoding($false)).GetBytes($Json)
        $process.StandardInput.BaseStream.Write($bytes, 0, $bytes.Length); $process.StandardInput.Close()
        $out = $process.StandardOutput.ReadToEnd(); $process.WaitForExit()
        return @{ Out = $out; Code = $process.ExitCode }
    } finally { $env:CLAUDE_PROJECT_DIR = $saved }
}
$repoJson = $repo.Replace('\', '\\')
foreach ($row in @(
    @('deny', "{`"tool_name`":`"PowerShell`",`"cwd`":`"$repoJson`",`"tool_input`":{`"command`":`"git push --force origin x`"}}"),
    @('deny', "{`"tool_name`":`"Bash`",`"cwd`":`"$repoJson`",`"tool_input`":{`"command`":`"docker system prune -af`"}}"),
    @('deny', "{`"tool_name`":`"Read`",`"cwd`":`"$repoJson`",`"tool_input`":{`"file_path`":`"C:\\work\\MULTIPLECORP\\.env`"}}"),
    @('deny-closed', 'this is not json'),
    @('silent', "{`"tool_name`":`"PowerShell`",`"cwd`":`"$repoJson`",`"tool_input`":{`"command`":`"git status`"}}"),
    @('silent', "{`"tool_name`":`"Read`",`"cwd`":`"$repoJson`",`"tool_input`":{`"file_path`":`"$repoJson\\AGENTS.md`"}}")
)) {
    $counts['hook-wire'] = 1 + [int]$counts['hook-wire']
    $result = Invoke-Hook $row[1] $repo
    $isDeny = $result.Out -match '"permissionDecision":\s*"deny"'
    $failedClosed = $result.Out -match 'failed closed'
    $ok = if ($row[0] -eq 'deny-closed') { $isDeny -and $failedClosed -and $result.Code -eq 0 } elseif ($row[0] -eq 'deny') { $isDeny -and (-not $failedClosed) -and $result.Code -eq 0 } else { (-not $result.Out) -and $result.Code -eq 0 }
    if (-not $ok) { $fail++; Write-Output "FAIL [hook-wire] expected $($row[0]), code $($result.Code), out: $($result.Out)" }
}

$summary = ($counts.GetEnumerator() | Sort-Object Name | ForEach-Object { "$($_.Name)=$($_.Value)" }) -join ' '
Write-Output "guard tests: $summary"
if ($fail -gt 0) { Write-Output "RESULT: FAIL ($fail)"; exit 1 }
Write-Output 'RESULT: PASS'
exit 0
