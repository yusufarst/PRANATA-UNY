# Git and environment discipline

Status: APPROVED | Updated: 2026-10-04 (Asia/Jakarta) | Custodian: Planning Agent

## Initial observed P0 state and current checkpoint authority

Inspected 2026-10-04: branch `main` is unborn (no commits); index is empty. Existing untracked `.gitignore` and AICWDF source are preserved. Origin is `https://github.com/yusufarst/PRANATA-UNY.git`. No remote configuration is changed.

Historical AUTH-001 permitted working-tree documentation edits only. Current [AUTH-002](APPROVAL_RECORDS.md#auth-002) permits approval recording and one initial P0 checkpoint following [APPR-001](APPROVAL_RECORDS.md#appr-001): stage only intended P0 artifacts, commit `docs: finalize P0 governance` using the Owner's existing configured author/committer, push normally with `git push -u origin main`, verify the published checkpoint and stop. No force push, AI attribution, unrelated staged file, execution branch/Task or raw input is permitted. Verify `git status --short`, `git ls-files`, `git diff --cached --name-only` and `git check-ignore` for every raw source before handoff. Never force-add ignored inputs. Before commit require bounded post-approval integrity PASS and careful staged review. After push require local HEAD = intended origin/main checkpoint, expected remote documentation, P0 DONE/P1 TODO, post-P0 handoff, no raw sources/Tasks/application and clean working tree except ignored local references. Stop and report any failed verification; P1 requires separate authorization.

## Future branch and environment baseline

After authorized planning/freeze/READY Tasks, use bounded `codex/*` branches by default for feature/fix work, or an explicitly Owner-selected branch. Proposed AICWDF flow: work branch → PR/CI → `develop` → staging/verification/UAT → PR with required CI/approval → `main` → controlled production release. Protect `main`; no direct production experimentation. `develop`, CI, branch protections and environments have not been created or verified.

Environments are conceptual local/development, isolated staging, and controlled production. Local/staging use synthetic or approved anonymized data; credentials/configuration stay separate. Exact infrastructure, deployment tooling/providers, backup and release permissions remain P9/P10 decisions. AICWDF's Coolify/Hostinger/Cloudflare examples are not a claim that PRANATA infrastructure exists or that providers have been selected.

Do not alter remote configuration, Git identity, protected branch settings, repository visibility or deployment infrastructure merely to satisfy a template. Later commit/release actions follow their explicit authorized scope. Preserve unrelated working-tree changes; do not clean/reset to simplify review. Do not import MULTIPLECORP attribution or other owner-specific rules into PRANATA without a PRANATA decision.

## Reviewable changes

Keep diffs bounded and documentation links valid. Distinguish created files from pre-existing files left unchanged. Review staged content for secrets/raw sources before any future authorized commit. A secret incident requires controlled remediation; deleting a line is not proof of historical removal. Every stop updates handoff and records actual Git checks. The current AUTH-002 session ends after the initial approved P0 checkpoint is committed, pushed and remotely verified, or with an explicit failure report. This content describes the checkpoint conditions; inspect live HEAD/refs/status for the actual resulting Git state.
