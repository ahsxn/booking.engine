# Ticketing v1 — approved decisions consolidated for implementation review

Status: **Design draft for explicit sign-off; do not implement yet.**

## Product mission

Build a single Laravel + server-routed Inertia + React + TypeScript application
in which a guest can browse events, reserve a limited quantity of tickets for
10 minutes, pay by card using Stripe, and immediately see a trustworthy payment
success result. Capacity and payment correctness must survive contention,
application crashes, network failures, missing webhooks, worker retries, and
customer retries. This is not an Eventbrite or Luma clone.

## Binding invariants

1. For every ticket type, `reserved_quantity + confirmed_quantity <= capacity`
   at all committed database states. Neither quantity can be negative.
2. A checkout request with a reused idempotency key and identical payload
   yields the same pending order, reference, ticket lines, and original expiry.
   A different payload using that key is rejected.
3. Retrying payment for the *same logical checkout* cannot produce a second
   charge. A new checkout in another tab is an independent purchase, even if it
   happens to use the same email or ticket selection.
4. The database order becomes `confirmed` only through trusted backend
   processing of successful Stripe payment tied to an existing protected
   reservation with the correct amount and currency.
5. If payment could still succeed, its capacity cannot be released. A timeout
   is not proof of payment failure.
6. Stripe.js `PaymentIntent.status === succeeded` can immediately produce a
   customer-facing payment-success screen; it must **not** directly mark the
   PostgreSQL order paid.
7. Duplicate, unordered, or missing webhook deliveries and worker crashes are
   recoverable without asking the customer to pay again.
8. No event/price/capacity claim from the browser overrides the server's
   authoritative order lines and inventory state.

## Product surface and seeded content

- One organizer for v1. All demo events, ticket types, images, and venues are
  created with database seeders. No event management/admin UI.
- Reference for the *browsing information hierarchy and interaction*:
  https://luma.com/venicebookclub (inspected 29 October 2026). Recreate the
  broad pattern of calendar identity/hero, month navigation, Upcoming/Past,
  image-led event list/cards, and event details; use original branding, text,
  event data, and assets rather than copied Luma assets or trademarks.
- Public event pages show title, description, cover, event-local date/time and
  clearly labeled time zone, venue, available ticket types, price, and
  Available/Sold out state. Venue details are public.
- Browsing is read-only, not a promise that capacity will still exist on Pay.
  Do not expose exact remaining-ticket counts.
- One-off scheduled events; calendar month navigation and upcoming/past views.
  No search, maps, social features, or waitlist in v1.
- Multiple general-admission ticket types per event, independently capped;
  event capacity is the sum of ticket-type capacities (no extra global cap).
- Only fixed-price paid tickets, one event per checkout, at most 10 units
  total per order, with an all-or-nothing selection across ticket types.
- Single merchant and GBP currency. Displayed totals are final; no extra
  checkout fees. Use integer minor units in the backend.
- New holds cannot start once the event begins, but an existing legitimately
  started hold/payment may finish.

## Customer journey

### Browse and select

1. Customer opens the calendar or event detail page; no login is required.
2. Customer selects ticket type(s) and quantity and clicks **Checkout**.
3. Laravel atomically creates one pending order with price/quantity snapshots
   and a protected 10-minute capacity hold. Name and email are **not** needed
   to create the hold. An immutable human-readable booking reference is
   generated now.
4. Customer is routed to the held checkout, showing the ticket summary, exact
   expiry/countdown, purchaser name and email, Stripe Payment Element and Pay.

### Hold and edits

- The 10-minute deadline starts at the committed hold creation, not when a
  ticket selection changes or a countdown first renders. The server clock is
  authoritative; the frontend countdown is advisory.
- Order ticket types, quantities and unit prices are immutable after hold
  creation. To change them, cancel the unstarted order and request a new hold;
  previously held capacity is no longer guaranteed.
- An unstarted pending checkout can be explicitly cancelled and its capacity
  released. A checkout with payment underway cannot be cancelled by the
  customer until a definitive safe outcome is known.
- The same checkout recovers after a lost response or same-tab refresh. A new
  checkout in another tab is independent, with a distinct logical key.
- No requirement for a cross-tab one-active-hold-per-browser policy. General
  HTTP request rate limits remain; advanced anti-hoarding rules are v2.

### Pay

1. The customer enters name, email and card details while the hold is active.
2. On **Pay**, the frontend validates Stripe Elements. Laravel validates the
   purchaser details and exact original deadline, saves the buyer, and marks
   the order payment-protected in PostgreSQL **before** permitting Stripe
   confirmation. No buyer-supplied amount is accepted.
