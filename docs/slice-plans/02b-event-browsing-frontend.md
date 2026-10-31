# Slice 2B — correction plan after failed visual/browser acceptance

## Slice identity and approval state

- **Revision:** 5, owner-approved correction plan, 30 October 2026. Sections 1–11 remain authoritative; current execution evidence follows section 11.
- **Roadmap:** `PLANS.md` → Slice 2B. No other slice may begin.
- **Status:** **Implementation awaiting Gate B final acceptance; automated verification passed, manual browser verification pending.**
- **Implementation owner:** Codex; approved implementation delivered, stop at Gate B.
- **Owner decisions:** Q1–Q3 and Gate A retained; early visual checkpoint explicitly accepted on 31 October 2026. Owner authorized the remaining work and requires no further visual redesign/polish.
- **Gate A/date:** Approved on 30 October 2026; mandatory early visual checkpoint accepted on 31 October 2026 before remaining implementation.
- **Gate B/date:** Approved
- **Retained approvals:** Slice 2A accepted; original Margin & Lantern branding; native server pagination/Inertia scrolling/history; visual calendar showing the visitor-local current month independently of event data, noninteractive and hidden on mobile.
- **Authority:** The owner's compact-list decision supersedes displaying every DTO field in cards. All event/ticket contracts remain intact. Gate A approves only the narrowly specified seed-cover changes below. No other backend changes are proposed.

## 1. Customer-visible result and boundaries

The organizer page will use a neutral-grey background, white compact cards, a clear date rail, right-aligned photographic thumbnails and a desktop calendar/sidebar. A card shows event-local start time, title, organizer, venue name, approved category and a truthful starting ticket price. No full postal address, detailed start/end schedule, visible IANA label, ticket descriptions or full ticket breakdown. Attendee visuals are supported as presentation, but none are fabricated for the live catalogue.

Upcoming/Past, numeric server pagination, native infinite scrolling, real anchors/new-tab semantics, retry/error handling and native browser history remain. Switching views preserves scroll near Events. Delayed skeleton cards replace visible loading paragraphs; append errors retain loaded rows. Functional calendar navigation/filtering, event details and all purchasing remain deferred.

This corrects A02's appearance/browsing while preserving A04/A05's event-local date/classification coverage and A07 exclusions. Automated tests alone cannot satisfy visual/browser acceptance.

**Non-goals:** no HTTP application logic, model, schema, migration, route, DTO/wire-format, pagination algorithm, dependency, global theme, inventory or payment changes. No customer event details, ticket selection, checkout, holds, maps, search, follow, submission, filter controls, fabricated attendance/counts/sales/availability or reference branding/assets. No custom navigation coordinator, queue/state machine, history store, framework patch or replacement observer. The only database writes proposed are the explicit cover-reference upgrade in section 5; normal seed replay remains non-destructive.

### Documents, skills and implementation inspected

Read completely: `AGENTS.md`, `PLANS.md`, `docs/V1_SPEC.md`, `docs/ARCHITECTURE.md`, `docs/ACCEPTANCE_TESTS.md`, `docs/SLICE_PLAN_TEMPLATE.md`, `docs/BOOST_PROJECT_RULES.md`, the approved Slice 2A plan and the active Slice 2B plan/history. Applicable `.ai/rules` were read through their index and keyword searches. Relevant skills: Inertia React, Tailwind, Wayfinder, testing, Laravel best practices and imagegen (asset planning only).

Inspected page/feed/card/cover/hero/calendar components, scoped CSS, event types, UI primitives, frontend tests/harness, seeders and their preservation tests, Event model/schema, package scripts and installed native Inertia source. PHP Inertia **3.5.2**, React/Core Inertia **3.9.1**, React **19.3.1**, Tailwind **4.3.3**, Laravel **13**, PHP **8.5**. Version-scoped Boost documentation confirms seeder execution, conditional query-builder updates and transaction retries. No dependency change is required.

### Corrected screenshot evidence

Both newly identified images were inspected directly, at original resolution:

- **CURRENT:** `Screenshot from 2026-10-30 22-30-18.png` — Margin & Lantern, beige page, verbose Lantern Pages card, illustrated cover and static October 2026 calendar.
- **TARGET:** `Screenshot from 2026-10-30 22-30-02.png` — Venice Book Club/Luma, neutral page, compact time-first cards, photographic thumbnails, category/price badges and calendar-footer view navigation.
- Older target images `21-53-21` and `21-57-21` also inform full cover/identity and narrow layout respectively.

The previous claim that no current-app screenshot was supplied was incorrect and is withdrawn. The current screenshot is valid desktop visual evidence. It cannot establish mobile behaviour, the complete cropped cover, browser zoom or navigation/history timing. Those require actual-application captures/traces. Raw image pixels below are approximate image measurements, not inferred CSS pixels.

### Root causes and current-versus-target comparison

| Area                 | Actual current evidence                                                                                                                                                                                                                                                    | Target and required correction                                                                                                                                                                                                                                                                                                     |
| -------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Background/surfaces  | Current warm `#f6f5f1` source token is visible across the page; white cards sit on beige, with green-grey metadata/borders.                                                                                                                                                | Neutral light grey near `#f7f7f7`, white cards, near-black headings and neutral metadata. Keep green in original identity rather than tinting the UI.                                                                                                                                                                              |
| Card dimensions      | First current card is approximately 916×500 image px; target first card approximately 906×308. Comparable image widths expose the extra current height (height/width about .55 versus .34).                                                                                | Remove detailed sections before tuning spacing. Initial CSS target 190–230px desktop, natural growth on mobile/long text. Challenge proportions at matched-viewport review; never force clipping to a measured image height.                                                                                                       |
| Typography           | Current bold title leads, followed by similarly prominent venue and large blocks of schedule/ticket text. Target has a quiet time above a medium-weight title and subdued supporting rows.                                                                                 | Existing Instrument Sans; time/metadata about 14px, title 18–20px/500–600, stronger heading/secondary hierarchy. Meet contrast requirements rather than copying faint reference text literally.                                                                                                                                    |
| Spacing              | Current large schedule gap, ticket separator and multiple text rows make the card dense despite its large height. A separate notice creates a large gap above the rail.                                                                                                    | About 14–16px card padding, 4–6px supporting-row gaps, 24–32px between dated entries; quiet compact read-only notice, no schedule/ticket separator.                                                                                                                                                                                |
| Metadata order       | Current title → organizer → venue/address → full start/end/offset/zone → ticket names/descriptions/prices.                                                                                                                                                                 | Time → title → organizer → venue name → category → starting price/actual supported attendee visuals. Preserve the hidden fields in payloads.                                                                                                                                                                                       |
| Date rail            | Current year repeated in bold dates, darker small dots/dashed line. Target day/month + lighter weekday, pale dot and fine broken rail.                                                                                                                                     | Short day/month and weekday, full accessible year/ISO date, lighter decorative rail. Preserve server order and one direct keyed list item per event; no regrouping/deduplication by local date.                                                                                                                                    |
| Calendar             | Current narrow panel has both Calendar and month headings, a Display only label and a large explanatory paragraph; controls are detached above it. Target sidebar is relatively wider, with a single month heading, spacious neutral grid and view controls at its bottom. | Wider relative panel (initial 18rem), one month/year caption, brief inline Display only label, detailed explanation sr-only; real view anchors in the surrounding panel footer. Keep local-current-month, six-week static table; omit target arrows, date selection, markers and fake controls. Target January 2023 is not copied. |
| Badges               | No categories; prices are right-aligned in a full ticket breakdown. Target category pill and small green price badge provide quick scanning.                                                                                                                               | Approved violet/orange editorial badges and pale-green From £… badge. Unknown events omit category. No availability/sold-out claim.                                                                                                                                                                                                |
| Thumbnails           | Current landscape SVG is contained within a square frame, leaving prominent bands. Target square photographic covers fill their frames.                                                                                                                                    | Four original square photographic PNGs. Approximately 128–144px desktop, 88–96px mobile (80px at 320px if needed). Keep reserved square geometry and source-aware failure handling. Square sources fill existing object-contain naturally; custom/legacy art remains uncropped.                                                    |
| Organizer header     | Current crop shows original cover edge, overlapping green M&L mark, title and intro; target shows organizer identity plus additional location/social controls. Neither latest crop shows the whole cover.                                                                  | Keep original wide cover/overlapping M&L identity and title/intro; tune spacing/divider/type against full/narrow references. Do not add unsupported organizer location/social/follow data or replace the organizer cover under the event-photo approval.                                                                           |
| Navigation/loading   | Current Upcoming/Past above the grid. Source inserts normal-flow Loading events… immediately and a generic append bar. Target places desktop controls under calendar; static references cannot establish loading behaviour.                                                | One responsive set of real anchors: desktop sidebar footer, mobile above results. Approved delayed card skeletons and sr-only announcements without loading-text shifts.                                                                                                                                                           |
| Attendees/map/search | Target has avatars/counts, map and utility controls. Current DTO supplies no attendees or category field.                                                                                                                                                                  | Categories are explicitly approved editorial data. Support an attendee presentation component but supply none live; omit map/unsupported controls/counts. These truthful differences are deliberate.                                                                                                                               |

Confirmed behavioural causes: Upcoming/Past **and Start** explicitly use `preserveScroll: false`; installed Inertia `CurrentPage.set` invokes `Scroll.reset` and `window.scrollTo(0,0)` for that policy. Immediate normal-flow loading text is a second confirmed layout-shift source. The distinct reported Back/Forward/accumulated-results defect remains **unresolved pending browser trace**, not disproved or explained by a screenshot. Earlier automated tests covered contracts, not rendered fidelity/history.

## 2. Proposed file-by-file change manifest

This is the approved full correction manifest; no removals. Actual first-increment and final-increment records follow section 11. Early screenshot acceptance was received before the remaining manifest was implemented.

