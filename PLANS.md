# Ticketing v1 — vertical-slice implementation plan

Status: **Slice 0 accepted; later slices are proposed and require their own approvals.**

**Roadmap revision (owner-agreed, 30 October 2026):** Split the original
Slice 1 into two separately planned and accepted slices: **1A — Event domain
and database** and **1B — Read-only Laravel/Inertia integration**. Slice 2
remains the Luma-inspired browsing experience. The scopes and numbering of
Slices 3–8 remain unchanged. This is a roadmap amendment, **not approval to
implement either new slice**.

**Operating rule:** every slice has **two distinct approvals**. First Codex
inspects the current repository and proposes a detailed implementation plan
without changing application code. The owner reviews and approves that plan.
Then Codex implements only the approved design, demonstrates and tests it, and
waits for owner acceptance before moving on. The product contract in
`docs/V1_SPEC.md` always governs this plan; `AGENTS.md` defines the detailed
approval and verification rules; `.ai/rules/` contains Laravel Boost project
coding conventions, recorded from `docs/BOOST_PROJECT_RULES.md`. Use
`docs/SLICE_PLAN_TEMPLATE.md` for every plan.

## Pre-Slice 0 — Record and approve Laravel Boost project rules

This is a **documentation-only setup gate**, before the first implementation
plan; it does not authorize application code.

1. In the owner's existing Laravel React/Inertia starter repository, inspect
   the installed Laravel Boost configuration and preserve its generated
   guideline block in `AGENTS.md`.
2. Read `docs/BOOST_PROJECT_RULES.md`. Use Boost's `record-rule` MCP tool to
   record the agreed application conventions under `.ai/rules/`, maintaining
   its generated `index.md`. Do not manually copy these rules into a
   Boost-managed index or replace the starter's `AGENTS.md`.
3. Present the rule files, glob coverage and index for the owner's review.
   Stop until the owner accepts the rules. Commit them with the other planning
   docs; do not edit application code, scaffold, migrate or install packages.
4. Only then ask Codex to plan Slice 0 using the current repository and the
   approved project rules. Slice 0 still requires its own **Gate A** approval.

## How to execute each slice

1. **Plan-only pass:** inspect the repository and authoritative documents.
   Create or revise `docs/slice-plans/NN-name.md` using the template. Specify
   precise migrations, indexes, classes, responsibilities, action and DTO
   contracts, controller/request flow, pages and props, transaction boundaries,
   locking, retries/idempotency and tests as applicable. Include explicit
   non-goals and uncertainties. No application code, scaffolding or dependency
   changes at this stage.
2. **Owner reviews Gate A:** challenge the classes, migration design, business
   boundaries, tests and any abstractions; iterate on the plan until the owner
   explicitly approves this slice's implementation plan.
3. **Implement and verify:** build only the approved work, following the
   applicable Laravel Boost `.ai/rules/` project rules and `AGENTS.md`.
   Pause for approval before material
   deviations, including schema, dependency, action-boundary, transaction or
   idempotency changes. Show actual test outputs and a working vertical demo.
4. **Owner reviews Gate B:** accept or request changes. Do not begin the next
   slice until the current implementation has been accepted.

The slices below are **roadmap scopes**, not pre-approved implementation
designs. Their technical details are intentionally decided just in time, with
visibility of the code produced by earlier slices.

## Why vertical slices

Each slice should produce a usable, reviewable part of the *actual* Laravel
application, not an unaudited giant build. **Slice 1A is intentionally a
backend-only foundation**, accepted through database inspection, real
PostgreSQL tests and repeatable seeding rather than a customer-visible demo.
Slice 1B then proves the smallest end-to-end Laravel/Inertia data path. Slice 2
builds the polished public browsing experience. Atomic holds, checkout and a
complete verified payment path follow. Reliability is tested from the first
capacity-changing slice and hardened at each step.

## Slice 0 — Audit and prepare the existing Laravel application

