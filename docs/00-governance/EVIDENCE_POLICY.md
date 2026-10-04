# Evidence and provenance policy

Status: APPROVED | Updated: 2026-10-04 (Asia/Jakarta) | Custodian: Planning Agent

## Classification

Every material finding uses one of these classifications; mixed claims must be separated.

| Classification | Meaning |
|---|---|
| OWNER_APPROVED_DECISION | Explicit PRANATA Owner direction with recorded provenance; candidate qualifications are preserved |
| AUTHORITATIVE_SOURCE | Verified issuing authority, applicable scope/version and approval/current validity; authority is limited to that scope |
| OBSERVED_CURRENT_PROCESS | Operational artifact demonstrates a recorded practice/structure; currentness still needs validation |
| LEGACY_IMPLEMENTATION_BEHAVIOR | Behavior or formula observed in legacy code/output; not accounting or legal policy |
| INFERENCE | Agent interpretation of cited evidence, explicitly provisional |
| UNRESOLVED_GAP | Missing/conflicting evidence or unvalidated authority; dependent work cannot invent the answer |

An official-looking filename or UNY heading is insufficient to establish authority. SOP templates, historical transaction artifacts and screenshots do not by themselves prove a current official approval sequence. The master framework is authoritative for operating rules because the Owner adopted it, not for UNY business/accounting policy. MULTIPLECORP is a structural reference only.

## Safe inventory and findings

[SOURCE_INVENTORY](SOURCE_INVENTORY.md) records a stable safe label, category, safe original filename, what it may inform, authority/classification, inspection method and coverage, caveats/conflicts and validation needed. Evidence reviews cite specific pages/tables, sheets/header ranges, code functions/line ranges or aggregate checks. Distinguish metadata/header sampling, extracted text, visual inspection, cached values and recalculated behavior; never claim all records or formulas were verified when only sampled.

Conflict findings link to GAP IDs with competing locators, apparent authority, impact and required validation. A closed gap needs an actual decision/source/approval record, updated owning specification and evidence, not an agent's guess. Preserve input revision/hash when safe and useful; local sources can change independently of repository history.

## Raw-source and privacy boundary

`reference-inputs/` stays local and Git-ignored. Inspect read-only. Do not copy/move raw workbooks, CSV, PDFs, DOCX, screenshots, videos, HTML or extracted record dumps into tracked paths. Do not upload internal files to external services without explicit Owner authorization. Only sanitized descriptions of structure, roles, headings, code semantics, conflicts and inspection limitations are allowed in documentation.

Never reproduce plaintext passwords, tokens, usernames/account identifiers, personal names/contact details, provider tax/bank identifiers, financial transaction values or internal sensitive records. Use safe labels where filenames are unsafe. A credential source may be described as historical plaintext credential data; its values are never printed, extracted into tracked evidence or reused as V1 credentials. See [PRODUCTION_DATA_SAFETY](PRODUCTION_DATA_SAFETY.md).

Prefer read-only bundled local parsers. Temporary inspection artifacts must remain outside tracked paths (or inside ignored local inputs), be minimized and cleaned when no longer necessary. Do not remove original sources. Future test fixtures must be synthetic/anonymized through an authorized policy, not raw-source copies.

## Evidence and approval

Quality evidence states inspection date, author role, checks/results, exact artifact references, known limitations and safe next action. PASS means the defined review checks passed, not business policy validation, Owner approval, operational correctness or release readiness. The source inventory container is approved with P0 under [APPR-001](APPROVAL_RECORDS.md#appr-001); its provisional historical-source classifications and inspection limitations remain unchanged.
