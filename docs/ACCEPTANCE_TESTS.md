# Ticketing v1 — executable acceptance contract

Status: **Proposed test specification for review. No tests have been run.**

These cases define *observable outcomes*. Their implementation must use real
PostgreSQL and a controllable Stripe adapter for injected failures. Include a
small separate end-to-end suite using Stripe test mode and test webhooks.
Do not claim the invariants are proven by SQLite tests, single-threaded mocks,
counting HTTP 200s, or a happy-path browser demo.

## Test-harness requirements

- Launch multiple independent Laravel workers/processes and real PostgreSQL
  connections for contentious requests. Do not share one transaction across
  concurrent actors.
- Use a controllable clock for time-bound tests and the authoritative database
  deadline for decisions. A paused scheduler must not allow an invalid hold.
- Provide deterministic fault-injection hooks at the boundaries listed below
  (throw, crash, timeout, duplicate, lost response), removable/disabled in
  normal production builds.
- Record order, order_item, payment_attempt, ticket_type counters, job/failed
  job states and Stripe fake side effects before/after each scenario.
- Define invariant assertions in reusable test helpers and run them after
  every committed state transition and at each concurrency test's finish.
- Separate infrastructure availability failures from application assertions;
  a missing PostgreSQL/Stripe test environment is a *blocked test*, not a pass.

## A. Public browsing and customer surface

| ID | Setup/action | Must observe |
| --- | --- | --- |
| A01 | Freshly migrate and seed demo data twice | Deterministic original events/ticket types, no unwanted duplicates; seeded demo content remains original, not copied from Luma |
| A02 | Visit calendar with upcoming and past events | Hero/identity, month navigation, Upcoming/Past, event image/details and usable responsive layout |
| A03 | Open event with two ticket types | Event local time zone, venue, prices, available/sold-out flags; no precise capacity number |
| A04 | Navigate month boundaries/year boundaries | Correct event grouping/time zone and no missing/duplicated events |
| A05 | Visit event after start | Visible as appropriate but no new hold; existing protected order may complete |
| A06 | Mobile and keyboard-only checkout | Usable responsive form, focus/labels/errors, countdown announced sensibly, disabled Pay while ambiguous |
| A07 | No v1 features | No login, admin, email actions, QR/check-in, waitlist, search, maps or extra hold-quota UI |

For Slice 2 (browse), produce desktop and mobile screenshots for human
comparison with the *structure and interaction* of the Venice Book Club Luma
reference. Do not reuse Luma's copyrighted imagery, logo or copy.

## B. Atomic capacity and order creation

| ID | Setup/action | Must observe |
| --- | --- | --- |
| B01 | Ticket capacity 20; exactly 1,000 distinct logical checkout requests concurrently request one | Exactly 20 committed pending orders/holds, 980 availability failures, `reserved_count=20`, `confirmed_count=0`, never oversold |
| B02 | Two ticket types, one unavailable; request both | Entire hold fails; no line or counter partially committed |
| B03 | Same checkout key, 100 simultaneous duplicate requests | One order with original lines/reference/expiry; capacity counted once |
| B04 | Same checkout key with different selection, buyer-independent retry | Explicit conflict, never mutate original order |
| B05 | Hold commits, HTTP response lost, browser retries | Original order and checkout capability recover, original expiry unchanged |
| B06 | Two ticket types acquired in inverse order by competitors | No permanent deadlock; deterministic lock order/retry; invariant preserved |
| B07 | Attempt to exceed max 10, use negative quantity, cross-event ticket type or browser price tamper | Rejected; no inventory mutation |
| B08 | Re-run seed data while holds exist or amend current ticket prices in test | Existing order line prices and amount immutable; new orders use current seed data/prices |
| B09 | Two separate tabs create two new checkouts | Distinct orders/keys; both may hold if real capacity exists; each remains internally idempotent |
| B10 | Cancel only unstarted checkout, then duplicate cancel | Capacity released exactly once; repeated cancel is no-op/conflict |

The 1,000-way contention harness must bypass only the *HTTP rate limiter* so
it measures inventory safety; rate limiting is tested separately.

## C. Expiration and protection

| ID | Setup/action | Must observe |
| --- | --- | --- |
| C01 | Pending order expires exactly at original 10-minute deadline | New Pay fails at/after deadline; reserved tickets safely released |
| C02 | Pause scheduler, then request capacity after expired holds | Accurate deadline respected; reclaim safely without overselling |
| C03 | Scheduler and Pay request race around deadline | Exactly one valid transition, never expired and payment-protected simultaneously |
| C04 | Payment initiation accepted at 9m59s, completes after 10m | Capacity remains protected and can become confirmed |
| C05 | Customer closes checkout after Pay, >15m pass | Resolve via Stripe cancellation/status; never release solely because 15m elapsed |
| C06 | Stripe cancellation definitely succeeds | PaymentIntent cannot later charge; inventory releases once |
| C07 | Stripe cancellation fails or result is unknown | Capacity remains protected; reconciliation continues |
| C08 | Payment definitely declined while original deadline remains open | Safe retry permitted without adding a second unresolved charge attempt |
| C09 | Payment definitely declined after original deadline | Original order releases; no retry on expired inventory |