**Customer-visible result:** The owner's existing Laravel React/Inertia
application runs as a minimal, testable v1 foundation without an in-scope
customer authentication workflow.

**In scope:**

- **Inspect and reuse the existing Git repository**, installed React/Inertia
  starter kit, Laravel Boost integration, framework version and dependencies.
  Do not create a second application or presume the starter is Laravel 13.
- Propose the exact safe cleanup of unused starter authentication, settings,
  dashboard and login/registration features, explaining each deletion or
  retained dependency in the Slice 0 plan before touching code.
- Audit the development environment; propose Docker Compose for PHP/Composer,
  Node/Vite, PostgreSQL, web, queue worker and scheduler as needed, with
  environment-example files and no actual secrets. Account for working
  existing infrastructure rather than replacing it automatically.
- Configure PostgreSQL for application and tests, unit/integration test runner,
  formatting, TypeScript checking, JS build, and a documented dev start path.
- Ensure Boost project rules, `AGENTS.md` and docs are tracked in the actual
  Git repository without overwriting the Boost-generated guideline block.

**Exit evidence:** From a clean environment, run the app, Inertia page, migrations,
PostgreSQL connection, type check, asset build, and first test successfully.
**Forbidden:** User accounts, admin pages, auth workflow, Redis or live Stripe.

## Slice 1A — Event domain and database

**Detailed plan:** `docs/slice-plans/01a-event-domain.md` (revision 1,
30 October 2026). **Status: Implementation awaiting Gate B acceptance;
Gate A approved explicitly on 30 October 2026.** Owner approved content/date policy, timezone validation
boundary, sold-out fixture deferral and started/past classification.
Slice 1B remains unstarted.

**Implementation evidence (30 October 2026):** all 15 approved manifest files
delivered; 84 focused PostgreSQL tests passed; full regression 128 tests /
575 assertions passed. PHP formatting, PHPStan, JS formatting/lint, TypeScript,
asset build and diff checks passed. Exact commands, resolved intermediate
failures, demo and remaining limitations are recorded in the slice plan.
No persistent development database migration or seeding was performed.

**Engineering result:** PostgreSQL contains a well-constrained, tested and
repeatably seeded catalogue of events and ticket types. This foundation slice
has **no new customer-visible page** and is intentionally separate from
Laravel HTTP and React work.

**In scope:**

- Create the `events` and `ticket_types` migrations with precise columns,
  foreign keys, uniqueness, checks and relevant indexes; define the event
  schedule in UTC alongside an event-local IANA time zone.
- Add lean Eloquent `Event` and `TicketType` models and their relationships.
- Model one organizer's original event content, public venue details, image
  references, one-off upcoming/past events and independently capped general-
  admission ticket types; store GBP prices as integer minor units.
- **Store `ticket_types.capacity` now** because capacity is a fundamental
  property of a ticket type. Require positive capacity unless an explicitly
  approved alternative is needed.
- **Defer `reserved_count`, `confirmed_count`, inventory movements, hold locks
  and oversell invariants to Slice 4.** They belong to the approved atomic
  hold and payment-capacity design, and can be introduced by a dedicated
  migration before any real purchases exist. Slice 1A must not invent paid
  orders or pre-populate accounting counters to simulate sales.
- Use deterministic, safely rerunnable seeders with meaningful original demo
  data and factories where they make test setup easier. Seed replay must not
  create duplicates or overwrite future customer transactions.
- Test migrations/constraints, relationships, seed replay, price/currency,
  event-local time zones, UTC schedules and event data against real PostgreSQL.
- Explicitly resolve or flag the need for a **sold-out demo**: do not label
  tickets sold out by inventing confirmed purchases without orders. If a
  distinct sales-closed/unavailable state is needed for examples, propose its
  semantics for approval rather than conflating it with sold-out accounting.