3. Display Stripe Payment Element *before* creating a PaymentIntent. On Pay,
   Laravel creates/reuses a PaymentIntent with automatic capture, GBP, exact
   saved order total, and stable Stripe idempotency; the browser receives the
   client secret for the existing order.
4. Stripe.js confirms the card payment, including 3D Secure when necessary.
5. If Stripe.js returns a PaymentIntent with `status = succeeded`, immediately
   display **Payment successful** and the already-created booking reference.
   Do not wait for Laravel's webhook or order database update.
6. If an attempt definitely fails, show the error. Permit a safe retry on the
   same order only while the *original* 10-minute deadline is still valid.
   After expiry, release safely and require a new checkout.
7. If the response is missing or ambiguous, disable Pay, automatically inspect
   the *existing* Stripe payment and local checkout state, and tell the
   customer payment may still succeed. Never encourage a second payment while
   the first may complete. This must work across same-tab refreshes.

### Payment protection and cancellation

- Once payment initiation is committed before the hold deadline, capacity
  remains protected after the original hold timer. The normal payment window
  is 15 minutes from initiation; it is **not** a forced release deadline.
- On apparently abandoned protected payments, attempt safe Stripe cancellation
  or authoritative status resolution. Only a confirmed cancellation or
  definitive final failure permits release; a successful payment confirms;
  Stripe/network uncertainty preserves the reservation, even beyond 15 minutes.
- If success is ever found without fulfillable capacity due to a technical
  inconsistency, do not mark a valid ticket purchase. Initiate an idempotent
  full technical refund and log/escalate the incident.

### Backend fulfillment

- The signed `payment_intent.succeeded` webhook normally triggers confirmation
  of the **existing** pending/payment-pending order. The browser's result is
  never an authorization to mark the order paid.
- Store the webhook processing job durably in PostgreSQL, return success to
  Stripe only after enqueue, then process in a Laravel worker.
- The worker verifies matching Stripe PaymentIntent, amount, currency, and
  protected order; in one PostgreSQL transaction it converts reserved capacity
  to confirmed capacity and marks the order `confirmed` with `paid_at`.
- Duplicated or out-of-order events cannot downgrade or confirm twice.
- Scheduled independent reconciliation can confirm a paid order if the
  webhook is lost. It never relies on customer interaction.

## Guest identity and confirmation boundaries

- No account, login, email verification, or per-attendee details. Name and email
  are collected only when paying; no emails are sent in v1.
- A strong unguessable checkout capability/token identifies the active guest
  order and is distinct from its display-only booking reference.
- Active-checkout recovery and refresh safety are required. Standalone
  post-purchase booking lookup, booking management, and cross-device recovery
  are out of scope. The on-screen success message/reference is the v1 customer
  confirmation.
- **Explicit accepted limitation:** if a buyer loses all checkout state and
  reference, v1 has no way to retrieve that confirmation; an entirely new
  independent checkout may result in another intentional or accidental
  purchase. Idempotency guarantees are scoped to the same logical checkout.

## Operational boundaries

- PostgreSQL is authoritative for inventory, orders, payments, and a database-
  backed Laravel queue. No Redis, separate payment microservice, or webhook-
  event-inbox table for v1.
- Scheduled expiration is direct database cleanup every minute, with precise
  expiry checks during capacity access. No per-hold expiration jobs.
- Scheduled reconciliation runs every minute, examining unresolved attempts
  older than approximately 60 seconds with increasing per-order backoff.
- Webhook jobs receive five retry attempts with backoff; persistent failures
  remain diagnosable and independent reconciliation provides further recovery.
- No organizer/admin UI, emails, QR/check-in, waiting room, purchase history,
  ordinary self-service refunds, ticket transfer, search/maps/social, or
  advanced anti-abuse restrictions in v1.
- Local development with Docker Compose, seeded demos, automated tests, logs,
  health checks, and command-line recovery tooling. No live production
  deployment during this phase.

## Acceptance contract

- At least 1,000 truly concurrent logical hold attempts against an inventory
  of 20 result in 20 holds and 980 capacity failures with zero overselling.
- Idempotency survives committed-but-response-lost crashes for order creation
  and Stripe PaymentIntent creation/reuse.
- Every boundary in the payment/webhook/release lifecycle is exercised by
  deterministic fault injection and real PostgreSQL integration tests.
- Customer-facing success does not wait for webhooks, but backend confirmation
  cannot occur without verified payment and protected capacity.
- Run Stripe test-mode integration and 3DS flows; never use live payments.

