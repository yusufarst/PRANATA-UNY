# Decision log

Status: APPROVED | Updated: 2026-10-04 (Asia/Jakarta) | Custodian: Planning Agent

## Provenance and status

U-001 is the Owner's supplied P0 planning request, received 2026-10-04. Its verified original is Codex local attachment `3d2c3978-d4fd-47bd-ba34-4a20a250298c` (`Pasted text.txt`, 19,912 bytes), SHA-256 `34c5fbc7bae585b544f9dc659be7fab161c73216f46eeb7797d70da437b72e0d`. [SOURCE_INVENTORY U-001](SOURCE_INVENTORY.md#u-001-original-owner-request) owns the safe local locator, identification evidence and availability limits. The original remains untouched outside the repository; this sanitized record preserves its project direction. OD-01–OD-20 below are **OWNER_APPROVED_DECISION**, active unless explicitly superseded by a later Owner decision. Recording them does not mean P0 artifacts are approved. Preserve qualifications such as candidate, future, preferred and unresolved.

Authority and conflict handling: [SOURCE_OF_TRUTH](SOURCE_OF_TRUTH.md). Locator: [DECISION_INDEX](DECISION_INDEX.md). Phase/authorization approvals: [APPROVAL_RECORDS](APPROVAL_RECORDS.md). No OD-01–OD-20 meaning has been superseded. The scoped administrative P0 completion/checkpoint supersession is recorded in the lifecycle section below.

## OD-01

PRANATA UNY is **one integrated web application and one shared data ecosystem**.

## OD-02

Specialized workspaces: **Asset Workspace, Procurement Workspace, Vendor Portal, Super Admin**. They may feel operationally distinct but must not become disconnected applications.

## OD-03

Asset and Procurement data must remain synchronized where their business lifecycle intersects.

## OD-04

Use a generic hierarchical **Organization Unit** model. Do not assume all origins are faculties. Support faculties, directorates, institutes, bureaus, units and other/future organizational structures as needed. No entity/schema design is frozen in P0.

## OD-05

Procurement requests may originate from faculties, directorates, institutes, bureaus or other Organization Units and go to the relevant central procurement operation. The exact official approval sequence must be validated during later planning and must not be invented.

## OD-06

Candidate integrated lifecycle: Need → Procurement Request → RUP → Procurement Process → Vendor → Contract → Execution → Handover / BAST → SPJ / Payment Tracking → Asset / Inventory / KDP → Reconciliation → Reporting / Finance / Audit. This is a **working integrated lifecycle model**, not official UNY procedure. Do not silently turn uncertain substeps into approved workflow.

## OD-07

Core problems: repeated manual entry; disconnected spreadsheets/documents; manual file exchange; unclear process ownership; stakeholders asking “sudah sampai mana?”; unclear waiting party/next action; slow handoff between Organization Units and central office; late/confusing unit asset input; end-period correction overload; difficult reconciliation; recurring overtime for periodic asset reporting; weak traceability across modules. Detailed P1 requirements and acceptance criteria remain unplanned.

## OD-08

Reuse structured process data to generate related documents where practical. Documents should generally be outputs/evidence of structured workflow and domain state rather than disconnected system state.

## OD-09

Keep application roles compact. Do not create dozens of global roles merely because formal procurement positions exist. Evaluate PPK, KPA, Procurement Officer, Pokja, Technical Team, Examiner/Receiver, Reviewer and similar positions as assignments/capabilities/process responsibilities where appropriate.

Candidate application roles: Internal User, Unit Admin, Central Operator, Reviewer / Approver, Vendor, Super Admin. This list is **not a frozen authorization model**; validate in P2/P5.

## OD-10

Owner must have Super Admin capability with access to all PRANATA workspaces and system administration.

## OD-11

**V1 authentication is LOCAL.** Preferred baseline: Laravel-native authentication, username/email + password, session-based authentication, password recovery, login throttling and framework-native secure password hashing. Exact starter/version and account/recovery/security contracts are later P5 decisions. Framework convenience OAuth must not override this local-only direction.

## OD-12

Anything depending on `uny.ac.id`, SSO UNY, LDAP UNY, an institutional identity provider or other UNY domain/API authorization is **FUTURE** and must not be a V1 dependency.

## OD-13

Future external integrations must remain modular and replaceable through explicit integration boundaries/adapters where justified. Do not add speculative integrations or infrastructure merely because a future boundary is anticipated.

## OD-14

The latest legacy PRANATA HTML is valuable implementation evidence for asset reporting behavior. It is **not automatically authoritative accounting policy**. The supplied local HTML is inspected as the available artifact; its latestness must be confirmed before relying on it as the final legacy revision.

## OD-15

Asset reporting should eventually support appropriate period/cutoff concepts: today/as-of date, specific month, quarter, semester, annual and custom range where semantically appropriate. Exact depreciation accounting treatment for arbitrary daily dates remains unresolved until validated. **Do not invent daily depreciation policy.**

## OD-16

Design for approximately **up to one million asset records**. Do not use browser-only processing of the full dataset. Later planning must consider server-side filtering, pagination, batch import, validation, reporting, reconciliation, indexing and justified background processing. P0 sets direction; no performance budgets/schema/indexes are designed yet.

## OD-17

Indonesian is the primary user-facing language. Follow AICWDF localization unless explicitly superseded: default `id-ID`, secondary `en`, clear language switch, plain copy, localizable strings and layout/translation verification. Technical identifiers remain English where practical.

## OD-18

Additional recurring production cost target is near zero outside client-funded infrastructure/domain. Paid recurring services require explicit Owner approval. No cost exception exists.