| Path                                                              | Action                                     | Responsibility                                                                                                                                                                                             |
| ----------------------------------------------------------------- | ------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `resources/js/pages/events/index.tsx`                             | Modify                                     | Keep page composition and handlers in place; correct shell/sidebar/nav placement and feedback geometry; later add delayed view skeleton and proven scroll option change. No new shared layout abstraction. |
| `resources/js/components/events/event-card.tsx`                   | Modify                                     | Make the existing default EventCard compact directly; format time, select truthful minimum price, use approved categories and optional supported attendee visuals. No temporary export.                    |
| `resources/js/components/events/event-feed.tsx`                   | Modify                                     | Retain direct keyed rows/native InfiniteScroll and retry ownership; later replace generic append loading with delayed matching skeleton and correct Start scroll policy.                                   |
| `resources/js/components/events/event-card-skeleton.tsx`          | Add after visual checkpoint                | Pure compact-card/date-label placeholder reusing Skeleton, no request state.                                                                                                                               |
| `resources/js/components/events/event-category-badge.tsx`         | Add                                        | Optional editorial badge using existing Badge.                                                                                                                                                             |
| `resources/js/components/events/event-attendee-stack.tsx`         | Add                                        | Supplied initials/avatar visuals using existing Avatar/Fallback; no fabricated live caller/count.                                                                                                          |
| `resources/js/components/events/event-cover.tsx`                  | Modify styling only                        | Compact reserved square sizing; retain lazy/async loading, source-specific fallback and object-contain. No frontend cover substitution.                                                                    |
| `resources/js/components/events/organizer-hero.tsx`               | Modify styling only                        | Original cover/mark/identity with corrected neutral surroundings, proportions and spacing.                                                                                                                 |
| `resources/js/components/events/visual-calendar.tsx`              | Modify presentation only                   | Single month caption, compact noninteractive grid/note; keep current-local-clock source and lifecycle. No navigation props.                                                                                |
| `resources/js/lib/event-presentation.ts`                          | Add                                        | Approved exact slug/category mapping and local category type; unknown omitted. No DTO change.                                                                                                              |
| `resources/css/app.css`                                           | Modify scoped browse styles                | Neutral tokens, compact cards/rail/badges, responsive panel/control placement and matching skeleton geometry. Preserve global tokens and local Tailwind aliases.                                           |
| `public/images/events/paper-trails-photo-v1.png`                  | Add                                        | Original generated autumn reading still-life.                                                                                                                                                              |
| `public/images/events/city-in-the-margins-photo-v1.png`           | Add                                        | Original generated book/city-window still-life.                                                                                                                                                            |
| `public/images/events/lantern-pages-photo-v1.png`                 | Add                                        | Original generated lantern/fiction-at-dusk still-life.                                                                                                                                                     |
| `public/images/events/new-year-new-chapter-photo-v1.png`          | Add                                        | Original generated winter reading/new-journal still-life.                                                                                                                                                  |
| `database/seeders/EventSeeder.php`                                | Modify four defaults only                  | New records receive the four public image URLs; retain firstOrCreate and every other seed value/behaviour.                                                                                                 |
| `database/seeders/RefreshEventCoversSeeder.php`                   | Add, explicitly opt-in                     | Guarded one-time/retry-safe legacy-cover upgrade. Never called by ordinary DatabaseSeeder.                                                                                                                 |
| `resources/js/tests/events-index.test.tsx`                        | Modify                                     | Compact projection, categories/price/time/optional attendees, loading lifecycle, router options and predecessor regressions. Inline illustrative fixtures remain test-only.                                |
| `tests/Feature/Database/Seeders/DatabaseSeederTest.php`           | Modify                                     | New-record image paths/files, unchanged exact catalogue, replay/edit preservation including covers.                                                                                                        |
| `tests/Feature/Database/Seeders/RefreshEventCoversSeederTest.php` | Add                                        | Exact legacy eligibility, conditional updates, idempotency, custom/edit preservation, missing assets/rows and atomic failure handling on PostgreSQL.                                                       |
| `docs/slice-plans/02b-event-browsing-frontend.md`                 | Modify                                     | Active proposal now; subsequent prompts/assets/checkpoint, implementation/test/acceptance evidence.                                                                                                        |
| `PLANS.md`                                                        | Modify during approved implementation only | Slice 2B correction/review tracking. Preserve now.                                                                                                                                                         |

**Unchanged:** all `app/**`, routes, models, migrations/schema, `DatabaseSeeder` and `TicketTypeSeeder`, event TypeScript contracts, packages/locks/config, bootstrap/Blade, shared UI primitives and unrelated tests/files. Keep `ticket-type-list.tsx` intact but unused in compact listings. No preview entry, fixture module, new application route, temporary card export, separate preview application or generic layout component. Ordinary Vite dev/build tooling remains.

### Exact React/TypeScript component contracts

| Component/module           | Contract and ownership                                                                                                                                                                                                                                                                                                           |
| -------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| EventsIndex                | Existing EventListingPageProps, server-selected view, existing request/error state plus local delayed-visual timer/ref. No remembered coordinator.                                                                                                                                                                               |
| EventFeed                  | Existing props and native scrolling/error ownership. Private AppendLoadingIndicator({ loading: boolean }) in the same file owns only its 200ms display delay/cleanup.                                                                                                                                                            |
| EventCard                  | { event: EventListing; attendees?: Array<{ name: string; initials: string }> }. Default export only. Derives category from approved slug mapping. Live caller passes event only.                                                                                                                                                 |
| EventCategoryBadge         | { category?: EventCategory }; renders nothing when absent. EventCategory is the local presentation type in event-presentation.ts, with label and violet/orange tone.                                                                                                                                                             |
| EventAttendeeStack         | { attendees: Array<{ name: string; initials: string }> }; render supported supplied visuals or nothing. No total/+N/remote portrait/availability contract.                                                                                                                                                                       |
| EventCardSkeleton          | { count?: number }, default one; aria-hidden presentation only, no event IDs, observer, timer or router.                                                                                                                                                                                                                         |
| EventCover / OrganizerHero | Current contracts unchanged. Cover src remains exactly the server-provided reference, not a slug-based image override.                                                                                                                                                                                                           |
| VisualCalendar             | No props. Native visitor-local current month, existing midnight/focus refresh and hydration-safe reserved frame; no relation to events/view/pages. Neutral semantic Monday-first 6×7 table, adjacent-month dates subdued, no buttons, links, tabindex, selectable/grid roles, today/event/availability markers or network calls. |

## 3. Laravel class, data and asset contracts

The HTTP path is unchanged: GET home → ListEventsRequest → ListEvents → native simple paginator/EventListingData → ListEventsController/Inertia scroll props → typed page. No route/controller/Form Request/DTO/action/model changes. Browser presentation never determines authoritative prices, capacity or payment state.

| Class                                           | Responsibility/input                                                                                                                       | Return/side effects/dependencies                                                                                                                                                                                                               |
| ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Database\Seeders\EventSeeder (existing)         | Same four fixed original event definitions; replace only cover defaults.                                                                   | run(): void; existing firstOrCreate creates missing records only. Existing Event model/database; no updated replay semantics.                                                                                                                  |
| Database\Seeders\RefreshEventCoversSeeder (new) | Explicit opt-in, fixed four-slug old-exact-reference → new-public-reference manifest; no user/browser input, force override or new config. | run(): void; validate target files before writes; transactionally update only matching cover_image_url fields; report updated/skipped/missing. Native filesystem inspection, DB query builder/transaction and inherited seeder console output. |

This is bounded seed-data maintenance, not a reusable business action or new application service. New public contracts/APIs: **not applicable**. Existing contracts retain all hidden fields.

- **Time:** native Intl.DateTimeFormat with en-GB, hour/minute two-digit, hourCycle h23 and event.timezone; show HH:mm with ISO time machine value. Test DST/year/local-date boundaries. Only the separate calendar uses the visitor's zone.
- **Venue:** supplied venueName only. No address-to-city parsing or new venue data.
- **Price:** minimum integer priceMinor among supplied ticket definitions, displaying that ticket's existing priceLabel prefixed From. No mutation/order change, fee, availability or sales inference. With no tickets, omit price rather than invent Free/Sold out.
- **Details:** address, end/zone/ticket descriptions remain in DTOs for future event details, which are not implemented.

### Q1 — approved editorial categories

| Known seeded slug    | Approved label  | Tone   |
| -------------------- | --------------- | ------ |
| paper-trails         | Reading circle  | violet |
| city-in-the-margins  | Book discussion | violet |
| lantern-pages        | Short fiction   | orange |
| new-year-new-chapter | Reading circle  | violet |

This is explicit frontend editorial presentation, not a stored category claim. Unknown slug: omit badge. No title heuristic, generic inferred category, filter or API. Q1 is settled; no renewed category approval requested.

Attendee visuals have no live source. The small presentation component is exercised with fictional names/initials in the existing test file only. No production fixture imports, mock mode/query flag or actual-app fake attendee row. The early screenshot checkpoint uses truthful application rows; it cannot demonstrate real attendance that does not exist.

### Q3 — exact asset manifest and acquisition

Choose **original generated photographs**, not stock acquisition. After final Gate A, use the available built-in image_gen tool under the imagegen skill, one distinct generation call per asset. Do not upload/use Luma screenshots or images as generation inputs; written composition guidance only. No API key, stock account, network download script, new library or dependency is required for that built-in path. Generated outputs and approved one-off preparation are recorded in the execution evidence below.

Each output is an opaque square PNG, target **1024×1024**, maximum **2 MiB per final file**. Copy only the selected output into its exact manifest path; do not overwrite existing assets. Record exact prompt, generation origin, final filename, dimensions, byte size and SHA-256 here during implementation. No separate provenance file or alternate image variants are planned.

Shared prompt direction: original editorial photographic still-life, real material textures, natural light, quiet neutral palette, clear central subject legible as a small square thumbnail. No logos, recognizable book covers/titles, text, watermark, identifiable people or real venue likeness; these are illustrative covers, not photographs documenting the actual venue/event/attendees.

