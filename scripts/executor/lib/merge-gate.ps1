# Merge gate of EXECUTOR_OPERATING_MODEL section 8.1, as a pure function over collected facts,
# so every negative path is testable without GitHub and without touching main.

# Minimum job set per Task (QUALITY_GATES 4.1). A Task not listed here cannot use bootstrap evidence.
$script:BootstrapRequiredJobs = @{
    'TASK-002' = @('app-boot', 'bootstrap-gate')
    'TASK-003' = @('app-boot', 'db-integration', 'bootstrap-gate')
    'TASK-004' = @('app-boot', 'db-integration', 'quality', 'bootstrap-gate')
}

function Get-PranataMergeFailures {
    param([hashtable]$Facts)
    $failures = New-Object System.Collections.ArrayList

    if (-not $Facts.TaskId) { [void]$failures.Add('task: PR branch does not name a Task') }
    if ($Facts.RiskClass -notin @('LOW', 'STANDARD', 'HIGH')) { [void]$failures.Add("task: risk class '$($Facts.RiskClass)' could not be read from the contract on main") }
    # A branch name is chosen by the agent; a finished or stopped Task cannot be merged under it. TODO stays valid
    # because section 2 records READY in TASK_PLAN, so the contract on main still says TODO during the Task.
    if ($Facts.TaskStatus -notin @('TODO', 'READY', 'IN_PROGRESS', 'VERIFYING')) { [void]$failures.Add("task: contract status on main is '$($Facts.TaskStatus)'") }
    if ($Facts.CheckpointTaskId -and $Facts.CheckpointTaskId -ne $Facts.TaskId) { [void]$failures.Add("task: PR names $($Facts.TaskId), checkpoint names $($Facts.CheckpointTaskId)") }
    if ($Facts.Bootstrap) {
        if ($Facts.Mode -ne 'SUPERVISED') { [void]$failures.Add('bootstrap: only allowed in a SUPERVISED session') }
        if (-not $script:BootstrapRequiredJobs.ContainsKey([string]$Facts.TaskId)) { [void]$failures.Add("bootstrap: not allowed for $($Facts.TaskId) (only TASK-002 to TASK-004)") }
        # Fail closed: bootstrap mode without collected evidence is never a pass.
        if ($null -eq $Facts.BootstrapFailures) { [void]$failures.Add('bootstrap 4.1: evidence was not collected') }
        foreach ($item in @($Facts.BootstrapFailures)) { if ($item) { [void]$failures.Add($item) } }
    }
    if ($Facts.PrState -ne 'OPEN') { [void]$failures.Add("8.1.5 PR state is $($Facts.PrState), expected OPEN") }
    if ($Facts.PrBase -ne 'main') { [void]$failures.Add("8.1.5 PR base is $($Facts.PrBase), expected main") }
    if ($Facts.IsDraft) { [void]$failures.Add('8.1.5 PR is a draft') }
    if ($Facts.PrMergeable -ne 'MERGEABLE') { [void]$failures.Add("8.1.5 PR mergeable state is '$($Facts.PrMergeable)'") }
    if (-not $Facts.Authorized) { [void]$failures.Add('8.1.5 no active authorization on main covers this Task') }
    if (-not $Facts.ReconcileScopeOk) { [void]$failures.Add('8.3 a reconciliation PR may change documents only') }
    if ($Facts.PrTextProblem) { [void]$failures.Add('GOV-08 PR title or body carries attribution') }

    if (-not $Facts.PrHeadSha -or $Facts.PrHeadSha -ne $Facts.VerifiedSha) {
        [void]$failures.Add("8.1.1 PR HEAD $($Facts.PrHeadSha) differs from verified SHA $($Facts.VerifiedSha)")
    }
    if (-not $Facts.Bootstrap) {
        if ($Facts.CiGateConclusion -ne 'success') { [void]$failures.Add("8.1.2 ci-gate on PR HEAD is '$($Facts.CiGateConclusion)', expected success") }
    }
    if ($Facts.ReviewSha -ne $Facts.PrHeadSha) { [void]$failures.Add('8.1.3 review disposition is missing or for another SHA') }
    if ($Facts.ReviewStatus -ne 'PASS') { [void]$failures.Add("8.1.3 review status is '$($Facts.ReviewStatus)', expected PASS") }
    if ($Facts.OpenBlockingFindings -gt 0) { [void]$failures.Add("8.1.3 $($Facts.OpenBlockingFindings) blocking finding(s) open") }
    if ($Facts.RiskClass -eq 'HIGH' -and -not $Facts.IndependentReviewDone) { [void]$failures.Add('8.1.3 independent review missing for a HIGH Task') }
    # Section 8.3: reconciliation PRs (LOW) carry no Codex review.
    if ($Facts.RiskClass -ne 'LOW' -and $Facts.CodexStatus -notin @('COMPLETED_NO_FINDINGS', 'COMPLETED_WITH_FINDINGS') -and -not $Facts.SubstituteReviewDone) {
        [void]$failures.Add("8.1.3 Codex status '$($Facts.CodexStatus)' is not final and no substitute review is recorded")
    }
    if (-not $Facts.MainIsAncestor) { [void]$failures.Add('8.1.4 main is not an ancestor of PR HEAD; a fast-forward is impossible') }
    if ($Facts.RemoteMainSha -ne $Facts.CheckedMainSha) { [void]$failures.Add('8.1.4 main moved since the check') }
    if ($Facts.GuardPathsTouched.Count -gt 0 -and $Facts.Mode -ne 'SUPERVISED') {
        [void]$failures.Add("8.1.6 guardrail paths touched, merge is SUPERVISED only: $($Facts.GuardPathsTouched -join ', ')")
    }
    if ($Facts.CommitIdentityProblems.Count -gt 0) {
        [void]$failures.Add("GOV-08 commit identity or attribution problem: $($Facts.CommitIdentityProblems -join '; ')")
    }
    return ,$failures
}

