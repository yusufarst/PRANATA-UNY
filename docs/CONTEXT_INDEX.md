# Context index

Status: APPROVED (APPR-004; AUTH-009 checkpoint only) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

Zero-context entry: [AGENTS.md](../AGENTS.md) → [CURRENT_HANDOFF](handoff/CURRENT_HANDOFF.md) → [PHASE_STATUS](PHASE_STATUS.md) → relevant owning records below. Project state: **PARTIALLY_PLANNED**. P0/P1/P2 **DONE — APPROVED AND PUBLISHED** under APPR-001/APPR-002/APPR-003. P3 **DONE — APPROVED** under APPR-004. AUTH-002–AUTH-008 **HISTORICAL / COMPLETED**. AUTH-009 authorizes only this P3 checkpoint until successful push/live verification; afterward it is automatically **HISTORICAL / COMPLETED**, current active phase authorization **NONE**. P4–P11 **TODO / NOT AUTHORIZED**. Planning Freeze **NOT REACHED**; Tasks **NONE**; Task Baseline **NOT READY**; Execution **NOT AUTHORIZED**; application implementation **NONE**. [APPR-004](00-governance/APPROVAL_RECORDS.md#appr-004) approves the corrected reviewed candidate; [AUTH-009](00-governance/APPROVAL_RECORDS.md#auth-009) is checkpoint only; starting main HEAD `9e7950db6f9d6c6ab900a6328778807e057fb812` matched the live remote before edits. Only the intended P3 checkpoint may be staged/committed/pushed after integrity PASS. Exact next safe action: **Complete only the APPR-004/AUTH-009 checkpoint and verify publication; then bootstrap PRANATA in Claude Code as EXECUTION AGENT — WAITING in a subsequent session. P4 needs separate Owner authorization; execution requires approved P4–P11, Planning Freeze and an explicitly READY P11 Task. Stop after P3.**

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
| P3 workflow index / business contracts | [WORKFLOW_CATALOG](03-workflows/WORKFLOW_CATALOG.md) / [WORKFLOW_CONTRACTS](03-workflows/WORKFLOW_CONTRACTS.md) |
| P3 canonical states and transition wording | [STATE_TRANSITIONS](03-workflows/STATE_TRANSITIONS.md) |
| P3 route intent / contextual returns / interactions | [ROUTE_CONTRACTS](03-workflows/ROUTE_CONTRACTS.md) / [INTERACTION_CONTRACTS](03-workflows/INTERACTION_CONTRACTS.md) |
| P3 exception/revision patterns / traceability / decisions | [EXCEPTION_REVISION_PATTERNS](03-workflows/EXCEPTION_REVISION_PATTERNS.md) / [WORKFLOW_TRACEABILITY](03-workflows/WORKFLOW_TRACEABILITY.md) / [WORKFLOW_DECISION_REQUESTS](03-workflows/WORKFLOW_DECISION_REQUESTS.md) |
| P3 preparation evidence / candidate identity | [P3_QUALITY_GATE](00-governance/evidence/P3_QUALITY_GATE.md) / [P3_ARTIFACT_MANIFEST](00-governance/evidence/P3_ARTIFACT_MANIFEST.json) |
| Current operational state / exact next action | [CURRENT_HANDOFF](handoff/CURRENT_HANDOFF.md) |

## Reserved future document locations

P1 contains five APPROVED product documents and P2 seven APPROVED domain artifacts with all authority/GAP qualifications. P3 contains APPROVED workflow/route/interaction contracts under APPR-004; approval does not grant execution readiness. P4–P11 contain only reserved `.gitkeep` markers. Approved P1/P2 phase snapshots are historical; PHASE_STATUS owns current progress.

| Phase | Reserved path | Future coverage when separately authorized |
|---|---|---|
| P1 | `docs/01-product/` | APPROVED product definition/scope/acceptance/reference/experience; APPR-002, P1 DONE |
| P2 | `docs/02-domain/` | APPROVED conceptual glossary/model/rules/responsibilities/lifecycles/traceability/decision requests; APPR-003, P2 DONE; qualifications retained |
| P3 | `docs/03-workflows/` | APPROVED workflow/transition/route/interaction contracts, exceptions, traceability and decision requests; APPR-004 |
| P4 | `docs/04-architecture/` | Application/database architecture, modules and justified integration boundaries |
| P5 | `docs/05-security/` | Auth/account/session/recovery, compact roles/assignments, backend policies, threats, audit and secrets |
| P6 | `docs/06-api-performance/` | API/concurrency/idempotency/performance contracts and scale strategy |
| P7 | `docs/07-ux-design/` | IA/pages/design/navigation/ID-EN localization and visual references |
| P8 | `docs/08-testing/` | Verification strategy and definition of done |
| P9 | `docs/09-operations/` | Infrastructure, observability, backup/recovery and cost inventory |
| P10 | `docs/10-release/` | UAT, migration/cutover, release/rollback and controlled execution |
| P11 | `docs/11-tasks/` | EXECUTION_CONTEXT, TASK_PLAN, definitive bounded Tasks/dependencies/counts; NONE created now |

Local `reference-inputs/` is ignored evidence, not a portable executor context. If absent for a later agent, approved documentation must contain required semantics; never invent them from inventory labels. Source limitations are recorded in the inventory and gaps.
