# Change control

Status: APPROVED | Updated: 2026-10-04 | Owner: Planning Agent | Phase: P0

This document owns how changes are classified, reviewed, and recorded. [SOURCE_OF_TRUTH](SOURCE_OF_TRUTH.md) owns authority precedence and document lifecycle; [APPROVAL_RECORDS](APPROVAL_RECORDS.md) owns approval evidence. The Owner retains final decision authority.

## Current authorization and decision boundaries

Current [AUTH-002](APPROVAL_RECORDS.md#auth-002) authorizes only recording [APPR-001](APPROVAL_RECORDS.md#appr-001), administrative lifecycle consistency, bounded integrity checks and the initial P0 checkpoint. Prior AUTH-001 preparation/correction permissions are historical; no later-phase planning is authorized. Preserve OD-01–OD-20 in [DECISION_LOG](DECISION_LOG.md); an inference, legacy behavior, source template, or framework default cannot silently replace explicit Owner direction.

Material scope, business/workflow, accounting meaning, user responsibility, authorization/visibility, architecture/stack, design direction, infrastructure, recurring-cost, or production-risk changes require explicit Owner review and decision. A technical-sounding change that alters business meaning remains material. Record uncertainty instead of treating an unvalidated proposal as truth.

No approval for P0 drafting, P0 completion, or a document change implies authorization for P1, later phases, planning freeze, Tasks, application implementation, Git publication, or production action. Each gate is separate and must have recorded authority. P0 is `DONE` under APPR-001; current phase status is owned by [PHASE_STATUS](../PHASE_STATUS.md).

## Reviewable change procedure

1. Identify the canonical owning document, current decision, supporting evidence, source classification, and any conflict. Consult [DECISION_INDEX](DECISION_INDEX.md), existing ADRs, and [GAP_REGISTER](GAP_REGISTER.md).
2. Determine whether the change fits current authorized documentation work or needs a material Owner decision. Analyze relevant scope, business, privacy/security, data/migration, cost, dependency, regression, and recovery effects; use `N/A` with a reason where appropriate.
3. Apply routine P0 documentation improvements and record their evidence. For a material proposal, prepare a concrete recommendation, useful alternatives, tradeoffs, affected documents/phases, and consequences. Keep dependent work pending while continuing independent authorized work.
4. After a decision, record exact authority, date, outcome, conditions, and affected revision. Update the owning documents, decision/index/gap links, applicable ADR, and [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md) together. Remove persistent contradictions without erasing historical rationale.

The phase authorization never expands automatically because another useful activity is adjacent. An unsafe or contradictory approved design should produce a concern and pause affected execution until resolved; do not implement a known unsafe fallback. Unrelated authorized work may continue.

## ADR discipline

An ADR is required for a material deviation from the preferred stack/architecture baseline. Use an ADR for other significant technical tradeoffs when its alternatives and rationale warrant a durable record; routine editorial fixes need no ADR. Follow the [ADR register and contract](../adr/README.md) rather than duplicating a template here.

An ADR records context, proposed/accepted choice, alternatives, consequences, risks, migration/rollback effects, affected canonical owners, and approval evidence. A proposed ADR is not Owner acceptance, implementation evidence, or execution authorization. Material changes require Owner approval even when described in an ADR. Preserve superseded/rejected decisions with successor or reason links.

## Later freeze and Task changes

Planning freeze is not reached. After an approved P0–P11 planning baseline and freeze, changes must remain traceable and keep affected specifications, ADRs, Tasks, tests, and handoff consistent. P11 will define stable Task IDs, baseline/current totals, dependencies, and controlled add/split/merge/supersede records under AICWDF §22. Existing IDs must not be silently renumbered. No Task plan, execution Task, or Task count is created in P0.

## Git boundary

AUTH-002 expressly permits staging intended P0 artifacts, one initial commit `docs: finalize P0 governance`, normal push to unchanged `origin/main` and remote verification after passing integrity checks. Remote reconfiguration, force push and adding `reference-inputs/` or raw sources remain forbidden. [GIT_WORKFLOW](GIT_WORKFLOW.md) owns checkpoint conditions and future conventions; P0 approval grants no later Git or execution authority.
