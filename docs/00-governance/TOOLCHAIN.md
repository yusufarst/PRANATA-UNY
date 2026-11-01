# Toolchain

Status: Approved baseline: APPR-004, published 0e3f7de · Amendment approved: APPR-005 (AUTH-010 remediation) | Updated: 2026-11-02 (Asia/Jakarta) | Custodian: Planning Agent

This registry owns tool capability policy and honest availability (AICWDF §5–§7, §6A). A named tool is a preference; verified capability is the requirement. No application dependency was installed or application environment created in P0.

## Stack and agent baseline

The preferred baseline is Laravel, Inertia, React, TypeScript, shadcn/ui, Tailwind CSS, PostgreSQL, Nginx, and a Modular Monolith. The framework's layered/MVC/application-action direction is a baseline, not a P4 module design. Redis/Valkey is added only for a justified requirement. Exact versions, application packages, topology, and operational services are undecided. Material deviations require ADR plus Owner approval under [CHANGE_CONTROL](CHANGE_CONTROL.md).

Agent roles follow GOV-07 (see [AGENT_OPERATING_MODEL](AGENT_OPERATING_MODEL.md)). OD-11's local Laravel-native authentication supersedes the framework Google default; no OAuth/authentication dependencies are installed now.

## Capability registry

| Capability | Preferred tool or path | Current P0 reality | Required verification later |
| --- | --- | --- | --- |
| Current documentation | Context7; current official documentation fallback | No Context7 tool exposed in this session; web/official-source retrieval is available. No application APIs were implemented or version-specific correctness claimed | Verify active version and current official APIs for version-sensitive planning or each relevant future Task; record topic, URL/version, date, and finding |
| Codebase impact analysis | Graphify; equivalent targeted analysis if unavailable | Graphify executable detected, but version probe failed (`uv trampoline failed to canonicalize script path`). No functioning graph capability verified; no application code exists. `rg` and targeted documentation review used for P0 | Repair or safely provide an equivalent when authorized significant code changes require it; verify capability and graph freshness before/after material changes |
| Browser E2E | Playwright; existing equivalent only if it satisfies the gate | No Playwright command on PATH; project package/browser/runtime availability unverified. Browser automation tools exposed by the client do not prove a project E2E suite exists. No browser journey tested | P8 defines journeys; authorized UI Tasks verify runnable automation, actual interactions, console/network evidence, and deterministic results |
| UI components | shadcn/ui and Tailwind CSS | Baseline only; no packages installed | Check compatible versions and reuse project components in authorized UI execution |
| Visual references | DesainPakaiAI or another relevant approved/selected reference | P0 had no selected design system. In P1 Owner selects primary SRC-047/VIS-04 (OD-21), directly inspected locally; detailed P7 design still absent | P7 translates chosen reference into approved design/responsive/motion/accessibility contracts |
| Static/automated checks | Project lint, typecheck, unit/feature/integration/authorization, route, E2E, build, security checks as applicable | No application, manifests, suite, or CI; application checks are N/A | P8 defines the strategy; P11 binds applicable checks/evidence to each Task |
| Safe source inspection | Local read-only file tools and existing bundled document runtimes | Source inspection tools are reused; a supplied app dependency bundle is available separately from system PATH | Choose only necessary format support, sanitize results, and distinguish extraction/visual review/unsupported content |

Current-doc fallback follows framework capability/adaptation rules (§5.1, §41); preserve the requirement to use current authoritative documentation rather than model memory. Graphify maps are navigation aids, never replacements for repository source or approved decisions. Critical UI journeys require actual browser E2E evidence (§26), regardless of the tool name.

## Observed local tools on 2026-10-04

Metadata/version probes only; these are machine capabilities, not installed PRANATA application dependencies.

| Tool | Observation | Verification limit |
| --- | --- | --- |
| Git | `2.55.0.windows.3`; initial version probe passed | AUTH-002–008 historical/completed; authority and current state: PHASE_STATUS / GIT_WORKFLOW; live baseline verified read-only |
| ripgrep (`rg`) | Command available and used | Targeted search works; no graph claimed |
| Node.js | `v24.18.0`; version probe passed | No application compatibility/build verification |
| npm | `11.16.0`; version probe passed | No package installation performed |
| Claude Code | `2.1.284`; version probe passed | Future executor availability does not authorize execution |
| Docker | Command detected | Engine, images, containers, and production use unverified |
| GitHub CLI (`gh`) | Command detected; version probe failed because configuration access was denied | Do not claim authenticated CLI usability; GitHub connector read-only structural fetch succeeded |
| Graphify | Command detected; version probe failed | Functional capability not verified; no graph generated |
| PHP, Composer, PostgreSQL CLI (`psql`) | Commands not found on PATH | Not proof of system-wide absence; detect again when authorized execution needs them |
| Python, FFmpeg, Poppler `pdftotext`, Playwright | Commands not found on PATH | Bundled runtimes may exist; PATH absence is not installation proof. Browser E2E unverified |

