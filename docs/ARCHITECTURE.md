# Ticketing v1 — proposed engineering architecture

Status: **Proposed, not yet approved or implemented.** Product decisions are
owned by `V1_SPEC.md`. This document proposes how to enforce them.

## Verified platform assumptions

- Laravel 13 has a React + TypeScript + Inertia starter option. Its default
  starter includes authentication; the implementation slice must explicitly
  remove/avoid customer auth, registration, settings, and unused scaffolding.
- PostgreSQL supports the required row locks, unique constraints, check
  constraints, and transactional updates. Laravel exposes `DB::transaction()`
  and `lockForUpdate()`; concurrency tests must use PostgreSQL.
- Stripe's deferred Payment Element renders before PaymentIntent creation and
  confirms in Stripe.js after a server-created Intent is returned.
- Stripe PaymentIntent cancellation is definitive only when Stripe reports
  cancellation; `processing` is not generally cancellable. Timeout alone is
  not evidence of failure.
- Laravel supports PostgreSQL-backed queue jobs and scheduled commands.

Official references:

- https://laravel.com/docs/13.x/starter-kits
- https://laravel.com/docs/13.x/queries
- https://laravel.com/docs/13.x/queues
- https://docs.stripe.com/payments/accept-a-payment-deferred?platform=web&type=payment
- https://docs.stripe.com/payments/payment-intents/verifying-status
- https://docs.stripe.com/api/idempotent_requests
- https://docs.stripe.com/api/payment_intents/cancel
- https://docs.stripe.com/webhooks

## Deployment topology

One Laravel codebase, run as separately managed local processes:

1. HTTP application server serving Inertia/React pages and checkout endpoints.
2. PostgreSQL database containing all authoritative state and the jobs table.
3. Laravel `queue:work` process consuming PostgreSQL queue jobs.
4. Laravel scheduler process (`schedule:run` or appropriate long-running dev
   equivalent) for expiration and reconciliation.
5. Vite development process for React/TypeScript.

No Redis, microservices, mail worker, admin service, or real-world hosting in
this phase. Use Docker Compose to reproducibly provision PHP/Composer, Node,
PostgreSQL and the app/worker/scheduler processes in the *implementation*
environment. This document project currently contains no app or runtime.

## Proposed PostgreSQL entities

Exact migrations and constraints are an implementation deliverable; do not
treat these illustrative names as already-created tables.

| Table | Essential fields and constraints |
| --- | --- |
| `events` | ID, slug (unique), title, description, original cover, venue/public address, event IANA time zone, UTC start/end; start before end |
| `ticket_types` | ID, event FK, label, positive capacity, price in GBP minor units, `reserved_count`, `confirmed_count`, availability flags; `CHECK reserved_count >= 0`, `CHECK confirmed_count >= 0`, `CHECK reserved_count + confirmed_count <= capacity` |
| `orders` | ID, event FK, unique display reference, unique checkout request key and its request fingerprint, opaque checkout capability hash, status, UTC `expires_at`, `payment_started_at`, nullable buyer name/email, immutable total_minor/currency, `paid_at`, version/timestamps |
| `order_items` | Order FK, ticket type FK, quantity, snapshot unit_price_minor, line_total_minor; unique `(order_id, ticket_type_id)`; positive quantity; total persisted from trusted server calculation |
| `payment_attempts` | Order FK, attempt number, unique stable Stripe idempotency key, unique nullable Stripe PaymentIntent ID, expected amount/currency, authoritative status/progress and timestamps; track one unresolved active attempt per order under a lock |
| `technical_refunds` | Order/payment attempt FK, reason, unique stable refund idempotency key, Stripe refund ID where known, lifecycle, attempts/last error; only exceptional automatic refunds |
| `jobs`, `failed_jobs` | Laravel PostgreSQL queue and failure tables |

Avoid creating a separate webhook-event inbox table for v1. The queued job
itself holds the verified event input; the order/payment state transitions are
idempotent. Preserve Stripe identifiers and diagnostic context on business
records. Stripe secrets and raw card details must not appear in logs.

### Identity and secrets

- Booking reference: human-readable, stable, unique, **not** an authorization
  credential. It exists from the initial hold.
- Checkout capability: cryptographically random and unguessable; only its
  hash need be stored server-side. It authorizes access to the active guest
  checkout. Do not rely on an incrementing order ID as authorization.
- Browser checkout creation key: generated and retained **before** first
  request, scoped to one logical checkout/tab, reused on retry. Attach a
  canonical payload fingerprint; conflicting reuse returns an error. A
  distinct checkout in another tab uses a distinct key.
- Stripe idempotency key: durable and distinct from the browser key, scoped to
  the logical Stripe mutation. Derive or allocate and commit it **before** the
  Stripe call. Do not rely on Stripe retaining idempotency results forever.
