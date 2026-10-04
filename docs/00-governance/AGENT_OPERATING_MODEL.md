# Agent operating model

Status: APPROVED (P1 operational amendment; P0 operating rules retained) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

## Roles

| Role | Responsibility | Boundary |
|---|---|---|
| Owner | Final decisions, phase approval, material scope/architecture/cost approval | Explicit approval is never inferred from silence |
| Codex / ChatGPT Planning Agent | Authorized phase documentation, evidence, decisions, gaps and executor clarity | AUTH-005: P1 approval/checkpoint only, exhausted after verified publication; no implementation or P2–P11 work |
| Claude Code future Execution Agent | Implement and verify an approved READY Task, update progress/evidence | No planning redesign or hidden-chat dependency |
| Review Agent | Check authority, scope, tests/evidence, drift and false completion | Findings are evidence; cannot grant Owner approval |
| Release Agent | Verify approved release path, staging/UAT/regression/rollback declarations | No release authorization exists yet |
| Maintenance Agent | Safely reproduce and resolve a bounded maintenance Task | Production DB zero-touch; preserve historical semantics |

Provider replacement does not alter canonical project truth. Tool adapters point to AGENTS.md.

## Phase authorization

Each phase requires its own explicit authorization. Read actual repository state, current handoff, phase status, decision index and approval records before work. [APPR-001](APPROVAL_RECORDS.md#appr-001) approves published P0; AUTH-001–003 are historical sessions and AUTH-002/003 checkpoints are completed. [AUTH-004](APPROVAL_RECORDS.md#auth-004) prepared P1 WHAT/WHY/product acceptance and is historical/completed. [APPR-002](APPROVAL_RECORDS.md#appr-002) explicitly approves the exact reviewed P1 candidate; P1 is DONE. [AUTH-005](APPROVAL_RECORDS.md#auth-005) authorizes only approval/checkpoint until verified publication, then expires. P2–P11 are not authorized. Source inspection and a product-level lifecycle do not finalize domain/accounting/workflows/security/design.

Finish an authorized phase's artifacts → self-review against framework and Owner direction → record evidence/known limitations → VERIFYING → Owner approves exact revision or requests corrections. Owner approval moves the phase to DONE; the next phase still needs explicit authorization. A quality PASS, created folder, completed conversation or commit never constitutes phase approval.

## Future execution boundary

No application implementation before P0–P11 planning, explicit Owner approvals, planning freeze and a READY Task baseline. P11 must create the definitive stable Task IDs/counts, dependency graph, minimal EXECUTION_CONTEXT, master checklist and bounded contracts; do not create those artifacts now.

Future read order: AGENTS.md → EXECUTION_CONTEXT → CURRENT_HANDOFF → TASK_PLAN → active READY Task → exact referenced sections → targeted source/impact analysis → current docs if required. If a prerequisite is absent, contradictory or not approved, stop dependent execution and record the gap. Do not read the entire planning corpus per Task or invent missing rules.

Each Task must specify one coherent outcome, scope/non-scope, dependencies/blocks/parallel safety, exact references, acceptance criteria, tests, completion evidence, DB/route/interaction/navigation/localization/auth/modularity/cost impact and tool/MCP needs. Authentication, permissions, money, inventory truth, schema and production changes need HIGH risk classification and broader verification. One Task per executor by default; parallel Tasks require explicit PARALLEL SAFE and compatible file ownership.

Task statuses: TODO, READY, IN_PROGRESS, BLOCKED, VERIFYING, DONE, SUPERSEDED. READY requires resolved ambiguity, satisfied dependencies and all AICWDF §22.8 conditions. DONE requires complete implementation, passing required checks and evidence. Automatically update Task status, checkbox, status/progress counters and handoff together. Baseline total remains fixed; current total changes only through controlled Task-plan changes. Preserve permanent IDs and supersession history; added/split/merged Tasks go through change control. These are future operating rules, not a Task queue.

## Review and escalation

Review decisions against their classification and exact source. Challenge assumptions, confidentiality leakage, contradictory summaries and phase creep. Distinguish a factual documentation correction from an Owner-reserved business or architecture change. Record review findings and their resolution before presenting the authorized phase. Business/legal/accounting conflicts block dependent later specifications rather than be silently solved by agents.

## Session end obligation

Update handoff with role/authorization/phase, absent or active Task, completed actions, changed files, evidence, decisions/gaps, tools, cost/DB impact, applicable checks and exact safe next action. Synchronize phase status/context. Current P0: approved/published/DONE; P1: DONE under APPR-002; AUTH-005 approval/checkpoint only until verified publication, then exhausted. No schema/data change or READY Task. Stop after the approved P1 normal commit/push/verification. Exact next gate: Owner separately authorizes P2 — Domain Model & Business Rules.
