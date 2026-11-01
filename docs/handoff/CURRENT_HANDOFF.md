# Current handoff

Status: Approved P0–P11 baseline (APPR-005, APPR-006) frozen as FRZ-001 and published · PCR-001 published · PCR-002 published · Execution: AUTH-013 | Updated: 2026-11-01 (Asia/Jakarta) | Role: EXECUTION AGENT (GOV-10) | Phase: execution, TASK-002 DONE | Authorization: AUTH-013 (execution), FRZ-001 change control (planning)

## Current state

Project **PRANATA UNY**. The remote repository `https://github.com/yusufarst/PRANATA-UNY`, specifically published `main`, is the durable shared Source of Truth; confirm it with `git ls-remote origin refs/heads/main` before acting.

- P0–P11 **DONE — APPROVED** (APPR-005, APPR-006); Planning Freeze **FRZ-001** effective; PCR-001 published; execution authorized by **AUTH-013**.
- **TASK-001 DONE**: executor guardrails, runner, watcher and capability report. **TASK-002 DONE** (2/78): Laravel 13 React skeleton, `app/Modules`, `bootstrap.yml` and the `-Bootstrap` evidence gate, on `main` at `561ceec`. No business feature yet.
- Executor mode: **SUPERVISED**. AUTONOMOUS is not active: it needs TASK-002–TASK-005 DONE, the `main` ruleset and the runner token (C-15).
- **PCR-002 published** (2026-11-01): Owner decision OD-37 forbids local Docker; heavy checks run on GitHub-hosted runners, first through the temporary bootstrap workflow ([QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)). C-16 (bootstrap evidence gate) is VERIFIED ([EXECUTOR_CAPABILITY_REPORT](../11-tasks/evidence/EXECUTOR_CAPABILITY_REPORT.md#c-16)).
- GAP-001–GAP-029: OPEN (resolved NONE).

## Handoff (AICWDF §36)

| Field | Current value |
|---|---|
| Current phase | Execution; approved phases 12/12. |
| Current Task / status | None active. **TASK-003 READY** (SUPERVISED bootstrap). |
| Last completed action | 2026-11-02: TASK-002 merged by verified fast-forward through the C-16 evidence gate. |
| Evidence | [TASK-002 COMPLETION EVIDENCE](../11-tasks/TASK-002.md#completion-evidence); [EXECUTOR_CAPABILITY_REPORT](../11-tasks/evidence/EXECUTOR_CAPABILITY_REPORT.md). |
| Task plan | Baseline 78 · Current 78 · DONE 2 · READY 1 · IN_PROGRESS 0 · BLOCKED 0 · VERIFYING 0 · TODO 75 · SUPERSEDED 0 · 3%. |
| Open blockers | (1) Owner, after TASK-005: apply the `main` ruleset. (2) Owner: install the restricted runner token with `scripts/executor/install-runner-token.ps1`, then `scripts/executor/tests/c15.probe.ps1` proves C-15. Neither blocks TASK-002–TASK-005. |
| Open non-blocking gaps | GAP-001–GAP-029 OPEN; handled as policy activation, release/cutover or V1-acceptance gates (D-28), never as invented rules. |
| Decisions made | OD-01–OD-36, GOV-01–GOV-12, D-01–D-29, and OD-37 (no local Docker; GitHub-hosted testing). |
| Decisions pending | None for TASK-003–TASK-005. For Claude Planning, non-blocking: the current starter kit ships Inertia 3 and Vite+ (Oxlint, Oxfmt) where the plan names Inertia 2, ESLint and Prettier, and the ESLint rules of TEST_STRATEGY need a decision before the i18n and `quality` work rely on them ([TOOLCHAIN](../00-governance/TOOLCHAIN.md#application-versions--task-002-2026-11-01)). Before TASK-040: where staging runs (D-19 as limited by OD-37). |
| Toolchain | See [TOOLCHAIN — Executor machine facts](../00-governance/TOOLCHAIN.md). Stage with `scripts/executor/stage.ps1`; a bulk add under `core.autocrlf=false` rewrites line endings on this machine. |
| Machine | RAM 7.2 GB, 0.1–1.1 GB free during TASK-001 without any PRANATA container; PHP, Composer and psql not installed. Other projects were not touched. Editing a file that holds carriage returns (`docs/00-governance/TOOLCHAIN.md`, `.gitignore`) must keep them (GOV-09); check before staging. |
| Cost | New recurring cost: NONE. Paid exception: NONE. |
| Database | Direct production DB touched: NO. Schema: Laravel skeleton tables only, tested on a runner service container. |
| Cleanup for the Owner | Public synthetic sandbox `yusufarst/pranata-uny-sandbox-c10` (used for C-10 and C-16) may be deleted by the Owner; the executor token cannot delete repositories. Disposable branches stay as they are. |
| Next READY Task | TASK-003. |

## TASK PLAN CHANGE proposal — Docker-free local development (for Claude Planning)

Status: **INCORPORATED into PCR-002** (published; mapping in [PCR-002_REVIEW_HANDOFF](PCR-002_REVIEW_HANDOFF.md)); kept for provenance. No contract was changed by the executor. Owner decision 2026-11-01: no local Docker or Docker Compose for PRANATA (not even read-only), no self-hosted runner on the Owner's PC. GitHub-hosted runners are the primary environment for resource-heavy verification; their service containers are allowed. Synthetic data only in public Actions. No paid service. Gates and acceptance criteria stay.

**Sequencing problem to solve.** PHP, Composer and PostgreSQL are absent locally, and §8.4 expects the full verification pyramid locally for TASK-001–TASK-004, while `ci.yml` only arrives in TASK-005. So TASK-002 and TASK-004 are affected as well as TASK-003.

**Proposed minimum revisions:**

| Document | Change |
|---|---|
| TASK-003 | Replace Compose scope with a Docker-free workflow: remove `compose.yaml`, `docker/`, `scripts/dev` up/down, the bind-mount measurement and the port checks. Keep `DatabaseGuard` and `.env.example`. Add a minimal GitHub Actions job with a PostgreSQL service that runs Pest on `pranata_test` and `migrate:fresh`, and a Gotenberg service reachable only from the job. Rewrite TAC-01 (no local container dependency), TAC-03 (Pest on `pranata_test`, run URL as evidence) and TAC-04 (Gotenberg reachable from the job, on GitHub infrastructure). Keep TAC-02 and TAC-05. |
| TASK-002 | TAC-01 "berjalan lokal": prove boot and the login page on a GitHub-hosted runner with PostgreSQL. Decide how the skeleton is generated without a local PHP: project-scoped portable PHP and Composer under `.pranata/tools/` (no PATH or global change), or a workflow that generates it. Line 47 "Lingkungan Docker (TASK-003)" follows TASK-003. |
| TASK-004 | TAC-01 `composer qa` and `npm run qa`: evidence from a GitHub-hosted run. |
| TASK-005 | Absorb or replace the minimal workflow from TASK-002/TASK-003 into `ci.yml`. |
| EXECUTOR_OPERATING_MODEL | §5 guardrail paths: allow `.github/workflows/**` for the Tasks that now need it. §8.4: bootstrap evidence may come from GitHub-hosted runs. §10: drop `docker compose -p pranata stop`. §11 Docker row: all local Docker denied; no self-hosted runner. §13: retire C-14. |
| EXECUTION_CONTEXT §5, EXECUTOR_BOOTSTRAP §3, INFRASTRUCTURE §2, TEST_STRATEGY §4 (rows "Pest lokal", "E2E lokal/CI", resource note), TOOLCHAIN | Local environment without containers; `pranata_*` databases exist on CI service containers only. |
| QUALITY_GATES §2.1 and §4, DOCUMENT_ENGINE, ADR-0005 | Keep the architecture. Document Tasks test Gotenberg on GitHub-hosted runners. Production deployment stays a separate Owner decision. |
| TASK-071 local parity check | The private 84-page BMU PDF never goes to public Actions; confirm how the parity check runs without a local stack. |

**Must stay true:** PostgreSQL behaviour (`pg_trgm`, `ltree`, `btree_gist`, numeric, `timestamptz`, roles), database safety, application functionality and every mandatory gate. Tests that did not run are never marked as passed.

**Already applied in the guardrails (TASK-001):** the guard denies every local Docker call; the runner allowlist holds none.

## Exact next safe action

**Executor:** continue TASK-003, then TASK-004 and TASK-005, in SUPERVISED bootstrap mode under AUTH-013, with no local Docker, PHP, Composer or PostgreSQL (OD-37). Merge through `merge-verified.ps1 -Bootstrap`.

## DO NOT DO

- No start of a BLOCKED Task; no Task outside AUTH-013; no production deployment, real institutional transaction, official policy activation with an open GAP, purchase or paid service.
- No planning amendment by the executor; changes go through a TASK PLAN CHANGE and Claude Planning.
- No GAP closure or resolution; no invented procurement, accounting, organization or security rule.
- No Git identity/configuration change, no force push, no history rewrite, no AI attribution.
- No access to MULTIPLECORP, BantuKasir or ELLIGBLE resources.
