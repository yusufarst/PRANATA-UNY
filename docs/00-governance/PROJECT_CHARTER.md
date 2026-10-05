# Project charter

Status: APPROVED (APPR-003; AUTH-007 approval/checkpoint lifecycle) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

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

P0/P1 are approved and published under APPR-001/APPR-002; P2 is DONE — APPROVED under [APPR-003](APPROVAL_RECORDS.md#appr-003), with reviewed qualifications retained. AUTH-002–AUTH-006 are historical/completed. [AUTH-007](APPROVAL_RECORDS.md#auth-007) permits only the P2 approval/checkpoint, automatically completed after successful publication/verification with active phase authorization NONE. [PRODUCT_OVERVIEW](../01-product/PRODUCT_OVERVIEW.md) owns approved product truth; [DOMAIN_MODEL](../02-domain/DOMAIN_MODEL.md) owns the approved conceptual P2 model. This charter retains the approved foundation.

Not authorized: P3–P11 specifications, application code/scaffold/packages, schema/migrations/auth/UI implementation, execution Tasks, planning freeze or production changes. AUTH-007 permits only the bounded P2 staging/commit/push/verification and then expires automatically. P1 remains byte-preserved APPROVED; P2 APPROVED retains all EVIDENCE_SUPPORTED/PROPOSED/BLOCKED_BY_GAP qualifications and 20 OPEN GAPs. P3–P11 folders remain reservations; no exhausted checkpoint grants a new Git action.

Preferred stack is a P0 baseline only: Laravel, Inertia, React, TypeScript, shadcn/ui, Tailwind CSS, PostgreSQL, Nginx, Modular Monolith. Redis/Valkey needs justification. Exact versions, module/schema boundaries, deployment provider and environment provisioning remain for authorized later planning.

## P0 exit

A zero-context agent can identify authority, project/phase state, absent Task baseline, safe boundary and next reading. Historical P0/P1 evidence remains intact; [P2_QUALITY_GATE](evidence/P2_QUALITY_GATE.md) owns preparation, reported independent review, approval and bounded checkpoint integrity evidence. Exact next safe action after verified checkpoint: **Owner separately authorizes P3 — Workflows, Routes & Interactions**. Stop after P2; no P3 authority.