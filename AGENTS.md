# PRANATA UNY agent entry point

Status: APPROVED (APPR-004; AUTH-009 checkpoint only) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

The repository is the durable Source of Truth for every Planning, Execution, Review, Release and Maintenance Agent. Preserve approved decisions and existing working-tree changes. Chat is contextual; record material decisions before relying on them.

## Read in this order

1. This entry point.
2. [CURRENT_HANDOFF](docs/handoff/CURRENT_HANDOFF.md) and [PHASE_STATUS](docs/PHASE_STATUS.md): current authorization and safe next action.
3. [SOURCE_OF_TRUTH](docs/00-governance/SOURCE_OF_TRUTH.md): authority, document lifecycle and canonical ownership.
4. The active Owner authorization in [APPROVAL_RECORDS](docs/00-governance/APPROVAL_RECORDS.md), then only relevant decisions and documents through [CONTEXT_INDEX](docs/CONTEXT_INDEX.md).

Check the actual branch, HEAD and working tree before acting. P0/P1/P2 **DONE — APPROVED AND PUBLISHED** under APPR-001/APPR-002/APPR-003. P3 **DONE — APPROVED** under APPR-004. AUTH-002–AUTH-008 **HISTORICAL / COMPLETED**. AUTH-009 authorizes only this P3 checkpoint until successful push/live verification; afterward it is automatically **HISTORICAL / COMPLETED**, current active phase authorization **NONE**. P4–P11 **TODO / NOT AUTHORIZED**. Planning Freeze **NOT REACHED**; Tasks **NONE**; Task Baseline **NOT READY**; Execution **NOT AUTHORIZED**; application implementation **NONE**. [APPR-004](docs/00-governance/APPROVAL_RECORDS.md#appr-004) binds the corrected reviewed manifest (19,555 bytes / `056bb00c9622c02788e3028d7dfa1de564ee8e75c651aaa0b966805cefdc060e`). [AUTH-009](docs/00-governance/APPROVAL_RECORDS.md#auth-009) permits only bounded approval/checkpoint publication. Verified published starting main HEAD: `9e7950db6f9d6c6ab900a6328778807e057fb812`, parent `8881c047f24451b30754b19fe0d1cf96b9078f90`; local HEAD, origin/main and live remote matched before P3 preparation; that initial worktree/index was clean. The exact reviewed P3 candidate/index was independently verified before approval edits. Preserve all approved P1/P2 bytes and qualifications; P2 BUSINESS_RULES remains canonical for 29 BR / 18 INV. Read [WORKFLOW_CATALOG](docs/03-workflows/WORKFLOW_CATALOG.md) through the context index. All 20 GAPs remain OPEN. Stage/commit/push only the intended P3 checkpoint after all bounded checks PASS, under AUTH-009; no continuing Git authority afterward. Exact next safe action: **Complete only the APPR-004/AUTH-009 checkpoint and verify publication; then bootstrap PRANATA in Claude Code as EXECUTION AGENT — WAITING in a subsequent session. P4 needs separate Owner authorization; execution requires approved P4–P11, Planning Freeze and an explicitly READY P11 Task. Stop after P3.**

## Operating boundaries

Current planning gates: Planning Freeze **NOT REACHED**; Tasks **NONE**; Task Baseline **NOT READY**; Execution **NOT AUTHORIZED**; application implementation **NONE**.

- Work is phase and Task gated. Codex/ChatGPT owns planning; future Claude Code executes only READY Tasks after approved P0–P11, planning freeze and an approved Task baseline. See [AGENT_OPERATING_MODEL](docs/00-governance/AGENT_OPERATING_MODEL.md).
- Future executors read this file, the P11 execution context, handoff, Task plan, active Task and its exact references. Those P11 artifacts do not exist yet; their absence forbids execution. Use minimal targeted context.
- Preserve [Owner decisions](docs/00-governance/DECISION_INDEX.md). No silent changes to scope, stack, architecture, permissions or business/accounting procedure. Follow [CHANGE_CONTROL](docs/00-governance/CHANGE_CONTROL.md); unresolved conflicts go into [GAP_REGISTER](docs/00-governance/GAP_REGISTER.md).
- Ordinary coding agents have no direct production database access. Protect historical data and secrets under [PRODUCTION_DATA_SAFETY](docs/00-governance/PRODUCTION_DATA_SAFETY.md) and [EVIDENCE_POLICY](docs/00-governance/EVIDENCE_POLICY.md).
- No dead buttons, routes, forms, links or interactions; child flows need clear contextual return paths. Mobile-first, responsive, adaptive, comfortable spacing, and shadcn/ui reuse follow [ENGINEERING_PRINCIPLES](docs/00-governance/ENGINEERING_PRINCIPLES.md).
- User-facing language defaults to plain Indonesian (`id-ID`); English (`en`) and a language switch remain required. Follow the canonical localization rules in [ENGINEERING_PRINCIPLES](docs/00-governance/ENGINEERING_PRINCIPLES.md).
- V1 authentication is local Laravel-native authentication. Workspace selection never grants permission. Reuse recorded direction; detailed authorization is deferred to P2/P5.
- Additional recurring production cost target is near zero beyond client-funded infrastructure/domain. Paid services require explicit Owner approval: [COST_POLICY](docs/00-governance/COST_POLICY.md).
- Verify current documentation for version-sensitive work, perform targeted impact analysis, require browser E2E for critical UI journeys, and activate MCP capabilities on demand: [TOOLCHAIN](docs/00-governance/TOOLCHAIN.md).
- Future Task DONE requires all required checks and completion evidence; update its checkbox, counters and handoff together. Never claim VERIFYING is DONE.
- End every meaningful session with an updated handoff, evidence, gaps, changed files and exact safe next action. Follow [GIT_WORKFLOW](docs/00-governance/GIT_WORKFLOW.md).

The unchanged [AICWDF v4.3 source](docs/00-governance/sources/AI_First_Complex_WebApp_Task_Framework_v4.3_EN.md) is the master operating framework; [FRAMEWORK_ADOPTION](docs/00-governance/evidence/FRAMEWORK_ADOPTION.md) maps its PRANATA adaptation and deferred coverage.
