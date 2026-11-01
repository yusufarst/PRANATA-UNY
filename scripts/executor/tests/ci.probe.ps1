# TASK-005 TAC-02, TAC-03, TAC-04, TAC-06 and TAC-07 against real runs of the ci workflow, synthetic content only.
# Every scenario opens a disposable draft PR from a throwaway worktree, reads the verdict and closes it without merge.
# The branches stay; main never moves. Scenarios that need ci.yml on main only make sense after TASK-005 is merged.
param(
    [string[]]$Scenarios = @('fast-fails', 'e2e-fails', 'docs-fails', 'stale-cancelled'),
    [string]$Base = 'HEAD'
)
$ErrorActionPreference = 'Continue'
foreach ($item in @(Get-ChildItem Env: | Where-Object { $_.Name -match '^(CLAUDE|ANTHROPIC_BASE_URL)' })) { Remove-Item -LiteralPath ("Env:" + $item.Name) }
$source = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
Set-Location $source
$Scenarios = @($Scenarios | ForEach-Object { $_ -split ',' } | Where-Object { $_ })
$repo = 'yusufarst/PRANATA-UNY'
$gotenbergTest = 'tests/Feature/Documents/GotenbergSmokeTest.php'
$results = New-Object System.Collections.ArrayList

function Say([string]$Name, [bool]$Ok, [string]$Evidence) {
    [void]$results.Add($Ok)
    Write-Output ("{0} {1}: {2}" -f $Name, $(if ($Ok) { 'VERIFIED' } else { 'FAILED' }), $Evidence)
}
function Write-Lf([string]$Path, [string]$Text) {
    $dir = Split-Path -Parent $Path
    if (-not (Test-Path -LiteralPath $dir)) { [void](New-Item -ItemType Directory -Path $dir -Force) }
    [IO.File]::WriteAllText($Path, $Text)
}
function Edit-File([string]$Path, [string]$From, [string]$To) {
    $text = [IO.File]::ReadAllText($Path) -replace "`r`n", "`n"
    if (-not $text.Contains($From)) { throw "marker not found in ${Path}: $From" }
    [IO.File]::WriteAllText($Path, $text.Replace($From, $To))
}
function Push-Commit([string]$Root, [string]$Message) {
    & git -C $Root -c core.autocrlf=input add -A 2>$null
    & git -C $Root -c core.autocrlf=input commit --quiet --allow-empty -m $Message 2>$null
    & git -C $Root push --quiet origin "HEAD:refs/heads/$(& git -C $Root branch --show-current)" 2>$null
    return (& git -C $Root rev-parse HEAD)
}
function Find-Run([string]$Sha) {
    $listing = (& gh api "repos/$repo/actions/runs?event=pull_request&head_sha=$Sha&per_page=20" 2>$null) -join "`n" | ConvertFrom-Json
    return @($listing.workflow_runs | Where-Object { $_.name -ceq 'ci' } | Sort-Object { [long]$_.id })
}
function Wait-Run([string]$Sha, [int]$Seconds = 900) {
    $deadline = (Get-Date).AddSeconds($Seconds)
    while ((Get-Date) -lt $deadline) {
        $runs = @(Find-Run $Sha)
        if ($runs.Count -gt 0 -and $runs[-1].status -ceq 'completed') { return $runs[-1] }
        Start-Sleep -Seconds 30
    }
    return $null
}
function Get-Jobs([long]$RunId) {
    $listing = (& gh api "repos/$repo/actions/runs/$RunId/jobs?per_page=50" 2>$null) -join "`n" | ConvertFrom-Json
    $map = @{}
    foreach ($job in $listing.jobs) { $map[$job.name] = [string]$job.conclusion }
    return $map
}

# Each scenario: what to change, and the conclusion expected of each job.
$catalog = @{
    'fast-fails' = @{
        Change = { param($wt) Write-Lf (Join-Path $wt 'tests/Feature/CiProbeFailureTest.php') "<?php`n`ntest('a required ci-fast check fails', function () {`n    expect(true)->toBeFalse();`n});`n" }
        Expect = @{ 'ci-fast' = 'failure'; 'ci-gate' = 'failure' }
    }
    'e2e-fails' = @{
        Change = { param($wt) Edit-File (Join-Path $wt $gotenbergTest) "->toBe('%PDF')" "->toBe('%NOPE')" }
        Expect = @{ 'ci-e2e' = 'failure'; 'ci-gate' = 'failure' }
    }
    'docs-fails' = @{
        Change = { param($wt) Write-Lf (Join-Path $wt 'docs/09-operations/CI_PROBE.md') "# Uji`n`n[rusak](tidak-ada.md)`n" }
        Expect = @{ 'docs-check' = 'failure'; 'ci-gate' = 'failure' }
    }
    'docs-only' = @{
        Change = { param($wt) Write-Lf (Join-Path $wt 'docs/09-operations/CI_PROBE.md') "# Uji sintetis`n`nBerkas ini hanya untuk PR uji.`n" }
        Expect = @{ 'changes' = 'success'; 'ci-fast' = 'skipped'; 'ci-e2e' = 'skipped'; 'docs-check' = 'success'; 'ci-gate' = 'success' }
    }
    'gotenberg-test-only' = @{
        Change = { param($wt) Edit-File (Join-Path $wt $gotenbergTest) 'Dokumen uji sintetis PRANATA.' 'Dokumen uji sintetis PRANATA, varian uji.' }
        Expect = @{ 'changes' = 'success'; 'ci-fast' = 'success'; 'ci-e2e' = 'success'; 'docs-check' = 'skipped'; 'ci-gate' = 'success' }
    }
    'gotenberg-test-only-fails' = @{
        Change = { param($wt) Edit-File (Join-Path $wt $gotenbergTest) "->toBe('%PDF')" "->toBe('%NOPE')" }
        Expect = @{ 'ci-e2e' = 'failure'; 'docs-check' = 'skipped'; 'ci-gate' = 'failure' }
    }
}