| Slug                 | Final path → public cover_image_url                                                                       | Distinct prompt subject                                                                                                                                        |
| -------------------- | --------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| paper-trails         | public/images/events/paper-trails-photo-v1.png → /images/events/paper-trails-photo-v1.png                 | An unbranded open book and loose reading notes on a wooden table, soft autumn afternoon window light; tactile paper, relaxed reading-circle atmosphere.        |
| city-in-the-margins  | public/images/events/city-in-the-margins-photo-v1.png → /images/events/city-in-the-margins-photo-v1.png   | An unbranded book by a window with soft anonymous urban architecture beyond, cool daylight, intimate literary still-life; no recognizable Manchester landmark. |
| lantern-pages        | public/images/events/lantern-pages-photo-v1.png → /images/events/lantern-pages-photo-v1.png               | A small brass lantern beside an open unbranded fiction book at dusk, warm practical light against a muted background; no actual British Library setting.       |
| new-year-new-chapter | public/images/events/new-year-new-chapter-photo-v1.png → /images/events/new-year-new-chapter-photo-v1.png | A fresh linen journal, unbranded books and tea on a quiet table in crisp winter morning light; no identifiable Oodi interior.                                  |

**Tool/asset requirements:** image_gen is available; selected output quality, dimensions and weight are not yet known. Local ImageMagick/cwebp and PHP GD/Imagick are unavailable, so this plan does not assume conversion/optimization tools. Inspect the generated files before changing references. If an output fails the budget/format, regenerate with the built-in tool or stop with an exact proposed preparation-tool change for approval; no silent install, CLI/API fallback or external stock substitution. A CLI fallback would require separate explicit approval and its API credentials. Generated-file and actual-page appearance are reviewed at the early checkpoint. Existing organizer art/mark remain original and unchanged as assets.

## 4. Routes and frontend design

Routes, authorization and throttling are unchanged. Use existing Wayfinder-generated hrefs and native Inertia requests; no new endpoint, CSRF boundary or backend query.

### Actual-application layout and responsive hierarchy

```text
EventsIndex — existing Laravel/Inertia page and page-owned handlers
  Skip link / minimal original-brand header
  OrganizerHero — original wide cover, overlapping M&L mark, title/intro
  Events heading (#events), quiet read-only notice
  Responsive grid (page markup, no extracted layout abstraction)
    View panel — first in DOM, right column on desktop
      VisualCalendar — dates hidden below 1024px
      ONE real Upcoming/Past anchor set — footer desktop, above feed mobile
    Feed column
      sr-only status / visible genuine errors
      Selected-view heading
      Reserved results wrapper
        EventFeed / native InfiniteScroll keyed by view
          direct li keyed by event.id → default EventCard
            time/title/organizer/venue/category/price
            right EventCover
            optional EventAttendeeStack (none live)
          native next slot → delayed append skeleton / Load more / Retry / end
        delayed view skeleton overlay (after checkpoint; outside tracked ul)
```

Initial shell near 68rem, sidebar near 18rem, 24–32px gutter; flexible feed, no sticky/sidebar-inner-scroll region. Under 1024px hide only calendar dates; keep the same real view anchors above results. Mobile 16px page padding; right thumbnail retained at 320/390px, natural long-title/venue wrapping. At extreme zoom allow stacking rather than clipping. No duplicated hidden focusable controls or interactive category chips.

First visual increment modifies the existing default EventCard and page directly, so all real rows use the design. Review focuses on **one representative Lantern Pages card within the real catalogue**; do not artificially truncate pagination to one row, create a staged export or add mock rows to production. Existing navigation behaviour stays intact until the screenshot stop; the early checkpoint is visual, not final history acceptance.

### Styling, shadcn reuse and accessibility

Reuse Instrument Sans, Card, Badge, Button, Avatar/Fallback, AlertError, Skeleton, Lucide and cn. Existing shadcn-style primitives are sufficient; do not run shadcn CLI or add a calendar package. A date-picker component would imply unsupported interaction and extra state.

Scoped browse colours/aliases only: proposed page #f7f7f7, white cards, #1a1a1a headings, metadata near #6b6b6b, decorative rails/dividers #d8d8d8/#e6e6e6. Violet/orange category tints; pale-green price badge with readable dark text. Keep global light/dark themes and local Tailwind --color aliases; provide neutral dark equivalents. These are design starting values, not claimed pixel samples from Luma.

Maintain skip link, one H1, Events H2, selected-view heading and semantic list/articles. Date rail has full accessible date/year and ISO dateTime despite short visible labels. Navigation stays anchors with aria-current, not fake tabs; preserve Enter, modified click and new-tab semantics. Visible focus, text contrast ≥4.5:1, meaningful control/focus contrast ≥3:1, usable touch spacing, reduced-motion skeletons, 200% zoom/400% reflow. Avoid duplicate image/title announcements; preserve escaped content and broken-image fallback. Static calendar caption and accessible noninteractive explanation, no keyboard dates. Loading status polite/sr-only; real errors visible; hidden old content inert and not announced.

### Skeleton behaviour — concrete proposed policy

- Page `navigating` still updates immediately to pause native auto-load. Start a **200ms** local timer only for a request to a different view; do not show a skeleton for a response completing before the threshold. Keep old rendered results during this interval. No minimum visible dwell or artificial network delay.
- When threshold elapses, render two `EventCardSkeleton` placeholders as an absolute overlay aligned to the compact list. Keep underlying real feed mounted and its height reserved; use visibility/inert/ARIA treatment so hidden old content is neither tabbable nor announced. Skeletons are outside the tracked `ul`, have no event/page IDs, and are `aria-hidden`.
- Keep a two-card minimum footprint for empty/very short content in the wrapper at idle too, avoiding a height insertion when navigation starts. Longer real lists retain their existing footprint while pending. New successful results can naturally change total document height; the top Events/card anchor must remain stable. Test short/empty-result clamping explicitly.
- One screen-reader-only polite status announces the requested view; remove the visible `Loading events…` paragraph. Real errors remain visible; they are not loading placeholders.
- Clear delay and visual state on finish/error/cancel/unmount; do not remember loaders in browser history. Late timers must never hide restored cards. PreserveState remains false, so successful view replacement/remount clears transient display state.
- Append uses one correctly sized compact skeleton below existing rows in the native next slot, also delayed 200ms by the feed-private AppendLoadingIndicator. Its `loading === false` branch immediately renders nothing, even before effect cleanup; do not leave a stale skeleton during native scroll restoration. Existing cards are never removed/replaced on append.
- Announce append loading with sr-only status; retain disabled Load more/Retry while loading/navigating, same generic errors and manual Retry after offline/500/429. Failed append retains the full existing list and next-page metadata; no automatic retry/backoff timer. Reduced motion disables skeleton pulse.

This is local display state and a timer, not a navigation coordinator/state machine. Do not unmount InfiniteScroll to show a placeholder or insert skeleton list children into its tracked items.

### Scroll/history diagnosis and bounded correction