# QUALITY_GATES 4.1 steps 2-4 as a pure function over what GitHub reported. A pull_request run tests the synthetic
# merge commit, not the PR HEAD, so the link to HEAD, base and tree comes from run metadata and the evidence artifact.
function Get-PranataBootstrapFailures {
    param([hashtable]$Run, [string]$TaskId, [int]$Pr, [string]$HeadSha, [string]$MainSha, [string]$Tree)
    $failures = New-Object System.Collections.ArrayList
    $required = $script:BootstrapRequiredJobs[$TaskId]
    if (-not $required) { return ,$failures }
    if ($Run.LookupError) { [void]$failures.Add("bootstrap 4.1.2 run lookup failed: $($Run.LookupError)"); return ,$failures }
    if (-not $Run.RunId) { [void]$failures.Add("bootstrap 4.1.2 no bootstrap run for PR $Pr on HEAD $HeadSha"); return ,$failures }

    if ($Run.RunStatus -cne 'completed') { [void]$failures.Add("bootstrap 4.1.3 run $($Run.RunId) status is '$($Run.RunStatus)', expected completed") }
    if ($Run.RunConclusion -cne 'success') { [void]$failures.Add("bootstrap 4.1.3 run $($Run.RunId) conclusion is '$($Run.RunConclusion)', expected success") }
    if ($Run.JobError) { [void]$failures.Add("bootstrap 4.1.3 job list failed: $($Run.JobError)") }
    $jobs = @($Run.Jobs)
    if ($Run.JobsTotal -ne $jobs.Count) { [void]$failures.Add("bootstrap 4.1.3 job list is incomplete: $($jobs.Count) of $($Run.JobsTotal)") }
    foreach ($name in $required) {
        if (@($jobs | Where-Object { $_.name -ceq $name }).Count -eq 0) { [void]$failures.Add("bootstrap 4.1.3 required job '$name' is missing") }
    }
    foreach ($job in $jobs) {
        if ($job.conclusion -cne 'success') { [void]$failures.Add("bootstrap 4.1.3 job '$($job.name)' is '$($job.conclusion)', expected success") }
    }

    $evidence = $Run.Evidence
    if (-not $evidence) {
        [void]$failures.Add("bootstrap 4.1.4 evidence artifact unavailable: $($Run.EvidenceError)")
        return ,$failures
    }
    $checks = @(
        @('pr', [string]$Pr), @('head_sha', $HeadSha), @('base_sha', $MainSha), @('tree_sha', $Tree),
        @('run_id', [string]$Run.RunId), @('run_attempt', [string]$Run.RunAttempt)
    )
    foreach ($check in $checks) {
        $actual = [string]$evidence.($check[0])
        if (-not $check[1] -or $actual -cne $check[1]) { [void]$failures.Add("bootstrap 4.1.4 evidence $($check[0]) is '$actual', expected '$($check[1])'") }
    }
    if ([string]$evidence.merge_sha -cnotmatch '^[0-9a-f]{40}$') { [void]$failures.Add("bootstrap 4.1.4 evidence merge_sha is '$($evidence.merge_sha)'") }
    return ,$failures
}

# Reads a GitHub API GET as an object; native stderr never decides, the exit code does.
function Invoke-PranataGhJson([string]$Path) {
    $ErrorActionPreference = 'Continue'
    $output = & gh api $Path 2>$null
    if ($LASTEXITCODE -ne 0) { throw "gh api $Path exited $LASTEXITCODE" }
    return (($output -join "`n") | ConvertFrom-Json)
}

