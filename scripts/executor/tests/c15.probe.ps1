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
Remove-Item -LiteralPath $body -Force
Remove-Item Env:GH_TOKEN

$after = (& gh api user --jq .login 2>$null)
$ownerIntact = ($before -and $before -eq $after)
$ok = ($read -eq $repoName) -and $denied -and $secretDenied -and $ownerIntact
Write-Output ("C-15 {0}: read repo={1}; ruleset attempt 403={2}; secrets 403={3}; Owner login unchanged={4}" -f $(if ($ok) { 'VERIFIED' } else { 'FAILED' }), ($read -eq $repoName), $denied, $secretDenied, $ownerIntact)
if (-not $ok) { exit 1 }
