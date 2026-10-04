# Git and environment discipline

Status: APPROVED | Updated: 2026-10-04 (Asia/Jakarta) | Custodian: Planning Agent

## Historical P0 inspection and completed checkpoint

Historical initial inspection on 2026-10-04: branch `main` was unborn (no commits); index was empty. Existing untracked `.gitignore` and AICWDF source were preserved. Origin was `https://github.com/yusufarst/PRANATA-UNY.git` and remains unchanged.

Historical AUTH-001 permitted working-tree documentation edits only. [AUTH-002](APPROVAL_RECORDS.md#auth-002) permitted approval recording and one initial P0 checkpoint following [APPR-001](APPROVAL_RECORDS.md#appr-001); that action is **COMPLETED**. Published checkpoint: `f6d889100306a63a5bd391da4310346fc427d4d8`, commit message `docs: finalize P0 governance`. At post-publication inspection on 2026-10-04, branch `main`, local HEAD, origin/main and the live remote main ref matched; remote verification **PASS** and starting working tree clean. The initial publication is no longer pending.

## Bounded documentation-state closure

[AUTH-003](APPROVAL_RECORDS.md#auth-003) permits one new normal commit `docs: close P0 publication handoff`, normal push to unchanged `origin/main`, verification and stop. Do not amend the published checkpoint. Use only the Owner's existing configured author/committer identity, no AI attribution, force push, unrelated staged file, execution branch/Task or raw input. Current active phase authorization is **NONE**; this correction grants no later-phase authority.

Verify `git status --short`, `git ls-files`, `git diff --cached --name-only` and `git check-ignore` for every raw source. Never force-add ignored inputs. Before commit require bounded integrity PASS, refreshed manifest, careful staged review and exact staged file bytes (per-command `core.autocrlf=false`, without persistent configuration change). After normal push require local HEAD = origin/main = live remote main at the new closure commit, the original P0 checkpoint retained in history, APPR-001 intact, consistent post-publication documentation, no raw sources/Tasks/application and clean working tree except ignored local references. Stop and report failed verification; P1 requires separate Owner authorization.

## Future branch and environment baseline

After authorized planning/freeze/READY Tasks, use bounded `codex/*` branches by default for feature/fix work, or an explicitly Owner-selected branch. Proposed AICWDF flow: work branch → PR/CI → `develop` → staging/verification/UAT → PR with required CI/approval → `main` → controlled production release. Protect `main`; no direct production experimentation. `develop`, CI, branch protections and environments have not been created or verified.

Environments are conceptual local/development, isolated staging, and controlled production. Local/staging use synthetic or approved anonymized data; credentials/configuration stay separate. Exact infrastructure, deployment tooling/providers, backup and release permissions remain P9/P10 decisions. AICWDF's Coolify/Hostinger/Cloudflare examples are not a claim that PRANATA infrastructure exists or that providers have been selected.

Do not alter remote configuration, Git identity, protected branch settings, repository visibility or deployment infrastructure merely to satisfy a template. Later commit/release actions follow their explicit authorized scope. Preserve unrelated working-tree changes; do not clean/reset to simplify review. Do not import MULTIPLECORP attribution or other owner-specific rules into PRANATA without a PRANATA decision.

## Reviewable changes

Keep diffs bounded and documentation links valid. Distinguish created files from pre-existing files left unchanged. Review staged content for secrets/raw sources before any future authorized commit. A secret incident requires controlled remediation; deleting a line is not proof of historical removal. Every stop updates handoff and records actual Git checks. AUTH-002 is completed history. AUTH-003 expires after the bounded correction is committed, normally pushed and remotely verified, or ends with an explicit failure report. Inspect live HEAD/refs/status and the session final report for the resulting closure SHA; no future publication success is claimed in advance. Exact next safe action: Owner separately authorizes P1 — Product Definition, Scope & Acceptance.
