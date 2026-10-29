# Agent operating model

Status: Approved baseline: APPR-004, published 0e3f7de · Amendment candidates: VERIFYING (AUTH-010 remediation; AUTH-012 P0–P11 planning) | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent

## Roles

| Role | Responsibility | Boundary |
|---|---|---|
| Owner | Final decisions, phase approval, material scope/architecture/cost approval | Explicit approval is never inferred from silence |
| Claude Planning — Lead Planning Agent (GOV-10) | Sole planning author for P0–P11 and the P11 Task backlog; proposals, decision packages, review candidates; planning branches, commits, pushes and review PRs under AUTH-012 | Cannot approve phases, freeze planning, make Tasks READY, merge unapproved candidates into `main` or write application code |
| ChatGPT — independent Second Review Agent | Reviews planning candidates, proposals and instructions: APPROVED / APPROVED WITH CONDITIONS / REVISE / BLOCKED; candidate verdicts READY_FOR_OWNER_APPROVAL / CORRECTIONS_REQUIRED | Does not author the plan; earlier ChatGPT blueprints, prototype and 56-Task draft are nonbinding; a verdict never constitutes Owner approval |
| Claude Code — Execution Agent | After approvals, Planning Freeze and a standing execution authorization: executes READY P11 Tasks autonomously under [EXECUTOR_OPERATING_MODEL](../11-tasks/EXECUTOR_OPERATING_MODEL.md) (GOV-12) | Application execution WAITING until approved P4–P11, Planning Freeze, Task Baseline and execution authority; never authors planning truth beyond Task-required documentation updates |
| Review Agent | Check authority, scope, tests/evidence, drift and false completion; phase/candidate verdicts READY_FOR_OWNER_APPROVAL / CORRECTIONS_REQUIRED | Findings are evidence; cannot grant Owner approval |
| Release Agent | Verify approved release path, staging/UAT/regression/rollback declarations | No release authorization exists yet |
| Maintenance Agent | Safely reproduce and resolve a bounded maintenance Task | Production DB zero-touch; preserve historical semantics |

Before GOV-07, planning was performed by Codex/ChatGPT under GOV-01 (historical). GOV-10 (2026-10-30) makes Claude Planning the sole planning author; no other session may author a competing planning baseline.

Provider replacement does not alter canonical project truth. Tool adapters point to AGENTS.md.

## Phase authorization

Current state: see [PHASE_STATUS](../PHASE_STATUS.md) and [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md). P0–P3 DONE — APPROVED AND PUBLISHED at `0e3f7de`; P0–P3 remediation and compatibility amendments plus P4–P11 candidates VERIFYING / NOT YET APPROVED; P4–P11 preparation authorized by [AUTH-012](APPROVAL_RECORDS.md#auth-012) (standing, not approval); application execution NOT AUTHORIZED.

Finish authorized artifacts → self-review/evidence → VERIFYING → independent review → Owner approves the exact candidate or requests corrections. Owner approval moves a phase to DONE; agent PASS, folders, conversation or commit do not grant approval. Under GOV-11 several reviewed phases may be approved in one explicit batch decision, each keeping its own reviewed identity (for branch candidates: the exact reviewed commit SHA). Phase/candidate review returns READY_FOR_OWNER_APPROVAL or CORRECTIONS_REQUIRED; independent Second Review of proposals and instructions returns APPROVED, APPROVED WITH CONDITIONS, REVISE or BLOCKED; neither is Owner approval.

## Future execution boundary

No application implementation before P0–P11 planning, explicit Owner approvals, planning freeze, a READY Task baseline and a standing execution authorization. The P11 candidate defines the stable Task IDs/counts, dependency graph, minimal [EXECUTION_CONTEXT](../11-tasks/EXECUTION_CONTEXT.md), master checklist ([TASK_PLAN](../11-tasks/TASK_PLAN.md)), Task contracts and the executor operating model; all are VERIFYING until approved.

Future read order: AGENTS.md → EXECUTION_CONTEXT → CURRENT_HANDOFF → TASK_PLAN → active READY Task → exact referenced sections → targeted source/impact analysis → current docs if required. If a prerequisite is absent, contradictory or not approved, stop dependent execution and record the gap. Do not read the entire planning corpus per Task or invent missing rules.

Each Task must specify one coherent outcome, scope/non-scope, dependencies/blocks/parallel safety, exact references, acceptance criteria, tests, completion evidence, DB/route/interaction/navigation/localization/auth/modularity/cost impact and tool/MCP needs. Authentication, permissions, money, inventory truth, schema and production changes need HIGH risk classification and broader verification. One Task per executor by default; parallel Tasks require explicit PARALLEL SAFE and compatible file ownership.

Task statuses: TODO, READY, IN_PROGRESS, BLOCKED, VERIFYING, DONE, SUPERSEDED (execution stages within them are defined in the executor operating model). READY requires resolved ambiguity, satisfied dependencies and all AICWDF §22.8 conditions. DONE requires complete implementation, passing required checks and evidence. Automatically update Task status, checkbox, status/progress counters and handoff together. Baseline total remains fixed; current total changes only through controlled Task-plan changes. Preserve permanent IDs and supersession history; added/split/merged Tasks go through change control. These are future operating rules, not a Task queue.

## Review and escalation

Review decisions against their classification and exact source. Challenge assumptions, confidentiality leakage, contradictory summaries and phase creep. Distinguish a factual documentation correction from an Owner-reserved business or architecture change. Record review findings and their resolution before presenting the authorized phase. Business/legal/accounting conflicts block dependent later specifications rather than be silently solved by agents.

## Session end obligation

Update handoff, changed files, evidence/decisions/gaps, tool/cost/DB impact, checks and next action together. Current state: see [PHASE_STATUS](../PHASE_STATUS.md) and [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md). P0–P3 DONE — APPROVED AND PUBLISHED at `0e3f7de`; P0–P3 remediation amendment candidate VERIFYING / NOT YET APPROVED; P4–P11 NOT AUTHORIZED; application execution NOT AUTHORIZED.

## Subsequent execution-agent onboarding

The read-only Claude Code bootstrap as EXECUTION AGENT — WAITING was completed in the AUTH-010 preflight (2026-10-06). The future autonomous bootstrap is specified in [EXECUTOR_BOOTSTRAP](../11-tasks/EXECUTOR_BOOTSTRAP.md) and is not active. Application execution remains blocked until approved P4–P11, Planning Freeze, the Task Baseline and a standing execution authorization. Do not scaffold/install dependencies/create schema/migrations/auth/UI/Tasks, infer unfinished planning, close GAPs or alter planning truth.
