# P0–P11 planning candidate quality gate

Status: Evidence for the AUTH-012 candidate — VERIFYING, not approved | Updated: 2026-10-30 (Asia/Jakarta) | Custodian: Planning Agent

This record states which integrity checks the Planning Agent ran on the branch candidate `docs/p0-p11-planning` before requesting independent Second Review, the internal pre-review that was performed, and their results. It is self-verification evidence, not the independent Second Review and not approval. The reviewed identity is the branch head SHA reported with the review request; a document cannot carry the hash of the commit that contains it.

## Scope

| Part | Content | Authority |
|---|---|---|
| AUTH-010 remediation | P0–P3 remediation candidate imported byte for byte (`9d15387`) | AUTH-010 (historical/completed); approval reserved APPR-005 |
| AUTH-012 compatibility amendment | OD-26–OD-34, GOV-10–GOV-12, GAP-026–GAP-029, CAP-22/23, AC-29–32, NFR-O01/O02, DC-080–087, BR-031–035, INV-020–022, WF-030–033 (TR-291–327, RT-033–036, IX-022–024) | AUTH-012 preparation |
| P4–P11 candidates | 4 architecture documents and 10 ADRs (PROPOSED); 2 security; 3 API/performance; 5 UX; 2 testing; 2 operations; 3 release; P11: TASK_PLAN, TRACEABILITY_MATRIX, EXECUTION_CONTEXT, EXECUTOR_OPERATING_MODEL, EXECUTOR_BOOTSTRAP and 78 Task contracts | AUTH-012 preparation |
| Handoff and status | PHASE_STATUS, CONTEXT_INDEX, CURRENT_HANDOFF, OWNER_DECISION_PACKAGE, SECOND_REVIEW_HANDOFF, AGENTS.md, GIT_WORKFLOW and TOOLCHAIN appendices | AUTH-012 preparation |

Base: approved `main` at `0e3f7de3acbed5213c13e22a61fc2728e57ccc77`. No application code, package manifest, migration, CI workflow or setting is part of the candidate.

## Checks

| ID | Check | Method | Result |
|---|---|---|---|
| QG-P-01 | Every relative Markdown link and heading anchor resolves | Link/anchor checker over all tracked and new `.md` files (GitHub-style slugs) | PASS — 0 broken links or anchors |
| QG-P-02 | Every ID referenced in P4–P11 and handoff documents is defined by its owning document | Checker for BR, INV, DC, WF, TR, RT, IX, CAP, AC, NFR, OD, GOV, GAP, PG, AUTHZ, SOD, SEC, PB, PERF, J, RB, UAT-P, ADR | PASS — 0 undefined references |
| QG-P-03 | Requirement and contract coverage by Tasks | Generated from Task contracts during rendering and re-checked over the rendered files | PASS — CAP-01–23, AC-01–32, NFR-P01–P04/O01–O02, WF-001–033 (183 TR), RT-001–036, IX-001–024, 74 PG, J-01–J-18, 23 AUTHZ, 9 SOD, 21 SEC, PB-01–11, 5 PERF, RB-01–15, UAT-P01–P12 each mapped to at least one Task |
| QG-P-04 | Task graph integrity | Renderer validation | PASS — 78 unique sequential IDs; every dependency names an existing lower-numbered Task (acyclic); every Task is upstream of TASK-078; BLOCKS derived from DEPENDS ON |
| QG-P-05 | Task contract completeness (AICWDF §23) | Heading check per Task file | PASS — all 33 required sections in 78/78 Tasks; STATUS TODO; non-empty scope, acceptance criteria and tests |
| QG-P-06 | Tables named by Tasks exist in DATA_MODEL | Checker | PASS |
| QG-P-07 | Capability and policy keys named by Tasks exist in ACCESS_CONTROL / POLICY_REGISTRY | Checker | PASS (wildcards resolve to defined capabilities) |
| QG-P-08 | Status honesty | Search for APPROVED/DONE/READY claims on P4–P11 or Tasks; TASK_PLAN counters | PASS — P4–P11 VERIFYING; 0 READY, 0 DONE; no GAP marked resolved; the GAP activation-gate reading is labelled as pending Owner decision D-28 |
| QG-P-09 | Design-system contrast values | Recomputed WCAG 2.x contrast for 19 colour pairs | PASS after correcting two values rounded upward (focus ring on canvas 4,7:1; simulation badge 8,1:1) |
| QG-P-10 | Sensitive data | Pattern scan of changed/new files (e-mail, long digit strings, phone numbers, credential assignments, private keys) | PASS — only the Owner's public GitHub noreply address recorded by Owner direction |
| QG-P-11 | Byte preservation of appended governance files | Prefix comparison before/after append | PASS — GIT_WORKFLOW and TOOLCHAIN keep their previous bytes; appended text uses LF only |
| QG-P-12 | Git identity and attribution | Before each commit: repository-local `user.name`/`user.email` equal the confirmed Owner identity; after each commit: raw object (`git cat-file -p`) and full message scanned for co-author, generated/assisted-by and AI/tool names | PASS for `e870458`, `6765e9b` and `72bf8a7`; the result for the commit that adds this revision is reported with the review request |
| QG-P-13 | Schema build order | For every table a Task creates, each referenced table (explicit `→` and conventional `*_id` columns in DATA_MODEL) is created by that Task or one of its ancestors | PASS after corrections; two evidence-file columns are deliberately added later by the owner of `file_objects` (DATA_MODEL §19 rule 5) |
| QG-P-14 | Verification harness reachability | Every Task after TASK-005 has TASK-005 (CI) upstream and every UI Task after TASK-007 has TASK-007 (E2E harness) upstream; TASK-001–TASK-004 use the bootstrap merge mode | PASS after making TASK-008 depend on TASK-007 |