# Collects what Get-PranataBootstrapFailures judges. Any error is recorded, so it can only lead to a refusal.
function Get-PranataBootstrapRun {
    param([string]$Repo, [int]$Pr, [string]$HeadSha, [string]$WorkDir)
    $result = @{
        LookupError = $null; RunId = $null; RunAttempt = $null; RunStatus = $null; RunConclusion = $null
        Jobs = @(); JobsTotal = -1; JobError = $null; Evidence = $null; EvidenceError = 'not read'
    }
    try {
        $listing = Invoke-PranataGhJson "repos/$Repo/actions/workflows/bootstrap.yml/runs?event=pull_request&head_sha=$HeadSha&per_page=100"
        # The filter is rechecked here: only a run proven to belong to this PR and HEAD counts.
        $runs = @($listing.workflow_runs | Where-Object {
            $_.head_sha -ceq $HeadSha -and $_.event -ceq 'pull_request' -and
            @($_.pull_requests | Where-Object { $_.number -eq $Pr }).Count -gt 0
        } | Sort-Object { [long]$_.id }, { [int]$_.run_attempt })
        if ($runs.Count -eq 0) { return $result }
        $latest = $runs[-1]
        $result.RunId = $latest.id; $result.RunAttempt = $latest.run_attempt
        $result.RunStatus = $latest.status; $result.RunConclusion = $latest.conclusion
    } catch {
        $result.LookupError = $_.Exception.Message
        return $result
    }
    try {
        $jobs = Invoke-PranataGhJson "repos/$Repo/actions/runs/$($result.RunId)/attempts/$($result.RunAttempt)/jobs?per_page=100"
        $result.JobsTotal = [int]$jobs.total_count
        $result.Jobs = @($jobs.jobs | ForEach-Object { @{ name = $_.name; conclusion = $_.conclusion } })
    } catch { $result.JobError = $_.Exception.Message }

    $dir = Join-Path $WorkDir ("bootstrap-evidence-" + [guid]::NewGuid().ToString('N').Substring(0, 8))
    try {
        $ErrorActionPreference = 'Continue'
        & gh run download $result.RunId -R $Repo -n bootstrap-evidence -D $dir 2>$null
        if ($LASTEXITCODE -ne 0) { throw "gh run download exited $LASTEXITCODE" }
        $result.Evidence = [IO.File]::ReadAllText((Join-Path $dir 'bootstrap-evidence.json')) | ConvertFrom-Json
    } catch {
        $result.EvidenceError = $_.Exception.Message
    } finally {
        if (Test-Path -LiteralPath $dir) { Remove-Item -LiteralPath $dir -Recurse -Force }
    }
    return $result
}

# Reads the PR branch name; the Task is never taken from a caller-supplied argument.
function Get-PranataTaskFromBranch([string]$Branch) {
    if ($Branch -match '^(feature|chore|fix)/task-(\d{3})-') { return @{ TaskId = "TASK-$($Matches[2])"; Reconcile = $false } }
    if ($Branch -match '^docs/reconcile-task-(\d{3})(-r\d+)?$') { return @{ TaskId = "TASK-$($Matches[1])"; Reconcile = $true } }
    return @{ TaskId = $null; Reconcile = $false }
}

# Section 8.3: exactly these three documents. Anything wider could rewrite a risk class or an authorization.
function Test-PranataReconcileScope([string[]]$Changed, [string]$TaskId) {
    $allowed = @("docs/11-tasks/$TaskId.md", 'docs/11-tasks/TASK_PLAN.md', 'docs/handoff/CURRENT_HANDOFF.md')
    return (@($Changed | Where-Object { $_ -cnotin $allowed }).Count -eq 0)
}

function Test-PranataAuthorizationText([string]$Records, [string]$TaskId) {
    if ($TaskId -notmatch '^TASK-(\d{3})$') { return $false }
    $number = [int]$Matches[1]
    if ($number -lt 1 -or $number -gt 78) { return $false }
    if ($Records -notmatch '(?ms)^## AUTH-013\s*$(.*?)(^## |\z)') { return $false }
    return ($Matches[1] -match 'State: \*\*ACTIVE\*\*')
}

# Raw metadata check over every commit the merge would publish.
function Get-PranataCommitProblems {
    param([string]$Root, [string]$Range, [string]$OwnerName, [string]$OwnerEmail)
    $problems = New-Object System.Collections.ArrayList
    $expected = "$OwnerName <$OwnerEmail>"
    foreach ($sha in @(& git -C $Root rev-list $Range)) {
        $raw = (& git -C $Root cat-file -p $sha) -join "`n"
        foreach ($field in @('author', 'committer')) {
            if ($raw -notmatch "(?m)^$field (.+) \d+ [+-]\d{4}$") { [void]$problems.Add("$sha has no $field"); continue }
            if ($Matches[1] -cne $expected) { [void]$problems.Add("$sha $field is '$($Matches[1])'") }
        }
        if (Test-PranataAttributionInCommand $raw) { [void]$problems.Add("$sha carries attribution text") }
    }
    return ,$problems
}
