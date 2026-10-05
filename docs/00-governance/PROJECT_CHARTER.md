# Project charter

Status: APPROVED (APPR-004; AUTH-009 checkpoint only) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

## Identity and purpose

Project: **PRANATA UNY**. Repository: `C:\Projects\PRANATA-UNY`; configured origin: `https://github.com/yusufarst/PRANATA-UNY.git`. The repository is newly created, without commits or application implementation at P0 inspection. Its project state is PARTIALLY_PLANNED.

The Owner directs one integrated application and shared data ecosystem with specialized Asset Workspace, Procurement Workspace, Vendor Portal and Super Admin. Integration must preserve traceability and synchronization where asset and procurement lifecycles intersect. This charter records direction; it is not a P1 scope or acceptance specification.

## Owner direction to preserve

The canonical [OD-01–OD-20 records](DECISION_LOG.md) preserve all supplied decisions and qualifications. They cover generic hierarchical Organization Units, a candidate integrated lifecycle, reduced manual entry and handoff confusion, structured data reused for documents, compact application roles with process assignments, Owner Super Admin, local V1 authentication, future institutional integrations, reporting cutoff uncertainty, scale up to approximately one million assets, localization, cost and action-oriented UX.

Candidate lifecycle: Need → Procurement Request → RUP → Procurement Process → Vendor → Contract → Execution → Handover / BAST → SPJ / Payment Tracking → Asset / Inventory / KDP → Reconciliation → Reporting / Finance / Audit. It remains a working model, not an official UNY procedure. Candidate roles remain subject to P2/P5 validation. Daily depreciation policy, official approvals and accounting rules must not be invented.

## Authority and collaboration

Owner: final project decision authority; approves every phase and material scope/architecture change. Codex/ChatGPT: Planning Agent and custodian of authorized planning documentation. Claude Code: future Execution Agent bound to READY Tasks, never hidden planning chats. Review, release and maintenance agents use the same repository entry point. See [AGENT_OPERATING_MODEL](AGENT_OPERATING_MODEL.md).

Master operating framework: AICWDF v4.3, retained unchanged under [sources](sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md). MULTIPLECORP is only a structural/governance reference. PRANATA product, permission, security and accounting decisions come from PRANATA evidence and Owner decisions.

## Current work boundary

P0/P1/P2 **DONE — APPROVED AND PUBLISHED** under APPR-001/APPR-002/APPR-003. P3 **DONE — APPROVED** under APPR-004. AUTH-002–AUTH-008 **HISTORICAL / COMPLETED**. AUTH-009 authorizes only this P3 checkpoint until successful push/live verification; afterward it is automatically **HISTORICAL / COMPLETED**, current active phase authorization **NONE**. P4–P11 **TODO / NOT AUTHORIZED**. Planning Freeze **NOT REACHED**; Tasks **NONE**; Task Baseline **NOT READY**; Execution **NOT AUTHORIZED**; application implementation **NONE**. [APPR-004](APPROVAL_RECORDS.md#appr-004) approves the corrected workflow/route/interaction candidate; [AUTH-009](APPROVAL_RECORDS.md#auth-009) is checkpoint only. Starting published main HEAD `9e7950db6f9d6c6ab900a6328778807e057fb812` live-verified. P1/P2 bytes and all 20 OPEN GAPs preserved; only the intended AUTH-009 checkpoint Git actions after integrity PASS.

Preferred stack remains the approved P0 baseline; exact versions/schema/modules/deployment stay later phases. [PRODUCT_OVERVIEW](../01-product/PRODUCT_OVERVIEW.md) owns approved product truth, [DOMAIN_MODEL](../02-domain/DOMAIN_MODEL.md) conceptual domain, [WORKFLOW_CATALOG](../03-workflows/WORKFLOW_CATALOG.md) indexes APPROVED P3.

## P0 exit

The approved P0 exit remains fulfilled: a new agent can locate authority/state/boundary and required context. P0/P1/P2 evidence remains historical; [P3_QUALITY_GATE](evidence/P3_QUALITY_GATE.md) owns current P3 preparation checks. Exact next safe action: **Complete only the APPR-004/AUTH-009 checkpoint and verify publication; then bootstrap PRANATA in Claude Code as EXECUTION AGENT — WAITING in a subsequent session. P4 needs separate Owner authorization; execution requires approved P4–P11, Planning Freeze and an explicitly READY P11 Task. Stop after P3.**
