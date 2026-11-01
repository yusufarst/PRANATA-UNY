# PRANATA UNY agent entry point

Status: Approved P0–P11 baseline (APPR-005, APPR-006) frozen as FRZ-001 · Execution: AUTH-013 | Updated: 2026-11-01 (Asia/Jakarta) | Custodian: Planning Agent

The repository is the durable Source of Truth for every Planning, Execution, Review, Release and Maintenance Agent. Preserve approved decisions and existing working-tree changes. Chat is contextual; record material decisions before relying on them.

## Read in this order

1. This entry point.
2. [CURRENT_HANDOFF](docs/handoff/CURRENT_HANDOFF.md) and [PHASE_STATUS](docs/PHASE_STATUS.md): current authorization and safe next action.
3. [SOURCE_OF_TRUTH](docs/00-governance/SOURCE_OF_TRUTH.md): authority, document lifecycle and canonical ownership.
4. The active Owner authorization in [APPROVAL_RECORDS](docs/00-governance/APPROVAL_RECORDS.md), then only relevant decisions and documents through [CONTEXT_INDEX](docs/CONTEXT_INDEX.md).

Check the actual branch, HEAD and working tree before acting. Current state: see [PHASE_STATUS](docs/PHASE_STATUS.md) and [CURRENT_HANDOFF](docs/handoff/CURRENT_HANDOFF.md). P0–P11 DONE — APPROVED (APPR-005, APPR-006 at `851013f`), published on `main` with the Planning Freeze FRZ-001; standing autonomous execution of TASK-001–TASK-078 authorized by AUTH-013; post-freeze change PCR-001 pending focused review. P2 BUSINESS_RULES remains the sole canonical BR/INV wording owner.

## Operating boundaries

Current gates: Planning Freeze REACHED (FRZ-001); Task Baseline 78 Tasks; READY and BLOCKED Tasks per [TASK_PLAN](docs/11-tasks/TASK_PLAN.md); application execution AUTHORIZED by AUTH-013 within its exclusions (production, real transactions, official policy activation with open GAPs, spending and GitHub administration stay Owner-only).

- Work is phase and Task gated. Agent roles follow GOV-10: Claude Planning is the sole planning author, ChatGPT performs independent Second Review only, the Owner decides, and Claude Code executes only READY P11 Tasks after approved P0–P11, Planning Freeze and a standing execution authorization. See [AGENT_OPERATING_MODEL](docs/00-governance/AGENT_OPERATING_MODEL.md).
- Future executors read this file, [EXECUTION_CONTEXT](docs/11-tasks/EXECUTION_CONTEXT.md), the handoff, [TASK_PLAN](docs/11-tasks/TASK_PLAN.md), the active Task and its exact references, following [EXECUTOR_OPERATING_MODEL](docs/11-tasks/EXECUTOR_OPERATING_MODEL.md). These P11 artifacts are approved (APPR-006) and execution follows AUTH-013; Tasks start only when READY. Use minimal targeted context.
- Preserve [Owner decisions](docs/00-governance/DECISION_INDEX.md). No silent changes to scope, stack, architecture, permissions or business/accounting procedure. Follow [CHANGE_CONTROL](docs/00-governance/CHANGE_CONTROL.md); unresolved conflicts go into [GAP_REGISTER](docs/00-governance/GAP_REGISTER.md).
- Ordinary coding agents have no direct production database access. Protect historical data and secrets under [PRODUCTION_DATA_SAFETY](docs/00-governance/PRODUCTION_DATA_SAFETY.md) and [EVIDENCE_POLICY](docs/00-governance/EVIDENCE_POLICY.md).
- No dead buttons, routes, forms, links or interactions; child flows need clear contextual return paths. Mobile-first, responsive, adaptive, comfortable spacing, and shadcn/ui reuse follow [ENGINEERING_PRINCIPLES](docs/00-governance/ENGINEERING_PRINCIPLES.md).
- User-facing language defaults to plain Indonesian (`id-ID`); English (`en`) and a language switch remain required. Follow the canonical localization rules in [ENGINEERING_PRINCIPLES](docs/00-governance/ENGINEERING_PRINCIPLES.md).
- V1 authentication is local Laravel-native authentication. Workspace selection never grants permission. Reuse recorded direction; the access model (OD-28) is specified in [ACCESS_CONTROL](docs/05-security/ACCESS_CONTROL.md) (APPROVED).
- Additional recurring production cost target is near zero beyond client-funded infrastructure/domain. Paid services require explicit Owner approval: [COST_POLICY](docs/00-governance/COST_POLICY.md).
- Verify current documentation for version-sensitive work, perform targeted impact analysis, require browser E2E for critical UI journeys, and activate MCP capabilities on demand: [TOOLCHAIN](docs/00-governance/TOOLCHAIN.md).
- Future Task DONE requires all required checks and completion evidence; update its checkbox, counters and handoff together. Never claim VERIFYING is DONE.
- End every meaningful session with an updated handoff, evidence, gaps, changed files and exact safe next action. Follow [GIT_WORKFLOW](docs/00-governance/GIT_WORKFLOW.md) (standing identity/attribution and byte rules: GOV-08 / GOV-09).

The unchanged [AICWDF v4.3 source](docs/00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md) is the master operating framework; [FRAMEWORK_ADOPTION](docs/00-governance/evidence/FRAMEWORK_ADOPTION.md) maps its PRANATA adaptation and deferred coverage.