## OD-19

PRANATA should be user friendly and action-oriented. Help users understand what needs action, who owns the current action, who/what is being waited on, current status, next step and relevant evidence/documents. Specific pages/interactions remain for P3/P7.

## OD-20

Workspace choice on login is a UX/workspace concept, **not a permission grant**. The backend must still enforce authorization.

## GOV-01 — Framework and durable authority

Classification: OWNER_APPROVED_DECISION (U-001 governing/session mandate). Adopt AICWDF v4.3 as master operating framework, adapted to PRANATA rather than mechanically copied. Repository is durable Source of Truth; chat contextual. Owner decides/approves; Codex/ChatGPT plans; future Claude executes READY Tasks without hidden chats. See [AUTH-001](APPROVAL_RECORDS.md#auth-001).

## GOV-02 — P0-only authorization and terminal state

Classification: OWNER_APPROVED_DECISION (U-001). Current work is P0 planning/documentation only. No P1, application implementation/scaffolding/packages/schema/auth/UI, P6–P10 plans, Task contracts/count, planning freeze or implementation before approved P0–P11 and READY baseline. End P0 VERIFYING, P1–P11 TODO, freeze NOT REACHED, Tasks NONE; only Owner approval can make P0 DONE.

## GOV-03 — Source and Git safety

Classification: OWNER_APPROVED_DECISION (U-001). Inspect local `reference-inputs/` but keep it ignored. Do not copy/move raw internal sources into tracked paths without explicit authorization. Never reproduce secrets/plaintext passwords; sanitize documentation. No commit or push this session; do not alter remote configuration. Leave P0 in the working tree for review.

## GOV-04 — Preferred stack and capabilities

Classification: OWNER_APPROVED_DECISION (U-001). Preferred stack unless changed later through ADR plus Owner approval: Laravel, Inertia, React, TypeScript, shadcn/ui, Tailwind CSS, PostgreSQL, Nginx, Modular Monolith. Redis/Valkey only if justified. Adopt current-doc, browser E2E, impact-analysis and MCP on-demand policies. No unnecessary application dependencies in P0. This is a preferred governance baseline, not approved P4 design.

## GOV-05 — Structural reference boundary

Classification: OWNER_APPROVED_DECISION (U-001). Study [MULTIPLECORP](https://github.com/yusufarst/MULTIPLECORP) only for entry point, thin adapter, governance, authority, phase/context/decision/gap/approval/evidence/handoff structure and future Task discipline. Do not import its product, business/domain/security/financial/permission/workflow truth or owner-specific directives.

## GOV-06 — Verification and handoff

Classification: OWNER_APPROVED_DECISION (U-001). Build the P0 structure, record safe source inventory and gaps, self-review against AICWDF, provide quality evidence and end-of-session report, leave exact next action as Owner review or corrections, and stop after P0.

## FD-01 — Adopted operating defaults

Classification: AUTHORITATIVE_SOURCE for operating policy, through GOV-01 framework adoption; not a new Owner product/domain decision. Inherit production DB zero-touch, minimal executor context, zero dead interactions, ID/EN localization, near-zero cost and all phase-appropriate AICWDF quality requirements. AICWDF-COMPAT-8 is the inherited password default unless a binding external requirement supersedes it; it is not a NIST compliance claim. P5 must record/check the exact auth/password contract once. V1 remains local under OD-11/12, so framework Google convenience-login defaults are not applied. Named hosting/tool examples do not prove installed capabilities or selected providers.

## P0 approval lifecycle record

Recorded 2026-10-04 (Asia/Jakarta): [APPR-001](APPROVAL_RECORDS.md#appr-001) approves the exact reviewed P0 Governance / Foundation pre-approval candidate; [AUTH-002](APPROVAL_RECORDS.md#auth-002) permits its administrative recording and one initial checkpoint. P0 is DONE; P1–P11 remain TODO, freeze NOT REACHED, Tasks NONE, baseline NOT READY and execution NOT AUTHORIZED. P1 requires separate explicit Owner authorization.

The U-001 OD-01–OD-20, GOV-01–GOV-06 and FD-01 sections above retain their original wording and qualifications. GOV-02's initial-session VERIFYING terminal state and GOV-03's initial-session no-commit/push restriction are historical and superseded only for the approved P0 completion/checkpoint by APPR-001/AUTH-002. No product, business/accounting, role, authentication, architecture, V1/FUTURE or source-safety direction is superseded; no GAP is resolved.

## P0 post-publication operational closure

Recorded 2026-10-04 (Asia/Jakarta), under [AUTH-003](APPROVAL_RECORDS.md#auth-003): the approved P0 checkpoint `f6d889100306a63a5bd391da4310346fc427d4d8` (`docs: finalize P0 governance`) is published; local HEAD, origin/main and the live remote ref matched during inspection. Remote verification PASS; [AUTH-002](APPROVAL_RECORDS.md#auth-002) is HISTORICAL / COMPLETED. The correction synchronizes current operational wording only, preserving the approval identity, historical review evidence and every OD/GOV/FD section and OPEN gap. It changes no business/product/domain or approved-governance substance and creates no new phase approval.

Current active phase authorization: NONE. Project state PARTIALLY_PLANNED; P0 DONE — approved and published; P1–P11 TODO / NOT AUTHORIZED; Planning Freeze NOT REACHED; Tasks NONE; Task Baseline NOT READY; Execution NOT AUTHORIZED; application implementation NONE. AUTH-003 permits one documentation correction commit/push/verification and expires upon completion. Exact next safe action: Owner separately authorizes P1 — Product Definition, Scope & Acceptance.
