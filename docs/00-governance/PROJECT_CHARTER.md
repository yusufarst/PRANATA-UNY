# Project charter

Status: Approved baseline: APPR-004, published 0e3f7de · Amendment candidate: VERIFYING (AUTH-010) | Updated: 2026-10-06 (Asia/Jakarta) | Custodian: Planning Agent

## Identity and purpose

Project: **PRANATA UNY**. Repository: `https://github.com/yusufarst/PRANATA-UNY` — published `main` is the durable shared Source of Truth; the Owner's working clone and local evidence context is `C:\Projects\PRANATA-UNY`. The repository is newly created, without commits or application implementation at P0 inspection. Its project state is PARTIALLY_PLANNED.

The Owner directs one integrated application and shared data ecosystem with specialized Asset Workspace, Procurement Workspace, Vendor Portal and Super Admin. Integration must preserve traceability and synchronization where asset and procurement lifecycles intersect. This charter records direction; it is not a P1 scope or acceptance specification.

## Owner direction to preserve

All current Owner Decisions recorded in [DECISION_INDEX](DECISION_INDEX.md) preserve all supplied decisions and qualifications. OD-01–OD-20 cover generic hierarchical Organization Units, a candidate integrated lifecycle, reduced manual entry and handoff confusion, structured data reused for documents, compact application roles with process assignments, Owner Super Admin, local V1 authentication, future institutional integrations, reporting cutoff uncertainty, scale up to approximately one million assets, localization, cost and action-oriented UX.

Candidate lifecycle: Need → Procurement Request → RUP → Procurement Process → Vendor → Contract → Execution → Handover / BAST → SPJ / Payment Tracking → Asset / Inventory / KDP → Reconciliation → Reporting / Finance / Audit. It remains a working model, not an official UNY procedure. Candidate roles remain subject to P2/P5 validation. Daily depreciation policy, official approvals and accounting rules must not be invented.

## Authority and collaboration

Owner: final project decision authority; approves every phase and material scope/architecture change. Agent roles follow GOV-07 in [DECISION_LOG](DECISION_LOG.md): Claude Project plans and proposes, ChatGPT performs independent Second Review, Claude Code executes only within explicit Owner authority, and the Owner decides. Review, release and maintenance agents use the same repository entry point. See [AGENT_OPERATING_MODEL](AGENT_OPERATING_MODEL.md).

Master operating framework: AICWDF v4.3, retained unchanged under [sources](sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md). MULTIPLECORP is only a structural/governance reference. PRANATA product, permission, security and accounting decisions come from PRANATA evidence and Owner decisions.

## Current work boundary

Current state: see [PHASE_STATUS](../PHASE_STATUS.md) and [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md). P0–P3 DONE — APPROVED AND PUBLISHED at `0e3f7de`; P0–P3 remediation amendment candidate VERIFYING / NOT YET APPROVED; P4–P11 NOT AUTHORIZED; application execution NOT AUTHORIZED.

Preferred stack remains the approved P0 baseline; exact versions/schema/modules/deployment stay later phases. [PRODUCT_OVERVIEW](../01-product/PRODUCT_OVERVIEW.md) owns approved product truth, [DOMAIN_MODEL](../02-domain/DOMAIN_MODEL.md) conceptual domain, [WORKFLOW_CATALOG](../03-workflows/WORKFLOW_CATALOG.md) indexes APPROVED P3.

## P0 exit

The approved P0 exit remains fulfilled: a new agent can locate authority/state/boundary and required context. P0/P1/P2 evidence remains historical; [P3_QUALITY_GATE](evidence/P3_QUALITY_GATE.md) owns current P3 preparation checks. Exact next safe action: see [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md).