The Codex workspace dependency bundle reported by the app is version `26.909.12148`; use its resolved paths for authorized document/source inspection as needed. Do not confuse bundled Python/document libraries with an application runtime or a verified Playwright setup.

## Executor machine facts — TASK-001 (2026-11-01)

Windows PowerShell 5.1.26100, Git 2.55.0.windows.3 with global `core.autocrlf=true`, GitHub CLI 2.96.0, Claude Code 2.1.310, Node 24.18.0, npm 11.16.0; PHP, Composer and psql not installed; PSScriptAnalyzer not installed (AST parse and ASCII check used instead). RAM 7.2 GB. Local Docker is not used for PRANATA (Owner decision 2026-11-01). Claude Code hook and headless facts verified against the official docs on 2026-11-01 and by test: see [EXECUTOR_CAPABILITY_REPORT](../11-tasks/evidence/EXECUTOR_CAPABILITY_REPORT.md). Windows PowerShell 5.1 mangles double quotes inside native arguments such as `gh --jq`: filter in PowerShell instead. Stage with `scripts/executor/stage.ps1`: with global `core.autocrlf=true` the working copy of an LF blob is CRLF, and staging it unchanged under `core.autocrlf=false` would rewrite every line. Scripts stay ASCII only because 5.1 reads BOM-less files with the ANSI code page.

## Safe bootstrap policy

When the authorized phase/Task genuinely needs a missing capability: detect existing tools, reuse a suitable equivalent, check compatibility and environment permission, then safely install/configure and verify only what is needed. Prefer project-local tooling, then development dependencies, then user-local tooling, with global/system scope only when justified (§6).

P0 does not authorize application packages, scaffolding, migrations, UI, or infrastructure setup. Do not install Graphify on an empty application repository, Playwright without a current UI execution need, or Redis/Valkey without justification merely to satisfy a registry row. No silent major compatibility upgrades. If credentials, billing, external account access, privileged operations, or unsafe infrastructure changes are required, complete safe independent preparation first and record the exact remaining action and reason. Paid tools obey [COST_POLICY](COST_POLICY.md).

After any later setup, record purpose, version, installation scope, configuration/files changed, actual verification result, and whether it is safe to continue. Availability and verification are separate states.

## MCP on-demand policy

Heavy/specialized MCP servers default to **OFF for a project session**, activated only for needed capabilities. Installed tooling and exposed MCP schemas are separate decisions. Prefer a native project CLI/package when sufficient. Do not repeatedly expose browser, cloud, Figma, or unrelated media tools for ordinary backend/documentation work. Inspect real client capabilities; do not invent enable/disable commands or assume fixed token costs (§6A).

| Server/capability | Installed for project | Session default policy | Activate when | Deactivate when | Scope / context risk |
| --- | --- | --- | --- | --- | --- |
| Context7/current docs | Not detected as exposed; installation unknown | OFF, on demand | Version-sensitive authoritative docs are needed | Requested topics are resolved | Client configuration unknown / unknown |
| Graphify/impact analysis | CLI detected but failed; MCP not exposed, installation unknown | OFF, on demand | Significant change needs relationship analysis | Analysis and freshness evidence recorded | User-local CLI observed; MCP scope unknown / unknown |
| Playwright/browser automation | Project installation unverified | OFF, on demand | Browser behavior or critical UI E2E needs verification | Browser evidence complete | Project/client scope to verify / unknown |
| GitHub connector | Exposed by this client; no project installation made | OFF unless reference/repository work requires it | Authorized repository/reference reads are needed | Those reads are complete | Client connection / unknown |
| Cloud/infrastructure, Figma, and other specialized MCP | Client tools may be exposed; no project installation made | OFF, on demand | Explicit authorized work requires the capability | That work is complete | Inspect actual configured scope / unknown |

If the client cannot temporarily deactivate exposure, document the limitation and prefer scoped profiles or the underlying native tool when practical. Do not sacrifice necessary verification to reduce context. No MCP configuration was changed in P0. Each future Task must state required servers, servers to leave OFF, native alternatives, and cleanup.

