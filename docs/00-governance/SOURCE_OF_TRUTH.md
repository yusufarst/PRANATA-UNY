# Repository Source of Truth

Status: APPROVED (APPR-004; AUTH-009 checkpoint only) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

## Authority hierarchy

1. Latest explicit Owner-approved decision recorded in this repository, with approval provenance and supersession history.
2. [AGENTS.md](../../AGENTS.md) and the adopted [AICWDF v4.3](sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md) operating rules, as explicitly adapted for PRANATA.
3. Active approved Task contract, once P11 exists; there is none in P0.
4. Accepted ADRs with required Owner approval.
5. Current approved owning product/domain/workflow/architecture/security/design and governance documents.
6. Current implementation and tests; none exist yet.
7. [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md), which summarizes and routes but cannot supersede authority.
8. Chat/session context, transient agent memory and generated output.

An unapproved draft cannot supersede an approved decision. New explicit Owner instructions are recorded before becoming durable truth; they apply immediately within their stated scope. [APPR-001](APPROVAL_RECORDS.md#appr-001) / [APPR-002](APPROVAL_RECORDS.md#appr-002) preserve exact approved P0/P1 identities, both DONE and published. P2 is approved/DONE under [APPR-003](APPROVAL_RECORDS.md#appr-003), with all qualifications intact. AUTH-002–AUTH-007 are completed history; the published P2 checkpoint was live-verified at P3 start. AUTH-008 is completed preparation; [APPR-004](APPROVAL_RECORDS.md#appr-004) approves the exact corrected P3 candidate, with qualifications retained. [AUTH-009](APPROVAL_RECORDS.md#auth-009) permits one bounded P3 checkpoint only; after verified publication it is automatically completed, current active phase authorization NONE. P4–P11 remain not authorized. Formal external policy and law are evidence of binding constraints when verified; a conflict with Owner direction requires domain/Owner validation, not silent circumvention.

## Conflict protocol

Identify exact contradictory passages and provenance. Check scope, dates, authority and explicit supersession. Record a safe conflict in [GAP_REGISTER](GAP_REGISTER.md); request the smallest missing decision or authoritative source. Block only dependent decisions/work, continue unaffected authorized work. Never promote legacy behavior or an inference into official procurement/accounting procedure. After resolution update the owning document, decision history, index, approval record and handoff; preserve superseded records.

## Canonical ownership registry

| Concern | Owning record |
|---|---|
| Agent entry / tool adapter | Root AGENTS.md / CLAUDE.md |
| Identity and P0 purpose | PROJECT_CHARTER.md |
| Authority / document lifecycle / ownership | This document |
| Phase progress | ../PHASE_STATUS.md |
| Authorization / phase approvals / freeze approvals | APPROVAL_RECORDS.md |
| Owner and recorded project decisions | DECISION_LOG.md; DECISION_INDEX.md is a locator |
| Unresolved questions and conflicts | GAP_REGISTER.md |
| Provenance, classification and handling | EVIDENCE_POLICY.md; SOURCE_INVENTORY.md is the file inventory |
| Agent responsibilities / session flow | AGENT_OPERATING_MODEL.md |
| Engineering constraints | ENGINEERING_PRINCIPLES.md |
| Material changes / ADR triggers | CHANGE_CONTROL.md; ../adr/README.md |
| Tools / capabilities / MCP | TOOLCHAIN.md |
| Recurring cost / exceptions | COST_POLICY.md |
| Production safety / sensitive-source boundaries | PRODUCTION_DATA_SAFETY.md |
| Branch/environment and Git safety | GIT_WORKFLOW.md |
| Current operational continuation | ../handoff/CURRENT_HANDOFF.md |
| Framework mapping / P0 verification | evidence/FRAMEWORK_ADOPTION.md / evidence/P0_QUALITY_GATE.md |
| Product identity / problems / outcomes / stakeholders / journeys | ../01-product/PRODUCT_OVERVIEW.md (P1 APPROVED / APPR-002) |
| V1 capabilities / priorities / future boundary | ../01-product/V1_SCOPE.md (P1 APPROVED / APPR-002) |
| Product acceptance / proposed performance and usability targets | ../01-product/ACCEPTANCE_CRITERIA.md (P1 APPROVED / APPR-002) |
| Product source-to-scope evidence coverage | ../01-product/REFERENCE_COVERAGE.md (P1 APPROVED / APPR-002); inventory owns original provenance |
| Product experience direction / primary video observations | ../01-product/PRODUCT_EXPERIENCE_DIRECTION.md (P1 APPROVED / APPR-002); P7 detailed design remains absent |
| P1 review evidence / candidate identity | evidence/P1_QUALITY_GATE.md / evidence/P1_ARTIFACT_MANIFEST.json |
| Domain terminology / concepts, relations, truth classification and identity | ../02-domain/DOMAIN_GLOSSARY.md / DOMAIN_MODEL.md (P2 APPROVED / APPR-003) |
| Domain-rule and invariant wording, status, authority and dependency | ../02-domain/BUSINESS_RULES.md (P2 APPROVED / APPR-003); sole canonical rule wording owner |
| Domain responsibility / lifecycle semantics and dispositions | ../02-domain/DOMAIN_RESPONSIBILITIES.md / DOMAIN_LIFECYCLES.md (P2 APPROVED / APPR-003) |
| Domain traceability / per-GAP P2 assessment / grouped decision requests | ../02-domain/DOMAIN_TRACEABILITY.md / DOMAIN_DECISION_REQUESTS.md (P2 APPROVED / APPR-003); GAP_REGISTER retains canonical GAP status |
| P2 preparation evidence / candidate identity | evidence/P2_QUALITY_GATE.md / evidence/P2_ARTIFACT_MANIFEST.json |
| Workflow IDs/index and business contracts | ../03-workflows/WORKFLOW_CATALOG.md / WORKFLOW_CONTRACTS.md (P3 APPROVED; APPR-004) |
| Canonical workflow-state and transition wording | ../03-workflows/STATE_TRANSITIONS.md; other artifacts reference IDs, not copies |
| Route intent/continuity and interaction outcomes | ../03-workflows/ROUTE_CONTRACTS.md / INTERACTION_CONTRACTS.md |
| Exception/revision patterns and workflow traceability/decisions | ../03-workflows/EXCEPTION_REVISION_PATTERNS.md / WORKFLOW_TRACEABILITY.md / WORKFLOW_DECISION_REQUESTS.md |
| P3 preparation evidence/candidate identity | evidence/P3_QUALITY_GATE.md / evidence/P3_ARTIFACT_MANIFEST.json; excludes itself |
| Later phase specifications | Reserved P4–P11 folders; no specifications/Tasks exist |

Indexes, README and handoff link to owning records. Correct a source record first, then its summaries. Do not create multiple authoritative copies of the same rule.

## Document lifecycle

Canonical project-specific owning documents follow the standard metadata contract: status, update date and custodian (an Owner or responsible Role field may identify the custodian). Inspection/review dates may label the relevant evidence update. The following purpose-specific exceptions avoid duplicating canonical authority:

- Thin adapters such as [CLAUDE.md](../../CLAUDE.md) may use minimal metadata and route to [AGENTS.md](../../AGENTS.md) and the canonical records it identifies. A full ownership/status header is not required when the adapter owns no independent project truth; it must not duplicate decisions, claim approval or establish a separate lifecycle.
- Operational dashboards/status documents such as [PHASE_STATUS.md](../PHASE_STATUS.md) may use their purpose-specific format: update date, canonical progress owner, phase statuses and authorization/approval references. They need not duplicate a document-status/custodian header when those fields are already expressed by that format.

These are metadata-format exceptions only. Canonical ownership and the authority hierarchy remain binding; current authorization, lifecycle state and approval evidence must remain traceable through the linked owning records. Adapters and dashboards remain subject to change control, review-manifest hashes and handoff discipline. Neither format can imply approval, supersede Owner decisions or bypass a phase gate.

Draft states are DRAFT → VERIFYING → APPROVED; SUPERSEDED/ARCHIVED preserve history. Phase statuses use TODO → IN_PROGRESS → VERIFYING → DONE, with BLOCKED when a real unresolved prerequisite prevents that phase's authorized work. Task statuses follow AICWDF when P11 exists. Document APPROVED and phase DONE require explicit Owner approval; VERIFYING never means approved.

The unchanged master source has its upstream baseline status; this does not approve PRANATA phase artifacts. OD records carry OWNER_APPROVED_DECISION classification from the supplied Owner direction, independently of their documenting container lifecycle. Approval records bind exact revision content using a file manifest/hashes or commit. APPR-001 permanently binds its reviewed pre-approval manifest; the unchanged P0 post-approval manifest is a **historical snapshot of the published P0 closure**, not a digest claim for P1-amended files. APPR-002 permanently binds the reviewed P1 pre-approval manifest identity. The unchanged P1 post-approval manifest is a historical checkpoint snapshot excluding itself; its digest is reported separately and does not replace either historical approval identity. Editing approved material creates a VERIFYING amendment with impact and authorization linkage; prior approved history at the baseline remains preserved. AUTH-004 granted preparation only; APPR-002 supplies explicit approval of the exact reviewed P1 amendments, preserving substantive P0 rules. AUTH-005 supplies the bounded administrative publication action only.

## Historical P2 checkpoint discipline

Historical AUTH-006 added the reviewed domain artifacts and administrative owning-record amendments. APPR-003 approves that exact pre-approval identity: **14,976 bytes / `d1f40247e83f8e5ddf419ab4dcc0eeca3e9bf64c3fafe7fff32739636f4450f3`**. AUTH-006 is completed preparation; AUTH-007 permits only the bounded approval/checkpoint and automatically completes after verified publication. The current P2 post-approval manifest hashes the complete Git-eligible checkpoint except itself; its digest is separately verified/reported and never replaces APPR-003. P0/P1 manifests remain untouched historical snapshots, not current-byte claims for amended governance. Approval preserves qualified rule statuses and all OPEN gaps; no accepted ADR, GAP resolution, P3 authorization or implementation readiness follows. AICWDF §13 exact transitions remain deferred to separately authorized P3.

Record meaningful chat decisions, safe evidence, open gaps and exact next action before stopping. Source records carry file/page/sheet/function locators and inspection limits. Local raw files are supporting evidence, not tracked specifications, and their availability is not guaranteed for another agent. A future executor must have all required approved semantics and acceptance criteria in the repository rather than depend on raw workbooks or hidden chats.

## Current P3 session discipline

P0/P1/P2 **DONE — APPROVED AND PUBLISHED** under APPR-001/APPR-002/APPR-003. P3 **DONE — APPROVED** under APPR-004. AUTH-002–AUTH-008 **HISTORICAL / COMPLETED**. AUTH-009 authorizes only this P3 checkpoint until successful push/live verification; afterward it is automatically **HISTORICAL / COMPLETED**, current active phase authorization **NONE**. P4–P11 **TODO / NOT AUTHORIZED**. Planning Freeze **NOT REACHED**; Tasks **NONE**; Task Baseline **NOT READY**; Execution **NOT AUTHORIZED**; application implementation **NONE**. AUTH-008 proposals cannot supersede approved P1/P2 or resolve absent official authority. The P3 manifest hashes the full Git-eligible candidate except itself; its own digest is separately reported. P0/P1/P2 manifests remain historical snapshots and are not refreshed. Workflow contracts own purpose/boundaries/evidence; STATE_TRANSITIONS alone owns state/transition wording; indexes/traceability/handoff point to those owners. P2 BUSINESS_RULES remains canonical for domain rules. Next: **Complete only the APPR-004/AUTH-009 checkpoint and verify publication; then bootstrap PRANATA in Claude Code as EXECUTION AGENT — WAITING in a subsequent session. P4 needs separate Owner authorization; execution requires approved P4–P11, Planning Freeze and an explicitly READY P11 Task. Stop after P3.**

## P3 approved checkpoint discipline

APPR-004 permanently binds the corrected reviewed PRE-APPROVAL manifest **19,555 bytes / `056bb00c9622c02788e3028d7dfa1de564ee8e75c651aaa0b966805cefdc060e`**, independently verified before edits. Initial pre-correction review identity **18,379 bytes / `2f5498228831bcce6a9cac9943ff38d64fa956d8c79a44966c4c5a424b8acf2b`** stays historical. Owner reports delta READY_FOR_OWNER_APPROVAL, REV-01/02/03 RESOLVED, final findings 0/0/0/0. AUTH-008 preparation completed; AUTH-009 permits bounded administrative approval/checkpoint only and automatically completes after successful push/live verification. Current post-approval P3 manifest hashes all eligible checkpoint files except itself; own size/digest reported separately, never replacing approval identity. All qualified workflow/domain authority, approved P1/P2 bytes and OPEN GAPs remain binding; no P4/freeze/Tasks/implementation readiness follows.
