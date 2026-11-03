# Current handoff

Status: Approved P0–P11 baseline (APPR-005, APPR-006) frozen as FRZ-001 and published · PCR-001 published · PCR-002 published · PCR-003 published · Execution: AUTH-013 | Updated: 2026-11-03 (Asia/Jakarta) | Role: EXECUTION AGENT (GOV-10) | Phase: execution, TASK-010 DONE, TASK-011 READY | Authorization: AUTH-013 (execution), FRZ-001 change control (planning)

## Current state

Project **PRANATA UNY**. The remote repository `https://github.com/yusufarst/PRANATA-UNY`, specifically published `main`, is the durable shared Source of Truth; confirm it with `git ls-remote origin refs/heads/main` before acting.

- P0–P11 **DONE — APPROVED** (APPR-005, APPR-006); Planning Freeze **FRZ-001** effective; PCR-001 published; execution authorized by **AUTH-013**.
- **TASK-001 DONE**: executor guardrails, runner, watcher and capability report. **TASK-002 DONE**: Laravel 13 React skeleton, `app/Modules`, `bootstrap.yml` and the `-Bootstrap` evidence gate. **TASK-003 DONE**: database reset guards, PostgreSQL and Gotenberg checks on GitHub-hosted runners, tightened executor guard. **TASK-004 DONE** (4/78): ESLint, Prettier, Vitest, architecture and route integrity tests, translation and link checkers, the negative checks and gitleaks. **TASK-005 DONE** (5/78): `ci.yml`, `ci-gate` and `ci-nightly.yml` on `main`; the `main` ruleset refuses a commit without a `ci-gate` result. **TASK-006 DONE** (6/78): design tokens, AppShell, composites, self-hosted fonts, id/en localization and localized error pages. **TASK-007 DONE** (7/78): Playwright on Chromium and WebKit, axe, per-run E2E databases, SETUP FAILURE and the sharded `ci-e2e` job. **TASK-008 DONE** (8/78): PostgreSQL roles `pranata_owner`/`pranata_app` with the `migrator` connection, generic integrity triggers, partitioned append-only `audit_events`, `AuditLogger` and `audit:partitions`; the E2E server signs in as a member of `pranata_app` only. **TASK-009 DONE** (9/78): `X-Idempotency-Key` middleware with `platform_idempotency_keys` and hourly pruning, `HasLockVersion` with 409 and the latest data, the base `Action` (transaction, at most 2 retries on 40P01/40001, 503 when exhausted), localized error mapping, `X-Request-Id` in the log context and audit, and `useCommand` with ConflictDialog, applied to the profile form. **TASK-010 DONE** (10/78): sign-in by username or email (COMPAT-8) with a status gate, a 5-a-minute limit per identity and address and a 10-failure, 15-minute lockout; database sessions with idle and absolute timeouts; forced password change PG-002 at `/password/change`; other sessions and the remember token revoked on every password change or reset; authentication audit events; saved `users.locale`; starter-kit account deletion removed (OD-38); `main` at `3e9c9eb`. No business feature yet.
- Executor mode: **AUTONOMOUS** under AUTH-013, through the runner. C-15 is VERIFIED and AUTH-013 needs no further scope authorization, so no prerequisite of EXECUTOR_OPERATING_MODEL §1 is left for the Owner. The guard now refuses tag pushes and tag writes; that Owner-approved guardrail change (2026-11-02) merged on a `fix/task-006-*` branch because the merge gate refuses a Task already DONE. It is not part of the TASK-006 contract.
- **PCR-002 published** (2026-11-01): Owner decision OD-37 forbids local Docker; heavy checks run on GitHub-hosted runners, first through the temporary bootstrap workflow ([QUALITY_GATES §4.1](../08-testing/QUALITY_GATES.md#41-workflow-bootstrap-sementara)). C-16 (bootstrap evidence gate) is VERIFIED ([EXECUTOR_CAPABILITY_REPORT](../11-tasks/evidence/EXECUTOR_CAPABILITY_REPORT.md#c-16)).
- GAP-001–GAP-029: OPEN (resolved NONE).

## Handoff (AICWDF §36)

| Field | Current value |
|---|---|
| Current phase | Execution; approved phases 12/12. |
| Current Task / status | **TASK-010 DONE**; TASK-011 READY. History of PR #38 (PCR-003, OD-38, published 2026-11-03, [APPROVAL_RECORDS](../00-governance/APPROVAL_RECORDS.md#pcr-003-publication-record)). **Guardrail path, done in SUPERVISED mode:** the `ci-fast` step "A forbidden module import must fail the architecture test" ran `rm -r app/Modules/Platform/Actions app/Modules/Identity`, which deleted the real Identity module, so the route probes could not boot (runs 41585417711, 41588911075). Fixed in `561baa6` (`.github/workflows/ci.yml` only): the probe refuses existing probe files, removes only `ForbiddenProbe.php` and `Identity/Models/Account.php` with their empty folders, and fails if `app/` is left changed. The whole-history secret scan then flagged the synthetic test password from `aa460ec` (run 41594312253); `fbd675b` allowlists that exact value under `tests/` only in `.gitleaks.toml`. Run 41594982055 on `fbd675b` is fully green. Because PR #38 touches `.github/workflows/ci.yml`, its merge is SUPERVISED only (EXECUTOR_OPERATING_MODEL §8.1.6). Slice 2 at `41b2820` (run 41588481276): idle/absolute session timeout, forced password change PG-002 at `/password/change` (TAC-04), other sessions and the remember token revoked on every password change or reset (TAC-05), COMPAT-8 password profile with an identity check, sign-in audit events, saved `users.locale` copied into the session at sign-in; every step up to Pest and the e2e shards green, the login page passes axe in both languages on both viewports (TAC-06). |
| Last completed action | 2026-11-03: PR #38 merged by verified fast-forward at `3e9c9eb` in SUPERVISED mode (it touches `.github/workflows/ci.yml`) under AUTH-013, after valid Codex findings (two P1, one P2) and independent-review findings (two P1, two P2) were fixed in `3e9c9eb` and a delta review passed; run 41597084810 green; TASK-010 reconciled DONE. Mode back to AUTONOMOUS. |
| Evidence | [TASK-010 COMPLETION EVIDENCE](../11-tasks/TASK-010.md#completion-evidence); [TASK-009](../11-tasks/TASK-009.md#completion-evidence); [TASK-008](../11-tasks/TASK-008.md#completion-evidence); [TASK-007](../11-tasks/TASK-007.md#completion-evidence); [TASK-006](../11-tasks/TASK-006.md#completion-evidence); [TASK-005](../11-tasks/TASK-005.md#completion-evidence); [TASK-004](../11-tasks/TASK-004.md#completion-evidence); [TASK-003](../11-tasks/TASK-003.md#completion-evidence); [TASK-002](../11-tasks/TASK-002.md#completion-evidence); [EXECUTOR_CAPABILITY_REPORT](../11-tasks/evidence/EXECUTOR_CAPABILITY_REPORT.md). |
| Task plan | Baseline 78 · Current 78 · DONE 10 · READY 1 · IN_PROGRESS 0 · BLOCKED 0 · VERIFYING 0 · TODO 67 · SUPERSEDED 0 · 13%. |
| Open blockers | NONE for TASK-011. TASK-040 waits on the staging decision. |
| Open non-blocking gaps | GAP-001–GAP-029 OPEN; handled as policy activation, release/cutover or V1-acceptance gates (D-28), never as invented rules. |
| Decisions made | OD-01–OD-36, GOV-01–GOV-12, D-01–D-29, and OD-37 (no local Docker; GitHub-hosted testing). Owner decision 2026-11-02 on account deletion, recorded below for Claude Planning to register. |
| Decisions pending | Ruleset `release-tags` keeps the admin role's `always` bypass ([MAIN_RULESET](../09-operations/MAIN_RULESET.md)); it applies to the runner token too, so the guard is what holds the executor back from tags. For Claude Planning, non-blocking: the current starter kit ships Inertia 3 where the plan names Inertia 2 ([TOOLCHAIN](../00-governance/TOOLCHAIN.md#application-versions--task-002-2026-11-01)); ARCHITECTURE §5 rule 3 conflicts with the dependency column of §4 ([TOOLCHAIN](../00-governance/TOOLCHAIN.md#quality-tooling--task-004-2026-11-02)). Before TASK-040: where staging runs (D-19 as limited by OD-37). |
| Toolchain | See [TOOLCHAIN — Executor machine facts](../00-governance/TOOLCHAIN.md). Stage with `scripts/executor/stage.ps1`; a bulk add under `core.autocrlf=false` rewrites line endings on this machine. |
| Machine | RAM 7.2 GB, 0.1–1.1 GB free during TASK-001 and about 50–70 MB free during TASK-004 without any PRANATA container, so `npm` work runs on GitHub-hosted runners; PHP, Composer and psql not installed. Other projects were not touched. Editing a file that holds carriage returns (`docs/00-governance/TOOLCHAIN.md`, `.gitignore`) must keep them (GOV-09); check before staging. |
| Cost | New recurring cost: NONE. Paid exception: NONE. |
| Database | Direct production DB touched: NO. Schema: Laravel skeleton tables, database roles, integrity trigger functions, `audit_events`, `platform_settings` and `platform_idempotency_keys`, tested on a runner service container. |
| Cleanup for the Owner | Public synthetic sandbox `yusufarst/pranata-uny-sandbox-c10` (used for C-10 and C-16) may be deleted by the Owner; the executor token cannot delete repositories. Disposable branches stay as they are. |
| Next READY Task | TASK-011 (SEC-D6 and SEC-D7 defaults apply: no SMTP needed; activation codes handed over in person). Deferred P3 findings of TASK-008, TASK-009 and TASK-010 are listed in their COMPLETION EVIDENCE; The deferred P3 on a remember cookie issued before a promotion to SUPER_ADMIN belongs with the role changes of TASK-011. The Codex threads of PR #34 and PR #38 stay unresolved for the Owner (the executor token cannot resolve them). Commands that need idempotency use the `idempotent` route middleware and `useCommand`; `profile.destroy` (starter-kit account deletion) fails under `pranata_app`, which holds no DELETE on `users`. Browser tests sign in once per project (login limit 5 a minute per address); a 419 cannot be provoked from a same-origin browser. SSR is off (`config/inertia.php`); turning it on first needs a per-request translation instance. |

## TASK PLAN CHANGE proposal — account deletion (for Claude Planning)

Status: **INCORPORATED into PCR-003** (OD-38; published 2026-11-03; TASK-010 TAC-07, TASK-011 TAC-06 with PCR003-SR-C1); kept for provenance. No contract was changed by the executor. This record is not part of the TASK-009 contract; it merged on a `fix/task-009-*` branch only because the merge gate takes the active Task.

Owner decision, binding for TASK-010 and every later Identity Task:

1. `pranata_app` gets no DELETE on `users` to support Laravel's default `profile.destroy`.
2. An account ends as DEACTIVATED: its active sessions are revoked and its audit history stays.
3. The kit's account-deletion UI and route are disabled or replaced where needed.
4. Permanent deletion of data needs a separately validated institutional retention and deletion policy (GAP-023).
5. The database security controls of TASK-008 stay as they are.
6. A change to an approved Task contract goes through Planning change control.
7. No redundant reviews or unnecessary tests; mandatory security and integrity checks stay.

Already consistent with the baseline: DATA_MODEL §2 (no DELETE on `users`) and §5 (`status` DEACTIVATED, `deactivated_at`), SECURITY_MODEL (DEACTIVATED cannot sign in; history stays), the offboarding runbook (deactivate, revoke sessions, history stays), TOOLCHAIN (`profile.destroy` is unapproved kit behaviour).

| Document | Proposed change |
|---|---|
| TASK-010 | IN SCOPE: remove the kit's `profile.destroy` route, its controller action and the delete-account UI. TAC: the route integrity test proves the route is gone (today `profile.destroy` is in `OWN_ACCOUNT_ROUTES` of `tests/Feature/RouteIntegrityTest.php`, and `tests/Feature/Settings/ProfileUpdateTest.php` calls it). The missing DELETE on `users` is already proven by `DatabaseRolesTest` (TASK-008); cite it, add no new test. |
| TASK-011 | "nonaktifkan" means status DEACTIVATED with `deactivated_at` set, plus revoking every session of the user, recorded in `audit_events`; no row of `users` is deleted. |
| GAP-023 | Permanent deletion stays out of V1 until the institutional retention and deletion policy is validated. |

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

**Executor:** start TASK-011 (READY) from `.pranata/executor/checkpoint.json` under the single runner and watcher, unless the Owner sets a hold. No local Docker, PHP, Composer or PostgreSQL (OD-37).

**Claude Planning:** nothing pending from the executor; the account-deletion proposal is incorporated into PCR-003.

## DO NOT DO

- No start of a BLOCKED Task; no Task outside AUTH-013; no production deployment, real institutional transaction, official policy activation with an open GAP, purchase or paid service.
- No planning amendment by the executor; changes go through a TASK PLAN CHANGE and Claude Planning.
- No GAP closure or resolution; no invented procurement, accounting, organization or security rule.
- No Git identity/configuration change, no force push, no history rewrite, no AI attribution.
- No access to MULTIPLECORP, BantuKasir or ELLIGBLE resources.