## P1 source-inspection session amendment

Historical AUTH-004 covered P1 planning only; APPR-002 approves that reviewed result and AUTH-005 covers approval/checkpoint only. Neither authorizes application setup or P2. Existing PowerShell/Node/Git, bundled Python/PIL and existing local VLC decoded primary video locally. PATH absence of FFmpeg/Python in P0 did not prove absence of another decoder. VLC software decoding produced representative and temporal frames; scratch/cache cleaned, configuration not saved, no new installation/application dependency. Inventory/experience documents record exact metadata and limits. Documentation audit uses Node file hashes/Git/link/ID checks; application/current-library API verification, graph generation and browser E2E remain N/A because no app/version-sensitive implementation exists.

DesainPakeAI skill was read for reference analysis. Its authenticated guide retrieval failed **INVALID_CREDENTIAL_FILE**; no guide content or successful authentication is claimed. Local Owner-selected reference analysis continued without uploading raw media. No MCP reconfiguration, tool account change, paid exception or recurring production dependency was introduced. P1 quality evidence owns resulting checks, rather than reclassifying P0 probes as current runtime proof.

## Historical P2 preparation verification — AUTH-006

P2 uses existing PowerShell, Node.js `v24.18.0` and Git for bounded documentation edits, file-byte hashes, links/anchors/identifier checks, preservation/diff review and read-only published-baseline verification. Remote baseline check succeeded after restricted-network retry; no Git writes/publication, remote/identity change or dependency install. Temporary audit helpers are outside the repository and are not application/import code.

Existing SOURCE_INVENTORY, ASSET_SOURCE_REVIEW, PROCUREMENT_SOURCE_REVIEW and approved P1 coverage support conceptual modeling. No raw-content reinspection/recalculation/media decoding/upload, current-law verification, new source-authority validation, runtime/version-sensitive application claim, MCP configuration, production DB access or recurring cost occurred. Application tests/browser E2E/security/performance/build/migration remain N/A because no application exists. Internal peer review is preparation evidence, not the required independent exact-manifest review or Owner approval; [P2_QUALITY_GATE](evidence/P2_QUALITY_GATE.md) owns results.

## Historical P2 approval/checkpoint verification — AUTH-007

