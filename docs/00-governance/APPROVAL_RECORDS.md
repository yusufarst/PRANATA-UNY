# Authorization and approval records

Status: APPROVED | Updated: 2026-10-04 (Asia/Jakarta) | Custodian: Planning Agent

## AUTH-001

- Kind: **OWNER AUTHORIZATION**, not phase approval.
- Received: 2026-10-04 (Asia/Jakarta), Owner's supplied planning-session request (safe source [U-001](SOURCE_INVENTORY.md#u-001-original-owner-request)). Original: Codex local attachment `3d2c3978-d4fd-47bd-ba34-4a20a250298c`, `Pasted text.txt`, 19,912 bytes; SHA-256 `34c5fbc7bae585b544f9dc659be7fab161c73216f46eeb7797d70da437b72e0d`. SOURCE_INVENTORY owns the locator, identification evidence and availability limits; original remains external and untouched.
- Authorized scope: prepare PRANATA P0 governance/foundation, inspect local evidence safely, preserve OD-01–OD-20, use AICWDF v4.3 and MULTIPLECORP structurally, perform self-review and handoff.
- Explicit exclusions: P1–P11 work, application implementation/dependencies, Task creation, planning freeze, commit/push, remote changes and raw-source promotion.
- Required terminal state: P0 VERIFYING; P1–P11 TODO; Planning Freeze NOT REACHED; Tasks NONE; Task Baseline NOT READY.
- Direction already approved in U-001: OD-01–OD-20 and the governing/session mandates recorded in [DECISION_LOG](DECISION_LOG.md). These do not approve the newly generated P0 artifacts.

## P0 correction authorization — 2026-10-04

The Owner's prior correction instruction authorized only OBS-01 source-provenance reproducibility and OBS-02 document metadata clarity, with affected manifest hashes, P0 integrity evidence and handoff refreshed. The Owner reports the independent review verdict READY_FOR_OWNER_APPROVAL, with 0 blockers, 0 major, 0 minor and 2 observations. This is review evidence, not phase approval. Preserve Owner decisions, product scope and GAP entries; do not start P1, create Tasks/application code, scaffold Laravel, commit or push. Terminal state remains P0 VERIFYING, P1–P11 TODO, Planning Freeze NOT REACHED and Tasks NONE.

## AUTH-002

- Kind: **OWNER AUTHORIZATION — P0 APPROVAL RECORDING + INITIAL CHECKPOINT ONLY**.
- Recorded at: **2026-10-04T16:59:58+07:00** (Asia/Jakarta), from the Owner's explicit current instruction.
- Source: external Codex local attachment `b91447c6-7929-404b-b010-34c722cdf211`, `Pasted text.txt`, **12,450 bytes**, SHA-256 `f39b828ef51768fdc8cc2c8bb0c01f789130235ced51f34c32488b824c8713d1`. Safe local locator: `%USERPROFILE%\.codex\attachments\b91447c6-7929-404b-b010-34c722cdf211\Pasted text.txt`. Original remains untouched outside Git; the durable scope and approval below do not depend on future attachment availability.
- Authorized: record APPR-001 against the exact reviewed pre-approval manifest; transition P0 VERIFYING → DONE; synchronize approval/decision/status/entry/context/handoff metadata; refresh the current post-approval manifest and integrity evidence; run bounded integrity checks; stage only intended P0 artifacts; create the initial commit `docs: finalize P0 governance`; push normally with `git push -u origin main`; verify the published checkpoint; stop.
- Supersession is limited: AUTH-001 and the correction authorization remain historical. Their P0 VERIFYING terminal state and no-stage/commit/push restrictions are superseded for this single checkpoint. OD-01–OD-20, GOV/FD substantive direction, source safety and every later-phase gate remain intact.
- Conditions: exact pre-approval candidate identity must match; all material integrity checks pass before commit/push; branch `main`; origin `https://github.com/yusufarst/PRANATA-UNY.git`; existing configured Owner author/committer identity only; no AI attribution; no force push or remote reconfiguration. A material failure blocks publication as DONE until corrected.
- Excluded: P1–P11 planning, application/scaffold/packages/schema/migrations/auth/UI, execution Tasks/Task plan/count, planning freeze, execution authorization, deployment/production work, raw-source alterations/promotion and unrelated staged files.
- Terminal state: P0 DONE; P1–P11 TODO; Planning Freeze NOT REACHED; Tasks NONE; Task Baseline NOT READY; Execution NOT AUTHORIZED. Separate explicit Owner authorization is required for P1.

