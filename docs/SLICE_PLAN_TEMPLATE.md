# Per-slice implementation plan — required template

Every vertical slice must have a separate plan at
`docs/slice-plans/NN-descriptive-name.md`, written and **explicitly approved
before application code changes**. This is a detailed engineering proposal,
not permission to implement. Use the roadmap in `PLANS.md`; preserve all
requirements in `docs/V1_SPEC.md`. If a section is not relevant, write
"Not applicable" and explain why. Never silently omit it.

During the planning pass, the agent may inspect the existing codebase and
create/edit the proposed slice-plan document; it must not scaffold files,
install packages, run migrations, edit application code or begin implementing.

Copy and complete the following sections for each slice.

---

## Slice identity and approval state

- **Slice number and name:**
- **Roadmap reference:** `PLANS.md` → Slice N
- **Status:** Proposed / Awaiting owner plan approval / Plan approved /
  Implementing / Awaiting implementation review / Accepted / Blocked
- **Proposed implementation owner/date:**
- **Owner plan approval and date:** Pending
- **Implementation acceptance and date:** Pending
- **Related approved architecture decisions:**

## 1. Customer-visible result and boundaries

- What exactly works at the end of the slice, as a demonstrable vertical path?
- Which approved v1 requirements and acceptance tests does it satisfy?
- Explicit non-goals and disabled functionality (especially payment gates).
- Expected user flow and affected routes/screens.
- Existing code and assumptions inspected (include relevant paths).

## 2. Proposed file-by-file change manifest

List **every** planned file to add/change/remove. Do not hide generated
scaffolding behind phrases like "standard Laravel setup". Group by server,
database, client, tests, dev tooling and documentation. Explain each file's
responsibility; distinguish new, modified and removed files.

| Path | Add / change / remove | Responsibility | Why needed |
| --- | --- | --- | --- |
| `app/...` | Add | ... | ... |

If the exact generated contents cannot be known before bootstrapping, state
the expected generated structure, cleanup requirements and inspection gate.

## 3. Laravel class and contract design

Read `.ai/rules/index.md` and every applicable Laravel Boost project rule,
as recorded from `docs/BOOST_PROJECT_RULES.md`. `AGENTS.md` governs approval,
scope and non-negotiable correctness. The expected coding conventions include:

- Invokable, thin single-action controllers for HTTP endpoints.
- Dedicated Form Requests for mutations; authorization where applicable.
- Focused action classes for business operations; reuse actions from jobs,
  scheduled recovery and HTTP when they implement the same invariant.
- Typed, preferably immutable DTO inputs/results and meaningful value objects.
- Lean Eloquent models; no speculative repository/service abstraction layers.

For every proposed class, document:

| Fully qualified class | Responsibility | Input contract | Return/side effects | Dependencies |
| --- | --- | --- | --- | --- |
| `App\...` | ... | ... | ... | ... |

Show the request/response path through controller → Form Request → DTO →
action → persistence → HTTP/Inertia response. State how errors map to user
behaviour. Identify the owner of each domain invariant. Explain any exception
to the normal layering or use of a new dependency/abstraction.

## 4. Routes and frontend design

- HTTP method, route, controller, authorization/checkout capability and
  throttling; CSRF/webhook distinctions where applicable.
- Inertia pages and React components, with exact proposed file paths.
- Typed server-provided props and page-local state.
- Customer loading, empty, expired, failure, retry and success states.
- Accessibility and responsive QA targets.
- What the browser is **not** trusted to determine.

## 5. Database and migrations

For each migration, provide exact planned table name, columns, SQL types,
nullability, defaults, foreign keys, unique indexes, check constraints, and
relevant non-unique indexes. Explain the need for each constraint and how it
prevents an invalid state. Include any data backfill or transition from the
existing schema, as well as rollback limitations.

For each stored status or counter, identify its allowed transitions or
updates and the operation that owns them. Distinguish schema/database
guarantees from application-only validation.

## 6. State transitions and transaction protocol

- List all affected states and allowed transitions with preconditions.
- For each transaction: start, locked rows, **global lock order**, validation,
  writes, commit, rollback and retry policy.
- Explain how the design preserves capacity, idempotency and payment rules
  under simultaneous requests, stale reads, crashes and retries.
- Identify work done **outside** database transactions, particularly Stripe
  network calls.
- Document how scheduled work, webhook jobs and HTTP requests coordinate.

For an entirely read-only/UI-only slice, explain why these are not applicable.

## 7. Idempotency, failures, queue and external integrations

- Request idempotency keys, persistence and conflicting payload behaviour.
- Stripe idempotency keys, attempt identity, response-loss recovery and
  cancellation behaviour, **if this slice involves payments**.
- Jobs, retry/backoff, failure handling and scheduling where applicable.
- Specific crash points and retry outcomes. Show what the user sees when
  the outcome is known, declined or ambiguous.
- Logging/correlation without secrets or card details.
- Dependencies/packages to add, with justification; otherwise "None".

## 8. Test-first verification matrix

Map each requirement/invariant to one or more concrete tests. Include the
exact test files and harness type; the expected result and failure injection
should be unambiguous.

| Scenario | Test file/type | Setup or injected failure | Expected durable result |
| --- | --- | --- | --- |
| ... | ... | ... | ... |

For capacity-changing code, use concurrent connections to real PostgreSQL,
not SQLite. For Stripe, distinguish deterministic fakes from actual Stripe
test-mode checks. Include duplicate delivery, process death, timeout, expiry
and race tests whenever the feature crosses those boundaries.

List the exact intended verification commands: targeted tests, regression
tests, static analysis, formatting, TypeScript checks, build and browser QA,
as applicable. In the **planning** pass, do not claim these have passed.

## 9. Implementation order and review demo

- Number the intended implementation steps inside this one slice, small
  enough to audit as code is written.
- Describe the end-to-end demonstration the owner can perform.
- State what must be visible in the final implementation review: file diff,
  changed behaviour, test commands and observed outputs, unresolved risks.

## 10. Trade-offs, questions and forbidden deviations

- Alternatives considered, rationale and cost of the chosen design.
- Unresolved choices requiring owner input **before coding**.
- Known risks and required follow-up in a later approved slice.
- Changes explicitly out of scope or not permitted without a new approval.

## 11. Approval record

**Gate A — plan approval**

- Approval status/date:
- Owner decisions and requested revisions:
- Final approved plan revision:

**Gate B — implementation acceptance**

- Files actually changed compared with the approved manifest:
- Test commands and actual results:
- Approved deviations, if any:
- Owner acceptance/date:

Do not treat a completed planning document as self-approval. Wait for a
separate, explicit owner instruction before implementing. Pause for fresh
approval on material deviations and for acceptance after the slice is built.