- Browser-visible Stripe client secrets are only for the appropriate checkout;
  do not log them or put them in public URL parameters.

## Order lifecycle

The order exists as soon as tickets are held. No second order is inserted by
the payment webhook.

| Current | Trigger and required proof | Next | Inventory effect |
| --- | --- | --- | --- |
| None | Atomic successful hold | `pending` | Reserve all items |
| `pending` | User cancels before payment | `cancelled` | Release once |
| `pending` | Original deadline elapsed, no payment protected | `expired` | Release once |
| `pending` | Buyer saved and Pay accepted before deadline | `payment_pending` | Remain reserved/protected |
| `payment_pending` | Authoritative definitive decline and original deadline still open | `pending` | Remain reserved until original expiry |
| `payment_pending` | Authoritative definitive decline/cancellation and original deadline passed | `expired` | Release once |
| `payment_pending` | Verified matching Stripe payment success and intact reservation | `confirmed` | Move reserved counts to confirmed counts |
| `payment_pending` | Unknown, missing, delayed, or noncancelable payment | `payment_pending` | Stay protected; retry reconciliation |
| Any unconfirmed exceptional captured case | Proven cannot fulfill despite capture | `refund_pending` | No valid ticket confirmation; reconcile refund safely |
| `refund_pending` | Stripe refund definitively succeeded | `refunded` | Record exceptional resolution |

Other attempted or duplicate transitions are no-ops or explicit conflicts, not
silent reversals. `confirmed` is never downgraded by a late failure webhook.
An order with multiple attempts cannot have two unresolved chargeable Intents.

**Two independent truths:** Stripe.js `succeeded` authorizes the browser to
show *payment succeeded* immediately, while only the server transaction sets
`orders.status = confirmed`. The customer may temporarily see payment success
while the durable order remains `payment_pending`; recovery must converge it.

## Transaction and external-call boundaries

**T1 — Create pending order and reservation (single PostgreSQL transaction)**

1. Validate event cutoff, quantities (1–10), ticket ownership and the price
   snapshot entirely on the server; enforce unique checkout request key.
2. Acquire `ticket_types` locks in deterministic ascending ID order; ensure
   currently valid available quantities account for confirmed and unexpired
   holds. Coordinate opportunistic expiry cleanup under a consistent locking
   protocol; design it so a late scheduler cannot resurrect or release a hold.
3. If any ticket type lacks capacity, roll back the *entire* selection.
4. Insert `orders(status=pending)`, unique reference, immutable `order_items`,
   10-minute deadline based on authoritative database time, and adjust the
   appropriate reserved counters.
5. Commit, then return the checkout capability and reference.
6. On a duplicate request key, compare canonical input fingerprint and return
   the existing order and **original expiry**, never a second hold.

**T2 — Prepare payment (short transaction before any external call)**

1. Lock existing pending order. Validate checkout capability, exact original
   expiry, immutable order total and permissible state.
2. Save required buyer name and email; move to `payment_pending` and record
   payment protection before making payment possible.
3. Insert or reuse a payment-attempt record with deterministic amount/currency
   and stable Stripe API idempotency key. Commit before calling Stripe.

**External Stripe API call — never inside T2**

1. Create or reuse the *same logical* PaymentIntent with `capture_method`
   automatic, GBP and exact saved order amount. Include server-generated order
   metadata for recovery.
2. If creation/retrieval succeeds, persist the Stripe PaymentIntent ID and
   association in a separate short transaction **before** returning a client
   secret to the browser.
3. If HTTP times out after Stripe may have created the object, reuse the same
   Stripe idempotency key on retry; do not create another chargeable Intent
   while the first may exist. Stripe API v1 may replay the original error
   response (including 500); ambiguous errors require inspection/recovery,
   not an unbounded new-key retry.
4. If an unresolved create operation outlives Stripe's idempotency retention,
   fail closed and reconcile rather than assuming a fresh key is safe.

**Stripe.js browser action**

- `elements.submit()` validates payment details, then existing/new client
  secret is passed to `stripe.confirmPayment()` (`redirect: if_required` where
  applicable). Only `status=succeeded` shows immediate customer success.
- Definitive decline can permit same-order retry while original hold is
  valid; no reset to 10 minutes. Ambiguity disables fresh payment and
  automatically checks existing Stripe intent status on the client.
- Browser result is never submitted to a trusted order-confirmation endpoint.

**T3 — Webhook receipt and durable queue write**

1. Verify raw body and Stripe signature; accept only necessary event types.
2. Insert one Laravel database-queue job containing the relevant verified
   event input; respond `2xx` only once its PostgreSQL write has succeeded.