## D. Stripe payment creation, browser result and idempotency

| ID | Setup/action | Must observe |
| --- | --- | --- |
| D01 | Enter buyer/card details after hold, click Pay | Name/email persisted, order marked protected before any client secret can be used |
| D02 | Stripe PaymentIntent create succeeds but Laravel never receives HTTP response | Retry uses same Stripe logical key/Intent; no second object or charge |
| D03 | Stripe create succeeds, Laravel crashes before saving intent ID | Retry/recovery associates original Intent before returning client secret |
| D04 | Stripe API returns ambiguous timeout/500 | Payment remains protected; no new idempotency key without conclusive resolution |
| D05 | Stripe key retention has elapsed while unresolved | Fail closed; do not assume a new key is safe; log/escalate/reconcile |
| D06 | Browser clicks Pay repeatedly or after refresh | No second charge for same checkout; same active payment attempt reused |
| D07 | Stripe.js PaymentIntent `succeeded` before webhook delivery | Immediate payment-success UI and stable existing reference; backend order may still be `payment_pending` |
| D08 | Browser loses Stripe confirmation response | UI says outcome unknown, disables Pay, automatically retrieves original intent status |
| D09 | Card declined or 3D Secure required/fails | Appropriate error/action; no false success; retry only under valid deadline rules |
| D10 | Browser tries to submit different amount/currency, or payment for another order | Server rejects/matching verification fails; no confirmation |

## E. Webhook, worker and reconciliation

| ID | Setup/action | Must observe |
| --- | --- | --- |
| E01 | Signed `payment_intent.succeeded` for matching protected order | Single transactional conversion reserved -> confirmed, `paid_at` set, same order updated |
| E02 | Invalid Stripe signature | Webhook rejected; no queued job |
| E03 | Webhook insertion into PostgreSQL queue fails | No successful Stripe acknowledgement; provider retry can recover |
| E04 | Job saved, server crashes before sending Stripe 2xx | Stripe retry may enqueue duplicate; only one order confirmation |
| E05 | Same webhook delivered concurrently 10 times | Capacity moved exactly once; one confirmed order |
| E06 | Late `payment_failed` delivered after success | Confirmed order remains confirmed; capacity unchanged |
| E07 | Worker crashes before order transaction commit | Transaction rolled back, queued job retry can confirm |
| E08 | Worker crashes after commit but before queue acknowledgement | Re-run detects confirmed order, no duplicate conversion |
| E09 | Five worker attempts exhausted | Failed job visible/logged; independent reconciliation still resolves order |
| E10 | Stripe payment succeeds but webhook is never delivered | Scheduled reconciliation discovers `succeeded` and confirms order without browser request |
| E11 | Several Stripe status lookups fail | Backoff applies; capacity remains protected until definitive result |
| E12 | Mismatched Stripe Intent ID, currency or captured amount | Never mark paid; record inconsistency for resolution |
| E13 | Captured payment but reservation provably cannot be fulfilled | No valid purchase confirmation; durable full technical refund obligation and retry-safe refund |
| E14 | Refund request succeeds then response is lost | Refund retry uses same idempotency identity, cannot refund twice |

## F. Customer-confirmation boundaries

| ID | Setup/action | Must observe |
| --- | --- | --- |
| F01 | Customer returns to active checkout in same tab after reload | Same hold or payment attempt, timer and state recovered |
| F02 | Customer closes tab and loses all checkout state | No promise of post-purchase lookup; documented v1 limitation |
| F03 | Customer starts entirely separate checkout | Different purchase, not forcibly deduplicated by name/email |
| F04 | Order awaiting backend webhook while Stripe.js already succeeded | UI shows Stripe payment success without driving an untrusted server confirmation |
| F05 | Payment state unknown while customer remains on page | No Pay Again control; automatic safe status retrieval; no manual recovery button |

## Fault-injection cut points

For every pair, inject a crash *before* and *after* the named durable action:

1. Pending order/hold PostgreSQL commit.
2. HTTP response that returns checkout URL/capability.
3. Payment protection and payment-attempt creation commit.
4. Stripe PaymentIntent API request/response (including network timeout).
5. Local association of Stripe PaymentIntent with the order.
6. Stripe.js confirm result reaching the browser.
7. Stripe webhook queue insertion commit and its HTTP acknowledgement.
8. Webhook worker's order confirmation database commit.
9. Expiry or Stripe cancellation transition and release of inventory.
10. Stripe refund API response and technical-refund ledger update.

## Evidence required to approve a slice

Each slice handoff must include:

- The visible/CLI demonstration performed and observed result.
- Exact commands and output summary for focused test suite, full regression,
  TypeScript check/build and formatting/lint where applicable.
- Database invariant assertions (counts before and after) for domain slices.
- Any flaky or blocked tests, not suppressed or described as successful.
- Remaining known risks and assumptions; no silent spec changes.
- A proposed next slice, which is **not begun** until the owner approves.

