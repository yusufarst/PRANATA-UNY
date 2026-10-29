# Agent operating model

Status: Approved baseline: APPR-004, published 0e3f7de · Amendment candidate: VERIFYING (AUTH-010) | Updated: 2026-10-06 (Asia/Jakarta) | Custodian: Planning Agent

## Roles

| Role | Responsibility | Boundary |
|---|---|---|
| Owner | Final decisions, phase approval, material scope/architecture/cost approval | Explicit approval is never inferred from silence |
| Claude Project — planning / orchestration / proposal agent | Authorized planning work, proposals and instructions | The role confers no phase authority; every P4–P11 phase needs explicit Owner authorization |
| ChatGPT — independent Second Review agent | Reviews proposals and instructions: APPROVED / APPROVED WITH CONDITIONS / REVISE / BLOCKED | A verdict never constitutes Owner approval |
| Claude Code — execution agent | Bounded documentation corrections only under an explicit Owner authorization that passed Second Review; later, READY P11 Tasks | Application execution WAITING until approved P4–P11, Planning Freeze, a READY P11 Task and execution authority |
| Review Agent | Check authority, scope, tests/evidence, drift and false completion; phase/candidate verdicts READY_FOR_OWNER_APPROVAL / CORRECTIONS_REQUIRED | Findings are evidence; cannot grant Owner approval |
| Release Agent | Verify approved release path, staging/UAT/regression/rollback declarations | No release authorization exists yet |
| Maintenance Agent | Safely reproduce and resolve a bounded maintenance Task | Production DB zero-touch; preserve historical semantics |

Before GOV-07, planning was performed by Codex/ChatGPT under GOV-01 (historical).

Provider replacement does not alter canonical project truth. Tool adapters point to AGENTS.md.

## Phase authorization

Current state: see [PHASE_STATUS](../PHASE_STATUS.md) and [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md). P0–P3 DONE — APPROVED AND PUBLISHED at `0e3f7de`; P0–P3 remediation amendment candidate VERIFYING / NOT YET APPROVED; P4–P11 NOT AUTHORIZED; application execution NOT AUTHORIZED.

Finish authorized artifacts → self-review/evidence → VERIFYING → independent review → Owner approves the exact candidate or requests corrections. Owner approval moves a phase to DONE; agent PASS, folders, conversation or commit do not grant approval. Phase/candidate review returns READY_FOR_OWNER_APPROVAL or CORRECTIONS_REQUIRED; independent Second Review of proposals and instructions returns APPROVED, APPROVED WITH CONDITIONS, REVISE or BLOCKED; neither is Owner approval.

## Future execution boundary

No application implementation before P0–P11 planning, explicit Owner approvals, planning freeze and a READY Task baseline. P11 must create the definitive stable Task IDs/counts, dependency graph, minimal EXECUTION_CONTEXT, master checklist and bounded contracts; do not create those artifacts now.

Future read order: AGENTS.md → EXECUTION_CONTEXT → CURRENT_HANDOFF → TASK_PLAN → active READY Task → exact referenced sections → targeted source/impact analysis → current docs if required. If a prerequisite is absent, contradictory or not approved, stop dependent execution and record the gap. Do not read the entire planning corpus per Task or invent missing rules.

Each Task must specify one coherent outcome, scope/non-scope, dependencies/blocks/parallel safety, exact references, acceptance criteria, tests, completion evidence, DB/route/interaction/navigation/localization/auth/modularity/cost impact and tool/MCP needs. Authentication, permissions, money, inventory truth, schema and production changes need HIGH risk classification and broader verification. One Task per executor by default; parallel Tasks require explicit PARALLEL SAFE and compatible file ownership.

Task statuses: TODO, READY, IN_PROGRESS, BLOCKED, VERIFYING, DONE, SUPERSEDED. READY requires resolved ambiguity, satisfied dependencies and all AICWDF §22.8 conditions. DONE requires complete implementation, passing required checks and evidence. Automatically update Task status, checkbox, status/progress counters and handoff together. Baseline total remains fixed; current total changes only through controlled Task-plan changes. Preserve permanent IDs and supersession history; added/split/merged Tasks go through change control. These are future operating rules, not a Task queue.

## Review and escalation

Review decisions against their classification and exact source. Challenge assumptions, confidentiality leakage, contradictory summaries and phase creep. Distinguish a factual documentation correction from an Owner-reserved business or architecture change. Record review findings and their resolution before presenting the authorized phase. Business/legal/accounting conflicts block dependent later specifications rather than be silently solved by agents.

## Session end obligation

Update handoff, changed files, evidence/decisions/gaps, tool/cost/DB impact, checks and next action together. Current state: see [PHASE_STATUS](../PHASE_STATUS.md) and [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md). P0–P3 DONE — APPROVED AND PUBLISHED at `0e3f7de`; P0–P3 remediation amendment candidate VERIFYING / NOT YET APPROVED; P4–P11 NOT AUTHORIZED; application execution NOT AUTHORIZED.

## Subsequent execution-agent onboarding

The read-only Claude Code bootstrap as EXECUTION AGENT — WAITING was completed in the AUTH-010 preflight (2026-10-06). Application execution remains blocked until approved P4–P11, Planning Freeze and an explicitly READY P11 Task. Do not scaffold/install dependencies/create schema/migrations/auth/UI/Tasks, infer unfinished planning, close GAPs or alter planning truth.
