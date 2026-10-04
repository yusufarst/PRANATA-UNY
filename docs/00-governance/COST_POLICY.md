# Cost policy

Status: APPROVED | Updated: 2026-10-04 | Owner: Planning Agent | Phase: P0

This document owns cost selection and approval rules (OD-18; AICWDF §4C). It does not select a hosting provider or create a production dependency.

## Production cost boundary

Target additional recurring production cost is **near zero, with 0 as the selection target**, outside client-funded infrastructure and domain. Quality, simplicity, reliability, and safe operation remain required. No paid recurring production service or production AI dependency is approved by this P0 session.

Choose capabilities in this order:

1. Existing project capability.
2. Native framework or platform capability.
3. Free open-source package.
4. Self-hosted open-source capability on existing funded infrastructure.
5. Reliable free-tier external capability with its risks documented.
6. Paid external service only after explicit Owner approval.

A free tier is not a guarantee of durable zero cost. Before making a critical external dependency, record quotas, expiry/pricing-change exposure, failure behavior, lock-in, data portability, and an exit path. Self-hosting also consumes infrastructure, storage, backup capacity, and operating effort; record material effects during the authorized planning phase.

## Paid-service decision gate

When a paid service is proposed, pause that dependent decision, complete independent authorized work, and prepare a reviewable proposal. Explain the requirement, compare useful native/free/open-source/self-hosted alternatives, state tradeoffs and estimated recurring cost with pricing provenance/date, and identify quota, data, exit, and operating implications. Obtain explicit Owner approval before making the service a requirement, installing a dependency that commits the project to it, or enabling billing.

Record the decision in [DECISION_LOG](DECISION_LOG.md), its approval in [APPROVAL_RECORDS](APPROVAL_RECORDS.md), and material stack/architecture effects through [CHANGE_CONTROL](CHANGE_CONTROL.md) and an ADR. A general planning request, available trial, or tool connection does not imply paid-production approval.

## Registry and later obligations

| Item | Current P0 state |
| --- | --- |
| New recurring production dependency | None |
| Paid production AI dependency | None |
| Approved paid-service exception | None |
| Hosting/domain provider and capacity | Not selected in P0 |
| Exact infrastructure operating estimate | Owed by P9, after authorization |

P9 must maintain the concrete cost inventory and any approved exception records. P10 must declare each release's additional recurring cost change as `NONE` or `APPROVED EXCEPTION`. P11 must include cost impact in every relevant Task. Development/planning tool availability is separate from permission to introduce a production service.