**Exit evidence:** On a disposable test PostgreSQL database, migrations and
repeatable seeds produce the intended catalogue; exact database constraints,
relationships and automated tests pass. Verify seed replay without a
destructive migration against the persistent development database.

**Forbidden:** Controllers, Form Requests, Inertia pages/props, React UI,
orders, held/confirmed counters, capacity reservation logic, checkout, admin
CRUD, Stripe, or copied Luma data/assets.

## Slice 1B — Read-only Laravel/Inertia event integration

**Customer-visible result:** A guest opens a simple public event listing and
sees real upcoming/past events and ticket information supplied by Laravel
from the seeded PostgreSQL database.

**In scope:**

- Add an invokable read-only Laravel controller and focused action/DTO
  contracts only where they clarify the query-to-Inertia boundary; follow
  the approved project rules without speculative repository/service layers.
- Query and order upcoming/past events, including event-local date/time
  labels, venue information, GA ticket type names, fixed prices and public
  availability information that is actually supported by the current schema.
- Return typed Inertia props and render a **minimal** React/TypeScript listing
  using the current server-routed Inertia setup; no independent API or SPA
  routing framework.
- Test guest HTTP access, query ordering, time-zone display, server props,
  TypeScript checks and a working read-only browser flow.
- Avoid claiming precise remaining capacity or genuine sold-out inventory
  until the authoritative reservation/confirmation accounting exists. Handle
  unavailable or past events honestly; defer any unresolved sold-out
  representation decision for the owner's approval.

**Exit evidence:** Real events appear in the browser from PostgreSQL through
Laravel and typed Inertia props, with focused HTTP/frontend tests passing.

**Forbidden:** Polished calendar/hero/card design (Slice 2), hold or checkout
mutations, inventory accounting, admin CRUD, Stripe or copied Luma assets.

## Slice 2A — Event browsing behaviour and pagination

**Customer-visible result:** Customers can browse Upcoming and Past
events in separate views, with Upcoming selected by default. Events
load progressively as customers scroll, without downloading the
entire catalogue.

**In scope:**

- Replace the combined Upcoming/Past listing with two separate views.
- Default to Upcoming when opening the event browsing page.
- Allow customers to switch between Upcoming and Past.
- Preserve the selected view in the URL and browser history.
- Upcoming events appear in ascending start-time order.
- Past events appear in descending start-time order.
- Implement server-side pagination using PostgreSQL.
- Prefer cursor pagination with deterministic ordering by
  event start time and ID.
- Implement infinite scrolling to progressively retrieve and
  append additional events.
- Ensure pagination works correctly with Inertia navigation
  and the existing React/TypeScript architecture.
- Investigate the installed Inertia version's native
  infinite-scrolling capabilities before proposing custom
  implementations or additional dependencies.
- Preserve the existing typed DTO/Inertia data boundaries,
  extending them only where necessary.
- Handle loading, empty, error and end-of-results states.
- Prevent duplicate or missing events during pagination.
- Preserve correct event-local date/time presentation.
- Test pagination boundaries, ordering, view switching,
  browser history, query efficiency and failure recovery.
- Test with more events than a single page can contain.

**Exit evidence:**

- Upcoming is the default view.
- Switching to Past displays past events only.
- Each view uses the correct chronological ordering.
- Scrolling loads subsequent pages without duplicates.
- Database queries retrieve bounded pages rather than
  loading the entire event catalogue.
- Browser Back/Forward navigation works correctly.
- Loading, empty, error and end-of-results states work.
- Backend and React tests pass.
- Desktop and mobile browser QA passes.

**Forbidden:**

- Final Luma-inspired styling or visual design.
- Calendar/month navigation.
- Event detail pages.
- Ticket selection or checkout.
- Capacity accounting, holds or Stripe.
- Admin functionality or new dependencies without approval.


## Slice 2B — Luma-inspired event calendar and visual design

**Customer-visible result:** A polished, responsive event browsing
experience inspired by the layout hierarchy and interactions of
https://luma.com/venicebookclub.

