# C-16 (QUALITY_GATES 4.1): the bootstrap evidence gate against real GitHub runs, synthetic content only.
# Part 1 runs on a disposable PR in this repository: merge-verified.ps1 -Bootstrap -DryRun, never a push to main.
# Part 2 runs on the Owner-approved sandbox, the only place where a base may really move: the library functions directly.
# Repository names are fixed here on purpose. The PR is closed without merge; its branch stays.
$ErrorActionPreference = 'Continue'
foreach ($item in @(Get-ChildItem Env: | Where-Object { $_.Name -match '^(CLAUDE|ANTHROPIC_BASE_URL)' })) { Remove-Item -LiteralPath ("Env:" + $item.Name) }
$source = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
. (Join-Path $source 'scripts\executor\lib\state.ps1')
. (Join-Path $source '.claude\hooks\lib\policy.ps1')
. (Join-Path $source 'scripts\executor\lib\merge-gate.ps1')
Set-Location $source

$ownRepo = 'yusufarst/PRANATA-UNY'
$sandboxRepo = 'yusufarst/pranata-uny-sandbox-c10'
$run = [guid]::NewGuid().ToString('N').Substring(0, 6)
$branch = "chore/task-002-c16-probe-$run"
$wt = Join-Path $source ".pranata\c16-$run"
$sandboxWork = Join-Path $env:TEMP "pranata-sandbox-c16-$run"
$workflowPath = '.github/workflows/bootstrap.yml'
$results = New-Object System.Collections.ArrayList
$merge = Join-Path $source 'scripts\executor\merge-verified.ps1'

# The gate job and header come from the real workflow; only app-boot is a fast stub.
$real = [IO.File]::ReadAllText((Join-Path $source $workflowPath)) -replace "`r`n", "`n"
$headerEnd = $real.IndexOf("  app-boot:`n")
$gateStart = $real.IndexOf("  bootstrap-gate:`n")
if ($headerEnd -lt 0 -or $gateStart -lt 0) { throw 'real workflow does not have the expected jobs' }
$header = $real.Substring(0, $headerEnd)
$gateJob = $real.Substring($gateStart)
function New-Job([string]$Name, [string]$Step, [string]$If = '') {
    $condition = if ($If) { "    if: $If`n" } else { '' }
    return "  ${Name}:`n    name: $Name`n$condition    runs-on: ubuntu-24.04`n    timeout-minutes: 8`n    steps:`n      - run: $Step`n`n"
}
function Get-Stub([string]$Kind) {
    switch ($Kind) {
        'green' { return $header + (New-Job 'app-boot' 'echo ok') + $gateJob }
        'fail' { return $header + (New-Job 'app-boot' 'exit 1') + $gateJob }
        'slow' { return $header + (New-Job 'app-boot' 'sleep 300') + $gateJob }
        'required-skipped' { return $header + (New-Job 'app-boot' 'echo ok' 'false') + $gateJob }
        'extra-skipped' { return $header + (New-Job 'app-boot' 'echo ok') + (New-Job 'extra' 'echo ok' 'false') + $gateJob }
    }
    throw "unknown stub $Kind"
}

function Say([string]$Name, [bool]$Ok, [string]$Evidence) {
    $status = if ($Ok) { 'VERIFIED' } else { 'FAILED' }
    [void]$results.Add(@{ name = $Name; ok = $Ok; evidence = $Evidence })
    Write-Output ("{0} {1}: {2}" -f $Name, $status, $Evidence)
}
function Set-Workflow([string]$Root, [string]$Content) {
    $file = Join-Path $Root $workflowPath
    if ($null -eq $Content) { if (Test-Path -LiteralPath $file) { Remove-Item -LiteralPath $file }; return }
    [void](New-Item -ItemType Directory -Path (Split-Path -Parent $file) -Force)
    [IO.File]::WriteAllText($file, $Content)
}
function Push-Commit([string]$Root, [string]$Message) {
    Add-Content -LiteralPath (Join-Path $Root 'c16-marker.txt') -Value ([guid]::NewGuid().ToString()) -Encoding ASCII
    & git -C $Root -c core.autocrlf=false add -A 2>$null
    & git -C $Root -c core.autocrlf=false commit --quiet -m $Message 2>$null
    $sha = (& git -C $Root rev-parse HEAD)
    & git -C $Root push --quiet origin "HEAD:refs/heads/$(& git -C $Root branch --show-current)" 2>$null
    return $sha
}
function Find-Runs([string]$Repo, [string]$Sha) {
    try { return @((Invoke-PranataGhJson "repos/$Repo/actions/workflows/bootstrap.yml/runs?event=pull_request&head_sha=$Sha&per_page=100").workflow_runs) } catch { return @() }
}
function Wait-Run([string]$Repo, [string]$Sha, [int]$Seconds = 480) {
    $deadline = (Get-Date).AddSeconds($Seconds)
    while ((Get-Date) -lt $deadline) {
        $found = @(Find-Runs $Repo $Sha | Sort-Object { [long]$_.id })
        if ($found.Count -gt 0 -and $found[-1].status -ceq 'completed') { return $found[-1] }
        Start-Sleep -Seconds 10
    }
    return $null
}
function Set-Review([string]$Sha) {
    $state = Initialize-PranataStateDirs $wt
    $body = '{"sha":"' + $Sha + '","status":"PASS","open_blocking_findings":0,"independent_review_done":true,"substitute_review_done":false,"codex_status":"COMPLETED_NO_FINDINGS"}'
    [IO.File]::WriteAllText((Join-Path $state.Dir "review-$Sha.json"), $body)
}
function Invoke-Gate([int]$Pr, [string]$Sha) {
    Set-Review $Sha
    $output = & powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -File $merge -Pr $Pr -VerifiedSha $Sha -Bootstrap -DryRun -Root $wt 2>&1
    return @{ Out = ($output | Out-String); Code = $LASTEXITCODE }
}
function Test-Refused([string]$Name, [int]$Pr, [string]$Sha, [string]$Pattern, [string]$Detail) {
    $gate = Invoke-Gate $Pr $Sha
    $line = (($gate.Out -split "`n") | Where-Object { $_ -match $Pattern } | Select-Object -First 1)
    Say $Name ($gate.Code -ne 0 -and [bool]$line) "$Detail; head=$Sha; refused=$($gate.Code -ne 0); line=$([string]$line.Trim())"
}

