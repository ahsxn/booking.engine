# Slice 2A — Simplified event browsing and pagination

## Slice identity and approval state

- **Revision:** 2 — owner-approved replacement for revision 1. The active proposal is sections 1–11 below; revision 1 and its execution evidence remain available through Git history, not in this active plan.
- **Status:** Implementation and automated verification complete; real-browser verification required before Gate B. Not accepted.
- **Roadmap:** `PLANS.md` → Slice 2A. No Slice 2B work.
- **Proposed implementation owner/date:** Codex, 30 October 2026.
- **Owner plan approval/date:** Explicit owner approval of Revision 2 on 30 October 2026, including the approved replacement/removal manifest.
- **Implementation acceptance/date:** Not accepted. The owner reports that real browser Upcoming/Past navigation fails in revision 1.
- **Authority:** The owner's current decisions replace the reference-time/cursor/coordinator design. Use live per-request classification, `simplePaginate(20)`, native anchors with minimal `router.visit()` handlers (owner-approved installed-Link API adjustment), native scroll/reset, and the unchanged event/ticket presentation DTOs. Read-only browsing need not be a consistent snapshot. Hold eligibility must be checked independently in the eventual hold transaction.

## 1. Customer-visible result and boundaries

A guest opens the home page with Upcoming selected, switches to Past using a native anchor backed by Inertia `router.visit()`, and loads subsequent server-paginated events with native infinite scrolling. Upcoming is ordered by `starts_at ASC, id ASC`; Past by `starts_at DESC, id DESC`. Event-local dates, offsets, prices and public ticket information keep their existing presentation contracts. Show basic loading, empty, error/retry and end states. Back/Forward must work in a real browser.

This delivers the browsing portion of A02, timezone/classification support for A04/A05 and A07's scope exclusions. It does not complete calendar/month navigation, final visuals, event details, ticket selection, holds, payments or inventory availability claims. No new dependencies, schema, routes, infrastructure, services, assets or global exception handling. Retain the read-only notice and existing minimal styling.

### Repository review and failed-navigation finding

The current worktree, including revision 1's tracked/untracked files and the owner's existing edits to `docs/slice-plans/01b-event-listing.md`, is preserved. Only this plan changes in this pass. The source inspection covered the current action/controller/request, both custom browsing DTOs, existing event/ticket DTOs and models, page/feed/hook/types, PHP/React tests, route, dependencies and installed framework source. The authoritative documents and `.ai/rules` were read in the preceding Slice 2A work; this revision rechecked the index and applicable app/action/controller/request/data/client/test rules and searched for relevant terms. Laravel, Inertia React, Wayfinder, Tailwind and testing skills inform this proposal.

The current page's `Link.onBefore` **always returns false**, preventing that Link's own visit, and delegates navigation to a feature coordinator. The replacement must remove this interception. Static rendering tests replace Link with a stub that does not exercise its actual visit lifecycle; coordinator tests invoke callbacks directly. Neither proves the reported failing browser path. This is a concrete integration/test gap; the precise runtime cause of the observed failure has not been reproduced or established during this read-only review. Do not present the new proposal as an already-tested fix.

No demonstrated requirement justifies retaining the custom request coordinator under the simplified scope. Remove its queue, generations, request-token ownership, popstate listener, context recovery and rate-limit timers. Keep only small component-local display state for request progress/failure.

### Installed APIs verified

- `composer show laravel/framework`: **13.39.0**. `composer show inertiajs/inertia-laravel`: **3.5.2**. Installed React/Core Inertia manifests: **3.9.1**; declared frontend major is 3 in `package.json`.
- Installed Eloquent `Builder::simplePaginate()` resolves `page` natively and issues **OFFSET `(page - 1) * 20`, LIMIT 21**, without a total COUNT. Native `Paginator` trims lookahead and retains next/previous state.
- Installed `AbstractPaginator::through()` supports typed transformation, including `@phpstan-this-out`; it preserves the paginator while mapping its items. `EventListingData` is already Arrayable, so native paginator serialization produces the public DTO arrays. No manual pagination envelope is required.
- Installed Inertia `ScrollMetadata::fromPaginator()` directly handles Laravel `Paginator`. `Inertia::scroll(fn () => $listEvents->handle($view))` can derive metadata and append behaviour automatically. Explicit `ScrollMetadata`, cursor extraction and projection metadata are unnecessary.
- Corrected during implementation: installed React Link does **not** accept `reset`; the supported router.visit API does. The owner explicitly approved minimal anchor click handlers using that API. InfiniteScroll exposes `params`, loading/next slots, `manual`, `onlyNext`, `preserveUrl` and native fetch helpers. v3 request callbacks include `onNetworkError` and `onHttpException`; `onError` alone is insufficient for transport failures.
- Native Laravel page resolution accepts positive integers and falls back to page 1 for missing/invalid values, including zero, negative, noninteger and array input. Pagination request handling remains Laravel's responsibility.

