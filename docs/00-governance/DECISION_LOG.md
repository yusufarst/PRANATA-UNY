# Decision log

Status: Approved baseline: APPR-001–APPR-006 (P0–P11 approved at 851013f) · Planning Freeze FRZ-001 | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent

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

## P3 post-publication completion

Recorded 2026-10-06 (Asia/Jakarta) under [AUTH-010](APPROVAL_RECORDS.md#auth-010): the approved P3 checkpoint `0e3f7de3acbed5213c13e22a61fc2728e57ccc77` (`docs: finalize P3 workflows`) is published on `main`; read-only verification in the AUTH-010 preflight confirmed local HEAD = origin/main = live remote main. [AUTH-009](APPROVAL_RECORDS.md#auth-009) is **HISTORICAL / COMPLETED** under its automatic completion rule. Administrative record only: no new approval, OD or ADR.

## P0–P3 remediation decision provenance

Source: [U-005](SOURCE_INVENTORY.md#u-005-p0p3-remediation-owner-decisions), recorded under [AUTH-010](APPROVAL_RECORDS.md#auth-010) on 2026-10-06 (Asia/Jakarta). OD-24, OD-25 and GOV-07–GOV-09 below are **OWNER_APPROVED_DECISION**. Recording them does not approve the P0–P3 remediation candidate. APPR-005 and AUTH-011 are reserved and not recorded.

## OD-24

Facility borrowing (peminjaman fasilitas), including facility booking and return procedures, is NOT PART OF V1. It remains a FUTURE opportunity only (V1_SCOPE FUT-04). No borrowing capability, acceptance criterion, domain concept, business rule, workflow, route or interaction is added to V1. Borrowing source materials remain evidence of existence only. GAP-020 stays OPEN solely for the official procedure and authority question if borrowing is later activated by a new Owner scope decision. This decision resolves no procedure, sets no precedence between historical borrowing sources and grants no permission. OD-12's local V1 boundary is preserved.

## OD-25

Multi-warehouse inventory operations are a V1 MUST operational requirement (provenance label OWNER-NEW-REQ-01). Goal: replace the current manual paper/book recording of incoming and outgoing warehouse goods with structured PRANATA operational recording. UNY operates multiple warehouses.

V1 supports operational semantics for: multiple warehouses; stock position by warehouse; incoming-goods receipt; outgoing-goods issue/usage, including issue to an employee or to an Organization Unit; the responsible warehouse officer/context; recipient context; evidence/acknowledgement where applicable; inter-warehouse transfer preserving source warehouse, destination warehouse, dispatch context, receipt context, discrepancy/exception and history/evidence, without invented accounting effects; physical stock count (stock opname); discrepancy; reconciliation; warehouse-scoped operational reporting; provenance/history; and action/owner/waiting/next-action semantics.

A Warehouse (Gudang) is a distinct concept, not an Organization Unit; one Organization Unit may operate several warehouses. Not every stored item is an Asset: Asset, Persediaan and KDP keep distinct meanings. Primary warehouse stock operations belong to Persediaan. An Asset may reference a warehouse as its physical location/context without becoming Persediaan. A warehouse does not determine accounting classification. No rack/bin model is decided.

V1 prioritizes going-forward digital recording. Full re-entry of historical warehouse paper/books is not a V1 prerequisite; any later digitization follows the historical intake path under validated rules.

An employee without a PRANATA account may be recorded as an issue recipient using only the minimum identity/context required by an approved rule. There is no HR integration and this decision defines no personal-data field; retention and privacy are governed by GAP-023. A recipient is participant/recipient context, not a responsibility type, unless an accountable action is later approved.

Scanning is not required in V1 and is FUTURE only (FUT-07): no hardware, barcode standard or technology is selected; scanning never becomes an acceptance, evidence or authority gate; manual search/selection is a complete V1 path. Official warehouse rules and authority remain GAP-025.

## GOV-07 — Operating agent model

Classification: OWNER_APPROVED_DECISION (provenance U-005), effective prospectively from its recording date. The Owner is the final decision and approval authority. Claude Project is the primary planning, orchestration and proposal agent for future authorized planning work; the role confers no phase authority, and every P4–P11 phase requires explicit Owner authorization. ChatGPT is the independent Second Review agent; a Second Review verdict never constitutes Owner approval. Claude Code is the execution agent: application execution stays WAITING until approved P4–P11, Planning Freeze, an explicitly READY P11 Task and execution authority all exist; Claude Code may perform bounded repository documentation corrections only under an explicit Owner authorization or instruction that has passed Second Review, and such authority never authorizes application implementation. Two review vocabularies stay distinct: phase/candidate review uses READY_FOR_OWNER_APPROVAL or CORRECTIONS_REQUIRED; independent Second Review of proposals and instructions uses APPROVED, APPROVED WITH CONDITIONS, REVISE or BLOCKED. Repository truth outranks chat or project memory. GOV-01 wording and provenance are unchanged; only its agent-assignment clause is superseded, prospectively.

## GOV-08 — Standing Git identity and no-attribution policy

Classification: OWNER_APPROVED_DECISION (provenance U-005). Every future commit uses only the Owner's configured Git author and committer identity. No AI system, model, agent, tool or bot — including Claude, Claude Code, Anthropic, OpenAI, ChatGPT and Codex — may appear as author, committer, co-author or attribution, and no attribution trailer may be added (Co-Authored-By naming an AI, tool or model; Generated-By; Assisted-By; AI-generated; AI-assisted; or an equivalent). Before every authorized commit, git config user.name and user.email are verified read-only and never changed without explicit Owner authorization; the reference identity is the Owner identity on the published commits unless the Owner states otherwise, and a mismatch stops the commit. After every commit and before any push, the raw commit metadata and the full message are inspected: author and committer are the Owner only, AI or tool co-authors none, AI or tool attribution trailers none. Any deviation stops publication; there is no automatic amend, and the Owner decides the remediation of an unpublished commit. Tool attribution settings are necessary but never sufficient. No force push and no rewrite of published history merely to change attribution. Branch names never carry AI or tool identity (GOV-09).

## GOV-09 — Byte preservation, line endings and neutral branch convention

Classification: OWNER_APPROVED_DECISION (provenance U-005). Line endings are not normalized, and no .gitattributes is added until the Owner's working-clone line-ending behavior has been inspected and a separate Owner decision exists. Existing BOMs, historical CRLF/LF patterns (including per-line endings in mixed files) and the bytes of untouched files are preserved where feasible; whole-file diffs caused only by line-ending conversion are avoided. Authorized staging uses per-command core.autocrlf=false after verifying that working-tree bytes equal the intended bytes. Future branches use the neutral form `<type>/<scope>` (for example docs/, feature/, fix/, chore/) and never carry AI, model or tool identity; this prospectively supersedes the earlier codex/* default. This decision creates no branch.

## P0–P3 remediation lifecycle record

Recorded 2026-10-06 (Asia/Jakarta) under [AUTH-010](APPROVAL_RECORDS.md#auth-010); provenance [U-005](SOURCE_INVENTORY.md#u-005-p0p3-remediation-owner-decisions). Process decisions recorded verbatim:

Checkpoint strategy (ODR-F):

- One bounded P0–P3 remediation checkpoint, in this sequence:
  1. preparation authorization (AUTH-010)
  2. documentation correction candidate
  3. exact candidate manifest
  4. independent review
  5. bounded correction and delta review, if required
  6. explicit Owner approval (reserved APPR-005)
  7. one approved remediation checkpoint (reserved AUTH-011)
  8. normal push
  9. remote verification
  10. that authorization becomes HISTORICAL / COMPLETED automatically
- No separate governance and domain commits, unless a future material blocker forces a split, and then only by Owner decision.

Optional findings (ODR-G):

- AUD-P03-016: DEFERRED. No external review-report storage is required before P4.
- AUD-P03-017: ADOPTED. Low-cost provenance cross-reference cleanup, without rewriting historical records.

Execution terminology:

- Documentation remediation execution and application execution are always stated separately.
- Before AUTH-010 is active, and after its completion: documentation remediation execution NOT AUTHORIZED; application execution NOT AUTHORIZED.
- While AUTH-010 is active: documentation remediation execution AUTHORIZED ONLY BY ACTIVE AUTH-010; application execution NOT AUTHORIZED.

Governing rules are recorded in [SOURCE_OF_TRUTH](SOURCE_OF_TRUTH.md): the [authority-basis rule](SOURCE_OF_TRUTH.md#authority-hierarchy), and the [snapshot rule and amendment-status convention](SOURCE_OF_TRUTH.md#document-lifecycle). Prospective supersessions: the GOV-01 agent-assignment clause, by GOV-07; and the [GIT_WORKFLOW](GIT_WORKFLOW.md) `codex/*` branch default, by GOV-09. GAP-020 V1 relevance is decided by OD-24; GAP-021–GAP-025 are added OPEN; no GAP is resolved. The P0–P3 remediation candidate is VERIFYING; no accepted ADR; no cost exception.

## Owner directions of 2026-10-30 — provenance

Recorded 2026-10-30 (Asia/Jakarta) under [AUTH-012](APPROVAL_RECORDS.md#auth-012) by the Planning Agent. Sources: [U-006–U-010](SOURCE_INVENTORY.md#u-006u-010-owner-directions-of-2026-10-30). GOV-10–GOV-12 and OD-26–OD-34 below are **OWNER_APPROVED_DECISION** for the recorded sanitized direction. Recording them approves no P0–P11 candidate. Directions the Owner marked provisional keep that qualification: they set planning direction and configuration defaults, not validated institutional policy; activation of a dependent official rule still follows the [GAP_REGISTER](GAP_REGISTER.md) discipline.

## GOV-10 — Planning authorship, agent roles and planning write access

Classification: OWNER_APPROVED_DECISION (U-006, U-007, U-009), prospective from 2026-10-30. Claude Planning — the Claude Project "PRANATA UNY" planning session acting as Lead Planning Agent — is the **sole planning author** for P0–P11, including the P11 Task backlog. Under AUTH-012 it may use bounded planning branches, commits, pushes and review pull requests with the Owner Git identity (GOV-08). Claude Code is the Execution Agent for READY Tasks once phase approvals, Planning Freeze and a standing execution authorization exist; it authors no planning truth beyond the bounded documentation updates an active Task requires. ChatGPT is the independent Second Review Agent only; its earlier blueprints, its rejected UI prototype and its 56-Task draft are nonbinding. No parallel session may author a competing planning baseline: on 2026-10-30 the Owner ordered the local branch created by a previous session to be deleted (done; its pre-existing working-tree content was preserved). GOV-07 is refined, not reversed: planning write access through branches and PRs is now authorized. No agent can grant Owner approval.

## GOV-11 — Consolidated approvals and efficient review

Classification: OWNER_APPROVED_DECISION (U-007 §4, §13, §22, §23, §27). The Planning Agent makes routine planning decisions within approved requirements. Material decisions are grouped into Owner decision packages with a recommendation, relevant alternatives, consequences and the exact affected artifacts. Several reviewed phase artifacts may be presented together for explicit batch approval when safe; phase-specific reviewed identities and evidence stay distinct. Preferred flow: draft → internal consistency check → review candidate → one substantive independent Second Review → focused corrections → delta check → Owner approval. A full re-review is warranted only when material meaning, architecture or risk changes. Silence is never approval, and a technically sound design is never proof of an official UNY rule.

## GOV-12 — Autonomous execution operating model (designed now, activated later)

Classification: OWNER_APPROVED_DECISION (U-007 §2, §9–§21, §24–§25). P8, P9 and P11 design a Claude Code execution system that — only after Owner approval of P4–P11, Planning Freeze and a separate standing execution authorization — moves Tasks through READY → IN_PROGRESS → IMPLEMENTED → VERIFIED → REVIEWED → PR → CI → MERGED → DONE → next READY without routine Owner interaction. Required properties: risk-based review (HIGH / STANDARD / LOW; findings P0–P3); bounded Codex PR review (NOT_REQUESTED, PENDING, STARTED, COMPLETED_NO_FINDINGS, COMPLETED_WITH_FINDINGS, FAILED, TIMEOUT; any wait measured from confirmed start; TIMEOUT is not PASS; mandatory CI never bypassed); root-cause CI failure handling; isolated test databases; one project-scoped watcher with evidence-based idle detection and durable checkpoints; explicit IMMEDIATE_SAFE_SHUTDOWN and SHUTDOWN_AFTER_CURRENT_TASK modes; strict isolation from MULTIPLECORP, BantuKasir and ELLIGBLE; compact context; mandatory progress footers. MULTIPLECORP is a methodological reference only. This decision activates nothing and claims no untested platform capability.

## OD-26

DPSA UNY comprises (a) **Aset dan Layanan Pengadaan (ALP)**, the current primary focus, and (b) **Prasarana, Sarana dan Rumah Tangga (PSRT)**, whose facility, booking, room, equipment and vehicle services are FUTURE. UNY headquarters has **two distinct warehouses: Gudang ALP and Gudang PSRT**. PRANATA provides one multi-warehouse capability with separate stock, movements, staff, permissions and reporting per warehouse; ALP warehouse operation is prioritized and the PSRT warehouse becomes operable when its phase is selected. PSRT household and facility services are not V1 requirements (consistent with OD-24). Source: U-006. Extends OD-25 without changing it.

## OD-27

V1 procurement covers all five methods of Peraturan Rektor UNY No. 34/2023 Article 24 — **Pengadaan Langsung, Quotation, Tender, Penunjukan Langsung and E-Purchasing** — superseding the P1 proposal of an initial Pengadaan Langsung/Tender family. Method applicability uses versioned rule policies per governing regime (selected by funding source and effective date), never monetary thresholds alone; a duly assigned official determines the final method, with an audit trail. National thresholds are not reused for transactions governed by a different regime. Official system boundaries are respected: RUP is announced in SiRUP; tender runs on the required official channel; e-purchasing transactions occur on the applicable official platform; PRANATA manages the related internal administration, references and documents. Vendor submission inside PRANATA is allowed only where officially applicable. Policy-specific numbering, HPS, SPPBJ, signing and method variants are preserved, as is the separation of selection, internal administration, document preparation, issuance and signature, goods receipt, payment, asset recognition, physical stock and accounting/depreciation. Source: U-006, U-007 §6.

## OD-28

Global application role groups stay compact: **Super Admin, Internal Employee, Vendor**. Detailed capability comes from unit scope, workspace scope, process assignment and action permission; official titles (PPK, Pokja, warehouse clerk, asset officer, approver) never become global roles. An ordinary Procurement worker cannot see or access Asset data and vice versa. ALP warehouse staff cannot view or manage PSRT stock unless separately assigned. Enforcement is at server, API, download and query level, not only by hiding menus. A Vendor sees only its own participation and permitted document/status information. Super Admin may have **audited read access** to confidential vendor offers for system management, never supplier-selection authority. Segregation of duties applies within the same package. Refines the OD-09 candidate list; OD-10 and OD-20 unchanged. Source: U-006.

## OD-29

Procurement outcomes: document generation and SPJ packs, procurement progress, vendor document-completeness tracking, HPS, RUP reference, contracts, amendments, BAST and validated official procedures, with one-time data entry and interconnected documents produced in a few clicks. Users see who is responsible, current progress, missing evidence, next actions and relevant reports. Number suggestions are checked by authorized personnel and never collide silently. HPS component data and arithmetic are captured; specialized formulas are enabled only after official checking. Source: U-006.

## OD-30

**Provisional** finance, asset and inventory directions (U-006); each requires validation against the applicable authoritative instrument before a final rule is activated:

1. Internal budget checks rely on official finance sources and captured references/evidence; internal budget commitments do not replace finance books and are released only after valid cancellation and finance verification.
2. Receipt/BAST, physical warehouse stock, asset recognition, financial payment and depreciation remain distinct states.
3. Purchased-asset registration direction: SP2D issuance date, with depreciation beginning the following month; donations without SP2D follow a separately validated path.
4. KDP completion requires verified handover, readiness for use, accountable approval and finance review.
5. Historical assets keep their original values and classifications until a justified correction.
6. Inventory transaction codes P01 and P02 remain distinct and unmapped until validated.
7. Warehouse physical stock increases on verified physical receipt or verified usable return and decreases when goods are physically handed over, with subsequent recipient confirmation.

## OD-31

The warehouse capability replaces paper-book item withdrawals with digital **request, approval, handover, recipient confirmation and return**, and with stock ledgers per warehouse. ALP and PSRT warehouse access are separate. The approver chain per request type is configurable; its official content is validated under GAP-025. Source: U-006, U-007 §5C.

## OD-32

The first internal **Procurement UAT** covers Goods, Other Services, Construction and Consultancy up to contract-document preparation, using synthetic data and no vendor offer submission; core mandatory scenarios must pass with no unresolved critical/high findings; a designated Procurement lead accepts the results with PPK/business/Finance checks. Later V1 functionality includes contract execution, amendments, BAST and vendor/asset/warehouse capabilities. Planning targets about 200 users and 1,000,000 assets with properly scoped server-side search and reporting (reaffirms OD-16/OD-22). Initial disaster-recovery design objectives are **RTO 4 hours and RPO 1 hour**; they stay design objectives until validated by real restore tests. Source: U-006, U-007 §8.

## OD-33

Claude Planning designs the UI/UX within approved boundaries; the rejected ChatGPT prototype is not a reference. Favor one-time data entry, clear next actions, automated reusable documents, low-click operation, practical role-based dashboards and mobile-responsive workflows. No dead buttons, incomplete navigation, fake interactivity, repeated data entry without a business reason, or frontend-only enforcement of restricted capabilities. OD-21 remains in force. Source: U-006, U-007 §7.

## OD-34

Visual identity: primary brand color **Deep Navy `#172554`**, soft page background **Ice Blue `#EFF6FF`**, primary content surface **Pure White `#FFFFFF`**. Navy is the recognizable identity; Ice Blue serves page canvases, surrounding areas and supporting sections; white stays dominant in working areas (cards, tables, forms, dialogs, data panels, document interfaces). The application should feel predominantly white, clean, spacious, elegant and professional. Avoid excessive dark surfaces, over-saturated blue, unnecessary gradients, distracting animation and color-only status indicators; meet applicable WCAG requirements; keep shadcn/ui, Tailwind CSS, React, TypeScript and Inertia. All other palette, typography, shadow, radius, spacing, navigation, motion and optional dark-mode decisions belong to P7. SRC-047/VIS-04 remains the primary reference, translated without copying its colors. Source: U-008.

## Owner procurement field findings of 2026-10-30 — provenance

Recorded 2026-10-30 (Asia/Jakarta) under [AUTH-012](APPROVAL_RECORDS.md#auth-012) by the Planning Agent. Source: [U-011](SOURCE_INVENTORY.md#u-011-owner-procurement-field-findings-of-2026-10-30). OD-35 and OD-36 below are **OWNER_APPROVED_DECISION** for the recorded sanitized direction. The employees' observations behind them are recorded as observed practice, not official rules; their reconciliation with PR 34/2023 is in [REGULATORY_SOURCE_REVIEW RG-07](evidence/REGULATORY_SOURCE_REVIEW.md#rg-07-selection-evaluation-and-award-field-findings-reconciled). Recording them approves no candidate and resolves no GAP.

## OD-35

PRANATA supports **more than one winning provider within one procurement package where the governing procedure permits**, for example award by item or by lot. Evaluation results, ranking, reserves, award, appointment (SPPBJ where applicable), contract, handover and payment tracking are therefore kept per **award scope** (whole package, lot or item); an indivisible award scope is never awarded twice unless the governing policy explicitly allows several providers for it (for example a framework agreement, PR 34/2023 Pasal 31 ayat (7)). A mandatory one-winner-per-package model is not used. The Owner confirmed that such cases exist at UNY and asked not to be asked the yes/no question again; which methods and procurement types permit lot or item award, and how such awards are documented, are procedural limits to be validated with the authorized UNY custodians (GAP-003/017/027), not a reason to withhold the capability. Source: U-011 (SRC-068 item A.7).

## OD-36

Offer evaluation is the **highest-priority procurement workflow**: PRANATA must make evaluation clear, accurate, accountable and efficient for procurement staff, not merely store documents. Procurement steps and document sets are method-, type- and value-aware (for example a more detailed construction document set than non-construction where required) instead of identical for all five methods, and include post-award inspection, supplier performance assessment and payment-request tracking without moving money. The field-findings memorandum is reference material, not a mandatory design; employees' observations (for example three quotations in Pengadaan Langsung) are recorded as observed practice and become rules only through validated sources. Claude Planning keeps full design discretion within approved boundaries. Source: U-011.

## GOV-08 application — Owner Git identity confirmation

Recorded 2026-10-30 (U-010): the Owner confirmed `Yusuf Arif Setiawan <134618411+yusufarst@users.noreply.github.com>` as author and committer, configured at repository-local level only; global configuration unchanged; no AI attribution of any kind; identity verified before each commit; no history rewrite or force push; ask again only on an actual mismatch or technical failure. Cloud planning sessions create commits without environment-supplied signatures (per-command option, no configuration change) so commit metadata matches the Owner's published commits.

## P0–P11 planning candidate lifecycle record

Recorded 2026-10-30 (Asia/Jakarta) under AUTH-012. Review branch `docs/p0-p11-planning` carries: (1) the imported AUTH-010 P0–P3 remediation candidate, byte for byte; (2) this record and the 2026-10-30 Owner directions; (3) the P0–P3 compatibility amendment; (4) the P4–P11 planning candidates; (5) the targeted procurement field-findings amendment (U-011; OD-35, OD-36). Everything on the branch is **VERIFYING / NOT APPROVED** until independent Second Review and explicit Owner approval; nothing is merged into `main`. The [OWNER_DECISION_PACKAGE](../handoff/OWNER_DECISION_PACKAGE.md) proposes consolidating the reserved APPR-005/AUTH-011 remediation gates into the batch approval; until the Owner decides, both stay reserved and unrecorded.

## Owner development-topology decision of 2026-11-01 — provenance

Recorded 2026-11-01 (Asia/Jakarta) under FRZ-001 change control by the Planning Agent. Source: [U-013](SOURCE_INVENTORY.md#u-013-owner-directive-docker-free-development-of-2026-11-01). OD-37 below is **OWNER_APPROVED_DECISION**; the planning changes that apply it form [PCR-002](APPROVAL_RECORDS.md#pcr-002). It changes the development and testing topology only, not approved business functionality, and resolves no GAP.

## OD-37

The Owner's Windows PC (about 7.2 GB usable RAM, shared with MULTIPLECORP, BantuKasir and ELLIGBLE) runs **no Docker or Docker Compose for PRANATA**, no self-hosted GitHub Actions runner and no PRANATA database or service; shared Docker, WSL and other projects are never restarted, stopped, reconfigured or otherwise disturbed. **GitHub-hosted GitHub Actions runners are the primary environment for resource-intensive application testing**; PostgreSQL and Gotenberg are permitted there as service containers. Locally only lightweight editing, Git operations, the Claude execution safeguards and necessary low-resource tools run. Global PHP, Composer, PostgreSQL, environment variables, PATH and system services are not changed without separate Owner authorization. No paid service or infrastructure purchase. No institutional reference data, private file or credential is uploaded to public GitHub. Laravel, React, PostgreSQL, the modular monolith, the official-policy activation gates and all essential quality and security requirements stay. Consequences: capability C-14 is SUPERSEDED / NOT APPLICABLE and never VERIFIED; the local Claude Code headless runner and its restricted token (C-15) are not a self-hosted Actions runner and stay a later Owner prerequisite; the D-19 staging clause cannot place a Docker Compose staging on this PC, so where staging runs is confirmed by the Owner before TASK-040. Source: U-013.
