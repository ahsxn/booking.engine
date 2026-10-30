# Laravel Boost project rules — owner-approved conventions to record

This document is the human-readable **source specification** for the v1
application's Laravel coding conventions (decisions Q73–Q76). It is not a
replacement for `docs/V1_SPEC.md`, the engineering design, or the mandatory
approval workflow in `AGENTS.md`.

In the owner's **existing** Laravel React/Inertia starter repository, use
Laravel Boost's `record-rule` MCP tool to create rules under `.ai/rules/` and
maintain `.ai/rules/index.md`. **Do not hand-create the index:** Boost uses
its index to discover which rules apply to which code paths. Show the exact
recorded rules and glob coverage to the owner before implementation. Commit
the `.ai/rules/` directory to Git. If `record-rule` is unavailable, stop and
explain the blocker; do not guess an equivalent integration.

Project rules are scoped coding conventions. Global product requirements,
payment safety guarantees, scope and approval gates remain authoritative in
`docs/V1_SPEC.md`, `docs/ARCHITECTURE.md` and `AGENTS.md`. A generated rule
cannot silently override those documents.

## Proposed rules and file applicability

Record these as concise, file-scoped rules. Names below are suggestions:
Boost owns the final filenames and index entries.

| Suggested rule | Applicable glob(s) | Convention to record |
| --- | --- | --- |
| Application structure | `app/**` | Use standard Laravel directories grouped by feature: `app/Http/Controllers/Checkout`, `app/Http/Requests/Checkout`, `app/Actions/Checkout`, `app/Data/Checkout`, `app/Models`, `app/Values`. Do not introduce a parallel `app/Features` architecture. Avoid speculative repository, service, interface or wrapper layers; require an approved justification for any new abstraction. |
| Controllers | `app/Http/Controllers/**` | One HTTP endpoint per invokable `__invoke` controller. Keep controllers thin: receive validated input, call an action and return an HTTP response or Inertia page. No business transactions in controllers. |
| Form requests | `app/Http/Requests/**` | Each mutating endpoint uses a dedicated Form Request for validation and applicable guest-checkout authorization. Request validation does not replace transaction-time business, capacity or price checks. |
| Actions | `app/Actions/**` | Business operations live in focused action classes with explicit typed contracts. Actions own business invariants, transactional mutations, errors and external-call boundaries; reuse the same action where HTTP, webhook jobs and reconciliation perform the same operation. No Stripe network calls while holding inventory database locks. |
| Data transfer objects | `app/Data/**` | Use typed, immutable DTOs where they clarify the HTTP-to-domain and action-result boundaries. Do not pass unchecked request arrays through domain operations. Do not generate DTOs for trivial data without a useful boundary. |
| Value objects | `app/Values/**` | Use value objects for meaningful concepts with enforceable invariants, such as money in integer minor units and currency. Do not wrap every scalar mechanically. |
| Eloquent models | `app/Models/**` | Keep models focused on persistence, relations and simple model behaviour. Complex orchestration and multi-record transactions belong in actions. |
| Asynchronous entry points | `app/Jobs/**`, `app/Console/Commands/**` | Jobs and scheduled commands orchestrate execution, delegate business transitions to actions and tolerate retries. Do not duplicate confirmation, release or reconciliation rules in jobs. |
| Database changes | `database/migrations/**` | Specify and use PostgreSQL constraints, foreign keys and indexes that protect data invariants. Keep authoritative inventory/accounting consistent with the approved transaction design; never rely solely on browser validation. |
| React/Inertia | `resources/js/**` | Use server-routed Inertia pages with React and TypeScript, typed server props, accessible UI and feature-cohesive components. Client-side state is never authoritative for inventory, prices or payment status. Do not add a separate SPA routing system. |
| Backend tests | `tests/**` | Test the business behaviour and failure paths of each action. Capacity contention tests must use real PostgreSQL and separate connections rather than SQLite or transaction mocks; use deterministic injected failures and Stripe test mode where appropriate. |

## Recording procedure and approval

1. Verify Laravel Boost is installed/configured in the existing repository
   and that its `record-rule` MCP tool is actually available.
2. Record the conventions above using that tool, preserving a helpful and
   appropriately scoped set of files. Prefer clear individual rules to a
   duplicated copy of the entire design specification.
3. Inspect the generated `.ai/rules/index.md` and the rules it references.
   Check that relevant globs cover the intended code paths, especially jobs,
   migrations, TypeScript pages and tests.
4. Show the owner the full rule diffs and flag any conflict with existing
   Boost guidelines, code or product requirements. **Stop for review.**
5. After explicit acceptance, commit `.ai/rules/` and the planning documents.
   Only then request the **plan-only** pass for Slice 0.

Avoid `infer-conventions` at this stage: the repository currently consists of
Laravel starter scaffolding, which does not demonstrate the owner's preferred
architecture. The explicit agreed decisions above are the correct inputs.

Official reference: https://laravel.com/framework/docs/boost#project-rules