## Internal pre-review

One read-only review by a fresh reviewer that had not authored the package was run on `72bf8a7` before the independent Second Review, focused on execution realism, cross-phase consistency, authority honesty and decision completeness. It reported 8 P1, 12 P2 and 4 P3 findings. All were verified against the files; dispositions:

| # | Sev. | Finding | Disposition |
|---|---|---|---|
| 1 | P1 | M0 Tasks required `ci-gate` and browser E2E before CI and the harness existed | FIXED — bootstrap merge mode for TASK-001–TASK-004 (EXECUTOR_OPERATING_MODEL §8.4); ruleset applied after TASK-005; E2E N/A with reason before TASK-007; TASK-008 depends on TASK-007 |
| 2 | P1 | Merge option A conflicted with "PR required" protection text | FIXED — protection made conditional on D-08 in QUALITY_GATES, RELEASE_PLAN, INFRASTRUCTURE and TASK-005 |
| 3 | P1 | Executor could alter its own guardrails with the Owner's admin login | FIXED — guardrail paths merge only in SUPERVISED mode; deny list for GitHub admin operations; new Owner decision D-27 (restricted token) and capability C-15 |
| 4 | P1 | TASK-001 tolerated bypass-permissions mode | FIXED — bypass mode forbidden in every case |
| 5 | P1 | Data model and document engine imposed an order on check/issue/sign that approved P3 leaves open | FIXED — `proc_sppbj_acts` with independent contributions; document issuance and signature as independent acts; order only from policy |
| 6 | P1 | Missing dependencies for referenced tables | FIXED — TASK-031/TASK-032 renumbered (pre-baseline) so vendor core precedes activities; added dependencies TASK-021→032/034, TASK-026→027, TASK-037→042; evidence-file columns deferred to TASK-016; redundant `proc_packages.method_determination_id` removed |
| 7 | P1 | Duplicate official depreciation run not stopped before damage | FIXED — partial unique index over QUEUED/RUNNING/COMPLETED, projections only at completion, unique official line per asset and period |
| 8 | P1 | Single-admin password reset could let an admin act as a decision holder | FIXED — decision-assignment holders treated as privileged for reset and TOTP; SEC-21, AUTHZ-24; D-12 recommendation extended |
| 9 | P2 | Hung executor session never treated as idle | FIXED — wall-clock limit per headless step and stall rule for live sessions |
| 10 | P2 | Acceptance criteria depended on later Tasks | FIXED — scenarios moved to the Tasks that create their subjects (TASK-027, TASK-028, TASK-043, TASK-073) |
| 11 | P2 | `ci-e2e` path filter missed configuration and dependency files | FIXED |
| 12 | P2 | Maker-checker constraints passed on NULL | FIXED — NULL-safe constraints for policies, templates and privileged grants |
| 13 | P2 | Stock card balance wrong for late-recorded movements | FIXED — late postings accepted only if dated balances stay ≥ 0; card ordered by physical time with computed running balance |
| 14 | P2 | Multi-step warehouse approval chain had no data model | FIXED — `inv_request_decisions` and a WF-030 clarification within the AUTH-012 amendment |
| 15 | P2 | TASK-001 deny list narrower than the operating model | FIXED |
| 16 | P2 | Bootstrap gate could never clear TASK-001 | FIXED — supervised and autonomous prerequisites separated |
| 17 | P2 | Executor could quarantine failing tests alone | FIXED — quarantine excludes active Task criteria and invariant/authorization/security tests without Owner approval; independent confirmation required |
| 18 | P2 | Executor-authored plan changes contradicted GOV-10 | FIXED — executor proposes; Planning Agent authors TASK PLAN CHANGE |
| 19 | P2 | GAP activation-gate reading and go-live gating presented as settled | ACCEPTED AS OWNER DECISION — new D-28; GAP_REGISTER, ARCHITECTURE, POLICY_REGISTRY, RELEASE_PLAN and P11 texts marked as pending Owner confirmation |
| 20 | P2 | Policy activation authority was global | FIXED — `POLICY_FAMILY` grant scope; AUTHZ-25 |
| 21 | P3 | Stock movements lacked a simulation flag | FIXED |
| 22 | P3 | A contract-form test could settle GAP-027(b) | FIXED — the unresolved band requires a recorded human choice |
| 23 | P3 | Shutdown precedence undefined for a blocked Task | FIXED — hold always wins |
| 24 | P3 | Minor inconsistencies (historical GAP range, CI trigger wording, guard regex, penetration test) | FIXED — including new optional decision D-29 |

## Limits

- No application exists; runtime, browser, accessibility, performance and security tests are N/A and are owned by the Tasks.
- Claude Code executor capabilities are UNVERIFIED until TASK-001 ([EXECUTOR_OPERATING_MODEL §13](../../11-tasks/EXECUTOR_OPERATING_MODEL.md#13-kemampuan-platform-yang-wajib-diverifikasi)).
- Regulatory values in [REGULATORY_SOURCE_REVIEW](REGULATORY_SOURCE_REVIEW.md) are observations from supplied texts (partly OCR-derived) and need custodian confirmation (GAP-026); they appear only as DRAFT policy examples.
- Framework and package versions are verified at TASK-002/TASK-003.
- The internal pre-review is not the independent Second Review, which has not yet taken place.
