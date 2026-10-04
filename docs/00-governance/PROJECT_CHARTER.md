# Project charter

Status: APPROVED (P1 operational amendment; P0 charter retained) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

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

Completed preparation under AUTH-001: P0 governance, decisions, safe evidence/gaps/framework/quality/handoff. [APPR-001](APPROVAL_RECORDS.md#appr-001) approves its exact reviewed candidate; published P0 and AUTH-002/003 checkpoints are complete. Historical [AUTH-004](APPROVAL_RECORDS.md#auth-004) prepared P1 product definition/scope/acceptance/reference/experience; [APPR-002](APPROVAL_RECORDS.md#appr-002) approves the exact reviewed candidate, P1 DONE. [AUTH-005](APPROVAL_RECORDS.md#auth-005) permits approval/checkpoint only until verified publication, then expires. [PRODUCT_OVERVIEW](../01-product/PRODUCT_OVERVIEW.md) now owns product identity/problem/outcomes/stakeholders; this charter retains P0 foundation rather than duplicating new product truth.

Not authorized: P2–P11 specifications, application code/scaffold/packages, schema/migrations/auth/UI implementation, execution Tasks, planning freeze, production changes or remote actions beyond the one approved normal P1 checkpoint. P1 documents are APPROVED with existing hypotheses/proposals/validation obligations preserved; later folders remain `.gitkeep` reservations. No exhausted checkpoint authorization grants a new Git action.

Preferred stack is a P0 baseline only: Laravel, Inertia, React, TypeScript, shadcn/ui, Tailwind CSS, PostgreSQL, Nginx, Modular Monolith. Redis/Valkey needs justification. Exact versions, module/schema boundaries, deployment provider and environment provisioning remain for authorized later planning.

## P0 exit

A zero-context agent can identify authority, project/phase state, absent Task baseline, safe boundary and next reading. P0 self-review/history remains in [P0_QUALITY_GATE](evidence/P0_QUALITY_GATE.md); P0 is DONE under APPR-001. P1 review evidence is separate in [P1_QUALITY_GATE](evidence/P1_QUALITY_GATE.md). Exact next safe action after checkpoint: Owner separately authorizes P2 — Domain Model & Business Rules.
