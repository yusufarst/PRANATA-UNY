# Context index

Status: APPROVED (APPR-003; AUTH-007 approval/checkpoint lifecycle) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

Zero-context entry: [AGENTS.md](../AGENTS.md) → [CURRENT_HANDOFF](handoff/CURRENT_HANDOFF.md) → [PHASE_STATUS](PHASE_STATUS.md) → relevant owning records below. Project state: **PARTIALLY_PLANNED**. P0/P1 **DONE — approved and published**, APPR-001/APPR-002; P2 **DONE — APPROVED**, [APPR-003](00-governance/APPROVAL_RECORDS.md#appr-003). Verified starting main HEAD `8881c047f24451b30754b19fe0d1cf96b9078f90`; checkpoint publication is verified from Git. AUTH-002–AUTH-006 are **HISTORICAL / COMPLETED**. [AUTH-007](00-governance/APPROVAL_RECORDS.md#auth-007) is checkpoint-only until verified publication, then automatically historical/completed with active phase authorization **NONE**. P3–P11 TODO / NOT AUTHORIZED; Planning Freeze NOT REACHED; Tasks NONE; Task Baseline NOT READY; Execution NOT AUTHORIZED; application implementation NONE. Exact next safe action after verified checkpoint: **Owner separately authorizes P3 — Workflows, Routes & Interactions**. Stop after P2.

| Need | Read |
|---|---|
| Project identity / P0 boundary | [PROJECT_CHARTER](00-governance/PROJECT_CHARTER.md) |
| Authority / document lifecycle / conflict / canonical ownership | [SOURCE_OF_TRUTH](00-governance/SOURCE_OF_TRUTH.md) |
| Who may plan, execute, approve, review, release or maintain | [AGENT_OPERATING_MODEL](00-governance/AGENT_OPERATING_MODEL.md) |
| Explicit Owner decisions and qualifications | [DECISION_INDEX](00-governance/DECISION_INDEX.md) → [DECISION_LOG](00-governance/DECISION_LOG.md) |
| Authorization and actual approval status | [APPROVAL_RECORDS](00-governance/APPROVAL_RECORDS.md) |
| Master framework and PRANATA adaptation | [AICWDF v4.3](00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md) / [FRAMEWORK_ADOPTION](00-governance/evidence/FRAMEWORK_ADOPTION.md) |
| Safe source inventory / classification / handling | [SOURCE_INVENTORY](00-governance/SOURCE_INVENTORY.md) / [EVIDENCE_POLICY](00-governance/EVIDENCE_POLICY.md) |
| Asset, inventory, finance, HTML observations | [ASSET_SOURCE_REVIEW](00-governance/evidence/ASSET_SOURCE_REVIEW.md) |
| Procurement and SOP observations | [PROCUREMENT_SOURCE_REVIEW](00-governance/evidence/PROCUREMENT_SOURCE_REVIEW.md) |
| Historical screenshots/login clip and new primary UI/motion video | [VISUAL_SOURCE_REVIEW](00-governance/evidence/VISUAL_SOURCE_REVIEW.md) / [PRODUCT_EXPERIENCE_DIRECTION](01-product/PRODUCT_EXPERIENCE_DIRECTION.md) |
| Missing authority or conflicting evidence | [GAP_REGISTER](00-governance/GAP_REGISTER.md) |
| Engineering / localization / interaction constraints | [ENGINEERING_PRINCIPLES](00-governance/ENGINEERING_PRINCIPLES.md) |
| Changes and architecture decisions | [CHANGE_CONTROL](00-governance/CHANGE_CONTROL.md) / [ADR policy](adr/README.md) |
| Current capabilities / future tools / MCP on demand | [TOOLCHAIN](00-governance/TOOLCHAIN.md) |
| Recurring costs / paid exceptions | [COST_POLICY](00-governance/COST_POLICY.md) |
| Sensitive sources / production safety | [PRODUCTION_DATA_SAFETY](00-governance/PRODUCTION_DATA_SAFETY.md) |
| Git / branch and environment policy | [GIT_WORKFLOW](00-governance/GIT_WORKFLOW.md) |
| Historical published P0 review / post-approval evidence and snapshot manifest | [P0_QUALITY_GATE](00-governance/evidence/P0_QUALITY_GATE.md) / [artifact manifest](00-governance/evidence/P0_ARTIFACT_MANIFEST.json) |
| P1 identity, problems, outcomes, stakeholders and primary product journeys | [PRODUCT_OVERVIEW](01-product/PRODUCT_OVERVIEW.md) |
| Proposed V1 capability/priorities, FUTURE/out of scope and release boundary | [V1_SCOPE](01-product/V1_SCOPE.md) |
| Stable product acceptance and proposed nonfunctional targets | [ACCEPTANCE_CRITERIA](01-product/ACCEPTANCE_CRITERIA.md) |
| Source-to-capability coverage, authority limits and later gap obligations | [REFERENCE_COVERAGE](01-product/REFERENCE_COVERAGE.md) |
| P1 experience direction and bounded direct video observations | [PRODUCT_EXPERIENCE_DIRECTION](01-product/PRODUCT_EXPERIENCE_DIRECTION.md) |
| P1 preparation/review/approval integrity and current post-approval manifest | [P1_QUALITY_GATE](00-governance/evidence/P1_QUALITY_GATE.md) / [P1_ARTIFACT_MANIFEST](00-governance/evidence/P1_ARTIFACT_MANIFEST.json) |
| P2 canonical terminology / conceptual concepts, relations, identity and truth classification | [DOMAIN_GLOSSARY](02-domain/DOMAIN_GLOSSARY.md) / [DOMAIN_MODEL](02-domain/DOMAIN_MODEL.md) |
| Canonical domain-rule and invariant wording / status / authority | [BUSINESS_RULES](02-domain/BUSINESS_RULES.md) |
| Domain responsibilities without permissions / lifecycle dispositions without transitions | [DOMAIN_RESPONSIBILITIES](02-domain/DOMAIN_RESPONSIBILITIES.md) / [DOMAIN_LIFECYCLES](02-domain/DOMAIN_LIFECYCLES.md) |
| OD/CAP/AC/source/GAP traceability and grouped missing validation | [DOMAIN_TRACEABILITY](02-domain/DOMAIN_TRACEABILITY.md) / [DOMAIN_DECISION_REQUESTS](02-domain/DOMAIN_DECISION_REQUESTS.md) |
| P2 review/approval integrity and current post-approval manifest | [P2_QUALITY_GATE](00-governance/evidence/P2_QUALITY_GATE.md) / [P2_ARTIFACT_MANIFEST](00-governance/evidence/P2_ARTIFACT_MANIFEST.json) |
| Current operational state / exact next action | [CURRENT_HANDOFF](handoff/CURRENT_HANDOFF.md) |

## Reserved future document locations

P1 contains the five APPROVED product documents, prepared under historical AUTH-004 and approved under APPR-002 with all qualifications retained. P2 contains seven APPROVED conceptual domain artifacts under APPR-003; EVIDENCE_SUPPORTED/PROPOSED/BLOCKED_BY_GAP qualifications and all 20 OPEN GAPs remain binding. AUTH-006 is completed preparation; AUTH-007 checkpoint authority expires automatically at verified publication. P3–P11 still contain only `.gitkeep` markers: **reserved**, not planned specifications or execution authorization. Future names describe coverage, not existing files. Approved P1 documents retain historical phase-state snapshots; current progress is owned by PHASE_STATUS.

| Phase | Reserved path | Future coverage when separately authorized |
|---|---|---|
| P1 | `docs/01-product/` | APPROVED product definition/scope/acceptance/reference/experience; APPR-002, P1 DONE |
| P2 | `docs/02-domain/` | APPROVED conceptual glossary/model/rules/responsibilities/lifecycles/traceability/decision requests; APPR-003, P2 DONE; qualifications retained |
| P3 | `docs/03-workflows/` | Critical workflows, route and interaction contracts |
| P4 | `docs/04-architecture/` | Application/database architecture, modules and justified integration boundaries |
| P5 | `docs/05-security/` | Auth/account/session/recovery, compact roles/assignments, backend policies, threats, audit and secrets |
| P6 | `docs/06-api-performance/` | API/concurrency/idempotency/performance contracts and scale strategy |
| P7 | `docs/07-ux-design/` | IA/pages/design/navigation/ID-EN localization and visual references |
| P8 | `docs/08-testing/` | Verification strategy and definition of done |
| P9 | `docs/09-operations/` | Infrastructure, observability, backup/recovery and cost inventory |
| P10 | `docs/10-release/` | UAT, migration/cutover, release/rollback and controlled execution |
| P11 | `docs/11-tasks/` | EXECUTION_CONTEXT, TASK_PLAN, definitive bounded Tasks/dependencies/counts; NONE created now |

Local `reference-inputs/` is ignored evidence, not a portable executor context. If absent for a later agent, approved documentation must contain required semantics; never invent them from inventory labels. Source limitations are recorded in the inventory and gaps.