Existing PowerShell/Node/Git only. Exact pre-approval manifest and all entries independently verified before edits; temporary audit/snapshot helpers outside the repository support file-byte preservation, links/anchors/IDs/counts, raw fingerprints/ignore checks and staged/committed blob equality. APPR-003 preserves the reviewed identity; the refreshed P2 manifest identifies post-approval bytes. AUTH-007 permits one normal Owner-identity checkpoint/push/live remote verification and automatically completes afterward. No installation, paid service, MCP reconfiguration, raw-content reinspection/upload, current-policy validation or production access; application checks remain N/A — no application. [P2_QUALITY_GATE](evidence/P2_QUALITY_GATE.md#p2-post-approval-integrity--2026-10-05) owns results; P3 remains NOT AUTHORIZED.

## Historical P3 preparation verification — AUTH-008

Existing PowerShell/Node/Git only. Read-only live remote verification passed after restricted-network retry. Temporary helpers outside the repository compare all tracked baseline bytes and 47 ignored raw fingerprints, inspect IDs/coverage/local links/anchors and generate/verify the manifest. No installation, MCP reconfiguration, paid service, raw-content reinspection/upload, production access or version-sensitive application work. Runtime/browser E2E/accessibility/performance/security checks N/A because there is no application. [P3_QUALITY_GATE](evidence/P3_QUALITY_GATE.md) owns actual checks; P4–P11 stay not authorized.

## P3 approval checkpoint verification — AUTH-009

Existing PowerShell/Node/Git only; exact corrected manifest/entries independently matched before approval edits. Temporary helpers/snapshots outside repository support protected-byte/ID/link/locator/raw-fingerprint checks and staged/committed blob equality. APPR-004 binds corrected pre-approval identity; current P3 manifest identifies post-approval bytes. AUTH-009 permits one normal Owner-identity checkpoint/push/live verification then automatically completes. No dependency installation, paid service, MCP reconfiguration, production access or raw-content upload. Runtime/E2E/security/performance and official procedure/accounting-result validation remain N/A/not claimed. [P3_QUALITY_GATE](evidence/P3_QUALITY_GATE.md#p3-post-approval-integrity--2026-10-05) owns evidence.

## P0–P11 planning candidate toolchain (AUTH-012)

The cloud planning session used Git, GitHub CLI and Python 3 standard-library scripts kept in its session scratchpad (Task renderer; link, anchor, ID, coverage and dependency-graph checks). Nothing was installed in or added to the repository, no MCP configuration changed and no paid service was used. The application toolchain is a candidate owned by [ARCHITECTURE](../04-architecture/ARCHITECTURE.md) (stack), [TEST_STRATEGY](../08-testing/TEST_STRATEGY.md) and [QUALITY_GATES](../08-testing/QUALITY_GATES.md) (quality tools and CI) and [INFRASTRUCTURE](../09-operations/INFRASTRUCTURE.md) (containers); exact versions are verified at TASK-002/TASK-003 and recorded here then. Claude Code executor capabilities are verified at TASK-001 ([EXECUTOR_OPERATING_MODEL §13](../11-tasks/EXECUTOR_OPERATING_MODEL.md#13-kemampuan-platform-yang-wajib-diverifikasi)). Graphify stays OPTIONAL and unverified; Context7 is used on demand for version-sensitive work.

## Docker-free development topology (PCR-002, OD-37)

Owner decision [OD-37](DECISION_LOG.md#od-37) (2026-11-01): the Owner's Windows PC (about 7.2 GB RAM, shared with MULTIPLECORP, BantuKasir and ELLIGBLE) runs no Docker or Docker Compose for PRANATA, no self-hosted GitHub Actions runner and no PRANATA database or service. GitHub-hosted runners are the primary environment for resource-intensive testing; PostgreSQL and Gotenberg run there as service containers.

| Scope | Tools | Rule |
| --- | --- | --- |
| Local, allowed | Editor, Git, GitHub CLI, Node/npm (project dependencies only), PowerShell, Claude Code and the executor safeguards | Lightweight checks only: ESLint, Prettier, `tsc` and Vitest on changed files, guard tests, link checks, the private parity check of TASK-071. A local `npm ci` is optional; when memory is short, rely on the GitHub-hosted run |
| Local, optional fallback | Portable PHP CLI and Composer under `.pranata/tools/` (ignored by Git) | Only when the GitHub-hosted scaffold of TASK-002 cannot be used, or for light PHP CLI checks. Official download, checksum verified and recorded here; invoked by explicit path; no PATH, environment-variable, registry or service change; not used if it needs a system component that is not already present; never a database server |
| Local, prohibited | Docker, Docker Compose, WSL changes, local PostgreSQL or Gotenberg, a self-hosted Actions runner, global PHP/Composer/PostgreSQL, PATH, system environment variables or services | A change needs separate Owner authorization |
| GitHub-hosted | Ubuntu runner image; PHP through a SHA-pinned setup action; PostgreSQL major pinned here (≥ 16, also the staging and production target); Gotenberg image pinned here | Public repository: standard runners without minute cost; synthetic data only. Versions are verified at TASK-002/TASK-003 and recorded here |

The Claude Code headless runner (`scripts/executor/runner.ps1`) is a local executor process, not a GitHub Actions runner. Its restricted token (C-15) is a later Owner prerequisite for AUTONOMOUS mode. Capability C-14 (Docker bind mount) is SUPERSEDED / NOT APPLICABLE. Exact versions, the runner image, job durations and any portable-tool checksum are recorded here by TASK-002–TASK-005.

## Application versions — TASK-002 (2026-11-01)

Generated on a GitHub-hosted runner by the temporary `scaffold` job (run `41325195031`) with the official installer `laravel/installer` v5.32.0, `laravel new app --react --pest --npm --no-boost`, from `laravel/react-starter-kit` branch `main` at `98d9b821baba4f4cc4120c96d35971366cec53e6`. The kit's tagged Packagist release was not used: `composer create-project` without a stability flag resolved to Laravel 12 (run `41324895646`). Source of the method: Laravel 13.x starter kit documentation through Context7. No portable tool was needed; no local PHP, Composer, PostgreSQL or Docker was used.

| Component | Version | Source of the version |
| --- | --- | --- |
| Runner image | `ubuntu-24.04` | `bootstrap.yml` |
| PHP | 8.5.12 (setup-php `2.40.0`) | Run log; the kit requires `^8.3` |
| Composer | 2.11.1 | Run log |
| Node, npm | 24.22.1, 11.19.1 | Run log; the executor machine has Node 24.18.0, npm 11.16.0 |
| Laravel framework | 13.39.0 | `composer.lock` |
| Fortify, Wayfinder, Tinker | 1.41.0, 0.1.23, 3.0.2 | `composer.lock` |
| Inertia | `inertia-laravel` 3.6.0, `@inertiajs/react` 3.10.0 | `composer.lock`, `package-lock.json` |
| Pest, PHPUnit, Pint, Larastan | 5.3.5, 13.4.5, 1.32.1, 3.13.2 | `composer.lock` |
| React, TypeScript, Tailwind CSS, Vite+ | 19.3.1, 5.9.3, 4.3.3, 1.4.0 | `package-lock.json` |
| shadcn/ui | Style `new-york`, base color `neutral`; components copied by the kit under `resources/js/components/ui` | `components.json` |
| PostgreSQL (CI service) | 18.6, image `postgres:18.6@sha256:3a4264520e121176619f54ff867d12774bc81e95e7b84db58d63bccdb6144b81` | Run `41326024572` |
| Browser for screenshots | Google Chrome 156.0.8078.47 of the runner image | Run `41326024572` |
| Actions, by commit SHA | `checkout` v7.0.1, `setup-node` v7.1.0, `upload-artifact` v7.0.2, `setup-php` 2.40.0 | `git ls-remote` on the public repositories |

Findings for Claude Planning, none silently resolved as a plan change:

- **Inertia major.** ARCHITECTURE and EXECUTION_CONTEXT §2 name Inertia 2. The current official starter kit ships Inertia 3. TASK-002 takes the kit's version, because its objective is the current stable kit and downgrading would leave the kit.
- **Frontend lint and format.** QUALITY_GATES and TASK-002 name ESLint and Prettier. The current kit ships Vite+ (`vp check`: Oxlint and Oxfmt) and neither of the named tools. Under this registry's rule that verified capability is the requirement, `app-boot` runs `npm run check` and `npm run types:check`; no package was added. Capability is not yet proven for the ESLint rules that TEST_STRATEGY requires (literal JSX text, react-hooks): `vite.config.ts` configures no such rule, so this needs a Planning decision before the i18n and `quality` work depend on it. `vp check` also checks the formatting of every repository file, so `vite.config.ts` excludes `docs/`, `scripts/`, `.claude/`, `.githooks/`, `.pranata/` and the root Markdown files (GOV-09).
- **Passkeys.** The kit enables passkey sign-in, which is outside the approved auth profile of password plus TOTP (EXECUTION_CONTEXT §3) and could bypass mandatory TOTP (SEC-D1). The feature, routes, table and pages are removed. An unused rate limiter and the `passkeys` block in `config/fortify.php` stay as the kit ships them. `laravel/passkeys` stays in `composer.lock` because `laravel/fortify` requires it, and `@laravel/passkeys` stays unused in `package.json` so that TASK-002 does not rewrite the kit's lockfile.
- **Kit behaviour left for later Tasks.** These come from the kit and are not approved behaviour: self-service account deletion (`profile.destroy`); email sign-in without username; password reset by email, shown on the login page, against TASK-011; a password profile of `min(12)` plus `uncompromised()` in production and framework defaults elsewhere, against AICWDF-COMPAT-8 (SECURITY_MODEL), where `uncompromised()` also calls an external service, which TASK-010 owns; and the `laravel/chisel` dependency. TASK-003 removed `laravel/sail`, a Docker wrapper, under OD-37.

## Integration environment — TASK-003 (2026-11-02)

Both services run only inside GitHub-hosted jobs, bound to `127.0.0.1` on the runner, and hold synthetic data only. Nothing runs on the Owner's machine.

| Component | Version or result | Source |
| --- | --- | --- |
| PostgreSQL service | 18.6, with `pg_trgm`, `ltree`, `btree_gist` created and used by tests | Run `41335757703`, `db:show` |
| Gotenberg service | 8.39.0, image `gotenberg/gotenberg:8.39.0@sha256:13672f317b443c793e972b1bd2155ec62a3e7b6891b411e8196442cc053ee5f5` | Run `41334071760` |
| Job durations | `app-boot` 54 s, `db-integration` 66 s, `bootstrap-gate` 7 s, without a Composer or npm cache | Run `41335757703` |

Facts verified by test and against the Laravel 13.x source:

- **Parallel database names.** Laravel appends `_test_<token>` to the database name, so workers use `pranata_test_test_1` and so on. This fits `pranata_test_<token>` in TEST_STRATEGY §4 with the token `test_1`.
- **Reset guard.** A `CommandStarting` listener checks the database that the command would really use: the selected or default connection, after any `DB_URL` override, on the PostgreSQL driver only. The console kernel forwards this event only outside unit tests, so `PlatformServiceProvider` also forwards it under `APP_ENV=testing`. `DB::prohibitDestructiveCommands` stays on for production, as the kit ships it. The guard checks the database name and driver, not the host. No destructive seeder exists yet, so `db:seed` is not guarded.
- **A missing database can be created.** `php artisan migrate --force` creates a missing PostgreSQL database by itself, which is one more reason the guard must run first.
- **Invalid workflow file.** A YAML error yields a failed `push` run and no `pull_request` run; the merge gate then refuses with "no bootstrap run".

## Quality tooling — TASK-004 (2026-11-02)

| Tool | Version | Note |
| --- | --- | --- |
| ESLint, `@eslint/js` | 9.39.5 | npm marks this line as no longer supported. ESLint 10 fails to resolve: `eslint-plugin-react` 7.37.5 accepts up to `^9.7`, and `--force` was not used |
| `typescript-eslint`, `eslint-plugin-react`, `eslint-plugin-react-hooks`, `eslint-config-prettier` | 8.72.1, 7.37.5, 7.1.1, 10.1.8 | Flat config in `eslint.config.js` |
| Prettier, `prettier-plugin-tailwindcss` | 3.9.10, 0.8.1 | Options copied from the kit's formatter; the first run changed no file |
| Vitest | 5.0.5, through `vp test run` | Vite+ ships it; `vitest.config.ts` stays apart from the Laravel plugin |
| Larastan | 3.13.2 at level 7 | Above the contract's starting level 5 and its target of at least 6, because the kit already passes level 7 |
| gitleaks | 8.30.1 (MIT), `linux_x64` SHA-256 `551f6fc83ea457d62a0d98237cbad105af8d557003051f41f3e7ca7b3f2470eb` | Downloaded and verified inside the `quality` job; scans the whole history |

ESLint and Prettier replace `vp check`, which TASK-002 used in `app-boot`; Vite+ still runs the build and Vitest.

- **Secret scan.** The first scan reported 8 findings over the history. All 8 are the password profile name `AICWDF-COMPAT-8` in planning documents, read one by one, and none is a credential. `.gitleaks.toml` allows exactly that string and keeps the default rules; the job proves this with a random token made at run time.
- **Literal JSX text.** `react/jsx-no-literals` is on. The 17 kit files that still hold English text are listed in `eslint.literal-baseline.json` and exempt until TASK-006 empties the list; any other file fails.
- **Architecture test.** `tests/Arch/ModuleBoundaryTest.php` follows the dependency column of ARCHITECTURE §4, with `Workflow` and `Audit` added for the business modules because §6 has their Actions write those records. Pest ignores a dependency on a class that does not exist, so the negative check in `quality` uses a real class.
- **Finding for Claude Planning.** ARCHITECTURE §5 rule 3 draws Access above Organization and Policy, while §4 has Access depending on both. The test follows §4, which is free of cycles; §5 rule 3 needs a correction.

## CI pipeline — TASK-005 (2026-11-02)

`ci.yml` and `ci-nightly.yml` replace the temporary `bootstrap.yml`. `actions/cache` v6.1.0 is pinned by SHA. All jobs run on GitHub-hosted `ubuntu-24.04`.

| Job | First run (`41341172993`) | With cache (`41341485098`) |
| --- | --- | --- |
| `changes` | 3 s | 6 s |
| `ci-fast` | 112 s | 61 s |
| `ci-e2e` | 113 s | 67 s |
| `docs-check` | 5 s | 6 s |
| `ci-gate` | 3 s | 2 s |

- **Filter.** `changes` fails closed: an unusable range or an empty diff runs everything, and any file outside `docs/` and the root Markdown files counts as code. Every path under `tests/` triggers `ci-e2e`, which covers Gotenberg tests outside `tests/e2e/` (PCR002-SR-C1).
- **Gate.** `ci-gate` requires `success` for a called job and `skipped` for a job the filter did not call; anything else fails, as does a failed `changes`.
- **Gotenberg group.** The two tests in `tests/Feature/Documents/GotenbergSmokeTest.php` skip in `ci-fast`, which has no Gotenberg service, and `ci-e2e` and `ci-nightly` refuse a skip with `--fail-on-skipped`.
- **Probe.** `scripts/executor/tests/ci.probe.ps1` proves the gate and the filter on disposable draft PRs.
- **Ruleset.** Instructions for the Owner: [MAIN_RULESET](../09-operations/MAIN_RULESET.md).