3. No extra webhook-inbox row, separate event transaction, or Redis enqueue.
   A crash after enqueue but before response may cause a duplicate job, which
   is harmless. A crash before enqueue results in Stripe retry.

**T4 — Webhook worker fulfills existing order (single DB transaction)**

1. Identify the existing payment attempt and order. Verify trusted event type,
   connected Stripe PaymentIntent, successful payment status, captured/received
   expected amount, GBP currency, and ownership of the still-protected order.
   Obtain any needed Stripe data *outside* inventory DB locks.
2. Lock the order and relevant ticket-type rows consistently. If confirmed,
   no-op. If conflicting/unknown, quarantine and reconcile; never guess.
3. Move each line from `reserved_count` to `confirmed_count`, set paid
   timestamps/identifiers and `orders.status=confirmed`, and commit.
4. If the worker crashes after commit but before acknowledging the job, its
   retry sees the existing confirmed order and makes no extra inventory move.
5. Late, duplicate and reordered failure events cannot downgrade `confirmed`.

**T5 — Expiry/cancel and release**

- The scheduler processes bounded batches every minute. An unstarted order
  after its original deadline transitions to `expired` and releases its
  reserved counters transactionally, exactly once.
- Checkout-access inventory rules also honor deadlines accurately; do not
  depend on the scheduler firing exactly on time for correctness.
- For payment-protected orders, never release based only on a timeout or stale
  Stripe status snapshot. First obtain a conclusive cancellation/failure from
  Stripe or confirmed success. Stripe cancellation and its reply occur outside
  locked DB transactions; only then make a final DB transition.
- If a cancellation request races with a Stripe confirmation and fails because
  the payment succeeded or is processing, preserve the reservation and resolve.

**T6 — Exceptional technical refunds**

- A captured payment that cannot be backed by protected capacity is an error,
  not a booking. Record a durable refund obligation with stable idempotency,
  attempt full refund through Stripe, then reconcile the refund to a known
  terminal outcome. An unknown refund result cannot trigger a second refund
  using a new logical key.
- This is rare recovery work, not a normal customer-refund UI.

## Scheduled recovery and queue operations

- **Expiry:** every minute; bounded direct PostgreSQL update/transactional
  service, not one queue job per hold. On-demand access prevents timer drift
  from creating false availability.
- **Payment reconciliation:** every minute, initially consider unresolved
  orders older than approximately 60 seconds, use individual exponential
  backoff and Stripe status API. Reuse the same trusted confirmation/release
  functions as the worker. Include orphan payment attempts, unknown creation,
  uncertain cancellations and exceptional refunds. It runs without customer
  interaction and is independent of the webhook queue.
- **Queue:** PostgreSQL `database` connection, worker with five attempts and
  increasing retry delay, `failed_jobs` and explicit logs. Supervisor/process
  management and scheduler must be documented. Keep job execution and retry
  timeout settings coherent; missing worker/scheduler health is an operational
  failure that should be visible.

## Security and observability

- Server-only pricing and eligibility checks, no trust in browser expiration,
  payment status, or mutable inventory counts.
- Strong random checkout capability and request keys, POST CSRF protection for
  normal browser routes, separate signed Stripe webhook route.
- No storing raw card details, Stripe secrets, client secrets in logs, or
  complete payment event payloads in uncontrolled application logs.
- Basic HTTP abuse rate limiting; do not add v2 per-browser hold quotas.
- Structured log correlation by internal order/reference (where safe), Stripe
  intent ID, job attempt ID; track failed webhooks, prolonged protected orders,
  uncompleted technical refunds, expired-hold backlog, and database errors.
- CLI-only inspect/reconcile/retry commands and read-only health checks; no
  admin UI. Access to operational commands stays outside the customer surface.

## Important design review checks (before implementation)

1. Specify a deterministic **global lock order** shared by hold creation,
   opportunistic expiry, cancellation, payment protection, confirmation and
   reconciliation. Include deadlock retries and tests proving release exactly
   once under races. Do not hold PostgreSQL locks while calling Stripe.
2. Prove recovery for `Stripe intent created / response lost / local intent ID
   not saved` and for a Stripe API v1 idempotent 500 result. Never assume
   provider-side idempotency is eternal.
3. Ensure a fresh tab creating a *different* checkout cannot be mistaken for
   a retry of the existing order; duplicated checkout pages should not cause
   a second charge against the same logical order.
4. Define a minimal operator action for a reservation left protected after a
   prolonged Stripe outage, without adding an admin panel.
5. Demonstrate that the frontend may report **Stripe payment success** before
   the database order is confirmed while preserving the original capacity
   and amount guarantee. No customer-facing claim of completed database
   fulfilment may be driven by untrusted browser input.

