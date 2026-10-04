# Toolchain

Status: APPROVED (P1 inspection amendment; P0 tool policy retained) | Updated: 2026-10-05 | Custodian: Planning Agent

This registry owns tool capability policy and honest availability (AICWDF §5–§7, §6A). A named tool is a preference; verified capability is the requirement. No application dependency was installed or application environment created in P0.

## Stack and agent baseline

The preferred baseline is Laravel, Inertia, React, TypeScript, shadcn/ui, Tailwind CSS, PostgreSQL, Nginx, and a Modular Monolith. The framework's layered/MVC/application-action direction is a baseline, not a P4 module design. Redis/Valkey is added only for a justified requirement. Exact versions, application packages, topology, and operational services are undecided. Material deviations require ADR plus Owner approval under [CHANGE_CONTROL](CHANGE_CONTROL.md).

Codex/ChatGPT is the Planning Agent. Claude Code is the future Execution Agent, limited to the eventual approved `READY` Tasks and targeted context. See [AGENT_OPERATING_MODEL](AGENT_OPERATING_MODEL.md). OD-11's local Laravel-native authentication supersedes the framework Google default; no OAuth/authentication dependencies are installed now.

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
| Git | `2.55.0.windows.3`; initial version probe passed | AUTH-002–004 historical/completed; AUTH-005 permits one approved P1 checkpoint through verified publication only; read-only inspection verified baseline |
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