$mainBefore = ((& git ls-remote origin refs/heads/main) -split '\s+')[0]
$pr = 0
try {
    # --- Part 1: disposable PR in this repository.
    & git fetch --quiet origin main 2>$null
    & git worktree add --quiet -b $branch $wt origin/main 2>$null
    if (-not (Test-Path -LiteralPath (Join-Path $wt '.git'))) { throw 'worktree could not be created' }
    [void](Initialize-PranataStateDirs $wt)
    Add-Content -LiteralPath (Join-Path $wt '.git-c16-exclude-check') -Value '' -ErrorAction SilentlyContinue
    Remove-Item -LiteralPath (Join-Path $wt '.git-c16-exclude-check') -ErrorAction SilentlyContinue

    Set-Workflow $wt (Get-Stub 'fail')
    $sha = Push-Commit $wt 'chore(task-002): c16 probe, failing job'
    $url = (& gh pr create --repo $ownRepo --draft --base main --head $branch --title 'chore(task-002): C-16 disposable probe, not for merge' --body 'Uji sekali pakai C-16 (QUALITY_GATES 4.1). Isi sintetis; ditutup tanpa merge.' 2>$null) | Select-Object -Last 1
    $pr = [int](($url -split '/')[-1])
    Write-Output "PR: $url"
    $r = Wait-Run $ownRepo $sha
    Test-Refused 'failed-job' $pr $sha "job 'app-boot' is 'failure'" "run=$($r.id) conclusion=$($r.conclusion)"

    Set-Workflow $wt (Get-Stub 'slow')
    $sha = Push-Commit $wt 'chore(task-002): c16 probe, cancelled job'
    $found = @()
    for ($i = 0; $i -lt 30 -and $found.Count -eq 0; $i++) { Start-Sleep -Seconds 5; $found = @(Find-Runs $ownRepo $sha) }
    Start-Sleep -Seconds 20
    Test-Refused 'run-in-progress' $pr $sha "status is '(in_progress|queued)'" "run=$($found[0].id) before completion"
    & gh run cancel $found[0].id --repo $ownRepo 2>$null | Out-Null
    $r = Wait-Run $ownRepo $sha
    Test-Refused 'cancelled-job' $pr $sha "conclusion is 'cancelled'" "run=$($r.id) conclusion=$($r.conclusion)"

    Set-Workflow $wt (Get-Stub 'required-skipped')
    $sha = Push-Commit $wt 'chore(task-002): c16 probe, skipped required job'
    $r = Wait-Run $ownRepo $sha
    Test-Refused 'skipped-required-job' $pr $sha "job 'app-boot' is 'skipped'" "run=$($r.id) conclusion=$($r.conclusion)"

    Set-Workflow $wt (Get-Stub 'extra-skipped')
    $sha = Push-Commit $wt 'chore(task-002): c16 probe, skipped extra job'
    $r = Wait-Run $ownRepo $sha
    Test-Refused 'skipped-extra-job' $pr $sha "job 'extra' is 'skipped'" "run=$($r.id) conclusion=$($r.conclusion)"

    Set-Workflow $wt $null
    $sha = Push-Commit $wt 'chore(task-002): c16 probe, no workflow'
    Start-Sleep -Seconds 60
    Test-Refused 'missing-run' $pr $sha '4\.1\.2 no bootstrap run' "runs for this HEAD=$(@(Find-Runs $ownRepo $sha).Count)"

    Set-Workflow $wt (Get-Stub 'green')
    $greenSha = Push-Commit $wt 'chore(task-002): c16 probe, green run'
    $r = Wait-Run $ownRepo $greenSha
    & gh pr ready $pr --repo $ownRepo 2>$null | Out-Null
    Start-Sleep -Seconds 10
    $gate = Invoke-Gate $pr $greenSha
    $line = (($gate.Out -split "`n") | Where-Object { $_ -match 'Bootstrap evidence' } | Select-Object -First 1)
    Say 'green-accepted' ($gate.Code -eq 0 -and $gate.Out -match 'Gate PASS') "run=$($r.id) attempt=$($r.run_attempt) conclusion=$($r.conclusion); $([string]$line.Trim()); refusals=$(if ($gate.Code -ne 0) { $gate.Out -replace '\s+', ' ' } else { 'none' })"

    # The head_sha filter, judged on real results: the PR HEAD finds the run, the synthetic merge SHA and another SHA find none.
    $evidenceRun = Get-PranataBootstrapRun -Repo $ownRepo -Pr $pr -HeadSha $greenSha -WorkDir (Join-Path $wt '.pranata\executor')
    $mergeSha = [string]$evidenceRun.Evidence.merge_sha
    $byHead = @(Find-Runs $ownRepo $greenSha)
    $byMerge = @(Find-Runs $ownRepo $mergeSha)
    $byOther = @(Find-Runs $ownRepo $mainBefore)
    $allMatch = @($byHead | Where-Object { $_.head_sha -ceq $greenSha }).Count -eq $byHead.Count
    Say 'head-sha-filter' ($byHead.Count -ge 1 -and $allMatch -and $byOther.Count -eq 0) "head=$greenSha runs=$($byHead.Count) allMatch=$allMatch; merge=$mergeSha runs=$($byMerge.Count); main=$mainBefore runs=$($byOther.Count)"

    $staleSha = Push-Commit $wt 'chore(task-002): c16 probe, new push'
    Start-Sleep -Seconds 5
    Test-Refused 'stale-after-push-old-sha' $pr $greenSha '8\.1\.1' "evidence was for $greenSha, PR moved to $staleSha"
    [void](Wait-Run $ownRepo $staleSha)
    $staleFacts = Get-PranataBootstrapRun -Repo $ownRepo -Pr $pr -HeadSha $greenSha -WorkDir (Join-Path $wt '.pranata\executor')
    $staleFailures = Get-PranataBootstrapFailures -Run $staleFacts -TaskId 'TASK-002' -Pr $pr -HeadSha $staleSha -MainSha $mainBefore -Tree (& git -C $wt rev-parse "$staleSha^{tree}")
    Say 'stale-evidence-for-new-head' (($staleFailures -join ';') -match 'evidence head_sha') "old run=$($staleFacts.RunId) judged against head=$staleSha; failures=$($staleFailures -join ' | ')"

    # --- Part 2: the sandbox, where the base can really move.
    & git clone --quiet "https://github.com/$sandboxRepo.git" $sandboxWork 2>&1 | Out-Null
    if (-not (Test-Path -LiteralPath (Join-Path $sandboxWork '.git'))) { throw 'sandbox could not be cloned' }
    $sandboxHead = "c16-head-$run"
    & git -C $sandboxWork switch --quiet -c $sandboxHead origin/main 2>$null
    $mainBase = (& git -C $sandboxWork rev-parse origin/main)
    Set-Workflow $sandboxWork (Get-Stub 'green')
    $h1 = Push-Commit $sandboxWork 'chore: c16 probe head'
    $sandboxUrl = (& gh pr create --repo $sandboxRepo --base main --head $sandboxHead --title "c16 probe $run" --body 'Synthetic capability probe.' 2>$null) | Select-Object -Last 1
    $sandboxPr = [int](($sandboxUrl -split '/')[-1])
    Write-Output "Sandbox PR: $sandboxUrl"
    $r1 = Wait-Run $sandboxRepo $h1
    $treeH1 = (& git -C $sandboxWork rev-parse "$h1^{tree}")
    $facts1 = Get-PranataBootstrapRun -Repo $sandboxRepo -Pr $sandboxPr -HeadSha $h1 -WorkDir $env:TEMP
    $failures1 = Get-PranataBootstrapFailures -Run $facts1 -TaskId 'TASK-002' -Pr $sandboxPr -HeadSha $h1 -MainSha $mainBase -Tree $treeH1
    Say 'sandbox-green-baseline' ($failures1.Count -eq 0) "run=$($r1.id) head=$h1 base=$mainBase failures=$($failures1 -join ' | ')"

    # Move the base through its own ruleset: ci-gate first on a branch, then a fast-forward.
    $moveBranch = "c16-move-$run"
    & git -C $sandboxWork switch --quiet -c $moveBranch origin/main 2>$null
    Set-Content -LiteralPath (Join-Path $sandboxWork "moved-$run.txt") -Value 'synthetic' -Encoding ASCII
    & git -C $sandboxWork -c core.autocrlf=false add "moved-$run.txt" 2>$null
    & git -C $sandboxWork -c core.autocrlf=false commit --quiet -m 'chore: c16 probe base move' 2>$null
    $m2 = (& git -C $sandboxWork rev-parse HEAD)
    & git -C $sandboxWork push --quiet origin "${moveBranch}:refs/heads/$moveBranch" 2>$null
    $gateState = 'missing'
    for ($i = 0; $i -lt 40; $i++) {
        $checks = ((& gh api "repos/$sandboxRepo/commits/$m2/check-runs" 2>$null) -join "`n" | ConvertFrom-Json).check_runs
        $check = @($checks | Where-Object { $_.name -ceq 'ci-gate' }) | Select-Object -First 1
        if ($check -and $check.conclusion) { $gateState = [string]$check.conclusion; break }
        Start-Sleep -Seconds 10
    }
    & git -C $sandboxWork push --quiet origin "${m2}:refs/heads/main" 2>$null
    $sandboxMain = ((& git ls-remote "https://github.com/$sandboxRepo.git" refs/heads/main) -split '\s+')[0]
    if ($sandboxMain -ne $m2) { Say 'sandbox-base-moved' $false "main did not move: ci-gate=$gateState main=$sandboxMain expected=$m2" }
    else {
        $failures2 = Get-PranataBootstrapFailures -Run $facts1 -TaskId 'TASK-002' -Pr $sandboxPr -HeadSha $h1 -MainSha $sandboxMain -Tree $treeH1
        Say 'sandbox-base-moved' (($failures2 -join ';') -match 'evidence base_sha') "run=$($r1.id) base moved $mainBase -> $sandboxMain; failures=$($failures2 -join ' | ')"

        # A new run after the move tests a merge whose tree differs from the HEAD tree, because main is no longer an ancestor.
        & git -C $sandboxWork switch --quiet $sandboxHead 2>$null
        $h2 = Push-Commit $sandboxWork 'chore: c16 probe head after base move'
        $r2 = Wait-Run $sandboxRepo $h2
        $treeH2 = (& git -C $sandboxWork rev-parse "$h2^{tree}")
        $facts2 = Get-PranataBootstrapRun -Repo $sandboxRepo -Pr $sandboxPr -HeadSha $h2 -WorkDir $env:TEMP
        $failures3 = Get-PranataBootstrapFailures -Run $facts2 -TaskId 'TASK-002' -Pr $sandboxPr -HeadSha $h2 -MainSha $sandboxMain -Tree $treeH2
        $onlyTree = (@($failures3).Count -eq 1) -and (($failures3 -join ';') -match 'evidence tree_sha')
        Say 'sandbox-tree-mismatch' $onlyTree "run=$($r2.id) head=$h2 base=$($facts2.Evidence.base_sha) evidenceTree=$($facts2.Evidence.tree_sha) headTree=$treeH2 merge=$($facts2.Evidence.merge_sha); failures=$($failures3 -join ' | ')"
    }
    & gh pr close $sandboxPr --repo $sandboxRepo 2>$null | Out-Null
} catch {
    Say 'probe-error' $false $_.Exception.Message
} finally {
    if ($pr -gt 0) {
        & gh pr close $pr --repo $ownRepo --comment 'Uji C-16 selesai; ditutup tanpa merge.' 2>$null | Out-Null
        Write-Output "PR state: $(& gh pr view $pr --repo $ownRepo --json state --jq .state 2>$null)"
    }
    $mainAfter = ((& git ls-remote origin refs/heads/main) -split '\s+')[0]
    Say 'main-unchanged' ($mainAfter -eq $mainBefore) "main before=$mainBefore after=$mainAfter"
    Set-Location $source
    if (Test-Path -LiteralPath $wt) { & git worktree remove --force $wt 2>$null }
    if ($sandboxWork -match 'pranata-sandbox-c16-[0-9a-f]{6}$' -and (Test-Path -LiteralPath $sandboxWork)) { Remove-Item -LiteralPath $sandboxWork -Recurse -Force -ErrorAction SilentlyContinue }
}
$failed = @($results | Where-Object { -not $_.ok }).Count
Write-Output "C-16 RESULT: $(if ($failed -eq 0) { 'VERIFIED' } else { "FAILED ($failed)" })"
exit $(if ($failed -eq 0) { 0 } else { 1 })
