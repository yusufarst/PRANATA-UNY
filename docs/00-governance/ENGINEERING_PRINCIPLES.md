# Engineering principles

Status: APPROVED | Updated: 2026-10-04 | Owner: Planning Agent | Phase: P0

This document owns engineering guardrails, not the future architecture, security model, accounting policy, or implementation. Authority and source classification belong to [SOURCE_OF_TRUTH](SOURCE_OF_TRUTH.md); explicit Owner direction is recorded in [DECISION_LOG](DECISION_LOG.md).

## Preserve project intent

- Keep the one integrated application and shared data ecosystem directed by OD-01–OD-03. Specialized workspaces do not become disconnected products.
- Preserve the generic hierarchical Organization Unit direction (OD-04–OD-05); do not silently replace it with faculty-only assumptions.
- Treat the OD-06 lifecycle as a candidate model. Official procurement steps, accounting meanings, handoffs, and approval paths require evidence and later validation.
- Preserve compact application roles while evaluating process responsibilities separately. OD-09 candidates are not an approved permission matrix; OD-10 preserves the Owner's Super Admin capability.
- Structured workflow data should support reusable document outputs where practical (OD-08). Legacy behavior is evidence, not automatic accounting authority (OD-14).
- Use the stack baseline in [TOOLCHAIN](TOOLCHAIN.md). Material changes require an ADR and Owner approval under [CHANGE_CONTROL](CHANGE_CONTROL.md).

## Bounded and maintainable work

Prefer existing capability, framework conventions, clear module boundaries, and the simplest justified mechanism. Isolate volatile external integrations when needed without creating speculative interfaces or services everywhere (AICWDF §15.1; OD-13). Detailed modules, database design, integration contracts, concurrency mechanisms, and API contracts belong to later phases.

Preserve established business semantics and historical traceability. Do not invent depreciation for arbitrary daily dates, capitalization thresholds, inventory codes, or correction policies. Record unresolved meaning in [GAP_REGISTER](GAP_REGISTER.md). Design and verification must eventually address approximately one million asset records (OD-16); browser-only full-dataset processing is prohibited. Server-side filtering, pagination, imports, reporting, indexes, reconciliation, and justified background processing remain later planning obligations rather than P0 designs.

## Authentication and authorization guardrails

OD-11 sets V1 local Laravel-native username/email and password authentication, sessions, recovery, throttling, and framework-native secure hashing. OD-12 keeps UNY domains, SSO, LDAP, and institutional identity/API authorization outside V1 dependencies. The local decision overrides the framework Google convenience-login default. Do not introduce Google OAuth or another provider into V1 without a later explicit Owner decision.

Authentication and authorization remain separate concerns. Workspace selection on login is a UX choice and grants no permission (OD-20); future backend enforcement must check actual access. The one-time detailed authentication and permission contracts are owed by P5. P0 neither selects an authentication package/version nor freezes the candidate roles.

## User experience and localization

User-facing UI defaults to plain Indonesian (`id-ID`), supports English (`en`), and includes a clear language switcher (OD-17; AICWDF §4B). Use localization for applicable user-facing copy; technical identifiers should remain English where practical. Preserve locale preference when practical and verify navigation, translations, and layout after language changes. The localization mechanism and page contracts belong to P7.

The application should make current status, action owner, waiting party, next step, and relevant evidence understandable (OD-19). Future UI must be accessible, mobile-first, responsive, adaptive, comfortably spaced, and practical for desktop operations. Use shadcn/ui before inventing primitives, and follow the future approved design reference/system. Important child flows require deterministic contextual back/cancel paths and sensible preservation of list state. Every visible clickable interaction must work or be intentionally disabled/hidden with an understandable state; dead interactions, broken routes, and silent form submission are release blockers (AICWDF §18, §25–§27).

## Evidence and safety

Success requires evidence, not confidence. Apply relevant static, automated, authorization, route, browser, localization, responsive, security, build, and performance checks when an application and authorized Tasks exist. Tests must verify the contract; do not weaken a valid test to claim success. Significant changes require targeted impact analysis and current documentation under [TOOLCHAIN](TOOLCHAIN.md). The future testing strategy belongs to P8.

The cost constraint in [COST_POLICY](COST_POLICY.md) and zero-touch production rules in [PRODUCTION_DATA_SAFETY](PRODUCTION_DATA_SAFETY.md) apply across agents and years of maintenance. Never reproduce credential values or internal sensitive source content in tracked docs. Follow [EVIDENCE_POLICY](EVIDENCE_POLICY.md) for sanitized provenance.

P0 verification is documentation verification only. No application, runtime success, security implementation, test suite, or production readiness is asserted by these principles.
