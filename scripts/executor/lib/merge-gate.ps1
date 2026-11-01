# Merge gate of EXECUTOR_OPERATING_MODEL section 8.1, as a pure function over collected facts,
# so every negative path is testable without GitHub and without touching main.

function Get-PranataMergeFailures {
    param([hashtable]$Facts)
    $failures = New-Object System.Collections.ArrayList
    $bootstrapTasks = @('TASK-001', 'TASK-002', 'TASK-003', 'TASK-004')

    if ($Facts.Bootstrap) {
        if ($Facts.Mode -ne 'SUPERVISED') { [void]$failures.Add('bootstrap: only allowed in a SUPERVISED session') }
        if ($Facts.TaskId -notin $bootstrapTasks) { [void]$failures.Add("bootstrap: not allowed for $($Facts.TaskId) (only TASK-001 to TASK-004)") }
    }
    if ($Facts.PrState -ne 'OPEN') { [void]$failures.Add("8.1.5 PR state is $($Facts.PrState), expected OPEN") }
    if ($Facts.PrBase -ne 'main') { [void]$failures.Add("8.1.5 PR base is $($Facts.PrBase), expected main") }
    if ($Facts.IsDraft) { [void]$failures.Add('8.1.5 PR is a draft') }
    if (-not $Facts.Authorized) { [void]$failures.Add('8.1.5 no active authorization covers this merge') }

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
    if ($Facts.CodexStatus -notin @('COMPLETED_NO_FINDINGS', 'COMPLETED_WITH_FINDINGS') -and -not $Facts.SubstituteReviewDone) {
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
        if ($raw -match '(?im)^co-authored-by\s*:' -or $raw -match '(?i)noreply@anthropic\.com' -or $raw -match '(?i)generated with' -or $raw -match ([string][char]0xD83E + [string][char]0xDD16)) {
            [void]$problems.Add("$sha carries attribution text")
        }
    }
    return ,$problems
}