## Phase approval register

P0 approval: **APPR-001 APPROVED**. No P1–P11 phase approvals, planning-freeze approvals or execution-baseline approvals exist. Preserve approval identity and supersession history; requests for correction never imply a new approval.

## APPR-001

- Approval subject: **P0 Governance / Foundation**.
- Approving authority: **Project Owner**.
- Approval state: **APPROVED**.
- Approved phase: **P0**.
- Recorded at: **2026-10-04T16:59:58+07:00** (Asia/Jakarta); explicit Owner instruction identified in AUTH-002, not inferred agent approval.
- Reviewed **PRE-APPROVAL** candidate manifest: `docs/00-governance/evidence/P0_ARTIFACT_MANIFEST.json`.
- Reviewed candidate manifest size: **8,893 bytes**.
- Reviewed candidate manifest SHA-256: **`25cd7c8da69ad5bccd5b5f0969c9c3346ac4dd085c23f3544977109dd8b00ec4`**.
- Identity evidence: before approval-recording edits, local file bytes matched that exact size/digest; all 40 manifest entries matched actual hashes/sizes and the complete 41-file Git-eligible set. The historical identity above remains the candidate the Owner reviewed and approved; it is never replaced by the later current-manifest hash.
- Initial independent review: **READY_FOR_OWNER_APPROVAL**, Owner-reported; 0 blockers, 0 major, 0 minor, 2 observations. Both observations were corrected.
- Delta review: **READY_FOR_OWNER_APPROVAL**, Owner-reported; OBS-01 provenance, OBS-02 metadata, regression, Owner decision, manifest, reference-input and phase-boundary checks PASS; 0 blockers, 0 major, 0 minor, 0 observations.
- Review blocking findings: **0**. Review verdicts are recorded from the supplied Owner instruction; no separately stored external review report or independently invented evidence is claimed.
- Approval scope: **P0 Governance / Foundation only**. Approved document metadata covers the governance/evidence containers; historical sources, candidate roles/lifecycle and unvalidated accounting/procurement procedures are not promoted to final business rules.
- Accepted/deferred gaps: all **GAP-001–GAP-020 remain OPEN** and retain their dependent future-phase blocks. P0 blockers: **0**; P0 non-blocking gaps: **20**. No gap resolution, accepted ADR, paid exception or later-phase decision is created.
- P1 authorization: **NOT INCLUDED**.
- Planning freeze: **NOT REACHED**.
- Tasks: **NONE**. Task Baseline: **NOT READY**.
- Execution authorization: **NOT GRANTED** / operational state **NOT AUTHORIZED**.
- Lifecycle implementation: administrative recording and checkpoint scope are AUTH-002; [DECISION_LOG lifecycle record](DECISION_LOG.md#p0-approval-lifecycle-record) and [PHASE_STATUS](../PHASE_STATUS.md) carry the transition; [P0_QUALITY_GATE](evidence/P0_QUALITY_GATE.md#p0-post-approval-integrity) carries bounded checks.
- Current **POST-APPROVAL** manifest: [P0_ARTIFACT_MANIFEST](evidence/P0_ARTIFACT_MANIFEST.json), refreshed after administrative edits and excluding itself to avoid recursive hashing. It identifies the current repository content, not a replacement approval identity. Its own final size/digest and the checkpoint SHA are verified separately in the session's Git/final report; no self-referential digest or future commit/push claim is embedded here.

Exact next safe action after AUTH-002 checkpoint verification: Owner may separately authorize **P1 — Product Definition, Scope & Acceptance**. Stop after P0; this approval does not authorize P1.
