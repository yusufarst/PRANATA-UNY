# Change control

Status: APPROVED (P1 state amendment; P0 change-control rules retained) | Updated: 2026-10-05 | Custodian: Planning Agent

This document owns how changes are classified, reviewed, and recorded. [SOURCE_OF_TRUTH](SOURCE_OF_TRUTH.md) owns authority precedence and document lifecycle; [APPROVAL_RECORDS](APPROVAL_RECORDS.md) owns approval evidence. The Owner retains final decision authority.

## Current authorization and decision boundaries

[APPR-001](APPROVAL_RECORDS.md#appr-001) approves published P0; AUTH-002/003 checkpoints are HISTORICAL / COMPLETED. Historical [AUTH-004](APPROVAL_RECORDS.md#auth-004) prepared P1 with no commit/push. [APPR-002](APPROVAL_RECORDS.md#appr-002) approves the exact reviewed P1 product/governance amendments; P1 is DONE. [AUTH-005](APPROVAL_RECORDS.md#auth-005) authorizes only administrative approval/checkpoint through verified publication, then expires; no P2–P11 authority exists. Preserve OD-01–OD-20 wording and new qualified OD-21–OD-23 in [DECISION_LOG](DECISION_LOG.md); inference/legacy/templates/framework defaults cannot replace explicit direction.

Material scope, business/workflow, accounting meaning, user responsibility, authorization/visibility, architecture/stack, design direction, infrastructure, recurring-cost, or production-risk changes require explicit Owner review and decision. A technical-sounding change that alters business meaning remains material. Record uncertainty instead of treating an unvalidated proposal as truth.

No approval for P0 drafting, P0 completion, or a document change implies authorization for P1, later phases, planning freeze, Tasks, application implementation, Git publication, or production action. Each gate is separate and must have recorded authority. P0 is `DONE` under APPR-001; current phase status is owned by [PHASE_STATUS](../PHASE_STATUS.md).

## Reviewable change procedure

1. Identify the canonical owning document, current decision, supporting evidence, source classification, and any conflict. Consult [DECISION_INDEX](DECISION_INDEX.md), existing ADRs, and [GAP_REGISTER](GAP_REGISTER.md).
2. Determine whether the change fits current authorized documentation work or needs a material Owner decision. Analyze relevant scope, business, privacy/security, data/migration, cost, dependency, regression, and recovery effects; use `N/A` with a reason where appropriate.
3. Apply routine documentation improvements within the authorized phase and record their evidence. For a material proposal, prepare a concrete recommendation, useful alternatives, tradeoffs, affected documents/phases, and consequences. Keep dependent work pending while continuing independent authorized work.
4. After a decision, record exact authority, date, outcome, conditions, and affected revision. Update the owning documents, decision/index/gap links, applicable ADR, and [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md) together. Remove persistent contradictions without erasing historical rationale.

The phase authorization never expands automatically because another useful activity is adjacent. An unsafe or contradictory approved design should produce a concern and pause affected execution until resolved; do not implement a known unsafe fallback. Unrelated authorized work may continue.

## ADR discipline

An ADR is required for a material deviation from the preferred stack/architecture baseline. Use an ADR for other significant technical tradeoffs when its alternatives and rationale warrant a durable record; routine editorial fixes need no ADR. Follow the [ADR register and contract](../adr/README.md) rather than duplicating a template here.

An ADR records context, proposed/accepted choice, alternatives, consequences, risks, migration/rollback effects, affected canonical owners, and approval evidence. A proposed ADR is not Owner acceptance, implementation evidence, or execution authorization. Material changes require Owner approval even when described in an ADR. Preserve superseded/rejected decisions with successor or reason links.

## Later freeze and Task changes

Planning freeze is not reached. After an approved P0–P11 planning baseline and freeze, changes must remain traceable and keep affected specifications, ADRs, Tasks, tests, and handoff consistent. P11 will define stable Task IDs, baseline/current totals, dependencies, and controlled add/split/merge/supersede records under AICWDF §22. Existing IDs must not be silently renumbered. No Task plan, execution Task, or Task count is created in P0.

## Git boundary

AUTH-002 initial publication and AUTH-003 `docs: close P0 publication handoff` are completed historical actions. AUTH-004 no-commit/push preparation restrictions are historical and superseded only by AUTH-005 for one approved P1 normal checkpoint. Require post-approval integrity and staged-byte checks before commit/push, then verify remote publication and stop; no continuing authority afterward. Do not amend published P0, reconfigure remote, force push or add ignored raw sources. [GIT_WORKFLOW](GIT_WORKFLOW.md) owns Git conditions; P0 approval grants no later Git/execution authority.
