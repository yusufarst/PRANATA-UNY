# Agent operating model

Status: APPROVED | Updated: 2026-10-04 (Asia/Jakarta) | Custodian: Planning Agent

## Roles

| Role | Responsibility | Boundary |
|---|---|---|
| Owner | Final decisions, phase approval, material scope/architecture/cost approval | Explicit approval is never inferred from silence |
| Codex / ChatGPT Planning Agent | Authorized P0–P11 documentation, evidence, decisions, gaps and executor clarity | Current authorization is P0 only; no implementation |
| Claude Code future Execution Agent | Implement and verify an approved READY Task, update progress/evidence | No planning redesign or hidden-chat dependency |
| Review Agent | Check authority, scope, tests/evidence, drift and false completion | Findings are evidence; cannot grant Owner approval |
| Release Agent | Verify approved release path, staging/UAT/regression/rollback declarations | No release authorization exists yet |
| Maintenance Agent | Safely reproduce and resolve a bounded maintenance Task | Production DB zero-touch; preserve historical semantics |

Provider replacement does not alter canonical project truth. Tool adapters point to AGENTS.md.

## Phase authorization

Each phase requires its own explicit authorization. Read actual repository state, current handoff, phase status, decision index and approval records before work. Historical AUTH-001 authorized P0 preparation/review. Current [APPR-001](APPROVAL_RECORDS.md#appr-001) approves P0; [AUTH-002](APPROVAL_RECORDS.md#auth-002) authorizes only approval recording and one initial checkpoint, then stop. P1 is not authorized. P0 prepares governance, evidence and gaps; source inspection does not start P1 product or P2/P3 domain/workflow design.

Finish an authorized phase's artifacts → self-review against framework and Owner direction → record evidence/known limitations → VERIFYING → Owner approves exact revision or requests corrections. Owner approval moves the phase to DONE; the next phase still needs explicit authorization. A quality PASS, created folder, completed conversation or commit never constitutes phase approval.

## Future execution boundary

No application implementation before P0–P11 planning, explicit Owner approvals, planning freeze and a READY Task baseline. P11 must create the definitive stable Task IDs/counts, dependency graph, minimal EXECUTION_CONTEXT, master checklist and bounded contracts; do not create those artifacts now.

Future read order: AGENTS.md → EXECUTION_CONTEXT → CURRENT_HANDOFF → TASK_PLAN → active READY Task → exact referenced sections → targeted source/impact analysis → current docs if required. If a prerequisite is absent, contradictory or not approved, stop dependent execution and record the gap. Do not read the entire planning corpus per Task or invent missing rules.

Each Task must specify one coherent outcome, scope/non-scope, dependencies/blocks/parallel safety, exact references, acceptance criteria, tests, completion evidence, DB/route/interaction/navigation/localization/auth/modularity/cost impact and tool/MCP needs. Authentication, permissions, money, inventory truth, schema and production changes need HIGH risk classification and broader verification. One Task per executor by default; parallel Tasks require explicit PARALLEL SAFE and compatible file ownership.

Task statuses: TODO, READY, IN_PROGRESS, BLOCKED, VERIFYING, DONE, SUPERSEDED. READY requires resolved ambiguity, satisfied dependencies and all AICWDF §22.8 conditions. DONE requires complete implementation, passing required checks and evidence. Automatically update Task status, checkbox, status/progress counters and handoff together. Baseline total remains fixed; current total changes only through controlled Task-plan changes. Preserve permanent IDs and supersession history; added/split/merged Tasks go through change control. These are future operating rules, not a Task queue.

## Review and escalation

Review decisions against their classification and exact source. Challenge assumptions, confidentiality leakage, contradictory summaries and phase creep. Distinguish a factual documentation correction from an Owner-reserved business or architecture change. Record review findings and their resolution before presenting P0. Business/legal/accounting conflicts block dependent later specifications rather than be silently solved by agents.

## Session end obligation

Update handoff with current role/authorization/phase, absent or active Task, actions completed, changed files, evidence, decisions/gaps, tools checked/installed, cost/DB impact, tests applicable or N/A, and exact safe next action. Synchronize phase status and context index. Verify raw inputs ignored, secret handling and actual Git changes. Current P0: approved/DONE; only AUTH-002 initial checkpoint publication/verification is permitted, with no schema/data change or READY Task. Stop after checkpoint; separate Owner P1 authorization is the next gate.
