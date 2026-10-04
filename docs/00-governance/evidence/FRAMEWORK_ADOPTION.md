# Framework adoption evidence

Status: APPROVED (P1 coverage amendment; approved P0 mapping retained) | Updated: 2026-10-05 | Custodian: Planning Agent

This record owns the AICWDF coverage map, explicit project exceptions, and structural-reference evidence. It is not an independent product specification or an Owner approval. The operating hierarchy is [SOURCE_OF_TRUTH](../SOURCE_OF_TRUTH.md); current authorization/status is [PHASE_STATUS](../../PHASE_STATUS.md).

## Historical P0 framework source and project-state snapshot

The P0 table/map/adaptation statements below describe their approved historical coverage, including the former absence of P1 authorization. Current state is P0 DONE / P1 DONE under APPR-002; AUTH-005 approval/checkpoint only through verified publication, then exhausted. See the P1 amendment at the end and PHASE_STATUS. Historical deferral wording confers no current authority.

| Item | Evidence |
| --- | --- |
| Framework | AICWDF-4.3, version 4.3 English, reusable master baseline |
| Preserved local source | [AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md](../sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md) |
| Local source size | 76,025 bytes; 3,587 lines |
| SHA-256 | `BB578ADABCD8EBDDCB97C278E6589B935137F26F851B7218DD86C141744B70FE` |
| Inspection | Full framework read, in bounded ranges, on 2026-10-04 |
| Project state | `PARTIALLY_PLANNED`; local framework and ignored historical references exist, no application exists |
| Authorization | P0 approved under [APPR-001](../APPROVAL_RECORDS.md#appr-001) and published; [AUTH-002](../APPROVAL_RECORDS.md#auth-002) COMPLETED; active phase authorization NONE; [AUTH-003](../APPROVAL_RECORDS.md#auth-003) documentation closure only; no application packages, P1+ work or Tasks |
| Delivery boundary | P0 `DONE`; P1–P11 `TODO`; planning freeze not reached; Tasks none; next phase requires separate authorization |

The framework is the master operating baseline; PRANATA's explicit Owner direction has precedence. Reuse the existing framework source without rewriting it. Its repeated numbering in §34/§42 is retained as source text; cite named sections rather than inferring extra requirements from repeated item numbers. Example entities, services, providers, and deployment paths in the framework are not PRANATA decisions.

## Coverage map

`APPLIED P0` means the governance rule is captured for review. `DEFERRED` means the requirement is preserved for its named later phase; it is not completed, omitted, or authorized now. `PROJECT OVERRIDE` identifies explicit Owner direction. [CONTEXT_INDEX](../../CONTEXT_INDEX.md) lists actual owners and reserved paths without fake specifications.

| Framework section | Concern | PRANATA canonical owner / later obligation | P0 disposition |
| --- | --- | --- | --- |
| §0–§1.2 | Purpose, real-state adoption, preserve valid decisions | This record; [PROJECT_CHARTER](../PROJECT_CHARTER.md); [DECISION_LOG](../DECISION_LOG.md) | APPLIED P0; no existing approved phase reset |
| §2–§3 | Repository truth, hierarchy, conflicts | [SOURCE_OF_TRUTH](../SOURCE_OF_TRUTH.md) | APPLIED P0 |
| §4 | Stack default and material deviations | [TOOLCHAIN](../TOOLCHAIN.md); [CHANGE_CONTROL](../CHANGE_CONTROL.md); `docs/04-architecture/` later | APPLIED P0 baseline; detailed P4 design DEFERRED |
| §4A.1–§4A.2, §4A.6–§4A.9 | Native authentication, one-time contract, separation/boundary | OD-11–OD-12 in decision log; engineering guardrails; `docs/05-security/` | APPLIED P0 local baseline; detailed auth/security contract DEFERRED P5 |
| §4A.3–§4A.4 | Google convenience login/provider contract | OD-11 local V1; OD-12 future institutional integrations | PROJECT OVERRIDE: Google/OAuth not a V1 dependency |
| §4A.5 | AICWDF-COMPAT-8 password default and external precedence | Preserved framework default; binding-external-requirement check and one-time auth contract owed to P5 | DEFERRED P5; no implementation or false NIST-compliance claim |
| §4A.10 | Login screen pattern | `docs/07-ux-design/` | DEFERRED P7; remove provider affordance inconsistent with local V1; no screen designed |
| §4A.11 | Auth cost/current-doc policy | [COST_POLICY](../COST_POLICY.md); [TOOLCHAIN](../TOOLCHAIN.md) | APPLIED P0 |
| §4B | Indonesian-first bilingual UI | OD-17; [ENGINEERING_PRINCIPLES](../ENGINEERING_PRINCIPLES.md); P7 localization, P8 verification | APPLIED P0 obligation: `id-ID`, `en`, clear switch; mechanisms/tests DEFERRED |
| §4C | Near-zero additional production cost | [COST_POLICY](../COST_POLICY.md) | APPLIED P0; no paid exceptions |
| §5–§7, §6A | Capabilities, bootstrap, registry, MCP on demand | [TOOLCHAIN](../TOOLCHAIN.md) | APPLIED P0 policy/probes; application setup DEFERRED; named tools not falsely verified |
| §8–§9 | Targeted context and agent roles | [AGENT_OPERATING_MODEL](../AGENT_OPERATING_MODEL.md); [AGENTS.md](../../../AGENTS.md) | APPLIED P0; Codex planning / future Claude execution |
| §10–§11 | Delivery model and P0 continuity | Charter; phase status; governance; handoff | APPLIED P0; deployment examples not selected |
| §12 | P1 product, scope, acceptance | Reserved `docs/01-product/` | DEFERRED P1; Owner direction is preserved without new product specification |
| §13 | P2 domain, lifecycles, business rules | Reserved `docs/02-domain/` | DEFERRED P2 |
| §14 | P3 workflows, routes, interactions | Reserved `docs/03-workflows/` | DEFERRED P3; official UNY procedure not invented |
| §15, §15.1 | P4 database, application architecture, replaceable integrations | Reserved `docs/04-architecture/`; OD-13 guardrail | DEFERRED P4 beyond stack/modularity baseline |
| §16 | P5 security, authentication, authorization | Reserved `docs/05-security/` | DEFERRED P5 beyond local/safety guardrails; roles remain candidates |
| §17 | P6 API, concurrency, idempotency, performance | Reserved `docs/06-api-performance/`; OD-16 scale direction | DEFERRED P6; no schema/index/queue/API design |
| §18 | P7 UX, IA, design, navigation, localization | Reserved `docs/07-ux-design/`; engineering principles | DEFERRED P7; no tokens, layouts, screens, or prototype |
| §19 | P8 test strategy and completion | Reserved `docs/08-testing/`; agent model | DEFERRED P8; documentation verification distinguished from runtime tests |
| §20 | P9 infrastructure, observability, backup/recovery | Reserved `docs/09-operations/`; safety/cost policy | DEFERRED P9; no provider/topology/recovery target selected |
| §21 | P10 release, migration, cutover, rollback, UAT | Reserved `docs/10-release/`; safety/cost policy | DEFERRED P10; no release runbook authored |
| §22–§24 | P11 count, stable IDs, dependencies, READY gate, contracts/execution | Reserved `docs/11-tasks/`; [AGENT_OPERATING_MODEL](../AGENT_OPERATING_MODEL.md) | DEFERRED P11; no Task files/plan/count or execution |
| §25–§27 | Route/interaction, browser E2E, UX integrity | Engineering principles; toolchain; P3/P7/P8/P11 contracts and evidence | APPLIED P0 future guardrail; runtime proof DEFERRED |
| §28 | Layered Task, critical-journey, release regression | Reserved `docs/08-testing/` and future Task contracts | DEFERRED P8/P11 |
| §29–§31 | Production DB zero-touch and durable-data maintenance | [PRODUCTION_DATA_SAFETY](../PRODUCTION_DATA_SAFETY.md) | APPLIED P0; operating mechanisms DEFERRED P9/P10 |
| §32 | Branch/environment model | [GIT_WORKFLOW](../GIT_WORKFLOW.md); P9/P10 environment/release verification | APPLIED P0 governance reservation; no remote or environment configuration |
| §33–§34 | Durable structure, concise entry point, thin adapters | [AGENTS.md](../../../AGENTS.md); [CLAUDE.md](../../../CLAUDE.md); context index | APPLIED P0; future concerns clearly reserved |
| §34A–§35 | Minimal execution context and Task dashboard | `docs/11-tasks/` later; agent model | DEFERRED P11; neither execution context nor Task plan fabricated in P0 |
| §36–§38 | Handoff, planner/executor continuity and readiness reports | [CURRENT_HANDOFF](../../handoff/CURRENT_HANDOFF.md); charter; agent model | APPLIED P0 handoff/planner boundary; executor report DEFERRED until Tasks exist |
| §39–§40 | Release integrity and maintenance flow | P8/P9/P10/P11 reserved owners; agent safety rules | DEFERRED; no release/maintenance Task created |
| §41–§43 | Adaptation, final operating contract/principles | This record; canonical governance documents | APPLIED P0 with explicit Owner overrides and future gates preserved |

P11 must eventually publish the definitive baseline/current Task totals, dependency graph, bounded end-to-end contracts, READY gates, risk classes, applicable verification, and automatic checklist/counter/handoff updates. That future obligation does not authorize creating a preliminary Task plan or inventing a count now.

## Project-specific adaptations and unresolved validations

- OD-11 selects local Laravel-native V1 authentication. It supersedes Google convenience sign-in, provider buttons, and provider setup assumptions. OD-12 independently excludes UNY domain/identity/API dependencies from V1. No MultipleCorp auth/security choice is imported.
- OD-17 retains all framework bilingual obligations: plain Indonesian default, English secondary locale, clear switch, localized copy, translation/layout/navigation verification. The primary-language direction does not make English optional.
- OD-18 keeps additional recurring costs near zero; framework 0 remains the selection target. No hosting, service, paid AI, or production exception is selected.
- Owner phase gating narrows broad framework planning/bootstrap examples: only P0 is authorized. Any later Task/runtime setup must wait for its own gate; source inspection tools may be reused safely now.
- OD-14–OD-16 preserve legacy provenance, unresolved accounting treatment, and scale direction. Detailed business policies, architecture, performance mechanisms, and import/cutover choices remain unvalidated later work.
- The framework password profile is a preserved later planning default, not a claim that P0 designed or implemented password security. P5 must check any binding external requirement and record the contract once; never call AICWDF-COMPAT-8 NIST-compliant.

## Structural reference inspection

The GitHub connector performed read-only inspection of [yusufarst/MULTIPLECORP](https://github.com/yusufarst/MULTIPLECORP) on 2026-10-04. Its `main` revision was pinned to **`a526daf47444d12b4ae5c51e1ae14c9dfe3f4978`**; the recursive Git tree was not truncated. Structural observations support adapted organization only (`INFERENCE` for reusable governance patterns), not PRANATA business/legal/accounting/security authority.

| Inspected pinned path | Structural pattern used |
| --- | --- |
| [AGENTS.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/AGENTS.md) | Main entry point routes agents through concise reading order and canonical owners |
| [CLAUDE.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/CLAUDE.md) | Tool adapter points back to shared repository rules |
| [AICWDF_ADOPTION.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/docs/00-governance/AICWDF_ADOPTION.md) | Section coverage map separates adopted defaults, project exceptions, and later obligations |
| [TOOLCHAIN.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/docs/00-governance/TOOLCHAIN.md) | Availability, verification, named preference, and on-demand MCP policy are distinct |
| [ENGINEERING_PRINCIPLES.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/docs/00-governance/ENGINEERING_PRINCIPLES.md) | Canonical guardrail document links to later owning specifications |
| [CHANGE_CONTROL.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/docs/00-governance/CHANGE_CONTROL.md) | Changes, approvals, rationale, affected revisions, and ADR history remain traceable |
| [PRODUCTION_DATA_SAFETY.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/docs/00-governance/PRODUCTION_DATA_SAFETY.md) | Dedicated owning safety document distinguishes guardrails from later procedures |
| [PHASE_STATUS.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/docs/PHASE_STATUS.md) | Phase status is separate from approval and execution permission |
| [CONTEXT_INDEX.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/docs/CONTEXT_INDEX.md) | Navigation map identifies owning concern and phase; reserved paths remain explicit |
| [evidence/P0_QUALITY_GATE.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/docs/00-governance/evidence/P0_QUALITY_GATE.md) | Documentation checks, application N/A, and approval interpretation have distinct evidence |
| [handoff/CURRENT_HANDOFF.md](https://github.com/yusufarst/MULTIPLECORP/blob/a526daf47444d12b4ae5c51e1ae14c9dfe3f4978/docs/handoff/CURRENT_HANDOFF.md) | Concise current state, actual verification, boundaries, and next safe action |

No product requirements, business rules, domain model, finance semantics, permission/workflow model, security decisions, delegated authority, approval IDs, operational providers, or runtime-success claims are copied from that reference. PRANATA direction comes from its own Owner decisions and sanitized source evidence. P0–P11 folders are adapted to this project; later specification/Task discipline is reserved rather than populated.

The historical adoption evidence belongs to P0 quality/approval history; its map alone did not approve P0 or authorize P1. Subsequent authority is explicitly recorded in APPROVAL_RECORDS.

## P1 framework coverage amendment

[AUTH-004](../APPROVAL_RECORDS.md#auth-004) supplies separate P1 planning authority; [P1_QUALITY_GATE](P1_QUALITY_GATE.md) owns the P1 self-review and [CONTEXT_INDEX](../../CONTEXT_INDEX.md) routes to actual product owners. Master source bytes and all P0 operating exceptions remain unchanged.

| Framework concern | P1 candidate coverage / limit |
|---|---|
| §12 problem, goals/non-goals, users/roles, primary journeys | PRODUCT_OVERVIEW source-qualified problems/outcomes/stakeholders/candidate roles/integrated journey map; no official workflow or permission finalization |
| §12 scope, functional requirements, release boundary | V1_SCOPE CAP-01–20, minimum MUST, SHOULD/OPTIONAL, FUTURE/OUT and later validation/release obligations; reviewed product proposal approved APPR-002 with existing validation obligations |
| §12 nonfunctional requirements, acceptance, success | ACCEPTANCE_CRITERIA AC-01–27/NFR-P01–04; measured product target proposals and explicit missing workload/baseline obligations |
| §4A/4B/4C and §12 auth/localization/cost | Local Laravel-native account boundary; id-ID/en/switch; near-zero extra recurring cost and institutional-integration independence, with P5/P7/P9 details deferred |
| §5.5/§18 experience reference | Owner-selected SRC-047/VIS-04 directly inspected; PRODUCT_EXPERIENCE_DIRECTION separates Owner preference/source observations/P7 decisions; no design system/tokens/screens fixed |
| §2/3/8/36 evidence/durable authority/handoff | U-002/AUTH-004, preserved OD-01–20 plus qualified OD-21–23, reference coverage, unchanged gaps, current state/index/handoff and separate P1 manifest |
| §13–22 later-phase gates | P2–P11 untouched reservations; formal rules/workflows/schema/security/performance mechanics/final design/test/infra/release/Tasks not authored; execution/freeze prohibited |

P1 is DONE under explicit [APPR-002](../APPROVAL_RECORDS.md#appr-002); P0 remains DONE under APPR-001. Self-review did not approve P1. [AUTH-005](../APPROVAL_RECORDS.md#auth-005) permits only approval/checkpoint through verified publication, then expires; no later-phase authority. Exact next safe action after checkpoint: Owner separately authorizes P2 — Domain Model & Business Rules.
