# C-15: run after the Owner installs the restricted token. The token is used only inside this process.
# The ruleset attempt sends an invalid body on purpose: 403 proves no Administration right, and even a token
# wrongly holding that right would get 422 and change nothing.
$ErrorActionPreference = 'Continue'
. (Join-Path $PSScriptRoot '..\lib\state.ps1')
$repoName = 'yusufarst/PRANATA-UNY'
$before = (& gh api user --jq .login 2>$null)
$token = Read-PranataRunnerToken (Get-PranataTokenPath)
if (-not $token) { Write-Output 'C-15 NOT AVAILABLE: token belum terpasang'; exit 2 }

$env:GH_TOKEN = $token
$read = (& gh api "repos/$repoName" --jq .full_name 2>$null)
$body = Join-Path $env:TEMP "pranata-c15-$PID.json"
[IO.File]::WriteAllText($body, '{}')
$attempt = (& gh api "repos/$repoName/rulesets" -X POST --input $body 2>&1 | Out-String)
$denied = $attempt -match 'HTTP 403'
$secretAttempt = (& gh api "repos/$repoName/actions/secrets/public-key" 2>&1 | Out-String)
$secretDenied = $secretAttempt -match 'HTTP 403'

# What a Task step needs. Nothing is written: invalid bodies get 422 with the right and 403 without it,
# and a dry-run push asks the server for write access without sending a ref.
$main = ((& git ls-remote "https://github.com/$repoName.git" refs/heads/main) -split '\s+')[0]
$pullWrite = (& gh api "repos/$repoName/pulls" -X POST --input $body 2>&1 | Out-String) -match 'HTTP 422'
$commentWrite = (& gh api "repos/$repoName/issues/21/comments" -X POST --input $body 2>&1 | Out-String) -match 'HTTP 422'
$checksRead = (& gh api "repos/$repoName/commits/$main/check-runs" --jq .total_count 2>$null) -match '^\d+$'
$actionsRead = (& gh api "repos/$repoName/actions/runs?per_page=1" --jq .total_count 2>$null) -match '^\d+$'
$tagBypass = (& gh api "repos/$repoName/rulesets/26329290" --jq .current_user_can_bypass 2>$null)
# Same wiring as the runner's child process. ProcessStartInfo because $env:X = '' would delete the variable.
$psi = New-Object Diagnostics.ProcessStartInfo
$psi.FileName = (Get-Command git -ErrorAction Stop).Source
$psi.Arguments = 'push --dry-run origin HEAD:refs/heads/pranata-c15-dry-run'
$psi.WorkingDirectory = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
$psi.UseShellExecute = $false
$psi.RedirectStandardOutput = $true
$psi.RedirectStandardError = $true
$psi.EnvironmentVariables['GIT_TERMINAL_PROMPT'] = '0'
$psi.EnvironmentVariables['GIT_CONFIG_COUNT'] = '2'
$psi.EnvironmentVariables['GIT_CONFIG_KEY_0'] = 'credential.https://github.com.helper'
$psi.EnvironmentVariables['GIT_CONFIG_VALUE_0'] = ''
$psi.EnvironmentVariables['GIT_CONFIG_KEY_1'] = 'credential.https://github.com.helper'
$psi.EnvironmentVariables['GIT_CONFIG_VALUE_1'] = '!gh auth git-credential'
$push = [Diagnostics.Process]::Start($psi)
$pushErr = $push.StandardError.ReadToEndAsync()
$pushOut = $push.StandardOutput.ReadToEndAsync()
$push.WaitForExit()
$pushWrite = ($push.ExitCode -eq 0)
if (-not $pushWrite) { Write-Output ("push dry-run: " + (@($pushErr.Result -split '\r?\n' | Where-Object { $_ }) | Select-Object -Last 1)) }
Remove-Item -LiteralPath $body -Force
Remove-Item Env:GH_TOKEN

$after = (& gh api user --jq .login 2>$null)
$ownerIntact = ($before -and $before -eq $after)
$ok = ($read -eq $repoName) -and $denied -and $secretDenied -and $ownerIntact
Write-Output ("C-15 {0}: read repo={1}; ruleset attempt 403={2}; secrets 403={3}; Owner login unchanged={4}" -f $(if ($ok) { 'VERIFIED' } else { 'FAILED' }), ($read -eq $repoName), $denied, $secretDenied, $ownerIntact)
$usable = $pushWrite -and $pullWrite -and $commentWrite -and $checksRead -and $actionsRead
Write-Output ("C-15 runner rights {0}: push={1}; pull requests={2}; comments={3}; check runs={4}; actions={5}; release-tags bypass={6}" -f $(if ($usable) { 'VERIFIED' } else { 'FAILED' }), $pushWrite, $pullWrite, $commentWrite, $checksRead, $actionsRead, $tagBypass)
if (-not ($ok -and $usable)) { exit 1 }
