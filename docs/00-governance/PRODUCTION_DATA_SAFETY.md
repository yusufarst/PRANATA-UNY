# Production data safety

Status: APPROVED | Updated: 2026-10-04 | Owner: Planning Agent | Phase: P0

This document owns the agent production-data boundary (AICWDF §29–§31). PRANATA has no application or production database established by this P0 work. Future security and operating procedures remain owed by P5, P9, and P10.

## Zero-touch default

Ordinary coding, planning, review, and maintenance agents must not directly connect to, query, mutate, or administer a production database, its credentials, or its volumes. A generic instruction such as “fix production” does not grant production-data access.

The future Task/maintenance default is:

```text
DB CHANGE: NONE
DIRECT PRODUCTION DB ACCESS: FORBIDDEN
```

Reproduce defects in development or staging with synthetic or sanitized data. Keep production credentials and secret-bearing exports outside ordinary agent context, tracked files, logs, screenshots, and chat. Do not request production secret files as a routine debugging shortcut. If sensitive content is encountered unexpectedly, cease reproducing it and report only a safe description/location for Owner or authorized operator handling.

## Forbidden shortcuts

Production must never be reset, wiped, refreshed, reseeded, truncated, dropped/recreated, or bulk-deleted to solve ordinary application defects. Do not run destructive ORM/database commands, delete database volumes or production storage, restore ad hoc over live data, change credentials/ownership ad hoc, or manually edit historical records to conceal a bug.

Examples of prohibited production actions include `migrate:fresh`, `migrate:refresh`, `db:wipe`, destructive seeds, `TRUNCATE`, `DROP DATABASE`, `docker compose down -v`, and `docker volume prune`. Listing them here is a prohibition, not an execution instruction.

## Controlled future evolution

After the planning/execution gates are met, an agent may design and prepare a necessary migration, test it outside production, document risk, and prepare rollback/recovery. The agent must not execute it directly against live production data. The controlled path requires an explicit need, reviewed migration design, staging rehearsal, verified backup, recorded approval, an authorized controlled executor, and post-change verification (AICWDF §29.3).

P9 must define environments, access responsibility, backup, retention, restore, and restore verification. P10 must define controlled migration execution, cutover, rollback/recovery, release approval, and the `DB CHANGE: NONE / APPROVED CONTROLLED MIGRATION` declaration. Do not infer a production operator, recovery target, backup topology, or hosting service from framework examples or the structural reference.

Historical data and its meaning remain durable state. Future remediation must preserve traceability and use approved correction/reconciliation semantics; detailed rules require later domain/workflow validation. A migration rollback alone is not proof of backup recovery.

## Local reference boundary

The permission to inspect ignored `reference-inputs/` is local evidence-reading permission. It does not authorize live system access, raw-data publication, credential reuse, or import into an application. Document only safe provenance and sanitized findings under [EVIDENCE_POLICY](EVIDENCE_POLICY.md); never copy plaintext passwords or raw sensitive files into tracked paths.
