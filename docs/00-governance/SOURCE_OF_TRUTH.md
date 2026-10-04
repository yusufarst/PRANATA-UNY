# Repository Source of Truth

Status: APPROVED | Updated: 2026-10-04 (Asia/Jakarta) | Custodian: Planning Agent

## Authority hierarchy

1. Latest explicit Owner-approved decision recorded in this repository, with approval provenance and supersession history.
2. [AGENTS.md](../../AGENTS.md) and the adopted [AICWDF v4.3](sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md) operating rules, as explicitly adapted for PRANATA.
3. Active approved Task contract, once P11 exists; there is none in P0.
4. Accepted ADRs with required Owner approval.
5. Current approved owning product/domain/workflow/architecture/security/design and governance documents.
6. Current implementation and tests; none exist yet.
7. [CURRENT_HANDOFF](../handoff/CURRENT_HANDOFF.md), which summarizes and routes but cannot supersede authority.
8. Chat/session context, transient agent memory and generated output.

An unapproved draft cannot supersede an approved decision. New explicit Owner instructions are recorded before becoming durable truth; they apply immediately within their stated scope. Owner adoption of the framework is recorded; [APPR-001](APPROVAL_RECORDS.md#appr-001) approves the exact reviewed P0 candidate and [AUTH-002](APPROVAL_RECORDS.md#auth-002) permits the administrative approval/checkpoint delta only. Formal external policy and law are evidence of binding constraints when verified; a conflict with Owner direction requires domain/Owner validation, not silent circumvention.

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
| Future phase specifications | Reserved numbered phase folders; no specifications exist |

Indexes, README and handoff link to owning records. Correct a source record first, then its summaries. Do not create multiple authoritative copies of the same rule.

## Document lifecycle

Canonical project-specific owning documents follow the standard metadata contract: status, update date and custodian (an Owner or responsible Role field may identify the custodian). Inspection/review dates may label the relevant evidence update. The following purpose-specific exceptions avoid duplicating canonical authority:

- Thin adapters such as [CLAUDE.md](../../CLAUDE.md) may use minimal metadata and route to [AGENTS.md](../../AGENTS.md) and the canonical records it identifies. A full ownership/status header is not required when the adapter owns no independent project truth; it must not duplicate decisions, claim approval or establish a separate lifecycle.
- Operational dashboards/status documents such as [PHASE_STATUS.md](../PHASE_STATUS.md) may use their purpose-specific format: update date, canonical progress owner, phase statuses and authorization/approval references. They need not duplicate a document-status/custodian header when those fields are already expressed by that format.

These are metadata-format exceptions only. Canonical ownership and the authority hierarchy remain binding; current authorization, lifecycle state and approval evidence must remain traceable through the linked owning records. Adapters and dashboards remain subject to change control, review-manifest hashes and handoff discipline. Neither format can imply approval, supersede Owner decisions or bypass a phase gate.

Draft states are DRAFT → VERIFYING → APPROVED; SUPERSEDED/ARCHIVED preserve history. Phase statuses use TODO → IN_PROGRESS → VERIFYING → DONE, with BLOCKED when a real unresolved prerequisite prevents that phase's authorized work. Task statuses follow AICWDF when P11 exists. Document APPROVED and phase DONE require explicit Owner approval; VERIFYING never means approved.

The unchanged master source has its upstream baseline status; this does not approve PRANATA phase artifacts. OD records carry OWNER_APPROVED_DECISION classification from the supplied Owner direction, independently of their documenting container lifecycle. Approval records bind exact revision content using a file manifest/hashes or commit. The initial review had no commit; APPR-001 permanently binds the reviewed pre-approval manifest identity, while the current post-approval manifest and AUTH-002 checkpoint identify subsequent administrative repository content. Editing approved material creates an amendment with impact and approval linkage, not a silent rewrite.

## Durable session discipline

Record meaningful chat decisions, safe evidence, open gaps and exact next action before stopping. Source records carry file/page/sheet/function locators and inspection limits. Local raw files are supporting evidence, not tracked specifications, and their availability is not guaranteed for another agent. A future executor must have all required approved semantics and acceptance criteria in the repository rather than depend on raw workbooks or hidden chats.
