# Guarded verified fast-forward of main to a PR HEAD (D-08 option A; EXECUTOR_OPERATING_MODEL 8.1-8.4).
# -Bootstrap applies section 8.4 and QUALITY_GATES 4.1: TASK-002 to TASK-004 only, SUPERVISED only, no ci-gate yet.
# Task and risk class come from the PR branch and the contract on main, never from the caller.
# Review evidence is read from .pranata/executor/review-<sha>.json, written after the reviews finish.
param(
    [Parameter(Mandatory = $true)][int]$Pr,
    [Parameter(Mandatory = $true)][string]$VerifiedSha,
    [switch]$Bootstrap,
    [switch]$DryRun,
    [string]$Root
)
$ErrorActionPreference = 'Stop'
# Git writes raw UTF-8; without this PS 5.1 decodes it with the console code page.
[Console]::OutputEncoding = New-Object Text.UTF8Encoding($false)
. (Join-Path $PSScriptRoot 'lib\state.ps1')
. (Join-Path $PSScriptRoot '..\..\.claude\hooks\lib\policy.ps1')
. (Join-Path $PSScriptRoot 'lib\merge-gate.ps1')
if (-not $Root) { $Root = Get-PranataRepoRoot }
$paths = Get-PranataStatePaths $Root

$mode = Get-PranataEffectiveMode $Root
if (-not (Test-PranataIdentity $Root)) { throw 'Git identity differs from the Owner reference; stopping (GOV-08).' }
if (Test-Path -LiteralPath $paths.Hold) {
    if ((Read-PranataJson $paths.Hold).mode -eq 'IMMEDIATE_SAFE_SHUTDOWN') { throw 'IMMEDIATE_SAFE_SHUTDOWN is active; merge refused.' }
}

& git -C $Root fetch origin main --quiet
if ($LASTEXITCODE -ne 0) { throw 'git fetch failed' }
$checkedMain = ((& git -C $Root ls-remote origin refs/heads/main) -split '\s+')[0]

$prInfo = $null
for ($attempt = 0; $attempt -lt 4; $attempt++) {
    $prInfo = (& gh pr view $Pr --repo yusufarst/PRANATA-UNY --json state,baseRefName,headRefName,headRefOid,isDraft,mergeable,title,body) -join "`n" | ConvertFrom-Json
    if ($prInfo.mergeable -ne 'UNKNOWN') { break }
    Start-Sleep -Seconds 5
}
$task = Get-PranataTaskFromBranch $prInfo.headRefName

& git -C $Root merge-base --is-ancestor $checkedMain $VerifiedSha
$mainIsAncestor = ($LASTEXITCODE -eq 0)

$riskClass = $null
$taskStatus = $null
if ($task.TaskId) {
    $contract = (& git -C $Root show "${checkedMain}:docs/11-tasks/$($task.TaskId).md" 2>$null) -join "`n"
    if ($contract -match '(?m)^RISK CLASS: (\w+)') { $riskClass = $Matches[1] }
    if ($contract -match '(?m)^STATUS: (\w+)') { $taskStatus = $Matches[1] }
}
if ($task.Reconcile) { $riskClass = 'LOW' }
$records = (& git -C $Root show "${checkedMain}:docs/00-governance/APPROVAL_RECORDS.md" 2>$null) -join "`n"

$ciConclusion = 'missing'
if (-not $Bootstrap) {
    # Filtered in PowerShell: PS 5.1 mangles double quotes inside native arguments such as --jq.
    $checkRuns = ((& gh api "repos/yusufarst/PRANATA-UNY/commits/$($prInfo.headRefOid)/check-runs") -join "`n" | ConvertFrom-Json).check_runs
    $gate = @($checkRuns | Where-Object { $_.name -ceq 'ci-gate' }) | Select-Object -First 1
    if ($gate -and $gate.conclusion) { $ciConclusion = [string]$gate.conclusion }
}
$bootstrapFailures = $null
$bootstrapRun = $null
if ($Bootstrap -and $script:BootstrapRequiredJobs.ContainsKey([string]$task.TaskId)) {
    $tree = (& git -C $Root rev-parse "$($prInfo.headRefOid)^{tree}")
    $bootstrapRun = Get-PranataBootstrapRun -Repo 'yusufarst/PRANATA-UNY' -Pr $Pr -HeadSha $prInfo.headRefOid -WorkDir $paths.Dir
    $bootstrapFailures = Get-PranataBootstrapFailures -Run $bootstrapRun -TaskId $task.TaskId -Pr $Pr -HeadSha $prInfo.headRefOid -MainSha $checkedMain -Tree $tree
}
$review = Read-PranataJson (Join-Path $paths.Dir "review-$VerifiedSha.json")
$changed = @(& git -C $Root diff --name-only "$checkedMain...$VerifiedSha")
$touched = @($changed | Where-Object { $_ -match $script:GuardPathRegex })
$reconcileOk = (-not $task.Reconcile) -or (Test-PranataReconcileScope $changed $task.TaskId)