Boost documentation searches confirmed [simple pagination](https://laravel.com/docs/13.x/pagination#simple-pagination), [native infinite scroll/reset](https://inertiajs.com/docs/v3/data-props/infinite-scroll) and [ordinary Inertia links](https://inertiajs.com/docs/v3/the-basics/links). Installed source/declarations were checked rather than assuming APIs from an older major.

## 2. Proposed file-by-file change manifest

No new file is proposed. This approved implementation manifest is **relative to the preserved revision 1 worktree**; the recorded owner-approved router.visit adjustment applies.

| Path                                                                 | Proposed operation                | Responsibility / reason                                                                                                            |
| -------------------------------------------------------------------- | --------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `app/Actions/Events/ListEvents.php`                                  | Change                            | Accept validated view scalar; filter/order and `simplePaginate(20)`; map items through existing DTO.                               |
| `app/Http/Controllers/Events/ListEventsController.php`               | Change                            | Thin invokable controller; supply selected view and native `Inertia::scroll()` paginator.                                          |
| `app/Http/Requests/Events/ListEventsRequest.php`                     | Change                            | Validate only query `view`; default missing view; use standard Form Request validation/redirect handling.                          |
| `app/Data/Events/EventBrowsingData.php`                              | Remove                            | View alone does not need a DTO; no browsing snapshot or cursor input remains.                                                      |
| `app/Data/Events/EventListingPageData.php`                           | Remove                            | Native Laravel Paginator owns pagination data/metadata.                                                                            |
| `resources/js/pages/events/index.tsx`                                | Change                            | Ordinary Wayfinder-backed Inertia navigation and simple view/visit status.                                                         |
| `resources/js/components/events/event-feed.tsx`                      | Change                            | Native InfiniteScroll, existing event/ticket rendering and a small local error state; no coordinator/context guard.                |
| `resources/js/hooks/use-event-browsing.ts`                           | Remove                            | Custom request coordination is no longer part of the design.                                                                       |
| `resources/js/types/events.ts`                                       | Change                            | Keep event/ticket types unchanged; replace snapshot/cursor page types with native simple-paginator shape and selected view.        |
| `tests/Feature/Actions/Events/ListEventsTest.php`                    | Change                            | Offset-page traversal/order, DTO/timezone and bounded-retrieval coverage; remove obsolete cursor/snapshot assertions.              |
| `tests/Feature/Http/Controllers/Events/ListEventsControllerTest.php` | Change                            | View validation, native page handling, Inertia scroll/reset protocol and retained public/error/rate regressions.                   |
| `resources/js/tests/events-index.test.tsx`                           | Change                            | Keep actual page/feed output tests; adapt props, controls and native router request/InfiniteScroll wiring.                         |
| `resources/js/tests/event-browsing.test.tsx`                         | Remove                            | Tests an abandoned coordinator/reference mechanism. Retain meaningful rendering/failure tests in the remaining frontend test file. |
| `docs/slice-plans/02a-event-browsing-pagination.md`                  | Change; record approvals/evidence | Active revision 2 proposal and historical revision 1 evidence.                                                                     |

Approval of this manifest must explicitly cover the proposed deletion of the obsolete coordinator test file; no test file is deleted in this planning pass. Adapt surviving tests to preserve distinct DTO/date/price/escaping/HTTP regressions rather than discarding them with the removed architecture.

**Keep unchanged:** `EventListingData.php`, `TicketTypeListingData.php`, models, migrations, factories, seeders, routes, package/lock files, test/build configuration, `.ai/rules`, `PLANS.md`, all other docs and unrelated owner edits. Wayfinder output remains ignored generated tooling output. No repository rollback/reset/clean operation.

## 3. Laravel class and contract design

| Class                                                 | Input                                                                    | Return / responsibility                                                                  | Dependencies / side effects                            |
| ----------------------------------------------------- | ------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| `App\Http\Requests\Events\ListEventsRequest`          | Query `view` only                                                        | `selectedView(): string`, PHPDoc `'upcoming'                                             | 'past'`; missing view becomes Upcoming                 | Standard FormRequest, no catalogue query. Guest authorization true. |
| `App\Actions\Events\ListEvents`                       | `handle(string $view): Paginator`, literal-union PHPDoc for view         | `Illuminate\Pagination\Paginator<int, EventListingData>` containing one bounded DTO page | Event query, CarbonImmutable, existing DTO; read-only. |
| `App\Http\Controllers\Events\ListEventsController`    | `__invoke(ListEventsRequest $request, ListEvents $listEvents): Response` | Render `events/index` with `view` and native scroll `events`                             | Inertia response only, no business/query logic.        |
| Existing `EventListingData` / `TicketTypeListingData` | Existing models                                                          | Existing immutable public item serialization                                             | Unchanged.                                             |

Flow: GET → Form Request → validated scalar view → action/PostgreSQL → native paginator of existing item DTOs → `Inertia::scroll()` → typed React page. No input/result pagination DTO, enum, value object, resource, paginator subclass, repository, service or custom metadata provider.

The request keeps query-only `validationData()` and a small view rule (`sometimes`, `required`, `string`, allowed Upcoming/Past values). Remove all reference-time and cursor rules, decoding/date helpers and `browsingData()`. Remove custom `failedValidation()`. Use the framework's named-home redirect setting (`$redirectRoute = 'home'`) to make an invalid document/Inertia view request recover at canonical home rather than redirecting back to the same invalid query indefinitely. Standard JSON validation responses remain 422. This is ordinary Form Request configuration, not a new exception/normalization layer. Show standard validation feedback from shared errors.

The action owns classification and sorting. Resolve live UTC `now()` once for **this request**, apply `starts_at > now` for Upcoming or `<= now` for Past, select the same public event columns, eager-load the same public ticket fields/ordering, sort both columns in the correct direction and call `simplePaginate(20)`. Append only normalized `view` to generated links. Use native `through(fn (Event $event): EventListingData => EventListingData::fromModel($event))`. Do not convert the paginator to a custom array or replace it with its collection. Unlike cursor pagination, page metadata does not require accessing `starts_at` on transformed DTOs.

Controller shape, illustrative **proposal only**:

```php
$view = $request->selectedView();

return Inertia::render('events/index', [
    'view' => $view,
    'events' => Inertia::scroll(fn () => $listEvents->handle($view))
        ->matchOn('data.id'),
]);
```

The native ScrollProp caches its resolved value for metadata/serialization; one action execution per events response. Matching IDs is optional framework configuration retained to avoid repeated IDs on retry/overlap; it does not make offset pagination a snapshot or recover skipped records.

## 4. Routes and frontend design

### HTTP, query and response contracts

Keep `GET /`, route `home`, existing controller, `web` stack and `throttle:60,1`. Guest/public; no auth/capability/CSRF mutation/webhook changes. `/` defaults to Upcoming; `/?view=upcoming` and `/?view=past` select explicit views. Native pagination uses `page` (`/?view=past&page=2`). Unknown query parameters have no application semantics and are not copied into generated paginator links. No backward-compatible snapshot/cursor interpretation is needed for this unreleased slice.

Native invalid page fallback is page 1, rather than revision 1's bad-cursor 400 contract. Valid pages past the results return an empty page. Keep per-page count fixed in code. Do not add manual page parsing, a maximum-page policy or custom normalization unless a concrete installed-framework failure is first demonstrated and a revision approved.

Application props become:

```typescript
type EventView = 'upcoming' | 'past';
type EventPage = {
    current_page: number;
    current_page_url: string;
    data: EventListing[];
    first_page_url: string;
    from: number | null;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
};
type EventListingPageProps = { view: EventView; events: EventPage };
```

This describes the installed paginator's wire format; it is not a new PHP DTO or custom metadata abstraction. `EventListing` and `TicketTypeListing` fields/casing remain unchanged, including server-derived local dates and price labels. Native page-object `scrollProps.events` uses `pageName: 'page'`, numeric current/next/previous pages and native reset state; no application-defined scroll metadata or totals.

### React structure and native navigation

Keep just the existing page and feed component. Page owns selected view from server props, two navigation links and basic navigation feedback. Feed owns existing item markup plus native loading/error controls. Remove the feature hook entirely; no replacement custom hook or state manager.

Owner-approved installed-API adjustment (30 October 2026): use native `<a href={home.url({ query: { view: target } })}>` with a minimal inline click handler calling `router.visit(home({ query: { view: target } }), { reset: ['events'], only: ['view', 'events'], preserveState: false, preserveScroll: false, ...localStatusCallbacks })`. Apply the same approach to Start of this view. `reset` causes a partial visit; `only` must include `view` as well as `events` so the selected server prop updates together with the reset feed. The router generates protocol headers. Do not manually set `X-Inertia-Reset`.

Leave default-prevented clicks, non-primary buttons, Meta/Ctrl/Shift/Alt activation and other-target anchors untouched. Normal activation (including the browser's Enter-to-click behavior) prevents the default and issues one native router visit. Real hrefs retain opening in new tabs and native history behavior. Use `aria-current="page"`, labelled navigation and existing focus styling. Keep only component-local progress/failure text. No coordinator, custom hook, request queue, popstate handler, nested navigation, generation counter or dependency. This explicitly supersedes the plan's unsupported `<Link reset>` example; it does not authorize additional coordination.

Feed uses `<InfiniteScroll key={view} data="events" onlyNext preserveUrl buffer={300}>` with direct list items/stable IDs and native next/loading slots. Preserve the existing view-only visible URL while scrolling; native Inertia history stores loaded props/scroll state. A fresh shared view URL starts at page one; direct `page=3` URLs render just that page, with a normal “Start of this view” link if appropriate. Back/Forward restoration is native and must be verified, not reimplemented.

Switching uses native `reset: ['events']` to replace accumulated rows and reset scroll metadata; `preserveState={false}` and view key remount feed-local transient state. Native InfiniteScroll cleanup handles its observers/request. While a view visit is active, its simple loading flag can put the feed in manual mode to avoid new automatic loads. Reset is **replacement behaviour**, not a claimed guarantee that every already-arrived asynchronous response is cancellable. Browser QA must cover switching/history during slow and out-of-order page loads. If the installed native combination fails that requirement, show the trace and request the smallest justified design change before introducing coordination machinery.

### Basic UI state

- Native page slot loading state keeps current rows visible and shows a polite loading message; Load more is keyboard-accessible and disabled while loading.
- A successful empty first page shows only the selected view's empty message. A valid empty deep page can offer Start of this view; it is not a server failure.
- Final nonempty results show “All upcoming/past events loaded” and no active next action.
- A small feed-local error string/flag is sufficient for failures. Native `params.onNetworkError` and `onHttpException` return false after setting a generic message, preserving rows and suppressing the default modal. Set native `manual` while an error is present; Retry calls the native next-slot fetch, and success clears the error. No manual page/cursor advancement.
- A failed view visit keeps the previous successfully loaded view/URL; a normal link retries the desired destination. Show basic feedback. Cancellation is not an error.
- For 429, show “Too many requests; try again shortly” and pause auto-loading until explicit retry. The server rate limiter remains authoritative. Remove custom retry timers/backoff, response parsing state and snapshot reload/recovery controls. Repeated manual retry can receive 429 again.
- No Results as of label, refresh-reference UI or context-mismatch recovery. Normal browser reload obtains current results.
- Retain semantic lists/time elements, public content escaping, visible focus, wrapping, loading/status announcements and current styling. No focus jumps on append. Real mobile/320px/200% zoom and keyboard checks remain required.

The browser never decides inventory, paid status, prices, authoritative hold eligibility or event-local dates.

## 5. Database and migrations

Not applicable: no migration, constraints, backfill, stored status/counter or data rollback. Retain the existing `(starts_at,id)` B-tree and models unchanged.

Queries use PostgreSQL LIMIT/OFFSET and no total COUNT. Hydration/response size is bounded (at most 21 parent models/lookahead, 20 DTOs); eager loading can include ticket definitions for the 21 lookahead parents. Offset **database work is not bounded independently of page depth**. Deep Past pages may scan/skip progressively more rows; disclose and measure this trade-off instead of claiming keyset performance. No index addition or optimization abstraction is proposed.

## 6. State transitions and transaction protocol

Read-only slice: no domain transaction, row lock, lock order/deadlock retry, writes/commit/rollback, capacity change or payment state. Native navigation/request UI states do not persist data.

Each request classifies using its own live UTC time. Events crossing their start, inserts, deletes or rescheduling between OFFSET pages can cause omissions or overlap; that limitation is now explicitly accepted. Equal start times still have deterministic ID ordering for an unchanged catalogue/time. An ID merge may reduce visible overlap but cannot guarantee completeness after changes.

The eventual hold transaction independently validates **current** event eligibility and inventory with its own authoritative clock/locking rules. No browse prop, `page`, prior classification or successful render authorizes a hold. Scheduler/webhook coordination is not applicable here.

## 7. Idempotency, failures, queue and external integrations

No checkout/Stripe idempotency key, queue job, schedule, webhook, refund or external mutation is involved. GET retries are read-only; use native next-page metadata rather than custom request identity. A response loss does not leave a durable operation to reconcile. Query/corrupt-timezone errors remain reported failures, not successful empty catalogues or UTC substitution.

Use existing exception reporting and generic UI feedback; do not log raw error response bodies or add correlation infrastructure. **Dependencies to add: None.** No browser installation/config changes are authorized by this proposal.

## 8. Test-first verification matrix

Use existing isolated real PostgreSQL and the current database/role guard, factories and Pest conventions. Freeze time for fixed-catalogue traversal assertions; travel time for live reclassification assertions. Keep existing tests for DTO fields/prices, ticket ordering/ticketless events, local dates/DST, data integrity, HTTP access/errors, rate limiting and escaping.

| Behaviour                       | Test file / harness                                                    | Required evidence                                                                                                                                                                                                                                                                        |
| ------------------------------- | ---------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Default/explicit Upcoming, Past | Existing HTTP controller test / PostgreSQL; existing React render test | Correct selected view, no inactive rows, normal hrefs and current marker.                                                                                                                                                                                                                |
| Multi-page order and ties       | Existing action test / PostgreSQL                                      | 53 Upcoming and 73 Past, plus 45 tied starts per view; traverse numeric pages and assert exact IDs/order for fixed fixtures/time.                                                                                                                                                        |
| Boundaries/exhaustion           | Existing action/HTTP/render tests                                      | Counts 0/1/19/20/21/40/41; exact 20/20/remainder sizes, native next/null and appropriate empty/end states.                                                                                                                                                                               |
| View validation                 | Existing HTTP test                                                     | Missing view defaults; invalid/array/blank view gives ordinary validation failure; document/Inertia request recovers at canonical home without redirect loop, JSON is 422.                                                                                                               |
| Native page resolution          | Existing HTTP test                                                     | Missing/zero/negative/noninteger/array and overflowing-invalid page values safely use native page 1; normal out-of-range positive page returns empty. Unknown cursor/reference ignored. No custom page validator.                                                                        |
| DTO projection/native scroll    | Existing action/HTTP test                                              | Native Paginator contains EventListingData; all item keys unchanged; full/partial responses have numeric scroll metadata, append ID matching and native reset contract; no custom context/cursor fields.                                                                                 |
| Live classification             | Existing action/HTTP test                                              | Advancing the server clock changes membership on the next request; no old snapshot reference persists. Do not retain a cross-request completeness guarantee.                                                                                                                             |
| Event-local date regression     | Existing action/HTTP/render tests                                      | London DST, Helsinki/New York local year/month boundaries and subsequent pages retain server labels/local dates/UTC instants.                                                                                                                                                            |
| Retrieval/query cost            | Existing action test / large PostgreSQL fixture                        | At least 1,000 Past plus 53 Upcoming, two tickets each; SQL LIMIT 21 and expected OFFSET, no COUNT; two SELECTs for nonempty/one for empty, at most 20 DTOs and 21 eager-load parents, no serialization N+1/writes. Record representative deep-page plans without a constant-cost claim. |
| Loading/error controls          | Existing React render tests plus real browser                          | Existing rows survive failure; native slots show loading/manual Retry/end, errors are escaped/generic. Use actual callback wiring checks without asserting custom coordinator behaviour.                                                                                                 |
| Actual navigation regression    | **Real browser mandatory**                                             | Activate both real anchors; correct network requests/URL/heading/data. Keyboard activation and modified clicks work. Static Link stubs are explicitly insufficient.                                                                                                                      |
| Scroll/history/races            | **Real browser mandatory**                                             | Multiple pages, switch after loading, Back/Forward/scroll restoration, switch/history during delayed page requests, reversed response order and cancellation. No Upcoming/Past mixture after native reset.                                                                               |
| Failure recovery/accessibility  | **Real browser mandatory**                                             | Offline/500/429 and recovery, failed view visit/retry, mobile, 320px/200% zoom, keyboard/focus and fresh console checks.                                                                                                                                                                 |
| Regression                      | Existing complete PHP/frontend suites                                  | Existing public foundation and remaining domain tests pass; no unrelated source changes.                                                                                                                                                                                                 |

Remove the obsolete reference/cursor/coordinator cases and coordinator test file only after explicit revised-plan approval includes those removals. Do not replace them with tests reimplementing Laravel's pagination or Inertia's internals. Native router-to-HTTP-request tests are useful integration coverage, but actual navigation is a browser acceptance check. No additional DOM/browser dependency is proposed.

Intended commands **after approval**, not run in this plan-only pass:

```bash
docker compose --profile test up -d postgres-test
php artisan config:clear --no-interaction
php artisan test --compact tests/Feature/Actions/Events/ListEventsTest.php
php artisan test --compact tests/Feature/Http/Controllers/Events/ListEventsControllerTest.php tests/Feature/ExampleTest.php
npm run test -- resources/js/tests/events-index.test.tsx
vendor/bin/pint --dirty --format agent
vendor/bin/pint app/Http/Requests/Events/ListEventsRequest.php --format agent
composer types:check
php artisan wayfinder:generate --with-form --no-interaction
npm run check
npm run types:check
npm run test
php artisan test --compact
npm run build
composer ci:check
git diff --check
git status --short
```

Rerun affected tests after changes. Browser evidence requires an actual browser and an isolated multi-page fixture session; no persistent development catalogue mutation or new QA infrastructure is implicitly authorized. No payment/concurrency fault tests apply to these reads. If required browser access is unavailable, explicitly leave the slice unverified; revision 1's automated counts are historical, not revision 2 evidence.

## 9. Implementation order and review demo

1. Obtain explicit revision 2 Gate A approval, including the manifest/removals and behavioural trade-offs. Preserve the current worktree/owner edits; no reset to an earlier slice.
2. Adapt the existing action/HTTP tests to the native paginator contract. Simplify the request/action/controller, retaining existing item DTOs. Remove the two obsolete pagination/input DTO files.
3. Replace coordinator navigation with minimal native-anchor router visits and native reset (owner-approved adjustment). Simplify feed props/state; remove the coordinator hook and approved obsolete test file. Retain the two existing page/feed component files.
4. Run focused and full checks. No additional feature, dependency, schema, service or global exception work.
5. Demonstrate actual Upcoming/Past click navigation **first** in a real browser, then multiple-page scrolling, Back/Forward, delayed/out-of-order requests, recovery and mobile/keyboard. Resolve the actual demo URL with Boost before sharing it; use isolated multi-page fixtures rather than pretending four seeded events prove scrolling.
6. Record every changed/deleted file, exact commands/results, browser observations and remaining risks in this plan. Stop at Gate B; no 2B work.

Gate B requires working real-link navigation, correct stable-fixture sorting/pagination/DTOs, basic failure states, passing applicable automated checks and actual browser observations. A remaining browser failure is not an accepted implementation. If browser checks cannot run, explicitly report them unverified rather than treating the earlier implementation's static tests as proof.

## 10. Trade-offs, questions and forbidden deviations

The owner's ten simplification decisions are the design inputs, not questions to reopen. This proposal recommends removing all three custom implementation files and the obsolete coordinator test file listed above; keeping the existing page/feed pair; and using native validation/home recovery and view-only scroll URL preservation.

Accepted/visible trade-offs for revised-plan review:

- Live classification and OFFSET pagination can skip/repeat records when time or catalogue contents change. Deterministic `(starts_at,id)` order solves ties for fixed results; it does not solve cross-request changes. Holds remain independently authoritative.
- `simplePaginate` avoids COUNT but deep Past offsets cost more database work than keyset traversal. No total or last-page number. Per-response hydration remains bounded.
- Native reset replaces accumulated data on view switching. It does not prove cancellation of an already-applying response. Adverse-order browser QA remains required; do not claim or rebuild stronger coordination before demonstrating a specific failure.
- Basic local error feedback replaces the custom retry state machine. There is no automatic recovery, timer/backoff, reference refresh or preserved intended-navigation queue. A customer can retry normally; 429 can recur until the server window permits it.
- Native invalid-page fallback replaces strict cursor errors. Invalid view input uses standard Form Request handling with a canonical home recovery destination.
- Scrolling preserves the selected-view URL; reload/shared view links begin at page one, while native history can restore loaded props/scroll. Explicit numeric page URLs remain usable.

Approval is still required for **this exact implementation revision**, including the obsolete test-file deletion. If the owner prefers retaining that file rather than deleting it, revise its scope before coding. No other material unresolved product choice is needed to present this design.

Forbidden deviations: reintroducing reference timestamps, cursors, custom pagination DTOs or request coordinators; adding dependencies/schema/services/routes; modifying unrelated files; changing the established item DTO contracts; weakened hold eligibility; or starting final visuals/calendar/details/checkout. If native behaviour proves insufficient, stop with concrete evidence and a focused alternative for owner approval.

## 11. Approval record

**Gate A — revision 2 plan approval**

- Status/date: Explicit owner approval of Revision 2 on 30 October 2026.
- Owner direction: Implement Revision 2 exactly, preserve the worktree and unrelated changes, verify real navigation where browser access exists, record evidence and stop at Gate B.
- Proposed revision: 2, active sections 1–11 above.
- Approved scope: exact replacement contracts/manifest, obsolete file/test removals, standard validation/home recovery, basic error behaviour and trade-offs.

**Gate B — revision 2 implementation acceptance**

- Owner acceptance/date: Pending. Slice is not accepted or fully verified. Slice 2B has not begun.
- Implemented: native `simplePaginate(20)` / typed DTO projection, live per-request classification, view-only Form Request with canonical home recovery, native scroll metadata/ID matching, native anchors with minimal router.visit handlers and native reset, native feed/loading/error/retry/end controls.
- Changed relative to the preserved Revision 1 worktree: `app/Actions/Events/ListEvents.php`, `app/Http/Controllers/Events/ListEventsController.php`, `app/Http/Requests/Events/ListEventsRequest.php`, `resources/js/pages/events/index.tsx`, `resources/js/components/events/event-feed.tsx`, `resources/js/types/events.ts`, `tests/Feature/Actions/Events/ListEventsTest.php`, `tests/Feature/Http/Controllers/Events/ListEventsControllerTest.php`, `resources/js/tests/events-index.test.tsx`, this plan.
- Removed as explicitly approved: `app/Data/Events/EventBrowsingData.php`, `app/Data/Events/EventListingPageData.php`, `resources/js/hooks/use-event-browsing.ts`, `resources/js/tests/event-browsing.test.tsx`. These were untracked at task start, so their removal does not appear as tracked Git deletions.
- Preserved: owner's existing `docs/slice-plans/01b-event-listing.md` edits, item DTOs, schema/models/factories/seeders/routes, dependencies and all other unrelated files. Pre-edit archive: `/tmp/slice2a-before-revision2.tar.gz`. No persistent development catalogue mutation.

### Installed API conflict requiring a decision

The earlier statement in this plan that installed React Link accepts `reset` was incorrect. Actual TypeScript checking rejects `reset` on both Links. Installed `node_modules/@inertiajs/core/types/types.d.ts` excludes it from `LinkComponentBaseProps`; `node_modules/@inertiajs/react/dist/index.js` neither consumes it nor copies it into visit parameters. It would be forwarded as a DOM attribute rather than resetting native scroll state. Static Link stubs cannot detect this runtime omission.

Owner decision (30 October 2026): **do not use a custom X-Inertia-Reset header**; use supported `router.visit()` with `reset: ['events']`, preserve native anchor semantics, add a test of the actual outgoing reset request, and require real-browser verification before Gate B. Implemented exactly that adjustment in the existing page/feed files; no coordinator/hook/queue/dependency. No custom header or type suppression was applied. The request also includes `only: ['view', 'events']` because reset visits are partial and must retrieve the selected view prop. Surviving PHP protocol tests now assert that the reset response includes view and page one.

### Actual verification (30 October 2026)

| Command                                                                                                                                                                       | Observed result                                                                                                                                                                                                 |
| ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `docker compose --profile test up -d postgres-test`                                                                                                                           | Running; initial sandbox socket denial resolved through approved escalation.                                                                                                                                    |
| `php artisan config:clear --no-interaction`                                                                                                                                   | Passed.                                                                                                                                                                                                         |
| `php artisan test --compact tests/Feature/Actions/Events/ListEventsTest.php` (test-first)                                                                                     | 32 expected failures against obsolete input contract after database access was enabled; initial sandbox attempt could not connect.                                                                              |
| `php artisan test --compact tests/Feature/Actions/Events/ListEventsTest.php tests/Feature/Http/Controllers/Events/ListEventsControllerTest.php tests/Feature/ExampleTest.php` | Passed: 59 tests, 608 assertions.                                                                                                                                                                               |
| `php artisan wayfinder:generate --with-form --no-interaction`                                                                                                                 | Passed; ignored generated output only.                                                                                                                                                                          |
| `npm run test -- resources/js/tests/events-index.test.tsx`                                                                                                                    | Passed: 13 tests.                                                                                                                                                                                               |
| `vendor/bin/pint --dirty --format agent`                                                                                                                                      | Passed after one PHPDoc alignment fix.                                                                                                                                                                          |
| `vendor/bin/pint app/Http/Requests/Events/ListEventsRequest.php --format agent`                                                                                               | Passed.                                                                                                                                                                                                         |
| `composer types:check`                                                                                                                                                        | Passed, zero errors, after approved escalation for PHPStan worker socket. Initial sandbox attempt could not listen.                                                                                             |
| `npm run types:check`                                                                                                                                                         | Failed: two unsupported `Link.reset` props. Resolved by the owner-approved router.visit change; final rerun passed.                                                                                             |
| `npm run check`                                                                                                                                                               | Initially failed formatting in this plan and three changed TSX files. Scoped formatter run on the three TSX files and this plan. Final check passed: 40 files formatted, 27 files linted, zero warnings/errors. |
| `npm run test`                                                                                                                                                                | Passed: 13 tests. Link stubs/state fixtures test wiring and output only, not browser behavior.                                                                                                                  |
| `npm run build`                                                                                                                                                               | Passed, 627 modules. Existing optional fontaine optimization warning; no dependency added.                                                                                                                      |
| `php artisan test --compact`                                                                                                                                                  | First full attempt: 183 passed, two migration errors caused by concurrent query-plan test command on the shared isolated database. Sequential rerun passed: 185 tests, 1,166 assertions.                        |
| `EVENT_PAGINATION_EXPLAIN=1 php artisan test --compact tests/Feature/Actions/Events/ListEventsTest.php --filter='bounds first and deep'`                                      | Initial concurrent run failed with migration collision; sequential rerun passed: one test, 45 assertions.                                                                                                       |
| `php artisan route:list --name=home --except-vendor`                                                                                                                          | Single existing public GET/HEAD home route.                                                                                                                                                                     |
| `git diff --check`                                                                                                                                                            | Passed at interim and final checks.                                                                                                                                                                             |
| `composer ci:check`                                                                                                                                                           | Executed: formatting/lint passed, then TypeScript failed on the two unsupported reset props; aggregate stopped before its later stages. Independent PHP and frontend suites ran as recorded above.              |

No browser automation tool, browser executable (`chromium`, `chromium-browser`, `google-chrome`, `playwright`) or installed `@playwright` package is available. No browser/dependency/config installation was performed. Boost browser logs only contained entries from before this implementation; they are not fresh verification evidence. Real navigation, scroll, history, races, errors and accessibility have **not** passed browser acceptance.

### Manual browser acceptance — required and not executed

Use the existing application at <http://localhost:8000> (resolved by Boost), starting the normal dev processes if needed. First verify navigation on the existing catalogue; the four seeded events are insufficient to accept pagination. For multi-page tests use a separate disposable catalogue/session with at least 53 Upcoming and 73 Past events (including tied starts and event-local boundary fixtures); keep persistent development data intact. No new fixture/QA infrastructure is included in this slice.

1. Open home with DevTools Network and Console visible. Upcoming must be selected. Click **Past**, then **Upcoming** using the actual links. Each must send one Inertia GET to the selected view, update the URL, heading, current marker and rows, and reset to page one. Inspect the router-generated reset header and response reset metadata. There must be no inactive-view rows. Verify Enter activation, Ctrl/Cmd-click and middle-click/new tab, then direct reload of both view URLs.
2. With the disposable multi-page fixture, scroll and use **Load more** in each view. Expect 20/20/13 Upcoming and 20/20/20/13 Past, correct ascending/descending start plus ID order, unchanged view URL, no duplicate IDs, existing rows visible while loading, and final end text without a next button. Verify server date/time/offset/price labels on subsequent pages. Direct page 3 should show that page and **Start of this view**; an out-of-range page should show empty plus Start. Successful empty page one should show only the selected empty message.
3. Load several pages, switch views and use Back/Forward. Verify selected view, rows, URL and native scroll restoration; reload/share a view-only URL should start at page one. No mixture of views or focus jump on append.
4. Throttle/delay page 2, then switch views and use history while it is pending. Repeat with reversed completion order, rapid switches and cancellation. Record request/response order and rendered IDs. Any mixture/stale application fails acceptance and requires a focused approved change; do not add a coordinator without evidence and approval.
5. Go offline before fetching the next page. Existing rows must stay; generic error plus **Retry** should appear, auto-loading should pause. Restore network and Retry; the same next page must append once and clear error. Repeat for HTTP 500 and 429 using a browser response override/proxy in the disposable session. A 429 must show “Too many requests; try again shortly”, remain manual until retry, and tolerate another 429 without timers or lost rows. Ensure raw response bodies never appear.
6. While viewing Upcoming, fail a Past visit offline/500/429. Previous URL/view/rows must remain with feedback. Restore the network and click the normal Past link to recover. Cancellation alone must not show an error. Open invalid view URLs as document and Inertia visits: recover at canonical home without looping; standard validation feedback should appear.
7. Test keyboard-only focus/Enter, load/retry disabled state during requests, visible focus and status announcements; check mobile viewport, 320px width and 200% zoom for wrapping/no blocked controls. Check a fresh console for errors throughout. Record observed results/screenshots before owner acceptance.

Remaining risks: real browser behavior is unverified; live classification/OFFSET may skip or overlap under catalogue/time changes; deep offsets do increasing database work. Holds and payment behavior are untouched.

Representative EXPLAIN evidence from `/tmp/slice2a-rev2-explain.log`: Upcoming page 1 index scan, 21 rows, 0.029 ms execution; Upcoming page 3 OFFSET 40, sequential scan/sort of 53 matches, 13 returned, 0.162 ms; Past page 1 backward index scan, 21 returned, 0.030 ms; Past page 46 OFFSET 900, sequential scan of 1,000 matches/sort producing 921 rows before Limit, 21 returned, 0.728 ms. These disposable-fixture measurements show growing database work and are not production latency guarantees. All nonempty pages use two SELECTs, no COUNT/write/lock, at most 21 ticket-parent IDs and 20 DTOs; empty page uses one SELECT.

### Verification after owner-approved router.visit adjustment

- `npm run test -- resources/js/tests/events-index.test.tsx`: **25 passed**. The actual application anchor handlers call the real installed Inertia router; the router is unmocked and the HTTP transport is mocked, with minimal host interfaces and mocked React hooks for handler extraction. Tests inspect the outgoing URL/method, router-generated reset header and partial prop headers for both views and the deep-page Start link. They also cover native modified/other-button/default-prevented/other-target activation and real-router offline/500/429 callback behavior. These are Node integration tests, not a browser pass; React state/display fixtures remain limited to output checks.
- `npm run types:check`: passed after correcting test-only fixture typing. No unsupported Link prop/type suppression remains.
- `npm run check`: passed, 40 formatted files / 27 linted files, zero warnings/errors, after fixing two test-only unbound-method warnings.
- `vendor/bin/pint --dirty --format agent`: passed.
- `php artisan test --compact tests/Feature/Http/Controllers/Events/ListEventsControllerTest.php`: **25 passed, 414 assertions** after the reset/view response assertion update.
- `npm run build`: passed, 627 modules; same existing optional fontaine warning.
- Final `composer ci:check`: **passed** end to end: formatting/lint, TypeScript, 25 frontend tests, config clear, Pint, PHPStan (zero errors), and **185 PostgreSQL/PHP tests, 1,170 assertions**.

Browser verification remains unavailable and **required before Gate B**, as the owner reiterated. The manual matrix above is the outstanding acceptance work. No claim of successful browser navigation/history/race/recovery checks is made. Implementation is ready for that verification, not owner acceptance yet.

Final preservation/diff verification: `cmp` confirmed the owner's pre-existing Slice 1B plan edits and both presentation DTO files are byte-for-byte unchanged from task start. `git diff --check` passed. Git status contains only the approved manifest files plus the preserved pre-existing Slice 1B document change. No Slice 2B work.
