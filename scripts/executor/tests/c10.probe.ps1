# C-10 and the PR half of C-05, on the Owner-approved disposable repository only (synthetic content).
# Questions: does a fast-forward of main mark the PR merged, and does a status-check ruleset refuse unproven commits?
# The repository name is fixed here on purpose: this script cannot act on any other repository.
# Re-runnable: it works from a fresh clone and uses per-run branch names.
$ErrorActionPreference = 'Continue'
foreach ($item in @(Get-ChildItem Env: | Where-Object { $_.Name -match '^(CLAUDE|ANTHROPIC_BASE_URL)' })) { Remove-Item -LiteralPath ("Env:" + $item.Name) }
$repoName = 'yusufarst/pranata-uny-sandbox-c10'
$source = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
$run = [guid]::NewGuid().ToString('N').Substring(0, 6)
$work = Join-Path $env:TEMP ("pranata-sandbox-c10-$run")
function Say([string]$Name, [string]$Status, [string]$Evidence) { Write-Output ("{0} {1}: {2}" -f $Name, $Status, $Evidence) }
function G { & git -C $work @args 2>&1 | Out-String }
function New-Commit([string]$File, [string]$Message) {
    Set-Content -LiteralPath (Join-Path $work $File) -Value ([guid]::NewGuid().ToString()) -Encoding ASCII
    [void](G -c core.autocrlf=false add $File)
    [void](G -c core.autocrlf=false commit --quiet -m $Message)
    return (G rev-parse HEAD).Trim()
}
function Wait-CiGate([string]$Sha) {
    for ($i = 0; $i -lt 40; $i++) {
        $runs = ((& gh api "repos/$repoName/commits/$Sha/check-runs" 2>$null) | ConvertFrom-Json).check_runs
        $gate = @($runs | Where-Object { $_.name -ceq 'ci-gate' }) | Select-Object -First 1
        if ($gate -and $gate.conclusion) { return [string]$gate.conclusion }
        Start-Sleep -Seconds 15
    }
    return 'timeout'
}
function Wait-PrState([int]$Number) {
    for ($i = 0; $i -lt 12; $i++) {
        $state = (& gh pr view $Number --repo $repoName --json state --jq .state 2>$null)
        if ($state -eq 'MERGED') { break }
        Start-Sleep -Seconds 10
    }
    return $state
}
function New-Pr([string]$Branch) {
    $url = (& gh pr create --repo $repoName --head $Branch --base main --title "probe $Branch" --body 'Synthetic capability probe.' 2>$null) | Select-Object -Last 1
    return [int](($url -split '/')[-1])
}
function Remote-Main { return ((& git ls-remote "https://github.com/$repoName.git" refs/heads/main) -split '\s+')[0] }