$start = (& git rev-parse $Base)
$mainBefore = ((& git ls-remote origin refs/heads/main) -split '\s+')[0]

foreach ($name in $Scenarios) {
    $tag = [guid]::NewGuid().ToString('N').Substring(0, 6)
    $branch = "chore/task-005-ci-probe-$name-$tag"
    $wt = Join-Path $source ".pranata\ci-probe-$tag"
    $pr = 0
    try {
        & git worktree add --quiet -b $branch $wt $start 2>$null
        if (-not (Test-Path -LiteralPath (Join-Path $wt '.git'))) { throw 'worktree could not be created' }

        if ($name -eq 'stale-cancelled') {
            # TAC-04: a new push cancels the run of the older commit.
            $first = Push-Commit $wt "chore(task-005): ci probe $name, first push"
            $url = (& gh pr create --repo $repo --draft --base main --head $branch --title "chore(task-005): CI probe $name, not for merge" --body 'PR uji sekali pakai TASK-005. Isi sintetis; ditutup tanpa merge.' 2>$null) | Select-Object -Last 1
            $pr = [int](($url -split '/')[-1])
            $runs = @()
            for ($i = 0; $i -lt 40 -and $runs.Count -eq 0; $i++) { Start-Sleep -Seconds 5; $runs = @(Find-Run $first) }
            $second = Push-Commit $wt "chore(task-005): ci probe $name, second push"
            $newer = Wait-Run $second
            $older = @(Find-Run $first)[-1]
            Say $name ($older.conclusion -ceq 'cancelled' -and $newer.conclusion -ceq 'success') "PR=$url old run=$($older.id) $($older.conclusion) on $first; new run=$($newer.id) $($newer.conclusion) on $second"
            continue
        }

        & $catalog[$name].Change $wt
        $sha = Push-Commit $wt "chore(task-005): ci probe $name"
        $url = (& gh pr create --repo $repo --draft --base main --head $branch --title "chore(task-005): CI probe $name, not for merge" --body 'PR uji sekali pakai TASK-005. Isi sintetis; ditutup tanpa merge.' 2>$null) | Select-Object -Last 1
        $pr = [int](($url -split '/')[-1])
        $run = Wait-Run $sha
        if (-not $run) { Say $name $false "PR=$url no completed run for $sha"; continue }
        $jobs = Get-Jobs $run.id
        $problems = @($catalog[$name].Expect.GetEnumerator() | Where-Object { $jobs[$_.Key] -cne $_.Value } | ForEach-Object { "$($_.Key) expected $($_.Value) got '$($jobs[$_.Key])'" })
        $actual = ($jobs.GetEnumerator() | Sort-Object Name | ForEach-Object { "$($_.Key)=$($_.Value)" }) -join ' '
        Say $name ($problems.Count -eq 0) "PR=$url run=$($run.id) head=$sha $actual$(if ($problems.Count) { ' PROBLEMS: ' + ($problems -join '; ') })"
    } catch {
        Say $name $false $_.Exception.Message
    } finally {
        if ($pr -gt 0) { & gh pr close $pr --repo $repo --comment 'Uji selesai; ditutup tanpa merge.' 2>$null | Out-Null }
        Set-Location $source
        if (Test-Path -LiteralPath $wt) { & git worktree remove --force $wt 2>$null }
    }
}

$mainAfter = ((& git ls-remote origin refs/heads/main) -split '\s+')[0]
Say 'main-unchanged' ($mainAfter -eq $mainBefore) "main before=$mainBefore after=$mainAfter"
$failed = @($results | Where-Object { -not $_ }).Count
Write-Output "CI PROBE RESULT: $(if ($failed -eq 0) { 'VERIFIED' } else { "FAILED ($failed)" })"
exit $(if ($failed -eq 0) { 0 } else { 1 })
