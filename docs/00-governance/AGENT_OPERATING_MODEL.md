# Agent operating model

Status: APPROVED (APPR-004; AUTH-009 checkpoint only) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

## Roles

| Role | Responsibility | Boundary |
|---|---|---|
| Owner | Final decisions, phase approval, material scope/architecture/cost approval | Explicit approval is never inferred from silence |
| Codex / ChatGPT Planning Agent | Authorized phase documentation, evidence, decisions, gaps and executor clarity | APPR-004 P3 approved; AUTH-009 single checkpoint only until verified publication, then completed; no P4–P11 or implementation authority |
| Claude Code future Execution Agent | Implement and verify an approved READY Task, update progress/evidence | No planning redesign or hidden-chat dependency |
| Review Agent | Check authority, scope, tests/evidence, drift and false completion | Findings are evidence; cannot grant Owner approval |
| Release Agent | Verify approved release path, staging/UAT/regression/rollback declarations | No release authorization exists yet |
| Maintenance Agent | Safely reproduce and resolve a bounded maintenance Task | Production DB zero-touch; preserve historical semantics |

Provider replacement does not alter canonical project truth. Tool adapters point to AGENTS.md.

## Phase authorization

P0/P1/P2 **DONE — APPROVED AND PUBLISHED** under APPR-001/APPR-002/APPR-003. P3 **DONE — APPROVED** under APPR-004. AUTH-002–AUTH-008 **HISTORICAL / COMPLETED**. AUTH-009 authorizes only this P3 checkpoint until successful push/live verification; afterward it is automatically **HISTORICAL / COMPLETED**, current active phase authorization **NONE**. P4–P11 **TODO / NOT AUTHORIZED**. Planning Freeze **NOT REACHED**; Tasks **NONE**; Task Baseline **NOT READY**; Execution **NOT AUTHORIZED**; application implementation **NONE**. [AUTH-009](APPROVAL_RECORDS.md#auth-009) authorizes only the approved P3 checkpoint. P2 qualifications remain binding. Each later phase needs separate explicit authorization.

Finish authorized artifacts → self-review/evidence → VERIFYING → independent review → Owner approves the exact candidate or requests corrections. Owner approval moves a phase to DONE; agent PASS, folders, conversation or commit do not grant approval.

## Future execution boundary

No application implementation before P0–P11 planning, explicit Owner approvals, planning freeze and a READY Task baseline. P11 must create the definitive stable Task IDs/counts, dependency graph, minimal EXECUTION_CONTEXT, master checklist and bounded contracts; do not create those artifacts now.

Future read order: AGENTS.md → EXECUTION_CONTEXT → CURRENT_HANDOFF → TASK_PLAN → active READY Task → exact referenced sections → targeted source/impact analysis → current docs if required. If a prerequisite is absent, contradictory or not approved, stop dependent execution and record the gap. Do not read the entire planning corpus per Task or invent missing rules.

Each Task must specify one coherent outcome, scope/non-scope, dependencies/blocks/parallel safety, exact references, acceptance criteria, tests, completion evidence, DB/route/interaction/navigation/localization/auth/modularity/cost impact and tool/MCP needs. Authentication, permissions, money, inventory truth, schema and production changes need HIGH risk classification and broader verification. One Task per executor by default; parallel Tasks require explicit PARALLEL SAFE and compatible file ownership.

Task statuses: TODO, READY, IN_PROGRESS, BLOCKED, VERIFYING, DONE, SUPERSEDED. READY requires resolved ambiguity, satisfied dependencies and all AICWDF §22.8 conditions. DONE requires complete implementation, passing required checks and evidence. Automatically update Task status, checkbox, status/progress counters and handoff together. Baseline total remains fixed; current total changes only through controlled Task-plan changes. Preserve permanent IDs and supersession history; added/split/merged Tasks go through change control. These are future operating rules, not a Task queue.

## Review and escalation

Review decisions against their classification and exact source. Challenge assumptions, confidentiality leakage, contradictory summaries and phase creep. Distinguish a factual documentation correction from an Owner-reserved business or architecture change. Record review findings and their resolution before presenting the authorized phase. Business/legal/accounting conflicts block dependent later specifications rather than be silently solved by agents.

## Session end obligation

Update handoff, changed files, evidence/decisions/gaps, tool/cost/DB impact, checks and next action together. P0/P1/P2 **DONE — APPROVED AND PUBLISHED** under APPR-001/APPR-002/APPR-003. P3 **DONE — APPROVED** under APPR-004. AUTH-002–AUTH-008 **HISTORICAL / COMPLETED**. AUTH-009 authorizes only this P3 checkpoint until successful push/live verification; afterward it is automatically **HISTORICAL / COMPLETED**, current active phase authorization **NONE**. P4–P11 **TODO / NOT AUTHORIZED**. Planning Freeze **NOT REACHED**; Tasks **NONE**; Task Baseline **NOT READY**; Execution **NOT AUTHORIZED**; application implementation **NONE**. Only intended P3 approval/checkpoint Git actions under AUTH-009 after integrity PASS. Next: **Complete only the APPR-004/AUTH-009 checkpoint and verify publication; then bootstrap PRANATA in Claude Code as EXECUTION AGENT — WAITING in a subsequent session. P4 needs separate Owner authorization; execution requires approved P4–P11, Planning Freeze and an explicitly READY P11 Task. Stop after P3.**

## Subsequent execution-agent onboarding

After verified P3 publication, a subsequent session may bootstrap Claude Code as **EXECUTION AGENT — WAITING**: read repository, AGENTS.md, CLAUDE.md, CURRENT_HANDOFF and approved P0–P3, understand unfinished P4–P11, and report waiting. No onboarding is performed in this checkpoint session. Do not scaffold/install dependencies/create schema/migrations/auth/UI/Tasks, infer unfinished planning, close GAPs or alter planning truth. Execution remains blocked until P4–P11 are complete/approved, Planning Freeze is reached and a P11 Task is explicitly READY.