**In scope:**

- Create an original organizer identity and visual presentation.
- Build the organizer hero and cover section.
- Implement the calendar layout and month navigation.
- Display events using image-led cards and clear event details.
- Show event-local dates, times, organizer and venue information.
- Integrate the Upcoming/Past views and infinite scrolling
  implemented in Slice 2A into the finished design.
- Ensure calendar dates and month grouping respect each event's
  IANA timezone.
- Use original branding, text, imagery and seeded event data.
- Maintain responsive layouts for desktop and mobile.
- Provide keyboard navigation, visible focus indicators,
  semantic markup and accessible interactions.
- Implement appropriate empty states and visual feedback.
- Test calendar month/year boundaries, event grouping,
  responsive behaviour and navigation.
- Perform human visual QA against current reference screenshots
  at desktop and mobile sizes.

**Exit evidence:**

- The browsing page follows the agreed Luma-inspired
  information hierarchy and interaction patterns.
- Calendar month navigation works correctly.
- Upcoming/Past switching and infinite scrolling remain functional.
- Event cards display correct information from PostgreSQL.
- Event-local dates are grouped into the correct calendar months.
- Desktop/mobile visual QA and accessibility checks pass.
- React, TypeScript, backend regression and build checks pass.

**Forbidden:**

- Event management or admin functionality.
- Event detail pages and ticket selection, which belong to Slice 3.
- Checkout, holds, inventory accounting or Stripe.

## Slice 3 — Event detail and ticket selection

**Customer-visible result:** Clicking an event opens its detailed page; users
select multiple GA ticket types/quantities and see a server-priced GBP total.

**In scope:**

- Original event page hierarchy and layout inspired by the same browsing
  reference: cover, title, event-local schedule, venue, description, ticket
  selector and Available/Sold out indicators.
- One-event selection, maximum 10 combined tickets, no exact availability
  counts, accessible validation and navigation.
- Read-only validation and total preview; no capacity guarantee until a
  successful Checkout click in the next slice.

**Exit evidence:** Demo selections, mobile views and validation tests.

## Slice 4 — Atomic hold, pending order and checkout entry

**Customer-visible result:** Clicking **Checkout** reserves selected tickets
for 10 minutes, creates the pending order, then opens the timed checkout page.

**In scope:**

- `orders`, `order_items`, immutable price snapshots, unguessable checkout
  capability and request-key uniqueness.
- Transactional ticket counters, deterministic row locking, all-or-nothing
  multi-type reservations, 10-minute hold, initial reference and exact expiry.
- Same-checkout retry after network loss, conflicting-payload error, explicit
  unstarted cancellation, expired-hold enforcement and release.
- Concurrent PostgreSQL test harness *before* Stripe is introduced.
- Checkout page shell showing ticket summary/reference/countdown, with no
  enabled Pay action in this slice.

**Exit evidence:** 1,000 competitors for 20 seats produces exactly 20 holds;
duplicate keys, crash after commit, expiration and cancellation tests pass.
**Forbidden:** Payment processing without protection, long database locks,
  copying browser totals into orders.

## Slice 5 — Buyer details and deferred Stripe Elements UI

**Customer-visible result:** On a valid held checkout, customers can enter
name, email and secure card details while the countdown runs.

**In scope:**

- Name/email validation on checkout, the Stripe Payment Element mounted in
  deferred mode before a PaymentIntent exists, 3DS-capable card UI and error
  presentation, accessible disabled/loading states.
- Timer and server-expired state, same-tab refresh recovery, original order
  reference and prices. No login/email or advanced anti-hoarding.
- Non-charging client-side form validation; **Pay remains feature-gated** until
  the next slice implements the complete trusted charge + webhook path.

**Exit evidence:** Browser can validate customer/card fields and recover a
hold after refresh without making any charge.

## Slice 6 — Complete Stripe pay-to-confirm path

