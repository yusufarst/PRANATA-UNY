# Current handoff

Status: Approved P0–P11 baseline (APPR-005, APPR-006) frozen as FRZ-001 and published · PCR-001 published · PCR-002 pending review (not on `main`) · Execution: AUTH-013 | Updated: 2026-11-01 (Asia/Jakarta) | Role: EXECUTION AGENT (GOV-10) | Phase: execution, TASK-001 DONE | Authorization: AUTH-013 (execution), FRZ-001 change control (planning)

## Current state

Project **PRANATA UNY**. The remote repository `https://github.com/yusufarst/PRANATA-UNY`, specifically published `main`, is the durable shared Source of Truth; confirm it with `git ls-remote origin refs/heads/main` before acting.

- P0–P11 **DONE — APPROVED** (APPR-005, APPR-006); Planning Freeze **FRZ-001** effective; PCR-001 published; execution authorized by **AUTH-013**.
- **TASK-001 DONE** (1/78): executor guardrails, runner, watcher and capability report on `main` at `170c1f1`. Application implementation: NONE yet.
- Executor mode: **SUPERVISED**. AUTONOMOUS is not active: it needs TASK-002–TASK-005 DONE, the `main` ruleset and the runner token (C-15).
- **Execution is paused for planning:** Owner decision OD-37 (2026-11-01) forbids local Docker. Claude Planning incorporated the proposal below into **PCR-002** on branch `docs/pcr-002-docker-free-development`; it awaits one focused Second Review and is not on `main`. Only `origin/main` binds the executor.
- GAP-001–GAP-029: OPEN (resolved NONE).

## Handoff (AICWDF §36)

| Field | Current value |
|---|---|
| Current phase | Execution; approved phases 12/12. |
| Current Task / status | None active. TASK-002 and TASK-003 BLOCKED by the PCR-002 publication gate. |
| Last completed action | 2026-11-01: TASK-001 merged by verified fast-forward (PR #2, PR #3) and reconciled. |
| Evidence | [TASK-001 COMPLETION EVIDENCE](../11-tasks/TASK-001.md#completion-evidence); [EXECUTOR_CAPABILITY_REPORT](../11-tasks/evidence/EXECUTOR_CAPABILITY_REPORT.md). |
| Task plan | Baseline 78 · Current 78 · DONE 1 · READY 0 · IN_PROGRESS 0 · BLOCKED 2 · VERIFYING 0 · TODO 75 · SUPERSEDED 0 · 1%. |
| Open blockers | (1) PCR-002: focused Second Review, then verified fast-forward of `main` and the state-transition commit. (2) Owner, after TASK-005: apply the `main` ruleset. (3) Owner: install the restricted runner token with `scripts/executor/install-runner-token.ps1`, then `scripts/executor/tests/c15.probe.ps1` proves C-15. |
| Open non-blocking gaps | GAP-001–GAP-029 OPEN; handled as policy activation, release/cutover or V1-acceptance gates (D-28), never as invented rules. |
| Decisions made | OD-01–OD-36, GOV-01–GOV-12, D-01–D-29, and OD-37 (no local Docker; GitHub-hosted testing). |
| Decisions pending | None for TASK-002–TASK-005. Before TASK-040: where staging runs (D-19 as limited by OD-37). |
| Toolchain | See [TOOLCHAIN — Executor machine facts](../00-governance/TOOLCHAIN.md). Stage with `scripts/executor/stage.ps1`; a bulk add under `core.autocrlf=false` rewrites line endings on this machine. |
| Machine | RAM 7.2 GB, 0.1–1.1 GB free during TASK-001 without any PRANATA container; PHP, Composer and psql not installed. Other projects were not touched. |
| Cost | New recurring cost: NONE. Paid exception: NONE. |
| Database | Direct production DB touched: NO. Schema/migrations: none yet. |
| Cleanup for the Owner | Public synthetic sandbox `yusufarst/pranata-uny-sandbox-c10` may be deleted by the Owner; the executor token cannot delete repositories. |
| Next READY Task | None until PCR-002 is published; then TASK-002. |

## TASK PLAN CHANGE proposal — Docker-free local development (for Claude Planning)

Status: **INCORPORATED into PCR-002** (pending review; mapping in [PCR-002_REVIEW_HANDOFF](PCR-002_REVIEW_HANDOFF.md)); kept for provenance. No contract was changed by the executor. Owner decision 2026-11-01: no local Docker or Docker Compose for PRANATA (not even read-only), no self-hosted runner on the Owner's PC. GitHub-hosted runners are the primary environment for resource-heavy verification; their service containers are allowed. Synthetic data only in public Actions. No paid service. Gates and acceptance criteria stay.

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

**Owner:** forward [PCR-002_REVIEW_HANDOFF](PCR-002_REVIEW_HANDOFF.md) to the Second Review Agent. **Claude Planning:** after an acceptable verdict, verified fast-forward of `main` and the state-transition commit that sets TASK-002 READY. **Executor, once that commit is on `origin/main`:** verify it and start TASK-002 in SUPERVISED bootstrap mode. Do not start TASK-002 or TASK-003 before that.

## DO NOT DO

- No start of a BLOCKED Task; no Task outside AUTH-013; no production deployment, real institutional transaction, official policy activation with an open GAP, purchase or paid service.
- No planning amendment by the executor; changes go through a TASK PLAN CHANGE and Claude Planning.
- No GAP closure or resolution; no invented procurement, accounting, organization or security rule.
- No Git identity/configuration change, no force push, no history rewrite, no AI attribution.
- No access to MULTIPLECORP, BantuKasir or ELLIGBLE resources.
