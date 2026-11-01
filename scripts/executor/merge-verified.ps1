# Guarded verified fast-forward of main to a PR HEAD (D-08 option A; EXECUTOR_OPERATING_MODEL 8.1-8.4).
# -Bootstrap applies section 8.4: TASK-001 to TASK-004 only, SUPERVISED only, no ci-gate yet.
# Review evidence is read from .pranata/executor/review-<sha>.json, written after the reviews finish.
param(
    [Parameter(Mandatory = $true)][int]$Pr,
    [Parameter(Mandatory = $true)][string]$VerifiedSha,
    [Parameter(Mandatory = $true)][string]$TaskId,
    [Parameter(Mandatory = $true)][ValidateSet('LOW', 'STANDARD', 'HIGH')][string]$RiskClass,
    [switch]$Bootstrap,
    [switch]$DryRun,
    [string]$Root
)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
. (Join-Path $PSScriptRoot 'lib\merge-gate.ps1')
if (-not $Root) { $Root = Get-PranataRepoRoot }
$paths = Get-PranataStatePaths $Root

$mode = 'SUPERVISED'
if ($env:PRANATA_EXECUTOR_MODE -eq 'AUTONOMOUS') { $mode = 'AUTONOMOUS' }
if (-not (Test-PranataIdentity $Root)) { throw 'Git identity differs from the Owner reference; stopping (GOV-08).' }
if (Test-Path -LiteralPath $paths.Hold) {
    $holdMode = (Read-PranataJson $paths.Hold).mode
    if ($holdMode -eq 'IMMEDIATE_SAFE_SHUTDOWN') { throw 'IMMEDIATE_SAFE_SHUTDOWN is active; merge refused.' }
}

& git -C $Root fetch origin main --quiet
if ($LASTEXITCODE -ne 0) { throw 'git fetch failed' }
$pr = (& gh pr view $Pr --json state,baseRefName,headRefOid,isDraft | ConvertFrom-Json)
$remoteMain = ((& git -C $Root ls-remote origin refs/heads/main) -split '\s+')[0]
& git -C $Root merge-base --is-ancestor $remoteMain $VerifiedSha
$mainIsAncestor = ($LASTEXITCODE -eq 0)

$ciConclusion = 'missing'
if (-not $Bootstrap) {
    # Filtered in PowerShell: PS 5.1 mangles double quotes inside native arguments such as --jq.
    $checkRuns = ((& gh api "repos/yusufarst/PRANATA-UNY/commits/$($pr.headRefOid)/check-runs") | ConvertFrom-Json).check_runs
    $gate = @($checkRuns | Where-Object { $_.name -ceq 'ci-gate' }) | Select-Object -First 1
    if ($gate -and $gate.conclusion) { $ciConclusion = [string]$gate.conclusion }
}
$review = Read-PranataJson (Join-Path $paths.Dir "review-$VerifiedSha.json")
$touched = @(& git -C $Root diff --name-only "$remoteMain...$VerifiedSha" | Where-Object { $_ -match $script:GuardPathRegex })

$facts = @{
    Bootstrap = [bool]$Bootstrap; Mode = $mode; TaskId = $TaskId; RiskClass = $RiskClass; Authorized = $true
    PrState = $pr.state; PrBase = $pr.baseRefName; IsDraft = [bool]$pr.isDraft; PrHeadSha = $pr.headRefOid; VerifiedSha = $VerifiedSha
    CiGateConclusion = $ciConclusion
    ReviewSha = $(if ($review) { $review.sha }); ReviewStatus = $(if ($review) { $review.status })
    OpenBlockingFindings = $(if ($review) { [int]$review.open_blocking_findings } else { 0 })
    IndependentReviewDone = $(if ($review) { [bool]$review.independent_review_done } else { $false })
    SubstituteReviewDone = $(if ($review) { [bool]$review.substitute_review_done } else { $false })
    CodexStatus = $(if ($review) { $review.codex_status } else { 'NOT_REQUESTED' })
    MainIsAncestor = $mainIsAncestor; RemoteMainSha = $remoteMain; CheckedMainSha = $remoteMain
    GuardPathsTouched = $touched
    CommitIdentityProblems = (Get-PranataCommitProblems -Root $Root -Range "$remoteMain..$VerifiedSha" -OwnerName $script:OwnerName -OwnerEmail $script:OwnerEmail)
}
$failures = Get-PranataMergeFailures -Facts $facts
if ($failures.Count -gt 0) {
    Write-Output 'MERGE REFUSED:'
    $failures | ForEach-Object { Write-Output " - $_" }
    exit 1
}
Write-Output "Gate PASS: main $remoteMain -> $VerifiedSha (PR #$Pr)"
if ($DryRun) { Write-Output 'DRY RUN: nothing pushed.'; exit 0 }

# Re-read main right before the push to shrink the race window; the push itself is a plain fast-forward.
$latest = ((& git -C $Root ls-remote origin refs/heads/main) -split '\s+')[0]
if ($latest -ne $remoteMain) { Write-Output "MERGE REFUSED: main moved to $latest"; exit 1 }
& git -C $Root push origin "${VerifiedSha}:refs/heads/main"
if ($LASTEXITCODE -ne 0) { throw 'fast-forward push failed' }

$after = ((& git -C $Root ls-remote origin refs/heads/main) -split '\s+')[0]
if ($after -ne $VerifiedSha) { throw "remote main is $after, expected $VerifiedSha" }
Write-Output "MERGED: remote main = $after"
for ($attempt = 0; $attempt -lt 6; $attempt++) {
    $state = (& gh pr view $Pr --json state --jq .state)
    if ($state -eq 'MERGED') { break }
    Start-Sleep -Seconds 10
}
Write-Output "PR #$Pr state: $state"
