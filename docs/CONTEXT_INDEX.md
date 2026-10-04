# Context index

Status: APPROVED | Updated: 2026-10-04 (Asia/Jakarta) | Custodian: Planning Agent

Zero-context entry: [AGENTS.md](../AGENTS.md) → [CURRENT_HANDOFF](handoff/CURRENT_HANDOFF.md) → [PHASE_STATUS](PHASE_STATUS.md) → relevant owning records below. Project state: **PARTIALLY_PLANNED**. P0 is **DONE — approved and published** under [APPR-001](00-governance/APPROVAL_RECORDS.md#appr-001), checkpoint `f6d889100306a63a5bd391da4310346fc427d4d8`, remote verification PASS. [AUTH-002](00-governance/APPROVAL_RECORDS.md#auth-002) is **HISTORICAL / COMPLETED**; current active phase authorization: **NONE**. [AUTH-003](00-governance/APPROVAL_RECORDS.md#auth-003) records documentation closure only. P1–P11 remain TODO / NOT AUTHORIZED; Planning Freeze NOT REACHED; Tasks NONE; Task Baseline NOT READY; Execution NOT AUTHORIZED; application implementation NONE. Exact next safe action: Owner separately authorizes P1 — Product Definition, Scope & Acceptance.

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
| Local screenshots and uninspected video | [VISUAL_SOURCE_REVIEW](00-governance/evidence/VISUAL_SOURCE_REVIEW.md) |
| Missing authority or conflicting evidence | [GAP_REGISTER](00-governance/GAP_REGISTER.md) |
| Engineering / localization / interaction constraints | [ENGINEERING_PRINCIPLES](00-governance/ENGINEERING_PRINCIPLES.md) |
| Changes and architecture decisions | [CHANGE_CONTROL](00-governance/CHANGE_CONTROL.md) / [ADR policy](adr/README.md) |
| Current capabilities / future tools / MCP on demand | [TOOLCHAIN](00-governance/TOOLCHAIN.md) |
| Recurring costs / paid exceptions | [COST_POLICY](00-governance/COST_POLICY.md) |
| Sensitive sources / production safety | [PRODUCTION_DATA_SAFETY](00-governance/PRODUCTION_DATA_SAFETY.md) |
| Git / branch and environment policy | [GIT_WORKFLOW](00-governance/GIT_WORKFLOW.md) |
| P0 review / post-approval integrity evidence and current manifest | [P0_QUALITY_GATE](00-governance/evidence/P0_QUALITY_GATE.md) / [artifact manifest](00-governance/evidence/P0_ARTIFACT_MANIFEST.json) |
| Current operational state / exact next action | [CURRENT_HANDOFF](handoff/CURRENT_HANDOFF.md) |

## Reserved future document locations

These folders contain only `.gitkeep` markers to preserve structure. They are **reserved**, not planned specifications or execution authorization. Proposed artifact names describe framework coverage; files do not exist yet.

| Phase | Reserved path | Future coverage when separately authorized |
|---|---|---|
| P1 | `docs/01-product/` | Product scope, goals/non-goals, users, functional/nonfunctional requirements, acceptance and release boundary |
| P2 | `docs/02-domain/` | Glossary, entities, invariants, responsibility, lifecycle/retention and business rules |
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
