# Merge gate of EXECUTOR_OPERATING_MODEL section 8.1, as a pure function over collected facts,
# so every negative path is testable without GitHub and without touching main.

function Get-PranataMergeFailures {
    param([hashtable]$Facts)
    $failures = New-Object System.Collections.ArrayList
    $bootstrapTasks = @('TASK-001', 'TASK-002', 'TASK-003', 'TASK-004')

    if (-not $Facts.TaskId) { [void]$failures.Add('task: PR branch does not name a Task') }
    if ($Facts.RiskClass -notin @('LOW', 'STANDARD', 'HIGH')) { [void]$failures.Add("task: risk class '$($Facts.RiskClass)' could not be read from the contract on main") }
    # A branch name is chosen by the agent; a finished or stopped Task cannot be merged under it. TODO stays valid
    # because section 2 records READY in TASK_PLAN, so the contract on main still says TODO during the Task.
    if ($Facts.TaskStatus -notin @('TODO', 'READY', 'IN_PROGRESS', 'VERIFYING')) { [void]$failures.Add("task: contract status on main is '$($Facts.TaskStatus)'") }
    if ($Facts.CheckpointTaskId -and $Facts.CheckpointTaskId -ne $Facts.TaskId) { [void]$failures.Add("task: PR names $($Facts.TaskId), checkpoint names $($Facts.CheckpointTaskId)") }
    if ($Facts.Bootstrap) {
        if ($Facts.Mode -ne 'SUPERVISED') { [void]$failures.Add('bootstrap: only allowed in a SUPERVISED session') }
        if ($Facts.TaskId -notin $bootstrapTasks) { [void]$failures.Add("bootstrap: not allowed for $($Facts.TaskId) (only TASK-001 to TASK-004)") }
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
