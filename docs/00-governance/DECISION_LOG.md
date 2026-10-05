# Decision log

Status: APPROVED (APPR-004; AUTH-009 checkpoint only) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

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

Historical closure terminal state: no active phase authorization; P0 DONE and P1–P11 TODO / NOT AUTHORIZED. AUTH-003 was exhausted by the published closure at `3e7d6a21287410ff977db9db996154043e1c8926`. The current P1 authorization is separately recorded below.

## P1 authorization and decision provenance

Received 2026-10-04 (Asia/Jakarta), [U-002](SOURCE_INVENTORY.md#u-002-p1-owner-request) explicitly authorizes **P1 planning only** under [AUTH-004](APPROVAL_RECORDS.md#auth-004). OD-01–OD-20 are reaffirmed with their original qualifications; their historical sections above are unchanged. The following new/extended directions are **OWNER_APPROVED_DECISION** from U-002; at issuance, generated P1 scope, metrics and acceptance were **VERIFYING proposals**. That preparation authorization created no P1 phase approval, later-phase authority or gap resolution. The separate explicit approval below preserves their qualifications.

## OD-21

Owner designates `reference-inputs/PRANATA_PRIMARY_UI_UX_MOTION_REFERENCE.mp4` as the **PRIMARY VISUAL / INTERACTION / MOTION REFERENCE**. Adopt and translate its overall visual sophistication, clean modern shell, modular cards, refined spacing, restrained hierarchy and smooth professional interaction/motion language into PRANATA's UNY context, four workspaces, Indonesian users, accessibility, responsive/mobile operation, large datasets and productive work. Literal cloning is not required. Actual bounded observations must remain distinct from Owner direction and future P7 decisions. P1 establishes experience/acceptance boundaries only; final IA, routes, tokens, components, responsive contracts, motion timings/easing and design system remain P7.

## OD-22

Product planning targets approximately **200 users and 1,000,000 asset rows**. This extends OD-16; 200 is a user-population planning envelope, **not proven simultaneous concurrency**. P1 defines measurable proposed interaction/search/import/reporting/background-feedback expectations; P4/P6/P8/P9 must validate representative workload and capacity under GAP-016 before implementation/release commitments. No schema/index/query/locking strategy is approved here.

## OD-23

U-002 requires evidence-based product evaluation of asset lifecycle/accounting/reporting, a **separate Persediaan transaction-ledger capability**, KDP, procurement intake/RUP/packages/method-related work/documents/contract/execution/BAST/SPJ/payment tracking, package-linked activities/events and Vendor Portal participation. Existing/new providers should reuse company/PIC/qualification evidence and understand pending actions, deadlines and status. Reaffirm **DATA ONCE, REUSE MANY TIMES** (OD-03/08). Eligible handover data reused in a classified downstream Asset/Inventory/KDP draft is a **product hypothesis**, with a responsible operator completing remaining information; it does not finalize eligibility, accounting classification, event trigger or official approval sequence. Proposed V1 prioritization belongs to [V1_SCOPE](../01-product/V1_SCOPE.md) and requires Owner approval; evidence mention alone does not mandate every historical menu/document variant.

## P1 approval lifecycle record

Recorded 2026-10-05 (Asia/Jakarta): [APPR-002](APPROVAL_RECORDS.md#appr-002) explicitly approves the exact reviewed **P1 — Product Definition, Scope & Acceptance** candidate. Historical pre-approval manifest: `docs/00-governance/evidence/P1_ARTIFACT_MANIFEST.json`, **12,747 bytes**, SHA-256 **`50425ec1d7f364a601a1083eee68af523b70135a63af155df6ed86461c8b8f91`**. Initial independent review READY_FOR_OWNER_APPROVAL (0 blockers / 0 major / 1 minor P1-PROD-01 / 0 observations); bounded correction and independent delta review READY_FOR_OWNER_APPROVAL, P1-PROD-01 RESOLVED, final counts all zero, as reported by the Owner.

[AUTH-005](APPROVAL_RECORDS.md#auth-005) supplies only approval recording, lifecycle/evidence/manifest synchronization, bounded integrity checks, one normal `docs: finalize P1 product definition` commit, normal push/remote verification and stop. AUTH-004 preparation/correction is historical/completed; its VERIFYING/no-commit/push restriction is superseded only for this checkpoint. The authorization expires at verified publication; no continuing Git/phase authority.

OD-01–OD-23 and historical GOV/FD decision sections retain their exact wording and qualifications. All 20 CAP, 27 AC (including corrected AC-09/AC-10), 4 proposed NFR targets, product hypotheses, FUTURE/deferred boundaries and primary UI/UX/motion direction are preserved. No formal domain/procurement/accounting/permission/workflow/schema/design decision is added; GAP-001–GAP-020 remain OPEN and unchanged.

P0 DONE; P1 DONE — APPROVED; P2–P11 TODO / NOT AUTHORIZED; Planning Freeze NOT REACHED; Tasks NONE; Task Baseline NOT READY; Execution NOT AUTHORIZED; application implementation NONE. Exact next safe action after checkpoint: **Owner separately authorizes P2 — Domain Model & Business Rules**.

## P2 authorization lifecycle record

Historical preparation snapshot, completed by APPR-003/AUTH-007 below. Recorded 2026-10-05 (Asia/Jakarta): [U-003](SOURCE_INVENTORY.md#u-003-p2-owner-request) / [AUTH-006](APPROVAL_RECORDS.md#auth-006) explicitly authorize **P2 planning only**. Starting clean main, local HEAD = origin/main = live remote main at `8881c047f24451b30754b19fe0d1cf96b9078f90`, published P1 checkpoint, parent `3e7d6a21287410ff977db9db996154043e1c8926`; AUTH-005 is exhausted/historical. Original P0/P1 approvals and OD-01–OD-23 section wording remain intact.

P2 captures conceptual meaning, responsibility, lifecycle/history, qualified business rules and domain traceability. Its rules distinguish explicit Owner direction, evidence, proposals and blocked authoritative meanings. No institutional procedure/accounting rule is invented, no new OD or accepted ADR is created, and all GAP-001–GAP-020 remain OPEN. P1's five owning documents, 20 CAP, 27 AC and 4 proposed NFR remain byte-preserved.

Owner's explicit P3 deferral bounds AICWDF §13: lifecycle meaning/dispositions/audit are conceptual in P2; exact transitions/workflows, implementation/schema and final permission enforcement remain later phases. P2 terminal **VERIFYING**; no APPR-003. P3–P11 TODO / NOT AUTHORIZED, freeze NOT REACHED, Tasks NONE, baseline NOT READY, execution NOT AUTHORIZED, application NONE. No staging/commit/push. Next safe action: independent P2 review against the exact candidate manifest, then Owner approval or bounded corrections/delta review. Stop after P2.

## P2 approval lifecycle record

Recorded 2026-10-05 (Asia/Jakarta): [APPR-003](APPROVAL_RECORDS.md#appr-003) explicitly approves only the exact reviewed **P2 — Domain Model & Business Rules** candidate. Reviewed pre-approval manifest: `docs/00-governance/evidence/P2_ARTIFACT_MANIFEST.json`, **14,976 bytes**, SHA-256 **`d1f40247e83f8e5ddf419ab4dcc0eeca3e9bf64c3fafe7fff32739636f4450f3`**; independently verified before edits, all 56 entries/57 eligible files intact. Owner-reported independent review **READY_FOR_OWNER_APPROVAL**; final **0 blockers / 0 major / 0 minor / 0 observations**; review changed no files.

[AUTH-007](APPROVAL_RECORDS.md#auth-007) permits only approval recording, current-state/evidence/manifest synchronization, bounded integrity checks, one normal `docs: finalize P2 domain model` commit, normal push/live remote verification and stop. AUTH-006 is **HISTORICAL / COMPLETED**; its VERIFYING/no-stage/commit/push conditions are superseded only for this checkpoint. **AUTH-007 applies only until this checkpoint is normally pushed and remote verification passes; it then becomes HISTORICAL / COMPLETED automatically, with no continuing phase/Git authority and no separate closure commit. After verified publication, current active phase authorization is NONE.** No self-referential resulting SHA/publication assertion is recorded in advance.

The 77 concepts, 9 areas, 29 BR, 18 INV, 6 responsibility dimensions, 16 responsibility types and all traceability retain reviewed substance. All OD-01–OD-23 and P1 CAP/AC/NFR are preserved. EVIDENCE_SUPPORTED/PROPOSED/BLOCKED_BY_GAP retain their qualifications; no institutional procedure, formula, threshold, responsibility or official signoff is invented. GAP-001–GAP-020 remain OPEN, resolved NONE/new NONE; A 0 / B 14 / C 4 / D 2 unchanged. No scope/stack/architecture/security/accounting procedure change, accepted ADR or cost exception.

P0/P1 DONE; P2 **DONE — APPROVED**; P3–P11 **TODO / NOT AUTHORIZED**; Planning Freeze NOT REACHED; Tasks NONE; Task Baseline NOT READY; Execution NOT AUTHORIZED; application implementation NONE. Exact next safe action after verified checkpoint: **Owner separately authorizes P3 — Workflows, Routes & Interactions**. P3 is not authorized by this approval/checkpoint; stop after P2.

## P3 authorization lifecycle record

Historical preparation snapshot, completed by APPR-004/AUTH-009 below.

Recorded 2026-10-05 (Asia/Jakarta): [AUTH-008](APPROVAL_RECORDS.md#auth-008) supplies explicit P3 planning-only authority. Main, HEAD, origin/main and live remote main match published P2 checkpoint `9e7950db6f9d6c6ab900a6328778807e057fb812`, parent `8881c047f24451b30754b19fe0d1cf96b9078f90`; initial worktree/index clean. AUTH-007 is exhausted/historical under its verified-publication completion rule. This is an administrative authorization record, not a new OD or official procedure. All OD-01–OD-23 section bodies and historical APPR identities remain preserved; all approved P1/P2 artifacts and GAP_REGISTER are byte-identical.

P0/P1/P2 **DONE — APPROVED AND PUBLISHED** under APPR-001/APPR-002/APPR-003. P3 **VERIFYING** under AUTH-008 planning only. AUTH-002–AUTH-007 **HISTORICAL / COMPLETED**; AUTH-007 has no continuing Git authority. P4–P11 **TODO / NOT AUTHORIZED**. Planning Freeze **NOT REACHED**; Tasks **NONE**; Task Baseline **NOT READY**; Execution **NOT AUTHORIZED**; application implementation **NONE**. Workflow/state/route/interaction proposals remain source-qualified and blocked where authority is unavailable. No schema/permission/API/design implementation, Tasks, phase freeze or production actions; no staging/commit/push. Next: **Independent P3 review against the exact candidate manifest; then Owner approval if READY_FOR_OWNER_APPROVAL, or bounded P3 corrections and delta review. P4 remains NOT AUTHORIZED. Stop after P3.**

## P3 approval lifecycle record

Recorded 2026-10-05 (Asia/Jakarta): [APPR-004](APPROVAL_RECORDS.md#appr-004) explicitly approves only the exact corrected, independently delta-reviewed **P3 — Workflows, Routes & Interactions** candidate. Permanent PRE-APPROVAL manifest identity: **19,555 bytes / SHA-256 `056bb00c9622c02788e3028d7dfa1de564ee8e75c651aaa0b966805cefdc060e`**, all 66 entries/67 eligible files independently verified before approval edits. Initial pre-correction review identity **18,379 bytes / `2f5498228831bcce6a9cac9943ff38d64fa956d8c79a44966c4c5a424b8acf2b`** remains historical. Owner-reported initial READY_FOR_OWNER_APPROVAL, 0/0/3/0; P3-REV-01/02/03 RESOLVED; independent delta READY_FOR_OWNER_APPROVAL, final 0/0/0/0.

[AUTH-009](APPROVAL_RECORDS.md#auth-009) records Owner instruction 26,169 bytes / `566afe669cc678839165423691439be94bf070d4adaf075d6c901ad1c9285f48`, bounded approval/status/evidence/manifest synchronization, integrity checks, one normal `docs: finalize P3 workflows` commit, normal push/live verification and stop. AUTH-008 preparation completed. AUTH-009 is automatically HISTORICAL / COMPLETED only after successful push/live verification, current active phase authorization NONE; no separate closure commit. This is lifecycle administration, not a new OD/ADR or institutional/accounting decision.

Approved P1/P2 bytes, OD-01–OD-23, CAP 20 / AC 27 / DC 77 / BR 29 / INV 18, APPR-001/002/003, qualified P3 meaning and corrected locators remain intact. Counts 28 WF / 27 stateful / 142 states / 148 TR / 31 RT / 21 IX / 10 EP; all GAP-001–GAP-020 OPEN, resolved NONE/new NONE, A 0 / B 15 / C 3 / D 2.

P0/P1/P2 DONE and published; P3 **DONE — APPROVED**; P4–P11 TODO / NOT AUTHORIZED; Planning Freeze NOT REACHED; Tasks NONE; Task Baseline NOT READY; Execution NOT AUTHORIZED; application implementation NONE. After verified checkpoint, subsequent Claude bootstrap may be **EXECUTION AGENT — WAITING** only. No onboarding now; P4 separately Owner-authorized; implementation requires approved P4–P11, Planning Freeze and an explicitly READY P11 Task. Stop after P3.