Installed source and [Inertia v3 scroll guidance](https://inertiajs.com/docs/v3/advanced/scroll-management) agree on the confirmed reset cause. Read `node_modules/@inertiajs/core/dist/index.js` sections `CurrentPage.set`, `Scroll.reset/restore`, `EventHandler.handlePopstateEvent`, `History`, `useInfiniteScrollData/elements`; React App/InfiniteScroll use synchronous swaps and native loading cleanup.

**Confirmed view-switch correction:** change normal Upcoming/Past and Start visits to `preserveScroll: true`. Keep `preserveState: false`, native `reset: ['events']`, `only: ['view','events']`, Wayfinder hrefs and all modified-click guards. Existing control placement keeps activation near Events; removing normal-flow loading text and preserving feed geometry addresses the other confirmed cause. No forced scroll-to-top, smooth animation, mount-time scrollIntoView, hash rewrite or new scroll store. If a short/empty target still clamps away from Events in the required browser test, show the trace and propose a focused native-anchor correction for approval; do not silently bolt on global scrolling.

**PreserveState analysis:** in installed React App, false replaces the page key/remounts transient local state. It does not itself call scroll reset. Native popstate also uses a fresh page swap and then restores saved scroll. Changing it to true speculatively risks carrying loader/error state across views and is not proposed.

**Native history:** popstate cancels pending native requests, restores stored page props, synchronously swaps the page and schedules saved document/region scroll restoration. InfiniteScroll has its own remembered pagination/element ranges and cleanup; direct keyed items and reserved image geometry must be preserved. `preserveUrl` alone is not evidence that history cannot work: native element remembering also persists page data. No replacement history implementation or framework patch is justified by static inspection.

**Unknown separate history fault:** owner reproduction is authoritative as a failure report, but its cause is not established by logs/source alone. Before any separate history fix, perform the diagnostic matrix below on the unchanged implementation. Trace native history props/scroll before/after append, before leaving, on restoration and after layout/observer settling. Distinguish wrong stored rows from correct rows with wrong geometry/scroll, and a late response from a restore problem. Also test immediate Back during native element bookkeeping versus a settled load. Inspect whether transient skeletons, image/font geometry or response order is implicated. These are investigation branches, not asserted causes.

Use native history/scroll/reset as-is unless an observed trace proves a further change necessary. If the distinct bug remains, pause its implementation with evidence and a minimal revised proposal. Approval of this plan does not authorize a speculative coordinator or framework configuration/package change.

## 5. Database, seed references and existing development data

**Schema/migrations/indexes/constraints: not applicable**; existing events.cover_image_url text field already supports local public paths. No new database fields, APIs or default-seeder update behaviour.

### New records and ordinary replay

EventSeeder changes only its four cover_image_url defaults from embedded SVG data URIs to section 3's public paths. Retain firstOrCreate keyed by slug. Titles, descriptions, venues/addresses, dates/zones, organizer, identities, ticket definitions/prices/capacities and timestamp/replay behaviour stay unchanged. DatabaseSeeder and TicketTypeSeeder are untouched. **Ordinary db:seed must preserve both customized covers and untouched legacy covers on existing rows**, as well as every other edit. Never use updateOrCreate, upsert, updateOrInsert, truncate or migrate:fresh on the development database.

### Explicit existing-data upgrade

A separate RefreshEventCoversSeeder, never called by DatabaseSeeder, provides the approved implementation path for existing development rows after final Gate A:

1. Freeze the exact four current SVG data-URI values from the pre-change EventSeeder into the upgrade's four-slug manifest, alongside new URLs. Compare the complete legacy value, not a data: prefix, partial SVG, title or “any known slug”.
2. Validate all four new files exist, have PNG MIME/dimensions 1024×1024 and meet the byte budget **before database writes**. Native PHP getimagesize/filesystem checks require no GD/image library. Missing/invalid file aborts with no changes.
3. Use Boost read-only queries to report eligible (exact legacy), already-new, custom and missing rows before applying. Preserve the pre-change references/evidence here; do not publish a fresh default seed over current edits. Prefix-only inspection cannot prove eligibility.
4. Run the opt-in command below only as the explicitly planned development upgrade after Gate A, file validation and focused tests. In one short transaction (attempts: 3), visit the four slugs in sorted order. Conditional query-builder UPDATE requires the exact slug AND expected full legacy cover value, and writes ONLY cover_image_url. No stale read followed by unconditional save, no Eloquent timestamp touch. A concurrent custom-cover edit makes the predicate fail; leave it untouched.
5. Report changed/already-new/custom/missing counts. Do not insert missing events, update unknown slugs, force a custom cover, modify ticket rows or any other event field/audit timestamp. Replay is a no-op. A database failure rolls back all updates; retry remains safe.

```bash
php artisan db:seed --class=RefreshEventCoversSeeder --no-interaction
```

This is a separate opt-in operation, **not silently changed normal seeding**. Final Gate A approval of this exact plan authorizes applying only its eligible legacy replacements in this development workspace. No production --force or destructive reset is proposed. Custom/missing rows are reported, not “fixed”; if they prevent showing the approved photographs at the checkpoint, explain that limitation rather than inventing a live source override.

Rollback, if requested: retain the prior-reference report and use a separately reviewed conditional reversal only where the current value still equals that generated path; preserve newer edits. No automatic data rollback or migration.

## 6. State transitions and transaction protocol

Customer inventory/order/payment transactions and locks: **not applicable**, untouched. The page remains read-only, with existing live-time classification, LIMIT/OFFSET and ID-match merge limitations.

Only seed-asset maintenance has new persistence: known exact legacy reference → generated public reference, inside the single short transaction above. Sorted slug order, three deadlock attempts, no inventory/ticket locks or network/filesystem generation inside the transaction. PostgreSQL remains authoritative; no cache/persistent client state.

Local page/feed loading remains native request pending → delayed visual placeholder if slow → new results or old results/error; append pending → append or old rows/manual Retry. Native Inertia owns cancellation/order/history. Existing calendar clock and image failure state stay local. No state machine or navigation abstraction.

## 7. Idempotency, failures and external integrations

No checkout/idempotency key, Stripe, jobs, webhook, refund or scheduling integration applies. Preserve rate limiting, generic customer errors and manual retry. Never add automatic retry/backoff or full-catalogue fetches.

Image generation is an implementation-time tool, not an application integration. The app serves committed same-origin public files without an image service or API key. Broken references still use the existing reserved fallback. Optional attendee test fixtures never reach live props.

Seed upgrade is idempotent through its exact-old-value predicate; custom/unknown/current references are skipped, missing/invalid images abort before writes and transaction failure rolls back. Main seeder edit-preservation behaviour is independently retained/tested.

## 8. Test and browser verification matrix

### Frontend automated coverage

Use the existing tests/harness, real page handlers/router request assertions and native Inertia component contract. Tests mock React lifecycle/transport/observer behaviour; they cannot prove rendered geometry, IntersectionObserver, scroll restoration or browser focus.

| Scenario                  | Setup / expected result                                                                                                                                                                                                  |
| ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Compact projection        | Full real-shaped fixture: time/title/organizer/venue/thumbnail and truthful minimum price shown; address/end/IANA/ticket descriptions/full breakdown absent from visible listing. Escaping retained.                     |
| Event-local time/date     | London DST fold, Helsinki year boundary, prior-local-date New York and different host zones; correct HH:mm, ISO machine time, server order and event-local rail.                                                         |
| Starting price            | Unsorted/equal/one/empty ticket definitions; matching minimum's server label, no mutation or invented free/availability/count.                                                                                           |
| Approved categories       | Four exact labels/tones; unknown slug omitted, no heuristic/filter/API.                                                                                                                                                  |
| Optional attendee visuals | Inline fictional initials only in tests exercise the small component/card. Ordinary page props render no attendees or counts. No preview entry, fixture import or live mock switch.                                      |
| Fast/slow view skeleton   | 199ms/200ms, fast finish, same-view activation, cancel/error/unmount; no early flash or stale timer; sr-only status, old rows recover on failure.                                                                        |
| Append/retry              | Delayed append below real rows; no skeleton children in tracked ul. Offline/500/429 retain existing rows and native next metadata; manual retry targets same page once.                                                  |
| Navigation options        | Real router visit: preserveScroll true, preserveState false, reset events and only view/events. Modified/default-target activation preserved. This tests wiring, not pixels.                                             |
| Calendar                  | Current visitor-local month even with zero events and Upcoming/Past/page changes; leap/year/TZ boundaries, native clock/midnight/focus cleanup, no interactive dates/markers/request; mobile hiding verified in browser. |
| Covers/empty/restart      | Local photo URL passed unchanged, legacy/custom sources still supported, lazy reserved geometry and source replacement/error fallback; existing empty/deep-page Start cases retained.                                    |
| Contracts/regression      | Existing PHP action/controller tests retain all DTO fields/values, ordering/pagination/merge/reset behaviour. Change frontend visibility assertions, not backend contracts.                                              |

### Asset and seed coverage on real PostgreSQL

| Scenario                    | Expected evidence                                                                                                                                                                                                                                                                                     |
| --------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Fresh normal seed           | Same four events/eight tickets and original business fields; exact four new public references; each file exists, PNG 1024×1024, ≤2 MiB and distinct. Replace old embedded-SVG expectations with the approved paths/files.                                                                             |
| Ordinary seed replay        | Full row/ticket snapshot including IDs/timestamps unchanged; explicitly cover legacy and custom cover references, edited title/venue/ticket price/capacity and unrelated rows. Existing preservation tests remain.                                                                                    |
| Opt-in exact legacy upgrade | Four authentic frozen old values update to their matching new paths; only cover fields differ. Titles/other edits/tickets/audit timestamps unchanged.                                                                                                                                                 |
| Eligibility boundaries      | Custom external/local/data-URI cover, similar-but-different SVG, unknown slug, missing row, already-new path: skipped. No insert, broad prefix update or normal-seed invocation.                                                                                                                      |
| Retry/failure safety        | Second upgrade no-op. Missing/invalid asset aborts before any writes; injected database failure rolls back all cover changes; subsequent retry succeeds. Test with real PostgreSQL queries, not mocked query builder.                                                                                 |
| Concurrent edit protection  | Deterministic second-connection custom-cover change before the guarded update must survive. Use test-scoped orchestration, no production test hooks/service/locks. Atomic exact-value predicate remains in the UPDATE.                                                                                |
| Replay after opt-in         | Normal DatabaseSeeder still leaves the upgraded/customized catalogue snapshot intact.                                                                                                                                                                                                                 |
| Static delivery             | Filesystem/PHP tests check file metadata and DTO URL values. Browser/server checks HTTP 200 and image/png, square render, lazy loading, transferred bytes and no image/layout error. Do not add a Laravel image route merely to satisfy HTTP tests; public static files are served by the web server. |

No high-contention inventory test applies to these cover fields; no inventory semantics change. Existing full regression still runs. Asset generation is not repeated in tests.

### Required actual-application browser diagnosis and acceptance

No usable browser automation/executable has been established in this environment; no browser tests are claimed. Use an available browser/operator without installing packages silently. If unavailable, hand these exact manual steps to the owner and leave verification pending.

| Check                       | Protocol and pass condition                                                                                                                                                                                                                                                                                                                                                       |
| --------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Baseline                    | Supplied current screenshot is the desktop baseline. Before navigation corrections, also capture the actual app at 1440/390 CSS px (320 stress), recording viewport/zoom/DPR/full header; compare with supplied target without assuming matching image-pixel scale.                                                                                                               |
| Reset diagnosis             | At Events, record scrollY and Events rect before click/start/response/render/RAF for Upcoming/Past and Start. Known policy resets to top; corrected policy stays near Events, including long→short/empty views.                                                                                                                                                                   |
| History diagnosis           | Disposable fixed catalogue: load ≥3 pages, record URL/view/rendered IDs/native page props and saved scroll/ranges before leaving and on Back/Forward after layout settles. Repeat immediate versus settled Back; use installed native APIs/devtools, no custom history interception. Identify wrong stored props, geometry or response timing before a further fix.               |
| Mandatory visual checkpoint | Actual Laravel/Inertia home, real compact rows/new approved covers/categories/minimum prices, no mocks; focus one representative Lantern Pages card. Desktop 1440, mobile 390 and 320, full header/calendar/controls/date rail. Present current/target/corrected comparison and asset metadata; stop for explicit owner screenshot acceptance before skeleton/navigation changes. |
| Photographs                 | Inspect all four in Upcoming/Past, normal/degraded network and broken source. Confirm original anonymous still-lifes, no copied branding/false attendee/venue implication, correct paths/MIME/bytes and no letterboxing or layout shift for square photos.                                                                                                                        |
| Final visual fidelity       | Compare both views at matched CSS viewports: neutral colours, compact density, time-first hierarchy, padding/type, date rail, square imagery, badges, original header, calendar/footer controls. Intentional absences: invented attendee counts, maps/search/follow and interactive calendar.                                                                                     |
| Final history/scroll        | Several loaded pages → switch view → Back/Forward repeatedly; deep page→Start; restore exact view/loaded rows/reference-card position with no duplicate/stale IDs or forced Events anchor on popstate.                                                                                                                                                                            |
| Pending races               | Slow append then switch/history, reversed completion, rapid cancellation; no old-view rows or stale loader. Failure requires trace and minimal approved correction, not a coordinator.                                                                                                                                                                                            |
| Loading stability           | Cached/100ms versus 800ms view/append requests. No fast flash/visible loading line; slow card skeletons sized correctly; Events/control/content anchor stable while pending; append rows survive offline/500/429. Check focus and layout-shift trace.                                                                                                                             |
| Accessibility/responsive    | 320/390/768/1024/1440px; long text/empty views; light/dark/reduced motion; 200% zoom/400% reflow. Keyboard skip/nav/Load more/Retry, screen-reader headings/status/errors. One navigation, no clipped text or date tab stops, mobile calendar hidden while anchors remain.                                                                                                        |
| Failure/privacy             | View/append/Start offline/500/429, retry, delayed fonts and broken images. Correct generic errors/manual recovery, no data loss/duplicate request/raw exception/new browser log error.                                                                                                                                                                                            |

Use existing disposable multi-page fixtures (at least 53 Upcoming/73 Past, tied/local-boundary starts) and fixed clock/order; persistent development rows are not rewritten for tests. Four production seeds cannot prove infinite scrolling. Resolve application URLs with Boost get-absolute-url before sharing; the checkpoint is the existing Laravel home, not a Vite HTML page.

### Full correction verification commands — actual first-increment results below

```bash
npm run test -- resources/js/tests/events-index.test.tsx
TZ=Pacific/Honolulu npm run test -- resources/js/tests/events-index.test.tsx
TZ=Pacific/Kiritimati npm run test -- resources/js/tests/events-index.test.tsx
npm run types:check
npm run check
docker compose --profile test up -d postgres-test
php artisan test --compact tests/Feature/Database/Seeders/DatabaseSeederTest.php tests/Feature/Database/Seeders/RefreshEventCoversSeederTest.php
php artisan test --compact tests/Feature/Actions/Events/ListEventsTest.php tests/Feature/Http/Controllers/Events/ListEventsControllerTest.php
vendor/bin/pint --dirty --format agent
composer ci:check
npm run build
git diff --check
git status --short
```

Focused tests after each meaningful increment; full composer ci:check covers JS checks/tests, PHPStan, Pint check and full PHP regression. Use the existing isolated PostgreSQL test service; do not run migrating suites concurrently on one database. Dirty PHP formatting now applies because the asset seeders/tests change. No manual route generation or dependency installation. Record command exit/results and actual counts during implementation, not predicted passes.

## 9. Implementation order and mandatory review stops

1. **Gate A — complete:** owner approved revision 5 on 30 October 2026. The approved sequence below remains; step 5 is accepted, steps 6–7 implementation/automated checks are recorded below, and execution stops at Gate B.
2. **After approval, baseline diagnosis:** use the actual app/supplied current image and browser trace before changing navigation. Record confirmed reset source and any distinct history failure; unresolved history stays unresolved until reproduced.
3. **Prepare approved assets/data safely:** generate/select four original square photos; inspect dimensions/size/provenance, record exact prompts/hashes. Change only new-record seed defaults; implement/test the separate guarded upgrade, preservation and asset checks. Report eligibility, apply only exact legacy replacements, retain custom edits. No reset/reseed workaround to obtain attractive screenshots.
4. **First visual increment in the actual application:** update existing page/hero/calendar/control layout and default EventCard directly, photo sizing, approved categories and truthful prices. Reuse existing primitives; all real rows still paginate normally. Finish sufficient styling for one representative Lantern Pages card and the surrounding full layout to be reviewable. Keep navigation behaviour/loading mechanism unchanged for now; no standalone entry, temporary export or live mock.
5. **MANDATORY OWNER SCREENSHOT STOP:** focused relevant tests/TS/checks, capture actual desktop/mobile/320px page and representative card plus all asset subjects. Present current/target/corrected comparison, exact changed files, preserved-data report and limitations. **Wait for explicit owner screenshot acceptance before implementing skeletons or navigation corrections.** If capture unavailable, give actual-app manual steps/request owner captures; automated passes do not waive the stop.
6. **Only after screenshot acceptance:** implement the approved 200ms delayed view/append skeletons, stable geometry/sr-only loading and confirmed preserveScroll correction, with focused lifecycle/router tests. Complete remaining loading/error/empty/retry/end states against the accepted design. No speculative history fix; a distinct failure requires trace and a minimal revised approval.
7. **Final verification/Gate B:** full commands/regression/build, real browser visuals/a11y/history/scroll/races, all four asset delivery checks. Record every changed file, actual commands/results/screenshots, deviations and remaining checks here; update only Slice 2B roadmap tracking. Stop for owner Gate B acceptance; do not start another slice.

The early review is a mandatory checkpoint inside Slice 2B. A static screenshot approval does not establish navigation/history acceptance. Final demo must include both real views, loaded pages/history and failure recovery.

## 10. Settled decisions and remaining approval requirements

**Settled, no renewed decision request:** Q1's four category labels/tones and unknown omission; Q2's real Laravel/Inertia checkpoint and removal of standalone preview/staged-card proposals; Q3's original photographic-style event covers. Visitor-current-month, noninteractive desktop-only calendar and native navigation/pagination remain approved.

**Gate A and early visual checkpoint approved:** owner accepted the design as sufficient for v1 on 31 October 2026 and authorized remaining loading/navigation work. No redesign/polish is requested. **Remaining approval: Gate B final acceptance after the focused browser checks below.**

**Asset status:** four original covers generated and prepared to the PNG dimensions/budget. Owner explicitly approved one-off pngjs preparation after the generator returned 1254px images twice. No new dependency or runtime pipeline. Photographic composition/page appearance were accepted at the early checkpoint; no additional asset work is proposed.

**History requirement still outstanding:** actual reproduction/trace of the separate reported Back/Forward defect. No unsupported diagnosis or custom fix is approved by this plan. A trace proving a further material change needs a focused revised proposal.

Trade-offs: known-slug editorial categories remain small/static; unknowns omit badges. Optional attendee presentation has test fixtures but no live data or fabricated checkpoint visuals. Generated illustrative covers do not claim to depict venues/attendees. Full details stay in the payload for later pages. PNGs avoid a new image-processing dependency but require byte/network review. Legacy/custom covers are preserved unless exact old references qualify for the explicit upgrade. Native scroll preservation can encounter short-document clamping, to be verified before any further anchor policy.

Forbidden without fresh approval: schema/routes/DTO/business-query changes, different seed replay semantics or overriding custom edits, reference assets/branding, new dependencies/services/preview infrastructure/history coordination, calendar actions, customer management/purchase functionality, unrelated cleanup or test deletion.

## 11. Approval and evidence record

- **Revision 3:** implemented after its Gate A; Gate B explicitly rejected. Historical commands below do not prove current visual/browser acceptance.
- **Revision 4:** proposed correction only, not implementation authorization.
- **Revision 5:** Gate A explicitly approved on 30 October 2026; first visual increment delivered.
- **Earlier planning pass:** only this document changed; planning-only checks below are historical.
- **Early visual checkpoint:** explicitly accepted on 31 October 2026. The owner states the current design is good enough for v1; remaining work proceeded only after this authorization.
- **Correction Gate B:** implementation and automated checks delivered for final review. Manual browser checks and explicit owner acceptance remain pending. No next slice.
- **Approved deviation:** one-off pngjs asset preparation, explicitly approved in this turn. No unapproved deviation.

Planning-only verification for revision 5:

| Command                                                                                                             | Actual result                                                                                                    |
| ------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| node_modules/.bin/vp fmt docs/slice-plans/02b-event-browsing-frontend.md --write, then --check                      | Passed; document formatting only.                                                                                |
| git diff --check                                                                                                    | Passed for tracked changes.                                                                                      |
| git diff --no-index --check -- /tmp/slice2b-correction-revision4.md docs/slice-plans/02b-event-browsing-frontend.md | No whitespace diagnostics; exit 1 because the untracked plan differs from its revision 4 backup. No Git staging. |
| sha256sum --status -c /tmp/slice2b-revision5-protected.sha256                                                       | Passed; all 181 other tracked/untracked files byte-for-byte preserved.                                           |
| git status --short                                                                                                  | Existing worktree retained; only this already-untracked plan edited in this pass.                                |

No application tests/build or browser checks ran in this planning task. Read-only source/docs/schema inspection is not browser testing.

## Revision 5 — first increment and accepted early visual checkpoint (historical evidence)

**First increment stopped at step 5 on 31 October 2026; the owner subsequently accepted it and authorized the remaining work.** This is the actual Laravel/Inertia application, not a preview app. All real rows use the default compact card, with one representative Lantern Pages card as the review focus. No mock rows/attendees or artificial one-row catalogue. Screenshot acceptance was received after this first-increment record; final evidence is recorded below.

### Actual changed files relative to this task's preserved worktree

| File                                                              | First-increment change                                                                              |
| ----------------------------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| `PLANS.md`                                                        | Slice 2B early-review tracking only.                                                                |
| `docs/slice-plans/02b-event-browsing-frontend.md`                 | Approvals, prompts/assets, actual evidence and checkpoint.                                          |
| `resources/css/app.css`                                           | Scoped neutral colours, compact cards/badges/rail/calendar. Global theme/original artwork retained. |
| `resources/js/pages/events/index.tsx`                             | One responsive calendar/navigation panel. Existing request/error/loading handlers retained.         |
| `resources/js/components/events/event-card.tsx`                   | Default compact card; event-local time, truthful price, category and optional supported attendees.  |
| `resources/js/components/events/event-category-badge.tsx`         | New optional existing-Badge presentation.                                                           |
| `resources/js/components/events/event-attendee-stack.tsx`         | New optional Avatar presentation; no fake live source/count.                                        |
| `resources/js/components/events/event-cover.tsx`                  | Compact square sizing; source/failure/lazy handling retained.                                       |
| `resources/js/components/events/organizer-hero.tsx`               | Original identity/art; type, size and spacing adjustment.                                           |
| `resources/js/components/events/visual-calendar.tsx`              | Compact static grid/caption; clock calculation/lifecycle unchanged.                                 |
| `resources/js/lib/event-presentation.ts`                          | Exact approved category mapping; unknown omitted.                                                   |
| `resources/js/tests/events-index.test.tsx`                        | Retained regressions; compact projection/time/min-price/category/attendee coverage.                 |
| `database/seeders/EventSeeder.php`                                | Four new-record cover defaults only; firstOrCreate unchanged.                                       |
| `database/seeders/RefreshEventCoversSeeder.php`                   | New opt-in validated conditional update; sorted transaction/retries/counts.                         |
| `tests/Feature/Database/Seeders/DatabaseSeederTest.php`           | Image paths/files and custom-cover preservation.                                                    |
| `tests/Feature/Database/Seeders/RefreshEventCoversSeederTest.php` | New real-PostgreSQL replay/eligibility/concurrent-edit/failure/legacy-preservation cases.           |
| `public/images/events/paper-trails-photo-v1.png`                  | New original generated photograph, approved resize to 1024×1024 PNG.                                |
| `public/images/events/city-in-the-margins-photo-v1.png`           | New original generated photograph, approved resize to 1024×1024 PNG.                                |
| `public/images/events/lantern-pages-photo-v1.png`                 | New original generated photograph, approved resize to 1024×1024 PNG.                                |
| `public/images/events/new-year-new-chapter-photo-v1.png`          | New original generated photograph, approved resize to 1024×1024 PNG.                                |

Twenty files changed/created; none removed. Four supplied screenshots and existing worktree changes preserved. `event-feed.tsx` is byte-for-byte equal to `/tmp/slice2b-rev5-before-feed.tsx` despite its pre-existing Git modified status. Its loading/native scroll/retry implementation remains unchanged. No EventCardSkeleton, previews, temporary export, coordinator, schema, HTTP backend, route or dependency change.

### Asset provenance and exact selected-generation prompts

Four independent built-in image_gen calls, with no reference-image inputs, created the selected originals. A fifth size-correction call still returned 1254×1254 (2,427,601 bytes) and was not selected. Selected originals remain unchanged in:

`/home/vscode/.codex/generated_images/01a18eca-5193-70c2-aa09-027b2be78a07/`

Selected originals were 1254×1254; two exceeded the final byte limit. Owner approval: **“Approve one-off pngjs preparation.”** Used already-installed pngjs **7.0.0**, once, for full-image bilinear downsampling to 1024×1024 and PNG compression (opaque RGB, deflate level 9). No crop, source overwrite, dependency/lock change, committed processing script or runtime pipeline. No stock acquisition or Luma asset.

All four final assets are valid square 1024×1024 PNGs under 2,097,152 bytes. Composition inside desktop/mobile screenshots remains for owner review.

| Final file                                               | Bytes   | Final SHA-256                                                      | Original generated filename                     |
| -------------------------------------------------------- | ------- | ------------------------------------------------------------------ | ----------------------------------------------- |
| `public/images/events/paper-trails-photo-v1.png`         | 1339150 | `fec71ac6b252a72a3aa181587a17f2ff224eae1b0510d3142341982d6bc73809` | `exec-69b51b17-f63b-46c4-99ad-72c27a3fab31.png` |
| `public/images/events/city-in-the-margins-photo-v1.png`  | 1213783 | `492ef12d27b5752cb5c32be0b6763e498636099eefb8a264945bed993e4b5f6e` | `exec-5d5c5116-ca44-498b-849f-c8afc27d64a1.png` |
| `public/images/events/lantern-pages-photo-v1.png`        | 1210539 | `7f3cce41377ce6cde542f41970f57649272fd7d0bda36d3816e23150b5816ce6` | `exec-ade238eb-fccf-4bc0-8f05-ce84974de77b.png` |
| `public/images/events/new-year-new-chapter-photo-v1.png` | 1462779 | `09d9e3e6008659ea767e0d8ec5adfbb8fb3f3939f0c90d115ebf7bdc2370ad0b` | `exec-51091b1f-f158-49d1-8799-e65b58cb13c1.png` |

Exact selected prompts, built-in mode:

**paper-trails**

```text
Use case: photorealistic-natural
Asset type: original editorial event cover for Margin & Lantern reading gatherings, square thumbnail.
Primary request: An unbranded open book and loose reading notes on a wooden table, soft autumn afternoon window light; tactile paper, relaxed reading-circle atmosphere.
Style: photographic editorial still-life, real material textures, natural light, quiet neutral palette, a clear central subject legible at small square sizes.
Composition: full-bleed square 1024 by 1024, opaque PNG; centre-safe composition with a simple background. Target final PNG under 2 MiB.
Constraints: original illustrative imagery, not documentation of an actual venue, event or attendees. No text, logos, recognizable book titles or covers, watermarks, identifiable people, or real venue likeness. No Luma branding or assets. Books may show soft indistinct page texture without legible writing.
```

**city-in-the-margins**

```text
Use case: photorealistic-natural
Asset type: original editorial event cover for Margin & Lantern reading gatherings, square thumbnail.
Primary request: An unbranded book by a window with soft anonymous urban architecture beyond, cool daylight, intimate literary still-life; no recognizable Manchester landmark.
Style: photographic editorial still-life, real material textures, natural light, quiet neutral palette, a clear central subject legible at small square sizes.
Composition: full-bleed square 1024 by 1024, opaque PNG; centre-safe composition with a simple background. Target final PNG under 2 MiB.
Constraints: original illustrative imagery, not documentation of an actual venue, event or attendees. No text, logos, recognizable book titles or covers, watermarks, identifiable people, or real venue likeness. No Luma branding or assets. Books may show soft indistinct page texture without legible writing.
```

**lantern-pages**

```text
Use case: photorealistic-natural
Asset type: original editorial event cover for Margin & Lantern reading gatherings, square thumbnail.
Primary request: A small brass lantern beside an open unbranded fiction book at dusk, warm practical light against a muted background; no actual British Library setting.
Style: photographic editorial still-life, real material textures, natural light, quiet neutral palette, a clear central subject legible at small square sizes.
Composition: full-bleed square 1024 by 1024, opaque PNG; centre-safe composition with a simple background. Target final PNG under 2 MiB.
Constraints: original illustrative imagery, not documentation of an actual venue, event or attendees. No text, logos, recognizable book titles or covers, watermarks, identifiable people, or real venue likeness. No Luma branding or assets. Books may show soft indistinct page texture without legible writing.
```

**new-year-new-chapter**

```text
Use case: photorealistic-natural
Asset type: original editorial event cover for Margin & Lantern reading gatherings, square thumbnail.
Primary request: A fresh linen journal, unbranded books and tea on a quiet table in crisp winter morning light; no identifiable Oodi interior.
Style: photographic editorial still-life, real material textures, natural light, quiet neutral palette, a clear central subject legible at small square sizes.
Composition: full-bleed square 1024 by 1024, opaque PNG; centre-safe composition with a simple background. Target final PNG under 2 MiB.
Constraints: original illustrative imagery, not documentation of an actual venue, event or attendees. No text, logos, recognizable book titles or covers, watermarks, identifiable people, or real venue likeness. No Luma branding or assets. Books may show soft indistinct page texture without legible writing.
```

### Development-data evidence

- Exact-reference read-only preflight: IDs 1/city-in-the-margins, 2/lantern-pages, 3/new-year-new-chapter, 4/paper-trails all **eligible exact legacy**; none custom/missing/already-new.
- Before snapshot: `/tmp/slice2b-rev5-development-before.json`. Frozen legacy references: `/tmp/slice2b-rev5-legacy-covers.json` and the new seeder manifest. Pre-edit worktree archive: `/tmp/slice2b-rev5-before.tar.gz`.
- `php artisan db:seed --class=RefreshEventCoversSeeder --no-interaction`: **passed, 4 updated, 0 already-new, 0 custom/skipped, 0 missing.**
- Full before/after JSON comparison: **only the four expected cover_image_url values changed**. All other event fields/IDs/dates/zones/audit timestamps and all eight ticket rows/prices/capacities/timestamps unchanged.
- Ordinary development DatabaseSeeder was not rerun. No persistent migration/reset or other database/business change. Real-PostgreSQL tests independently cover ordinary replay, custom/stale covers, rollback and competing-connection safety.

### Actual verification commands/results

| Command/check                                                                                                                                                                                                                                                         | Observed result                                                                                                                                                                                                                                                                         |
| --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Built-in image_gen + separately approved pngjs preparation                                                                                                                                                                                                            | Four final assets validated above. No silent dimension/budget waiver.                                                                                                                                                                                                                   |
| `npm run test -- resources/js/tests/events-index.test.tsx`                                                                                                                                                                                                            | **50 passed**, including existing navigation/append/error/clock regressions. Rerun after fixture typing correction: 50 passed.                                                                                                                                                          |
| `TZ=Pacific/Honolulu npm run test -- resources/js/tests/events-index.test.tsx`                                                                                                                                                                                        | **50 passed.**                                                                                                                                                                                                                                                                          |
| `TZ=Pacific/Kiritimati npm run test -- resources/js/tests/events-index.test.tsx`                                                                                                                                                                                      | **50 passed.**                                                                                                                                                                                                                                                                          |
| `docker compose --profile test up -d postgres-test`                                                                                                                                                                                                                   | Existing isolated service running. Initial sandbox socket denial resolved through approved escalation.                                                                                                                                                                                  |
| First focused refresh test against empty scaffold                                                                                                                                                                                                                     | Expected failure: no upgrade/report implemented yet. Initial socket failure was infrastructure, not application evidence.                                                                                                                                                               |
| `php artisan test --compact tests/Feature/Database/Seeders/DatabaseSeederTest.php tests/Feature/Database/Seeders/RefreshEventCoversSeederTest.php`                                                                                                                    | **20 passed, 148 assertions.** Initially four missing-event fixture errors hit the existing restrictive ticket FK; corrected only the fixture by deleting its children first. No schema change.                                                                                         |
| `php artisan test --compact tests/Feature/Database/Seeders/DatabaseSeederTest.php tests/Feature/Database/Seeders/RefreshEventCoversSeederTest.php tests/Feature/Actions/Events/ListEventsTest.php tests/Feature/Http/Controllers/Events/ListEventsControllerTest.php` | **77 passed, 743 assertions**, real isolated PostgreSQL. Final run after MIME typing correction; includes competing PostgreSQL connection test.                                                                                                                                         |
| `npm run types:check`                                                                                                                                                                                                                                                 | **Passed.** Initial run found five new test fixtures missing their required past array; corrected fixtures without weakening harness types.                                                                                                                                             |
| `composer types:check`                                                                                                                                                                                                                                                | **Passed, zero PHPStan errors.** Worker socket required approved escalation. Removed redundant MIME null-coalescing after successful native image inspection; no suppression/baseline change.                                                                                           |
| `npm run check`                                                                                                                                                                                                                                                       | Passed: 49 files formatted / 35 linted, zero warnings/errors; final rerun after evidence edits also passed (49 formatted / 35 linted).                                                                                                                                                  |
| `vendor/bin/pint --dirty --format agent`                                                                                                                                                                                                                              | Passed; one blank-line fix in the new seeder.                                                                                                                                                                                                                                           |
| `npm run build`                                                                                                                                                                                                                                                       | **Passed**, 2,204 modules. Existing optional fontaine warning retained; no package installed. Normal ignored Wayfinder/build output only.                                                                                                                                               |
| `curl` actual `http://localhost:8000` and four image URLs                                                                                                                                                                                                             | Home **200 text/html**; four **200 image/png**, exact delivered byte counts match asset table. Actual Upcoming props contain Lantern Pages/New Year photo references. Past also returned 200 with City/Paper Trails photo references. Delivery checks, not browser layout verification. |
| Preservation                                                                                                                                                                                                                                                          | Feed `cmp` passed. Hash baseline for all 170 unrelated tracked/untracked files passed. Git/tar/Node comparison confirmed exactly 20 changed/created files, zero removals; no preview or post-checkpoint skeleton file exists.                                                           |

**Final preservation/formatting:** `git diff --check` passed; explicit document formatting passed; unrelated-file hash and untouched-feed checks passed. Read-only manifest comparison used existing Git/tar/Node tools after unavailable Python tarfile/nested-spawn limitations; no dependency was installed. Existing browser-log sample contains Vite/HMR activity, not a visual pass.

**Not run/claimed at that earlier checkpoint:** final aggregate composer ci:check/full-suite correction regression; actual browser screenshots/layout, keyboard/a11y, infinite-scroll/history/race acceptance. Those remain required for final Gate B after checkpoint acceptance. Logs show Vite/HMR activity, not successful visual QA. No usable browser automation/executable is available here; none installed.

### Early visual review protocol — completed and accepted

URL resolved through Boost and verified reachable: **http://localhost:8000**.

1. Hard-refresh so current server props include the new image references. At **1440 CSS px**, 100% zoom, inspect original cover/overlapping M&L mark, neutral page, date rail, compact cards and right photographic thumbnails. Desktop calendar/sidebar and footer Upcoming/Past must be visible.
2. Representative **Lantern Pages**: event-local **18:00**, original title/organizer, **The British Library**, orange **Short fiction**, truthful **From £15.00**. No full address/end/IANA/ticket breakdown or fabricated attendees/counts. Inspect other original covers across the real views.
3. At **390 CSS px**, then **320 CSS px**, capture full header and first card. Calendar dates hidden, one real view-control set visible above results, thumbnail right-aligned, long text wraps without horizontal clipping. Record viewport/zoom/DPR.
4. Compare supplied current 22-30-18 and target 22-30-02/older mobile references against the actual corrected page. Original identity/organizer artwork/content, GBP, current-month static calendar and omitted unsupported avatars/maps/search/follow are intentional differences.
5. Return explicit screenshot acceptance or requested visual revisions. **No skeleton, scroll-preservation or history correction until acceptance.** Existing loading text and preserveScroll:false intentionally remain at this checkpoint.

No browser passes are claimed. The section 8 keyboard/screen-reader/reflow/contrast/history matrix still needs actual execution. Supplied screenshots are the visual baseline, not a runtime history trace.

## Revision 5 — remaining loading/navigation implementation and Gate B handoff

**31 October 2026 (Europe/London).** Early visual checkpoint accepted before this work. Accepted organizer/card/calendar/category/photograph design preserved without further polish. Final owner acceptance is pending; no other slice started.

### Actual changed files in this final increment

| File                                                     | Change                                                                                                                                                                                                                                                                                                                                                             |
| -------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `resources/js/pages/events/index.tsx`                    | Screen-reader-only view loading status; local 200ms delay for a different view; two skeletons in an absolute overlay; old feed stays mounted/occupies its height but becomes invisible/inert/ARIA-hidden while covered. Two-card minimum frame at idle and pending. Timer/visual state clears on finish/error/cancel/unmount. View visits use preserveScroll:true. |
| `resources/js/components/events/event-feed.tsx`          | Private AppendLoadingIndicator owns a 200ms delay; matching skeleton in the native next slot below existing rows. Loading:false returns no indicator synchronously before effect cleanup. Start visits preserve scroll. Existing keyed rows, native params/manual/loading callbacks and Retry remain.                                                              |
| `resources/js/components/events/event-card-skeleton.tsx` | New approved pure presentation component, count defaults to one; reuses Card/Skeleton and accepted dimensions, aria-hidden and reduced-motion aware. No event/page identities, controls or request state.                                                                                                                                                          |
| `resources/js/tests/events-index.test.tsx`               | All 50 prior cases retained; 17 additional timing/lifecycle/failure/Retry cases, plus preserveScroll:true assertions on real router requests. Test-local hook driver executes our display timers/effect cleanup; it does not simulate browser layout/history.                                                                                                      |
| `docs/slice-plans/02b-event-browsing-frontend.md`        | Early acceptance, final manifest, diagnosis, actual checks and manual Gate B protocol.                                                                                                                                                                                                                                                                             |
| `PLANS.md`                                               | Slice 2B review tracking only.                                                                                                                                                                                                                                                                                                                                     |

Six files changed/created relative to the preserved early-checkpoint worktree; no removals. No CSS, accepted event/hero/calendar components, category mapping, PNG assets, shared primitives, package/lock, route, PHP/backend/schema or data changes in this increment. No custom coordinator, hook module, queue, state machine, history store, observer paginator, feature or dependency. No development seeding/migration/database write.

### Loading and scroll/history diagnosis

**Confirmed causes corrected:** the normal-flow loading paragraph above the grid changed document geometry on every view request. Both view and Start visits explicitly set preserveScroll:false, leading installed CurrentPage.set → Scroll.reset → window.scrollTo(0,0). These visits now preserve scroll through the supported native option, while retaining preserveState:false, reset:['events'], only:['view','events'], Wayfinder hrefs and ordinary/modified anchor semantics.

**Loading policy:** no skeleton for a different-view request finishing before 200ms; no artificial minimum dwell. Slow view requests show two matching placeholders outside the tracked list while old rows retain their footprint. A same-view refresh keeps existing content without a view overlay. Slow append shows one placeholder below existing rows, with prior rows/next-page ownership intact. Visible loading paragraphs are removed; polite role=status announcements are sr-only. Timers clear on every relevant end path and are never remembered. Real errors, empty/end states and manual Retry remain visible as before.

**Native history source review:** installed React/Core 3.9.1 and PHP adapter 3.5.2 remain. EventHandler.handlePopstateEvent cancels native requests, restores cached page props with a fresh component swap and schedules native scroll restoration. InfiniteScroll owns remembered next/previous/last-page state and element ranges, including with preserveUrl. Its next slot is rendered outside the tracked ul. Direct li/event IDs, view keys, reserved cover geometry, reset/merge protocol and native cancellation are retained. New placeholders do not participate in element tagging. The installed React adapter clears loading synchronously before restoration; the append indicator's loading:false guard preserves that ordering despite its own delayed state.

**Evidence limit:** the owner's previously reported distinct Back/Forward failure has not been reproduced with a browser here. Static source inspection and timer/router tests cannot establish its exact runtime cause or prove it fixed. No independent speculative history patch, forced anchor/scroll, native metadata override or package change was introduced. The manual matrix explicitly covers immediate versus settled history, Forward without scrolling the new view first, short/empty targets, cancellation and loaded rows. Native document-position persistence is scroll-event driven; inspect the actual entry/viewport rather than inferring success from preserveScroll alone. Browser traces remain required before claiming a separate history repair necessary/successful.

Official version-scoped Boost guidance and installed source were consulted before implementation: [scroll management](https://inertiajs.com/docs/v3/advanced/scroll-management), [native infinite scroll](https://inertiajs.com/docs/v3/data-props/infinite-scroll), [visit callbacks](https://inertiajs.com/docs/v3/the-basics/manual-visits). No source/documentation upgrade was assumed.

### Actual automated verification

| Command                                                                          | Actual result                                                                                                                                                                                                                                                                           |
| -------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `npm run test -- resources/js/tests/events-index.test.tsx`                       | **67 passed** after final source changes. First timer implementation passed 66; native Retry regression added and rerun passed 67.                                                                                                                                                      |
| `npm run types:check`                                                            | **Passed.**                                                                                                                                                                                                                                                                             |
| `npm run check`                                                                  | **Passed**, 50 formatted / 36 linted files, no warnings/errors.                                                                                                                                                                                                                         |
| `composer ci:check`                                                              | **Passed end to end:** JS format/lint, TypeScript, **67 frontend tests**, config clear, Pint check, PHPStan zero errors and **198 real-PostgreSQL/PHP tests, 1,236 assertions**. Approved escalation allowed required local worker/database sockets. No concurrent migrating PHP suite. |
| `TZ=Pacific/Honolulu npm run test -- resources/js/tests/events-index.test.tsx`   | **67 passed.**                                                                                                                                                                                                                                                                          |
| `TZ=Pacific/Kiritimati npm run test -- resources/js/tests/events-index.test.tsx` | **67 passed.**                                                                                                                                                                                                                                                                          |
| `npm run build`                                                                  | **Passed**, 2,205 modules. Existing optional fontaine warning remains; no dependency installed. Normal ignored build/Wayfinder outputs only.                                                                                                                                            |

Final delivery/preservation checks:

| Check                                                           | Actual result                                                                                                                       |
| --------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| `sha256sum --status -c /tmp/slice2b-final-protected.sha256`     | Passed for **186** protected tracked/untracked files, including accepted visual components/CSS/images and backend/dependency files. |
| Git/tar/Node comparison with `/tmp/slice2b-final-before.tar.gz` | Exactly the six final-increment manifest files changed/created; **zero removals**. Existing worktree preserved.                     |
| Scoped `vp fmt … --check` and `git diff --check`                | Passed. Documentation subsequently formatted after this evidence update.                                                            |
| `curl` home and `/?view=past`                                   | Both **200 text/html**. Actual local endpoint delivery only; not a browser navigation/history pass.                                 |

Coverage includes 199/200ms boundaries, fast completion, same-view refresh, cancellation before/after threshold, offline/500/429 retaining old rows, unmount/remount with fresh props, append immediate removal before effects, repeated append delays, native Retry once, decorative/reduced-motion placeholders and native reset/partial/preserve options. The remount test checks local UI cleanup only; it is not a real popstate or pixel-restoration test.

No PHP source was changed in this increment; the full CI Pint/PHPStan/Pest checks still ran. Actual browser layout, focus/screen-reader announcements, observer/history/race/error outcomes were **not run or passed** here. Browser executables/automation packages remain unavailable; no new tool was installed. Recent browser logs show Vite/React development connection information, not acceptance evidence.

### Focused manual browser verification — required for Gate B

Use the actual application at **http://localhost:8000** (resolved with Boost), hard-refreshed to the current assets. Test desktop and mobile. Four preserved seeded events cannot prove multi-page append/history; use a disposable >40-event catalogue built with the existing factories for those cases, without rewriting persistent development data. No multi-page browser fixture session was created in this task.

1. **Fast and slow view loading:** scroll to Events and note its viewport position. Switch Upcoming/Past with a fast/cached request: no visible loading line or skeleton flash. Repeat with an existing network throttle/delay over 200ms: two skeleton cards appear at about 200ms, controls/Events stay in place while pending, old feed is not keyboard reachable while covered, and new rows replace the overlay on completion. Loading announcements must be accessible without visible shifting text. Same-view activation should not show the different-view overlay.
2. **Scroll preservation and short targets:** switch both directions after scrolling through results; expect to stay around Events, with the correct active view/URL/rows. Include few/no-event targets and a tall viewport; record any document-height clamping instead of treating preserveScroll configuration as a pixel pass. Test a direct deep page and Start of this view. Keyboard Enter and Ctrl/Cmd/middle-click retain native semantics.
3. **Append and retry:** with ≥3 pages, delay the next page over 200ms. Existing cards stay unchanged, one matching placeholder appears below them, no visible loading text moves earlier rows, and completion appends exactly once. Test both observer loading and Load more. Record event article IDs/order and ensure exhaustion stops requests.
4. **Back/Forward:** record URL, selected view, all rendered event IDs and scrollY/reference-card position after several appended pages. Switch views, then Back/Forward repeatedly; the correct cached rows/view and prior viewport must restore with no duplicates/stale data. Also switch and use Back/Forward **without scrolling the new view first**, and compare immediate Back with Back after observers/history settle. Repeat while a slow append/view request is pending; no late row/placeholder/error may overwrite the restored view.
5. **Failure recovery:** go offline before append; all loaded cards remain, error/Retry appear, auto-loading pauses, and Retry fetches the same native next page once after reconnection. Fail a view switch and deep-page Start too: old data/URL stay and normal links retry. Where existing response override/proxy tooling supports it, repeat with HTTP 500 and 429; only generic/rate-limit text is shown, never raw bodies, and repeated 429 remains manual. Do not add testing routes/packages to induce these failures.
6. **Cancel/finish cleanup:** rapidly alternate views and navigate Back during a delayed request; then wait beyond 200ms. There must be no stale skeleton, busy/inert state, spurious cancellation error or mixed rows. Check fresh console output, keyboard fallback and reduced-motion pulse behaviour.

For a failure, capture request/response order, URL/view, rendered event IDs, scrollY, Events/reference-card rectangles, document maximum scroll, and (where readable) native history page props/scroll metadata before/after restoration. Distinguish missing cached rows from correct rows with wrong geometry/position. Do not introduce coordination or a framework workaround without that evidence. The accepted live-time/OFFSET catalogue limitations remain; use fixed fixtures/clock for deterministic pagination checks.

**Gate B:** awaiting these manual results and explicit owner final acceptance. No subsequent slice is started.

## Historical revision 3 implementation evidence — not correction acceptance

The following retains the previous actual file/command record. Its design and old visual/full-field expectations are superseded by sections 1–11 above. No application changes have been made by this correction planning pass.

### Actual changed-file manifest

| File                                                  | Actual change                                                                                                                                                                                                 |
| ----------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `resources/js/components/events/ticket-type-list.tsx` | Added; complete ordered ticket names, descriptions and server price labels, with honest ticketless state.                                                                                                     |
| `resources/js/components/events/event-cover.tsx`      | Added; reserved lazy cover thumbnail, escaped title/fallback and source-specific image failure handling.                                                                                                      |
| `resources/js/components/events/event-card.tsx`       | Added; semantic dated article, right-hand thumbnail, public organizer/venue/address and complete authoritative schedule/tickets.                                                                              |
| `resources/js/components/events/organizer-hero.tsx`   | Added; original wide CSS cover and overlapping M&L mark/identity.                                                                                                                                             |
| `resources/js/components/events/visual-calendar.tsx`  | Added; no props, visitor-local clock, neutral static month table, midnight/focus subscription with cleanup, safe server/client clock boundary.                                                                |
| `resources/js/pages/events/index.tsx`                 | Modified; compose approved shell/hero/feed/calendar, skip link, labelled view navigation and styled feedback. Existing router handlers and options retained.                                                  |
| `resources/js/components/events/event-feed.tsx`       | Modified; render EventCard in existing direct keyed items, reuse Button/AlertError/Skeleton; preserve scroll options and failure/retry ownership.                                                             |
| `resources/css/app.css`                               | Modified; scoped light/dark browse colours, original cover artwork, decorative date rail, calendar/table spacing and narrow reflow. Global tokens untouched.                                                  |
| `resources/js/tests/events-index.test.tsx`            | Modified; retain predecessor tests, adapt cover exclusion, add cover/date/static-calendar/timezone/lifecycle coverage. Alias factories load the actual new component/primitives in the existing Node harness. |
| `docs/slice-plans/02b-event-browsing-frontend.md`     | Approved Q1/Q2 revision, implementation evidence and manual review protocol.                                                                                                                                  |
| `PLANS.md`                                            | Preserve earlier planning edits; update Slice 2B approved current-month policy and review status.                                                                                                             |

No files removed. No tests deleted. Existing event/ticket TypeScript and PHP DTOs, app/backend/database/routes/tests, shared UI primitives, dependencies/lockfiles and screenshots remain unchanged. The protected tracked paths and both screenshot hashes match `/tmp/slice2b-before/protected.sha256` (`sha256sum --status -c ...` passed). Prior client files/planning documents are preserved under `/tmp/slice2b-before`. Ignored build/Wayfinder outputs were regenerated by the normal build plugin only. No persistent development database migration or seeding.

### Actual verification commands and results

| Command / check                                                                                                                                 | Observed result                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| ----------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Initial `npm run test -- resources/js/tests/events-index.test.tsx` after changing cover/date expectations                                       | 24 passed, 2 expected failures against the old listing (no image/date article yet).                                                                                                                                                                                                                                                                                                                                                                         |
| Same focused test after card extraction                                                                                                         | First import failed because the Node test config does not resolve new `@/` aliases; existing-style alias factories loading actual modules fixed it. Then **26 passed**.                                                                                                                                                                                                                                                                                     |
| Same focused test after hero/calendar and final test-fixture correction                                                                         | **36 passed**. Includes existing real Inertia router/mock transport tests, calendar markup/clock subscription and cover fallback tests. No browser DOM claim.                                                                                                                                                                                                                                                                                               |
| `TZ=Pacific/Honolulu npm run test -- resources/js/tests/events-index.test.tsx`                                                                  | **36 passed**. Frozen `2027-01-01T00:30Z` displays December 2026.                                                                                                                                                                                                                                                                                                                                                                                           |
| `TZ=Pacific/Kiritimati npm run test -- resources/js/tests/events-index.test.tsx`                                                                | **36 passed**. Same frozen instant displays January 2027.                                                                                                                                                                                                                                                                                                                                                                                                   |
| `npm run types:check`                                                                                                                           | Passed, no TypeScript errors.                                                                                                                                                                                                                                                                                                                                                                                                                               |
| `npm run check`                                                                                                                                 | Passed: 46 formatted files, 32 linted files; no warnings/errors.                                                                                                                                                                                                                                                                                                                                                                                            |
| `docker compose --profile test up -d postgres-test`                                                                                             | Existing isolated test service running. Initial sandbox Docker socket restriction resolved through approved escalation.                                                                                                                                                                                                                                                                                                                                     |
| `php artisan test --compact tests/Feature/Actions/Events/ListEventsTest.php tests/Feature/Http/Controllers/Events/ListEventsControllerTest.php` | **57 passed, 595 assertions** on PostgreSQL. Initial sandbox attempt was blocked at connection setup (57 errors, zero assertions); approved local-socket access allowed successful rerun.                                                                                                                                                                                                                                                                   |
| `composer ci:check`                                                                                                                             | Passed end to end: formatting/lint, TypeScript, **36 frontend tests**, config clear, Pint check, **PHPStan zero errors**, **185 PostgreSQL/PHP tests, 1,170 assertions**. PHP/database suites ran sequentially.                                                                                                                                                                                                                                             |
| `npm run build`                                                                                                                                 | Passed; 2,199 modules, production CSS/JS/font manifests generated. Existing optional fontaine fallback-optimization warning remains; no dependency installed.                                                                                                                                                                                                                                                                                               |
| Scoped source/document formatter                                                                                                                | Passed on the approved client files and plan only.                                                                                                                                                                                                                                                                                                                                                                                                          |
| `git diff --check` / protected file hashes                                                                                                      | Passed. Final checks repeated after documentation/focus-style polish; no backend or contract drift.                                                                                                                                                                                                                                                                                                                                                         |
| Static contrast calculation from actual hex tokens                                                                                              | All measured text pairs meet 4.5:1: minimum **5.18:1 light**, **6.84:1 dark** across foreground/muted/primary/destructive and their used surfaces. Local focus ring colour uses full-opacity primary. Compiled Tailwind v4 utilities reference `--color-*`; those aliases are redeclared inside the page scope so its light/dark tokens resolve locally without changing global theme configuration. This arithmetic is not a rendered accessibility audit. |

The independence fixture was corrected to preserve both appended rows rather than accidentally replacing them when constructing its deep-page variant; the focused test was rerun successfully. Styling-only focus-ring/scoped Tailwind-token/documentation polish followed full CI. Final reruns passed: `npm run check` (46 formatted, 32 linted), focused frontend tests (36 passed), `npm run build` (2,199 modules), protected hashes and `git diff --check`. Full CI was not repeated after these style/document-only refinements. No backend/runtime behaviour changed after regression verification.
