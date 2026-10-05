# Git and environment discipline

Status: APPROVED (APPR-003; AUTH-007 approval/checkpoint lifecycle) | Updated: 2026-10-05 (Asia/Jakarta) | Custodian: Planning Agent

## Historical P0 inspection and completed checkpoint

Historical initial inspection on 2026-10-04: branch `main` was unborn (no commits); index was empty. Existing untracked `.gitignore` and AICWDF source were preserved. Origin was `https://github.com/yusufarst/PRANATA-UNY.git` and remains unchanged.

Historical AUTH-001 permitted working-tree documentation edits only. [AUTH-002](APPROVAL_RECORDS.md#auth-002) permitted approval recording and one initial P0 checkpoint following [APPR-001](APPROVAL_RECORDS.md#appr-001); that action is **COMPLETED**. Published checkpoint: `f6d889100306a63a5bd391da4310346fc427d4d8`, commit message `docs: finalize P0 governance`. At post-publication inspection on 2026-10-04, branch `main`, local HEAD, origin/main and the live remote main ref matched; remote verification **PASS** and starting working tree clean. The initial publication is no longer pending.

## Historical bounded documentation-state closure

[AUTH-003](APPROVAL_RECORDS.md#auth-003) permitted the completed normal closure commit `3e7d6a21287410ff977db9db996154043e1c8926`, `docs: close P0 publication handoff`, normal publication to unchanged `origin/main`, verification and stop. It granted no later-phase authority; its conditions/history below remain historical and confer no new Git action.

Verify `git status --short`, `git ls-files`, `git diff --cached --name-only` and `git check-ignore` for every raw source. Never force-add ignored inputs. Before commit require bounded integrity PASS, refreshed manifest, careful staged review and exact staged file bytes (per-command `core.autocrlf=false`, without persistent configuration change). After normal push require local HEAD = origin/main = live remote main at the new closure commit, the original P0 checkpoint retained in history, APPR-001 intact, consistent post-publication documentation, no raw sources/Tasks/application and clean working tree except ignored local references. Stop and report failed verification; P1 requires separate Owner authorization.

## Future branch and environment baseline

After authorized planning/freeze/READY Tasks, use bounded `codex/*` branches by default for feature/fix work, or an explicitly Owner-selected branch. Proposed AICWDF flow: work branch → PR/CI → `develop` → staging/verification/UAT → PR with required CI/approval → `main` → controlled production release. Protect `main`; no direct production experimentation. `develop`, CI, branch protections and environments have not been created or verified.

Environments are conceptual local/development, isolated staging, and controlled production. Local/staging use synthetic or approved anonymized data; credentials/configuration stay separate. Exact infrastructure, deployment tooling/providers, backup and release permissions remain P9/P10 decisions. AICWDF's Coolify/Hostinger/Cloudflare examples are not a claim that PRANATA infrastructure exists or that providers have been selected.

Do not alter remote configuration, Git identity, protected branch settings, repository visibility or deployment infrastructure merely to satisfy a template. Later commit/release actions follow their explicit authorized scope. Preserve unrelated working-tree changes; do not clean/reset to simplify review. Do not import MULTIPLECORP attribution or other owner-specific rules into PRANATA without a PRANATA decision.

## Historical P1 preparation and completed approved checkpoint

AUTH-004 P1 baseline verified on 2026-10-04: main, clean start, HEAD = origin/main = live remote main at `3e7d6a21287410ff977db9db996154043e1c8926`; origin unchanged. Read-only remote check required a restricted-network retry; it succeeded. Initial commit and closure authority are exhausted. Under historical AUTH-004, no stage/commit/push was permitted; the reviewed working-tree candidate and ignored raw sources were preserved. Keep diffs/links bounded, record new versus modified files, review source/secret safety, preserve Owner identity/remote/config and update handoff. P0 manifest remains a historical published snapshot; use a separate P1 candidate manifest. [APPR-002](APPROVAL_RECORDS.md#appr-002) now approves that exact candidate; [AUTH-005](APPROVAL_RECORDS.md#auth-005) supersedes only the preparation publication restriction for one approved P1 checkpoint.

AUTH-005 permits intended P1/approval/lifecycle artifacts only, after all material integrity checks PASS. Stage explicit paths with per-command `core.autocrlf=false`, verify index bytes equal the manifest/worktree, then commit `docs: finalize P1 product definition` using the existing configured Owner author/committer only and no attribution metadata. Push normally with `git push origin main`; do not amend P0, force push or change upstream/remote/identity. Verify local HEAD = origin/main = live remote main, retained P0 ancestry, remote approval/status/product/source-safety/phase-boundary content, and clean worktree except ignored references. Publication completion belongs to live Git/session evidence. AUTH-005 expires after verified publication; stop. Exact next safe action: Owner separately authorizes P2 — Domain Model & Business Rules.

## Historical P2 preparation boundary

Historical [AUTH-006](APPROVAL_RECORDS.md#auth-006) authorized P2 planning only; preparation is completed and its restrictions below are superseded only for the APPR-003/AUTH-007 checkpoint. P0/P1 are approved and published; starting main HEAD = origin/main = live remote main `8881c047f24451b30754b19fe0d1cf96b9078f90`, clean worktree, empty index, unchanged origin. AUTH-005 checkpoint is exhausted and the preceding conditions remain historical only.

Leave the candidate in the working tree for exact-manifest independent review. **No staging, commit or push**, no branch/identity/remote change, no force-add of ignored sources. The P2 manifest includes all Git-eligible candidate files except itself, with created/amended/preserved dispositions and byte hashes. P0/P1 manifests are untouched historical snapshots; do not refresh them for P2 governance amendments. Verify HEAD/index/raw ignore safety and reviewable file diffs before reporting. Terminal P2 VERIFYING; P3–P11 remain not authorized.

## P2 approved checkpoint — AUTH-007

[APPR-003](APPROVAL_RECORDS.md#appr-003) approves only the exact reviewed pre-approval manifest: **14,976 bytes / `d1f40247e83f8e5ddf419ab4dcc0eeca3e9bf64c3fafe7fff32739636f4450f3`**, verified before edits with all entries/coverage intact. P2 DONE — APPROVED; AUTH-006 historical/completed; P3–P11 not authorized. [AUTH-007](APPROVAL_RECORDS.md#auth-007) authorizes the single checkpoint only.

After all bounded material integrity checks PASS, stage explicit intended P2/approval lifecycle paths using per-command `core.autocrlf=false` without persistent configuration changes. Verify staged bytes equal the manifest/worktree; exclude raw inputs/unrelated files. Create one normal `docs: finalize P2 domain model` commit using the existing configured Owner author/committer identity only, without attribution metadata. No amend, force push, remote/identity/topology change or force-add. Push normally with `git push origin main`.

Then independently verify local HEAD = origin/main = live remote main, P0/P1 ancestry, exact commit message/identity, remote P2 DONE/APPR-003 and reviewed identity, current manifest/coverage/domain counts/qualifications, all 20 OPEN GAPs, no raw sources/application/Task artifacts and a clean worktree except ignored references. Resulting SHA and live publication evidence belong to Git/session/final report. **AUTH-007 applies only until this checkpoint is normally pushed and remote verification passes; it then becomes HISTORICAL / COMPLETED automatically, with no continuing phase/Git authority and no separate closure commit. After verified publication, current active phase authorization is NONE.** A failed material check blocks commit/push; a publication failure must be reported accurately. Exact next safe action after verified checkpoint: **Owner separately authorizes P3 — Workflows, Routes & Interactions**. Stop after P2.