# Read last: a move of main while the facts above were collected fails the 8.1.4 check.
$remoteMain = ((& git -C $Root ls-remote origin refs/heads/main) -split '\s+')[0]

$facts = @{
    CheckpointTaskId = (Get-PranataCheckpoint $Root).task_id
    Bootstrap = [bool]$Bootstrap; BootstrapFailures = $bootstrapFailures; Mode = $mode; TaskId = $task.TaskId; RiskClass = $riskClass; TaskStatus = $taskStatus
    Authorized = (Test-PranataAuthorizationText $records $task.TaskId); ReconcileScopeOk = $reconcileOk
    PrState = $prInfo.state; PrBase = $prInfo.baseRefName; IsDraft = [bool]$prInfo.isDraft; PrMergeable = $prInfo.mergeable
    PrHeadSha = $prInfo.headRefOid; VerifiedSha = $VerifiedSha
    PrTextProblem = (Test-PranataAttributionInCommand "$($prInfo.title)`n$($prInfo.body)")
    CiGateConclusion = $ciConclusion
    ReviewSha = $(if ($review) { $review.sha }); ReviewStatus = $(if ($review) { $review.status })
    OpenBlockingFindings = $(if ($review) { [int]$review.open_blocking_findings } else { 0 })
    IndependentReviewDone = $(if ($review) { [bool]$review.independent_review_done } else { $false })
    SubstituteReviewDone = $(if ($review) { [bool]$review.substitute_review_done } else { $false })
    CodexStatus = $(if ($review) { $review.codex_status } else { 'NOT_REQUESTED' })
    MainIsAncestor = $mainIsAncestor; RemoteMainSha = $remoteMain; CheckedMainSha = $checkedMain
    GuardPathsTouched = $touched
    CommitIdentityProblems = (Get-PranataCommitProblems -Root $Root -Range "$checkedMain..$VerifiedSha" -OwnerName $script:OwnerName -OwnerEmail $script:OwnerEmail)
}
$failures = Get-PranataMergeFailures -Facts $facts
if ($failures.Count -gt 0) {
    Write-Output 'MERGE REFUSED:'
    $failures | ForEach-Object { Write-Output " - $_" }
    exit 1
}
Write-Output "Gate PASS: $($task.TaskId) ($riskClass), main $remoteMain -> $VerifiedSha (PR #$Pr)"
if ($bootstrapRun) {
    # QUALITY_GATES 4.1 step 5: these values go into COMPLETION EVIDENCE.
    Write-Output "Bootstrap evidence: run $($bootstrapRun.RunId) attempt $($bootstrapRun.RunAttempt), merge $($bootstrapRun.Evidence.merge_sha), head $($bootstrapRun.Evidence.head_sha), base $($bootstrapRun.Evidence.base_sha)"
}
if ($DryRun) { Write-Output 'DRY RUN: nothing pushed.'; exit 0 }

# Re-read main right before the push to shrink the race window; the push itself is a plain fast-forward.
$latest = ((& git -C $Root ls-remote origin refs/heads/main) -split '\s+')[0]
if ($latest -ne $remoteMain) { Write-Output "MERGE REFUSED: main moved to $latest"; exit 1 }
& git -C $Root push origin "${VerifiedSha}:refs/heads/main"
if ($LASTEXITCODE -ne 0) { throw 'fast-forward push failed' }

$after = ((& git -C $Root ls-remote origin refs/heads/main) -split '\s+')[0]
if ($after -ne $VerifiedSha) { throw "remote main is $after, expected $VerifiedSha" }
Write-Output "MERGED: remote main = $after"
$state = $null
for ($attempt = 0; $attempt -lt 6; $attempt++) {
    $state = (& gh pr view $Pr --repo yusufarst/PRANATA-UNY --json state --jq .state)
    if ($state -eq 'MERGED') { break }
    Start-Sleep -Seconds 10
}
Write-Output "PR #$Pr state: $state"