try {
    & git clone --quiet "https://github.com/$repoName.git" $work 2>&1 | Out-Null
    if (-not (Test-Path -LiteralPath (Join-Path $work '.git'))) { Say 'clone' 'FAILED' 'sandbox could not be cloned'; exit 1 }

    # Workflow must exist on main; the first run of this probe seeded it.
    $existingRules = ((& gh api "repos/$repoName/rulesets") | ConvertFrom-Json)
    if (-not @($existingRules | Where-Object { $_.name -ceq 'c10-probe' }).Count) {
        $ruleset = @{
            name = 'c10-probe'; target = 'branch'; enforcement = 'active'; bypass_actors = @()
            conditions = @{ ref_name = @{ include = @('refs/heads/main'); exclude = @() } }
            rules = @(
                @{ type = 'required_status_checks'; parameters = @{ strict_required_status_checks_policy = $false; required_status_checks = @(@{ context = 'ci-gate' }) } },
                @{ type = 'required_linear_history' }, @{ type = 'non_fast_forward' }, @{ type = 'deletion' }
            )
        } | ConvertTo-Json -Depth 8
        $file = Join-Path $work '.git\ruleset.json'
        [IO.File]::WriteAllText($file, $ruleset)
        & gh api "repos/$repoName/rulesets" -X POST --input $file | Out-Null
    }
    $count = @((& gh api "repos/$repoName/rulesets") | ConvertFrom-Json).Count
    Say 'ruleset' $(if ($count -ge 1) { 'OK' } else { 'FAILED' }) "sandbox rulesets=$count"
    if ($count -lt 1) { exit 1 }

    # B: a commit with no ci-gate result must be refused.
    [void](G switch --quiet -c "probe-b-$run")
    $shaB = New-Commit "b-$run.txt" 'chore: probe b'
    $before = Remote-Main
    $outB = G push origin "${shaB}:refs/heads/main"
    Say 'C-10b' $(if ((Remote-Main) -eq $before -and $outB -match '(?i)rule|required|reject') { 'VERIFIED' } else { 'FAILED' }) "unproven commit refused, main unchanged=$((Remote-Main) -eq $before)"

    # C: ci-gate passing on a work branch first, then the fast-forward is accepted and the PR is merged.
    [void](G switch --quiet -C "probe-c-$run" origin/main)
    $shaC = New-Commit "c-$run.txt" 'chore: probe c'
    [void](G push -u origin "probe-c-$run")
    $prC = New-Pr "probe-c-$run"
    $ciC = Wait-CiGate $shaC
    $outC = G push origin "${shaC}:refs/heads/main"
    $stateC = Wait-PrState $prC
    Say 'C-10c' $(if ($ciC -eq 'success' -and (Remote-Main) -eq $shaC -and $stateC -eq 'MERGED') { 'VERIFIED' } else { 'FAILED' }) "ci-gate=$ciC, remote main=$(Remote-Main), expected=$shaC, PR state=$stateC"

    # D: ci-gate failing must keep the commit off main.
    [void](G fetch --quiet origin main)
    [void](G switch --quiet -C "probe-d-$run" origin/main)
    $shaD = New-Commit 'fail-marker.txt' 'chore: probe d'
    [void](G push -u origin "probe-d-$run")
    $ciD = Wait-CiGate $shaD
    $before = Remote-Main
    $outD = G push origin "${shaD}:refs/heads/main"
    Say 'C-10d' $(if ($ciD -eq 'failure' -and (Remote-Main) -eq $before) { 'VERIFIED' } else { 'FAILED' }) "ci-gate=$ciD, main unchanged=$((Remote-Main) -eq $before)"

    # E: force push and deletion of main must both be refused.
    [void](G switch --quiet -C "probe-e-$run" 'origin/main~1')
    $shaE = New-Commit "e-$run.txt" 'chore: probe e'
    $before = Remote-Main
    $outE = G push --force origin "${shaE}:refs/heads/main"
    $outF = G push origin --delete main
    Say 'C-10e' $(if ((Remote-Main) -eq $before) { 'VERIFIED' } else { 'FAILED' }) "force push and delete refused, main unchanged=$((Remote-Main) -eq $before)"

    # C-05, PR half: a headless step opens a PR under the project attribution settings.
    Copy-Item -Recurse (Join-Path $source '.claude') (Join-Path $work '.claude')
    [void](G switch --quiet -C "probe-f-$run" origin/main)
    $shaF = New-Commit "f-$run.txt" 'chore: probe f'
    [void](G push -u origin "probe-f-$run")
    Push-Location $work
    $prompt = "Run exactly this one command with the PowerShell tool and nothing else: gh pr create --repo $repoName --head probe-f-$run --base main --title `"probe f`" --body `"Synthetic probe body.`""
    $result = $prompt | & claude -p --model haiku --output-format json --setting-sources project --strict-mcp-config --permission-mode dontAsk --max-turns 3 --allowedTools 'PowerShell(gh pr *),Bash(gh pr *)' 2>$null | Out-String
    Pop-Location
    $body = (& gh pr view "probe-f-$run" --repo $repoName --json body --jq .body 2>$null) -join "`n"
    $clean = ($body -match 'Synthetic probe body') -and ($body -notmatch '(?i)generated with|claude\.(ai|com)|co-authored-by')
    Say 'C-05-pr' $(if ($clean) { 'VERIFIED' } else { 'FAILED' }) "headless PR body has no footer=$clean, length=$($body.Length)"
} finally {
    Set-Location $source
    if ($work -match 'pranata-sandbox-c10-[0-9a-f]{6}$') { Remove-Item -LiteralPath $work -Recurse -Force -ErrorAction SilentlyContinue }
}