**Customer-visible result:** A test-mode card payment succeeds and Stripe.js
immediately shows payment success. A verified webhook job updates the
**existing** order to confirmed.

**In scope:**

- `payment_attempts` with durable Stripe idempotency and one unresolved
  payment path per order.
- Commit buyer/protection state **before** Stripe API calls, create/reuse
  PaymentIntent outside DB locks, persist Intent association before releasing
  the client secret.
- Stripe.js confirmation with 3DS support, immediate success and definite
  decline handling; no duplicate Pay action while unknown.
- Signed webhook endpoint, durable PostgreSQL queued job, worker, idempotent
  transactional reserved-to-confirmed conversion, queue retries/failed jobs.
- Deterministic Stripe fake tests plus test-mode happy-path and webhook tests.

**Exit evidence:** One complete Stripe test purchase with expected DB order,
protected capacity and confirmation; duplicate webhooks/Pay/crash tests pass.
**Gate:** Do not enable public live charging; subsequent recovery slice is
required before v1 is considered safe for real customers.

## Slice 7 — Timeouts, abandonment and independent reconciliation

**Customer-visible result:** Abandoned checkout capacity returns safely;
payments with unknown outcomes do not prompt customers to pay twice.

**In scope:**

- Scheduled 1-minute expiry sweep, precise deadline on capacity access.
- 15-minute normal protected payment window followed by Stripe status
  resolution/safe cancellation. Preserve capacity on uncertainty.
- Scheduled 1-minute payment reconciliation starting around 60 seconds after
  uncertainty; backoff and idempotent confirmation, cancellation or release.
- Retryable technical full refunds when an anomalous capture cannot be
  fulfilled; durable refund ledger and fault injection.
- Operational CLI inspection/reconcile commands and diagnostic logs.

**Exit evidence:** All C, D, E and F failure tests in `ACCEPTANCE_TESTS.md` pass,
including a missed webhook, repeated worker crash, Stripe outage and
cancellation race. A human can reproduce these scenarios in dev.

## Slice 8 — Adversarial verification, quality and handoff

**Customer-visible result:** Final polished browse-to-payment flow is usable
and behaves safely under fault injection.

**In scope:**

- Execute complete real-PostgreSQL concurrency suite, Stripe test-mode web
  integration, 3DS, duplicate/out-of-order webhook and payment/refund recovery.
- Independent adversarial review specifically hunting oversell, double charge,
  false success and early release; fix and add regressions for each finding.
- Responsive/accessibility polish, local bootstrap docs, health checks,
  operations instructions, seed assets and clear v1 limitations.
- Compile verification report: commands, exact outputs/results, measured
  concurrency invariants, residual risks and future-scope exclusions.

**Exit evidence:** All acceptance tests green, full dev demo, no unverified
critical invariants, explicit product-owner acceptance. No deployment.

## Slice tracking template

For each slice maintain:

```
Slice: N / descriptive title
Status: Not started | Planning | Plan awaiting approval | Plan approved |
        Implementing | Implementation awaiting review | Accepted | Blocked
Approved scope:
Detailed plan document:
Plan approval and date:
Customer demo:
Database/payment invariants touched:
Tests to add before implementation:
Verification commands and observed results:
Known risks / deviation requests:
Implementation acceptance and date:
```

## Planning issues to resolve before Slice 0

- The original planning workspace was empty, but the owner has since reported
  an existing Laravel React starter application with Laravel Boost installed.
  Inspect that actual repository before planning changes; do not assume its
  versions, database setup, existing classes or development tooling.
- Confirm the architecture's exact schema indexes, global row-lock order, and
  response-loss recovery approach in design review before implementing Slice
  4. These are correctness prerequisites, not optional cleanup tasks.
- The product owner's historical broad design decisions are consolidated in
  `V1_SPEC.md`; do not attempt to re-open v2 topics merely because the official
  Laravel starter kit includes them by default.
