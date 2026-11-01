# PRANATA UNY

PRANATA is planned as one integrated web application and one shared data ecosystem, with Asset Workspace, Procurement Workspace, Vendor Portal and Super Admin. Owner direction is recorded in [DECISION_LOG](docs/00-governance/DECISION_LOG.md); P1 product definition is approved under APPR-002. The [P2 domain model](docs/02-domain/DOMAIN_MODEL.md) is approved under APPR-003 with every rule/source/GAP qualification preserved.

Current state: see [PHASE_STATUS](docs/PHASE_STATUS.md) and [CURRENT_HANDOFF](docs/handoff/CURRENT_HANDOFF.md). P0–P11 DONE — APPROVED (APPR-005, APPR-006 at `851013f`) and frozen as FRZ-001; autonomous execution of TASK-001–TASK-078 authorized by AUTH-013.

Start with [AGENTS.md](AGENTS.md), then [CURRENT_HANDOFF](docs/handoff/CURRENT_HANDOFF.md), [PHASE_STATUS](docs/PHASE_STATUS.md) and [CONTEXT_INDEX](docs/CONTEXT_INDEX.md). [PROJECT_CHARTER](docs/00-governance/PROJECT_CHARTER.md) defines the P0 foundation. [P0_QUALITY_GATE](docs/00-governance/evidence/P0_QUALITY_GATE.md) records review evidence.

The repository is the durable Source of Truth. Local historical materials in `reference-inputs/` remain Git-ignored; only sanitized inventories and observations belong in documentation. Do not upload or commit raw sources, credentials or personal/financial records.

## Development without local containers

Owner decision [OD-37](docs/00-governance/DECISION_LOG.md#od-37) applies: this machine runs no Docker, Docker Compose, PHP service, database, Gotenberg or self-hosted Actions runner for PRANATA. Do not install PHP, Composer or PostgreSQL globally, and do not change PATH, system environment variables or services. The other projects on this machine stay untouched.

**Allowed locally:** an editor, Git, GitHub CLI, Node and npm with project dependencies, PowerShell, and light checks such as `npm run check` and `npm run types:check` after an optional `npm ci`. Executor checks: `scripts/executor/tests/guard.tests.ps1` and `scripts/executor/tests/scripts.tests.ps1`.

**Everything that needs PHP, PostgreSQL, Gotenberg or a browser** runs on GitHub-hosted runners, with synthetic data only:

1. Create a branch from the latest `main` and stage with `scripts/executor/stage.ps1`.
2. Open a draft PR. The `ci` workflow starts on every PR update and uses its own disposable PostgreSQL and Gotenberg service containers.
3. Read the result:

   ```bash
   gh run list --branch <branch> --limit 1
   ```

   ```bash
   gh run view <run-id> --log-failed
   ```

4. Merge only through `scripts/executor/merge-verified.ps1`.

A check that did not run is recorded as NOT RUN, never as passed. The test suite and destructive commands such as `migrate:fresh` refuse any database outside the `pranata_*` set and `APP_ENV=production`. Versions and job durations: [TOOLCHAIN](docs/00-governance/TOOLCHAIN.md).

## Project documents

[WORKFLOW_CATALOG](docs/03-workflows/WORKFLOW_CATALOG.md) indexes the APPR-004 APPROVED operational contracts. [P3 quality evidence](docs/00-governance/evidence/P3_QUALITY_GATE.md) and [candidate manifest](docs/00-governance/evidence/P3_ARTIFACT_MANIFEST.json) preserve the exact approved candidate identity and identify post-approval checkpoint bytes.
