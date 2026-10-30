# Slice 1A — Event domain and database

## Slice identity and approval state

- **Slice number and name:** 1A — Event domain and database.
- **Roadmap reference:** `PLANS.md` → Slice 1A; Slice 1B is separate.
- **Status:** Awaiting owner plan approval; revision 1, plan only.
- **Proposed implementation owner/date:** Codex, principal engineer; 30 October 2026.
- **Owner plan approval and date:** Pending.
- **Implementation acceptance and date:** Pending.
- **Related approved decisions:** Owner reports Slice 0 completed, verified, accepted and committed. Current instructions approve total ticket capacity in 1A and defer all inventory accounting to Slice 4. `V1_SPEC.md` governs requirements; architecture is a proposal except where explicitly approved. This plan does not approve the rest of that architecture.

## 1. Customer-visible result and boundaries

This is a backend foundation, demonstrated through the isolated PostgreSQL test database rather than a screen. After implementation, two new tables hold original, repeatably seeded events and independently capped GA ticket types. Models expose their relationships. Database tests prove the constraints and seed replay.

**Already approved requirements:** one organizer, one-off scheduled events, public venue details, event-local IANA zones, UTC instants, upcoming/past sample events, multiple independently capped ticket types, fixed positive GBP prices in integer minor units, positive total capacity. No event-level capacity beyond the sum of ticket capacities. Implements acceptance **A01**; supplies data prerequisites for A03/A04/A05 without claiming their HTTP, display or hold behavior. B08's non-destructive replay prerequisite is covered; order snapshots remain a later-slice test.

**Explicit non-goals:** controllers, Form Requests, HTTP endpoints, Inertia props/pages, React components, ticket selection UI, checkout, orders, holds, reserved/confirmed counters, inventory accounting/actions/locks, payments, Stripe, authentication, dashboards, email, QR codes, event management, jobs and scheduler changes. No new user flow or affected route/screen. No sold-out claim or sales-closed feature. No Slice 1B work.

### Inspected evidence and authority

Read completely: `AGENTS.md`, `PLANS.md`, `docs/V1_SPEC.md`, `docs/ARCHITECTURE.md`, `docs/ACCEPTANCE_TESTS.md`, `docs/SLICE_PLAN_TEMPLATE.md`, `docs/BOOST_PROJECT_RULES.md`, `.ai/rules/index.md`. Applicable rules read: `app.md`, `models.md`, `migrations.md`, `tests.md`; keyword search also checked action/validation boundaries. No recorded glob applies specifically to these Markdown plans or seeders/factories; use the approved database/model conventions there. Applied Laravel best-practices and testing-best-practices skills, including model, migration, index, isolation, data and assertion guidance.

Read-only inspection on 30 October 2026 established:

| Area | Actual repository/runtime |
| --- | --- |
| PHP and dependencies | PHP CLI 8.5.9; `composer show --direct`: Laravel 13.39.0, Boost 2.10.4, Pest 5.3.4, Pest Laravel plugin 5.0.1, Larastan 3.13.2, Pint 1.32.1, Faker 1.24.1; no dependency changes proposed |
| Frontend packages | `package.json`: Inertia React/Vite ^3, React ^19.2, TypeScript ^5.7; irrelevant to this backend implementation |
| Database | Boost schema/query: development PostgreSQL 17.11, database `laravel`, UTC session; nine infrastructure/migration tables, no event/ticket tables |
| Isolated tests | Read-only query against existing `postgres-test`: PostgreSQL 17.11, database/role `laravel_test`, UTC. Compose uses tmpfs and host port 5433, distinct from persistent development PostgreSQL |
| Migrations | Three `0001_01_01_*` migrations retain users/password-reset/sessions, cache/locks and jobs/batches/failed-jobs; do not change them |
| Models/factories | Only dormant `app/Models/User.php` and `database/factories/UserFactory.php`; typed PHPDoc, `#[Fillable]`, `casts(): array`, factory generics and `fake()` provide relevant conventions; no event-domain pattern exists |
| Seeds | `database/seeders/DatabaseSeeder.php` has empty `run(): void` and `WithoutModelEvents`; no user seeded |
| Database config | `config/database.php` defaults to pgsql; `config/app.php` is UTC; pgsql config has no explicit session timezone override; runtime sessions observed UTC |
| Test isolation | `phpunit.xml` pins pgsql/database/role and clears DB_URL; `tests/TestCase.php` rejects unsafe topology/configuration and verifies actual database/role before migration traits run |
| Existing tests | `tests/Pest.php` binds Feature tests to TestCase. InfrastructureTest uses `DatabaseMigrations`, real queries, independent connection/process probes and named `test()` cases. No global refresh trait |
| Checks/runtime | `compose.yml`, `.github/workflows/tests.yml`, `composer.json`, `package.json`, `phpstan.neon`: sequential test execution, PHPStan level 7, existing type/build/lint commands; preserve all |
| Working tree | `PLANS.md` already contained an uncommitted owner-agreed 1A/1B roadmap split when planning began; preserve it. Latest inspected commit: `912a63d` |

Boost version-scoped documentation was consulted for migrations, timestampTz, Eloquent date casts, relationships, factories and database testing. No tests, migrations or seeders were run during this planning pass. Older status headings in the design and Slice 0 document are historical; the owner's present Slice 0 acceptance instruction governs. Those documents are outside this edit scope.

## 2. Proposed file-by-file change manifest

The following is the complete proposed implementation manifest. Only the two documentation rows are changed during this planning task. No files are removed.

| Path | Add/change | Responsibility / why needed |
| --- | --- | --- |
| `app/Models/Event.php` | Add | Lean event persistence, schedule casts, timezone assignment validation and ticket relationship |
| `app/Models/TicketType.php` | Add | Lean ticket persistence, integer casts and parent relationship |
| `database/migrations/2026_10_30_000100_create_events_table.php` | Add | Events schema and PostgreSQL constraints/indexes below |
| `database/migrations/2026_10_30_000200_create_ticket_types_table.php` | Add | Ticket schema and PostgreSQL constraints/indexes below |
| `database/factories/EventFactory.php` | Add | Valid event fixtures with explicit upcoming/past states |
| `database/factories/TicketTypeFactory.php` | Add | Valid independently capped, paid GBP fixtures with parent factory |
| `database/seeders/EventSeeder.php` | Add | Fixed original catalogue and original cover data, insert missing events only |
| `database/seeders/TicketTypeSeeder.php` | Add | Fixed ticket definitions keyed by event slug and stable ticket code |
| `database/seeders/DatabaseSeeder.php` | Change | One transaction calling EventSeeder then TicketTypeSeeder; retain no-account behavior and WithoutModelEvents |
| `tests/Feature/EventSchemaTest.php` | Add | Migration replay, column/index contract and direct-SQL constraint behavior |
| `tests/Feature/Models/EventTest.php` | Add | Event relations, timezone assignment and UTC/local instant behavior |
| `tests/Feature/Models/TicketTypeTest.php` | Add | Parent identity and integer price/capacity behavior |
| `tests/Feature/Database/Seeders/DatabaseSeederTest.php` | Add | Exact seeded catalogue, replay, preservation and rollback evidence |
| `PLANS.md` | Change | Link proposal/status now; later record this slice's approvals/review status only |
| `docs/slice-plans/01a-event-domain.md` | Add, later change | This plan and actual Gate A/Gate B records |

**Client/dev tooling:** not applicable; no source, config, dependency, CI, test guard or runtime changes. Test directories above are under the existing `tests/Feature` root. Generate PHP files with Artisan after approval; pin migration filenames to the manifest after generation so event migration precedes ticket migration. Inspect generated output before filling it; retain no extra generated controller/request/test/seed scaffolding. Use `make:test --pest EventSchemaTest` etc. without a `Feature/` prefix, then move model/seeder tests into the listed subdirectories.

## 3. Laravel class and contract design

All classes below are proposed technical recommendations, subject to Gate A. Existing inherited framework APIs remain available; no new business action API is introduced.

| Fully qualified class | File | Responsibility and methods | Inputs → outputs / side effects | Dependencies |
| --- | --- | --- | --- | --- |
| `App\Models\Event` | `app/Models/Event.php` | `ticketTypes(): HasMany`; protected `casts(): array`; protected `timezone(): Attribute` mutator | Relationship yields only this event's tickets, ordered `display_order`, then `id`. Immutable datetime casts for starts/ends/created/updated. Timezone assignment accepts an exact PHP IANA identifier or `UTC`, otherwise throws `InvalidArgumentException` before persistence | Eloquent Model, Attribute, HasFactory<EventFactory>, HasMany; native DateTimeZone |
| `App\Models\TicketType` | `app/Models/TicketType.php` | `event(): BelongsTo`; protected `casts(): array` | Parent event; integer casts for price_minor, capacity and display_order. No remaining-capacity or sold-out method | Eloquent Model, HasFactory<TicketTypeFactory>, BelongsTo |
| `Database\Factories\EventFactory` | `database/factories/EventFactory.php` | `definition(): array`, `upcoming(): static`, `past(): static` | Valid complete attributes; unique slug, original placeholder content and cover; base schedule fixed, states relative to controlled test clock | Factory<Event>, Faker, CarbonImmutable |
| `Database\Factories\TicketTypeFactory` | `database/factories/TicketTypeFactory.php` | `definition(): array` | Valid attributes: parent Event factory, unique code, GA name, positive integer price and capacity, GBP, display order | Factory<TicketType>, Event factory, Faker |
| `Database\Seeders\EventSeeder` | `database/seeders/EventSeeder.php` | `run(): void` | No arguments; firstOrCreate by exact slug using fixed attributes; no existing-row updates | Seeder, Event, literal seed definitions |
| `Database\Seeders\TicketTypeSeeder` | `database/seeders/TicketTypeSeeder.php` | `run(): void` | No arguments; require each declared event slug to exist (firstOrFail); firstOrCreate by event_id + code; no existing-row updates | Seeder, Event, TicketType, literal ticket definitions |
| `Database\Seeders\DatabaseSeeder` | `database/seeders/DatabaseSeeder.php` | `run(): void` | No arguments; DB::transaction invokes the two child seeders in order, attempts: 3; exception propagates after rollback | Existing Seeder/WithoutModelEvents, DB, child seeders |
| Anonymous migration extending `Illuminate\Database\Migrations\Migration` (one per migration file) | Two migration paths above | `up(): void`, `down(): void` | Create/drop only the respective table, with ticket table dropped before events on rollback | Schema, Blueprint, DB for named CHECK statements |

Use explicit fillable catalogue fields following the existing attribute convention, typed properties/PHPDoc and generic relationships/factories. IDs and timestamps are not fillable. No model observers, saving hooks, service provider changes, repository, DTO, value object, event-status enum, domain framework or custom cast. The timezone mutator is simple scalar validation and still applies with `WithoutModelEvents`; a saving hook would not.

**Ownership:** PostgreSQL owns required fields, keys, schedule ordering, positive prices/capacity and GBP-only currency; the timezone mutator owns identifier membership on Eloquent assignment. Seeders own fixed content and repeatability; DatabaseSeeder owns all-catalogue atomicity. Raw SQL/query-builder writes bypass model validation and casts (explicit limitation in sections 5/10).

**Request path:** not applicable: CLI seeder → Eloquent persistence → PostgreSQL; no HTTP request, Form Request, action, DTO or response boundary exists. Constraint/validation failures terminate the CLI operation with an exception and non-success exit; no customer error screen is added.

## 4. Routes and frontend design

Not applicable: no HTTP methods/routes/controllers, authorization/capabilities, throttling, CSRF/webhook changes, Inertia pages/props, React paths, browser state, loading/empty/error/retry screens, responsive/accessibility QA or browser demo in this backend slice. Existing public foundation remains unchanged. Browser input is never used to set authoritative catalogue price/capacity; there is no catalogue mutation endpoint.

## 5. Database and migrations

All specifications below are **proposed** for Gate A. Exactly two new tables; no organizer, recurrence, venue, image, inventory or status tables. PostgreSQL signed numeric types require explicit checks; Laravel's unsigned names are not a PostgreSQL positivity guarantee.

### Events — `2026_10_30_000100_create_events_table.php`

| Column | Exact PostgreSQL type | Nullable | Default | Purpose |
| --- | --- | --- | --- | --- |
| id | bigint, sequence-backed (`bigserial` from Laravel id()) | No | generated sequence value | Primary identity; not an authorization secret |
| slug | varchar(160) | No | None | Stable lowercase public identity/seed key |
| title | varchar(255) | No | None | Event title |
| description | text | No | None | Original plain text; no rich-HTML contract |
| organizer_name | varchar(120) | No | None | Display attribution for the one v1 organizer; no account/organizer relation |
| cover_image_url | text | No | None | Original cover reference; seed with self-contained SVG data URL |
| venue_name | varchar(255) | No | None | Public venue name |
| venue_address | text | No | None | Public human-readable address; no maps/coordinates/private address state |
| timezone | varchar(64) | No | None | Event-local IANA identifier, separate from instant storage |
| starts_at | timestamp(0) with time zone | No | None | UTC instant, second precision |
| ends_at | timestamp(0) with time zone | No | None | UTC instant, strictly after start |
| created_at | timestamp(0) with time zone | Yes | None | Eloquent-managed audit time |
| updated_at | timestamp(0) with time zone | Yes | None | Eloquent-managed audit time |

Keys/indexes (B-tree): `events_pkey` on id; unique `events_slug_unique` on slug; non-unique `events_starts_at_id_index` on (starts_at, id), supporting future schedule range/order scans with a tie-breaker. No foreign keys. No index on description, address, timezone or ends_at without a demonstrated query need.

Named checks:

| Constraint | Predicate / guarantee |
| --- | --- |
| events_slug_format_check | `slug ~ '^[a-z0-9]+(-[a-z0-9]+)*$'`; nonempty, canonical case, case-variant duplicate avoided without citext |
| events_required_text_check | `btrim(title) <> '' AND btrim(description) <> '' AND btrim(organizer_name) <> '' AND btrim(cover_image_url) <> '' AND btrim(venue_name) <> '' AND btrim(venue_address) <> '' AND btrim(timezone) <> ''`; blocks blank required content |
| events_schedule_check | `isfinite(starts_at) AND isfinite(ends_at) AND ends_at > starts_at`; blocks equal/reversed/non-finite schedules |

No stored upcoming/past flag or recurrence: each row is one occurrence. Proposed boundary for future consumers/tests: upcoming means `starts_at > reference_now`; past/started means `starts_at <= reference_now`, including in-progress events. This matches the no-new-hold-at-start cutoff but does not implement it. If product wants in-progress represented separately, decide in Slice 1B; do not silently change this proposal.

**Timezone enforcement boundary:** mutator uses exact membership in `DateTimeZone::listIdentifiers(DateTimeZone::ALL)` plus `UTC`; reject abbreviations like BST, numeric offsets, invented identifiers and surrounding whitespace. Database enforces nonblank only, not IANA membership. A PostgreSQL CHECK cannot directly query pg_timezone_names; no dynamic timezone lookup function, trigger or reference table is proposed. Direct SQL can store a nonblank invalid identifier; this is a limitation needing explicit approval in section 10. All slice-owned writes use Eloquent assignments; test seed zones against PHP and PostgreSQL timezone catalogs.

**UTC semantics:** timestamptz stores an instant, not the IANA identity or original textual offset. Keep app/test/development sessions UTC; seed and factory persistence uses explicit UTC CarbonImmutable values. Callers must normalize offset-bearing input to UTC before Eloquent persistence; casts alone do not promise normalization on assignment. Tests use UTC inputs and direct PostgreSQL offset-bearing writes to prove instant equivalence. No timezone config change. Event-local conversion is for reading/testing only and does not mutate the stored schedule. A future write interface must validate/normalize its input in its own approved boundary.

### Ticket types — `2026_10_30_000200_create_ticket_types_table.php`

| Column | Exact PostgreSQL type | Nullable | Default | Purpose |
| --- | --- | --- | --- | --- |
| id | bigint, sequence-backed (`bigserial`) | No | generated sequence value | Primary identity |
| event_id | bigint | No | None | Parent event |
| code | varchar(64) | No | None | Stable per-event seed identity independent of editable name |
| name | varchar(120) | No | None | GA ticket display name |
| description | text | Yes | None | Optional explanation of pricing; no seating entitlement |
| display_order | integer | No | 0 | Nonnegative display position; ties allowed, resolved by id |
| price_minor | integer | No | None | Positive GBP pence; never floating-point pounds |
| currency | varchar(3) | No | 'GBP' | Explicit currency for later snapshot boundaries |
| capacity | integer | No | None | Positive total allocation of this ticket type, independently capped |
| created_at | timestamp(0) with time zone | Yes | None | Eloquent-managed |
| updated_at | timestamp(0) with time zone | Yes | None | Eloquent-managed |

Keys/indexes (B-tree): `ticket_types_pkey`; unique `ticket_types_event_id_code_unique` on (event_id, code); non-unique `ticket_types_event_id_display_order_id_index` on (event_id, display_order, id). No extra event_id-only index because both composite indexes lead with it. No name uniqueness: ticket labels may repeat; stable code identifies a seeded definition. No uniqueness on display_order so inserting/reordering does not require renumbering peers.

Foreign key `ticket_types_event_id_foreign`: event_id → events.id, **ON DELETE RESTRICT, ON UPDATE RESTRICT**, immediate/not deferrable. Deleting a populated event fails instead of silently removing ticket definitions; no delete UI or action in this slice. Ticket types cannot reference missing parents. `Event hasMany TicketType`; `TicketType belongsTo Event`.

Named checks:

| Constraint | Predicate / guarantee |
| --- | --- |
| ticket_types_code_format_check | `code ~ '^[a-z0-9]+(-[a-z0-9]+)*$'` |
| ticket_types_name_check | `btrim(name) <> ''` |
| ticket_types_display_order_check | `display_order >= 0` |
| ticket_types_price_minor_check | `price_minor > 0`; free tickets excluded by v1 |
| ticket_types_currency_check | `currency = 'GBP'`; reject lowercase and other currencies |
| ticket_types_capacity_check | `capacity > 0`; zero is not a sold-out marker |

Integer range is 1..2,147,483,647 for price/capacity, 0..2,147,483,647 for ordering. This technical representation limit is not a new sale policy. SQL inserts must bind minor units as integers; models/factories/seeders do not accept decimal pounds. PostgreSQL numeric coercion is not a substitute for future request validation.

**Migration transition/rollback:** both tables are absent; no backfill and no existing migration edits. Use Laravel schema primitives plus explicit named PostgreSQL CHECK statements within each transactional migration. No extensions, functions or triggers. Down drops ticket_types before events; populated-table rollback destroys catalogue data and must be limited to disposable tests. Use forward migrations after acceptance rather than editing these migrations. No migration/seed execution against persistent development during this plan; no destructive development replay in implementation verification.

**Future Slice 4 compatibility:** capacity is a total, not availability. No capacity mutator, reservation operation or counter exists. A later approved additive migration can introduce nonnegative reserved_count/confirmed_count with zero defaults, validate existing capacities and add an overflow-safe combined-capacity check before enabling holds. It must verify that no historical purchases require a backfill. Do not run any of that now. Seed replay never changes capacity/price on existing rows, so it cannot reset future accounting. Event-level summed capacity is derived if needed later, not stored.

## 6. State transitions and transaction protocol

No event status, inventory state, payment state or accounting transition is introduced. Schedule classification is derived from starts_at and a supplied reference clock. Capacity is static catalogue input; no oversell guarantee is claimed from this schema alone.

**Catalogue seed transaction:** DatabaseSeeder opens one short `DB::transaction(..., attempts: 3)`, inserts missing events in slug order, then missing tickets in event-slug/code order, and commits. Required parents must exist. A missing parent, invalid attribute or constraint failure rolls back every insertion from this invocation. Existing rows remain unchanged. No explicit `FOR UPDATE`, inventory row lock, advisory lock, external call or time-dependent mutation. PostgreSQL acquires ordinary insert/unique/FK locks; processing parent events before children in stable key order reduces seed contention. This is not the global hold lock protocol, which belongs entirely to Slice 4.

Invoke DatabaseSeeder as the supported all-catalogue entry point. Child seeders are implementation helpers; standalone TicketTypeSeeder without prerequisites fails explicitly. Unique constraints remain the duplicate barrier even if two CLI seed invocations overlap; firstOrCreate uses the installed framework's unique-race handling. A residual deadlock is retried at most three transaction attempts; other errors propagate. No persistent idempotency ledger. An operator can rerun after failure. Do not promise a 1,000-way inventory property from seed ordering.

Scheduler/webhook/HTTP coordination is not applicable; none is introduced or changed. No Stripe work occurs inside or outside transactions. No customer outcome depends on this CLI operation.

## 7. Idempotency, failures, queue and external integrations

### Deterministic, non-destructive seed contract

Use literal identities and absolute UTC schedules, never now() offsets or Faker in demo seeders. Event lookup key is slug; ticket lookup key is (resolved event_id, code). Use firstOrCreate with attributes supplied only for insertion. Do not truncate, delete obsolete rows, upsert-update, updateOrCreate, reset sequences or overwrite existing timestamps/content/price/capacity. Editing a display name does not create another seeded ticket because code is stable. IDs are sequence-generated and not promised to be identical across installations; natural identities and content are deterministic.

Rerunning after committed success leaves the full rows, IDs and audit timestamps unchanged; rerunning after pre-commit failure inserts the missing catalogue once. If an operator intentionally changes an event slug or ticket code, the old seed identity is absent and replay would create it again. Treat those keys as operationally immutable; changing seed identities or applying content corrections requires an explicit future data-change decision. Existing-row preservation means replay does not repair operator edits automatically. It does repair a missing ticket under a still-present event. The seed transaction is atomic; numeric sequences may have gaps after rollback, which is acceptable.

### Exact proposed catalogue (requires owner content approval)

All four events belong to original organizer **Margin & Lantern**, stored as organizer_name on each event. No user/organizer account is created. These are fictional demonstration gatherings at public venues, not announcements of actual venue bookings.

| Slug / title | Original description | Public venue/address | IANA zone | UTC start → end |
| --- | --- | --- | --- | --- |
| paper-trails / Paper Trails: An Autumn Reading Circle | Bring a short passage about a journey and join a relaxed conversation about the places books carry us. | Barbican Centre; Silk Street, London EC2Y 8DS, United Kingdom | Europe/London | 2026-09-19 13:00:00Z → 15:00:00Z |
| city-in-the-margins / The City in the Margins | Explore how overlooked streets become memorable settings, then trade reading recommendations over a guided discussion. | Manchester Central Library; St Peter's Square, Manchester M2 5PD, United Kingdom | Europe/London | 2026-10-17 13:00:00Z → 15:00:00Z |
| lantern-pages / Lantern Pages: Stories After Dusk | Share an evening of short fiction and discuss how writers build atmosphere with light, silence and surprise. | The British Library; 96 Euston Road, London NW1 2DB, United Kingdom | Europe/London | 2026-11-14 18:00:00Z → 20:00:00Z |
| new-year-new-chapter / A New Year, A New Chapter | Gather for a fresh reading season, compare discoveries and choose a personal reading challenge for the months ahead. | Central Library Oodi; Töölönlahdenkatu 4, 00100 Helsinki, Finland | Europe/Helsinki | 2027-01-16 12:00:00Z → 14:00:00Z |

Each event has two independently capped GA definitions (no reserved seating or special admission privileges):

| Event slug | code / name | Description | display_order | GBP price_minor | capacity |
| --- | --- | --- | --- | --- | --- |
| paper-trails | standard-ga / General admission | Admission to the reading circle. | 0 | 1200 | 30 |
| paper-trails | supporter-ga / Supporter admission | The same admission at a voluntary supporter price. | 1 | 1800 | 10 |
| city-in-the-margins | standard-ga / General admission | Admission to the discussion. | 0 | 1000 | 24 |
| city-in-the-margins | supporter-ga / Supporter admission | The same admission at a voluntary supporter price. | 1 | 1600 | 8 |
| lantern-pages | standard-ga / General admission | Admission to the story evening. | 0 | 1500 | 40 |
| lantern-pages | supporter-ga / Supporter admission | The same admission at a voluntary supporter price. | 1 | 2200 | 12 |
| new-year-new-chapter | standard-ga / General admission | Admission to the reading gathering. | 0 | 1400 | 28 |
| new-year-new-chapter | supporter-ga / Supporter admission | The same admission at a voluntary supporter price. | 1 | 2000 | 8 |

Cover references are self-contained `data:image/svg+xml;base64,...` URLs from original literal static SVG markup in EventSeeder: four abstract book/lantern compositions with distinct colors, no scripts, external links, embedded third-party art or copied Luma material. No image files, asset generator, network request or frontend styling work. Exact vector drawing details are routine implementation within that content contract. A later approved visual slice can replace references with original asset paths; replay will preserve existing rows rather than silently replace their covers.

**Time predictability:** freeze tests at `2026-10-30T12:00:00Z`, giving exactly two past and two upcoming events. The September/October London events render at 14:00 local; November at 18:00; Helsinki January at 14:00. Dates never slide on replay. Over time all fixed examples will become past; acknowledge that rather than rescheduling existing events. A refreshed demo season needs an approved new content set. Factories may use the test clock for named states; seeders never do. Audit times use insertion time; tests control it and verify replay does not change it.

**Sold-out alternatives:** recommend defer genuine sold-out fixtures until Slice 4/paid-order support can represent authoritative accounting. Past/started is not sold out. A separate sales-closed flag would be a new product/schema choice requiring approval; zero capacity conflicts with positive capacity. Neither alternative is included. No fabricated payment, confirmed order or hidden counter.

**Other idempotency/integration sections:** request keys, Stripe keys, payment response-loss/cancellation, queues, retries/backoff jobs and scheduling are not applicable because no such boundary exists. CLI exceptions identify failing records/constraints through normal error reporting; no new logging layer or secrets. Process death before commit rolls back catalogue writes; after commit replay is harmless. Test an injected exception before commit without production fault hooks. Packages/dependencies to add: **None**.

## 8. Test-first verification matrix

All new tests are Pest **Feature/database tests** using existing `Tests\TestCase` and its live identity guard. Use `DatabaseMigrations` per file, matching existing infrastructure tests. This deliberately favors independently replayed migrations over introducing a second lazy-refresh convention; no global trait changes. Sequential execution only. Run against existing isolated PostgreSQL `laravel_test` / role `laravel_test` on port 5433 (or existing Compose postgres-test:5432). No SQLite, database mocks, parallel runner, persistent-development destructive migration or new database service.

Use `test()` consistently, named datasets for invalid values, factories per test and real writes. For constraint failures assert `QueryException` plus PostgreSQL SQLSTATE/constraint identity; then assert no invalid row committed. Each expected failed statement runs in its own explicit transaction/savepoint and is rolled back before subsequent queries, avoiding PostgreSQL's aborted-transaction state. Do not assert brittle whole error messages. Direct query-builder writes intentionally bypass model checks to exercise database protections. No row fixture in global beforeEach; freeze only tests that depend on time and restore via travelBack in afterEach.

| Scenario / proposed test name | Exact file | Setup / assertion |
| --- | --- | --- |
| creates the event catalogue on isolated PostgreSQL | `tests/Feature/EventSchemaTest.php` | Guard + DatabaseMigrations; assert live identity/UTC; information_schema verifies listed column types, nullability, defaults and timestamptz precision; pg_catalog verifies named keys/checks/FK/index column order and restrictive actions. Insert valid parent/child and verify persistence |
| reverses and reapplies only the event domain migrations | `tests/Feature/EventSchemaTest.php` | Within isolated test harness, require the two migration objects, down ticket then event; assert domain tables absent and existing users/jobs tables retained; up event then ticket, insert valid rows and assert constraints work. No standalone migrate:fresh CLI command |
| rejects missing required catalogue values | `tests/Feature/EventSchemaTest.php` | Dataset null in each nonnullable non-generated field for both tables; SQLSTATE 23502. Omit fields with defaults in valid test; assert GBP/display_order defaults; nullable description/audit fields accepted |
| rejects malformed event identities and blank content | `tests/Feature/EventSchemaTest.php` | Empty/uppercase/space-bearing slug; whitespace-only required text fields including timezone/cover; SQLSTATE 23514 and respective constraint |
| keeps event slugs unique | `tests/Feature/EventSchemaTest.php` | Two rows same slug rejected, 23505; different slug accepted |
| requires finite events ending after their start | `tests/Feature/EventSchemaTest.php` | Equal/reversed start/end; +/-infinity bounds via direct SQL rejected, 23514. Positive one-second duration and overnight interval accepted; no future-only constraint |
| requires ticket ownership and preserves populated events | `tests/Feature/EventSchemaTest.php` | Orphan event_id insert rejected, 23503; parent delete and referenced PK update rejected, 23503; parent and ticket remain intact |
| keeps ticket codes unique within their event | `tests/Feature/EventSchemaTest.php` | Duplicate event_id/code rejected 23505; same code under second event allowed; same name allowed; malformed code and blank name rejected 23514 |
| requires positive total ticket capacity | `tests/Feature/EventSchemaTest.php` | Raw insert/update dataset 0/-1 rejected 23514, null 23502; 1 and 2,147,483,647 accepted; 2,147,483,648 rejected 22003. Failed update preserves old capacity. No counter created/tested |
| stores only paid GBP minor-unit prices | `tests/Feature/EventSchemaTest.php` | 1/1250/max integer accepted; 0/-1 rejected 23514, null 23502, overflow 22003; EUR/USD/gbp rejected 23514. No float arithmetic or currency conversion |
| permits display ties but rejects negative ordering | `tests/Feature/EventSchemaTest.php` | Same event/order accepted; 0 accepted, -1 rejected 23514; optional ticket description null accepted |
| returns only an event's tickets in stable display order | `tests/Feature/Models/EventTest.php` | Two events with tickets, ties/inverse insertion order; relation yields exactly matching IDs ordered display_order/id, never the second event's tickets |
| accepts IANA identifiers and rejects abbreviations and offsets | `tests/Feature/Models/EventTest.php` | Event create/update through assignment: Europe/London, Europe/Helsinki, America/New_York, UTC accepted; BST, +01:00, Mars/Olympus, whitespace rejected with InvalidArgumentException before DB write. Repeat through DatabaseSeeder with WithoutModelEvents to prove supported write path |
| preserves UTC instants while displaying event-local time | `tests/Feature/Models/EventTest.php` | Explicit known UTC winter/summer London dates → known local times/offsets, Helsinki January conversion; immutable casts/UTC ISO serialization and unchanged refreshed stored instants. Direct SQL offset input `2026-07-18 14:00:00+01` reads as `13:00:00+00` |
| keeps distinct instants across an autumn clock change | `tests/Feature/Models/EventTest.php` | London `2026-10-25T00:30:00Z` → 01:30 +01; `01:30:00Z` → 01:30 +00. Persist a valid interval, verify ordering by instants despite repeated wall time; no ambiguous-local-input parser introduced |
| belongs to its exact event | `tests/Feature/Models/TicketTypeTest.php` | Factory::for event, assert parent identity and relationship persistence; no unrelated parent returned |
| returns integer pence and independent total capacities | `tests/Feature/Models/TicketTypeTest.php` | Two types same parent with 1250/2000 pence and capacities 3/7; refresh asserts PHP integers/exact values and GBP; changing one type's capacity does not alter its sibling or create event capacity |
| seeds the exact original catalogue without accounts or sales | `tests/Feature/Database/Seeders/DatabaseSeederTest.php` | Seed empty DB, assert exactly 4 events/8 tickets, all literal identities/fields/prices/caps from section 7, single organizer name, valid original cover data URLs, no external image links; zero users/jobs; no orders/payment/counter schema |
| repeats seeds without changing identities or existing values | `tests/Feature/Database/Seeders/DatabaseSeederTest.php` | Freeze initial clock, seed; capture all rows including IDs/timestamps; advance time, seed again; exact full-row equality, 4/8 counts |
| preserves edits and unrelated records on replay | `tests/Feature/Database/Seeders/DatabaseSeederTest.php` | Change seeded title, venue, ticket name, price and positive capacity; add unrelated factory event/ticket; rerun; all edits/IDs/timestamps preserved, no duplicate from ticket name change, unrelated records unchanged |
| restores only a missing seeded ticket | `tests/Feature/Database/Seeders/DatabaseSeederTest.php` | Delete one ticket with existing event, replay; exactly 4/8 rows and declared ticket restored, seven retained tickets untouched |
| seeds fixed upcoming and past examples across replay dates | `tests/Feature/Database/Seeders/DatabaseSeederTest.php` | travelTo baseline: exact two past/two upcoming slug sets using starts_at. At exact event start classify started; at 2028 reference all four past. Replay never moves schedules |
| rolls back catalogue writes if ticket seeding fails | `tests/Feature/Database/Seeders/DatabaseSeederTest.php` | Container-bind a test-local TicketTypeSeeder subclass that calls parent::run then throws; run real DatabaseSeeder, catch exception; no new event/ticket persists, preexisting unrelated records unchanged. Remove binding and replay: complete catalogue once. No production injection facility |
| fails clearly if a ticket seeder's parent is absent | `tests/Feature/Database/Seeders/DatabaseSeederTest.php` | Call TicketTypeSeeder alone on empty DB; ModelNotFoundException, zero tickets; normal DatabaseSeeder succeeds |

Factory behavior is exercised through these fixtures and relationship tests; no separate test mirrors every factory attribute. Seeding tests assert known literal expected results rather than loading the seeder's private definitions to compute expectations. Database timezone membership is not claimed: raw invalid nonblank names are outside the approved recommendation's DB guarantee. If owner requires DB IANA enforcement, revise schema/tests before coding.

### Exact planned verification commands (not run during planning)

From the existing host/devcontainer setup, after confirming no other test runner is active:

```bash
php artisan test --compact tests/Feature/EventSchemaTest.php
php artisan test --compact tests/Feature/Models/EventTest.php tests/Feature/Models/TicketTypeTest.php
php artisan test --compact tests/Feature/Database/Seeders/DatabaseSeederTest.php
vendor/bin/pint --dirty --format agent
vendor/bin/pint app/Models/Event.php app/Models/TicketType.php database/migrations/2026_10_30_000100_create_events_table.php database/migrations/2026_10_30_000200_create_ticket_types_table.php database/factories/EventFactory.php database/factories/TicketTypeFactory.php database/seeders/EventSeeder.php database/seeders/TicketTypeSeeder.php database/seeders/DatabaseSeeder.php tests/Feature/EventSchemaTest.php tests/Feature/Models/EventTest.php tests/Feature/Models/TicketTypeTest.php tests/Feature/Database/Seeders/DatabaseSeederTest.php --format agent
composer types:check
php artisan test --compact
npm run check
npm run types:check
npm run build
git diff --check
```

First targeted execution is test-first (expected red before implementation); rerun the affected test file after each change. Formatting includes new files explicitly because --dirty may omit untracked files. Final full tests run after formatting; JS checks/build are unchanged-surface regression evidence required by the project, not new frontend work. Do not add dependencies or widen formatting edits outside the manifest to make a check pass; report blockers. Browser QA: not applicable. Inventory 1,000-way contention, process-kill/timeout/expiry/webhook tests: not applicable until those operations exist; retain later-slice obligations.

If using Compose instead, prefix each PHP command with the existing isolated-test runtime:

```bash
docker compose run --rm --no-deps -e APP_ENV=testing -e DB_CONNECTION=pgsql -e DB_HOST=postgres-test -e DB_PORT=5432 -e DB_DATABASE=laravel_test -e DB_USERNAME=laravel_test -e DB_PASSWORD=secret -e DB_URL= app
```

Tests themselves use TestCase's identity guard before migration traits. No manual migrate:fresh, seed CLI, config clearing or development DB writes are needed for the review demo. Test commands mutate only the disposable test DB. If the approved isolated server is unavailable or fails its guard, stop verification and report blocked; do not substitute SQLite or the development connection.

## 9. Implementation order and review demo

1. Obtain explicit Gate A approval and resolve section 10; record exact approved revision/decisions here. Recheck working tree, rules and installed versions.
2. Generate only the four proposed Pest tests; implement the schema/relationship/seed contracts in small groups and observe appropriate failing tests against the guarded test database.
3. Generate migrations via Artisan and normalize names to the manifest. Define both schemas/checks/indexes and prove migration/constraint tests.
4. Generate models/factories; implement focused relations/casts/timezone assignment and prove model tests. No auth changes.
5. Generate both child seeders; implement fixed original data, non-destructive identities and DatabaseSeeder transaction. Prove seed/replay/preservation/failure tests.
6. Run affected tests, formatter/static analysis, full regression and existing JS checks/build. Inspect final diff; record actual commands/results and any blocked checks. No unapproved material deviations.
7. Demonstrate on isolated PostgreSQL: run EventSchemaTest to show migration reversal/reapply; run DatabaseSeederTest to show 4 events/8 tickets, replay equality, preserved edits and rollback. Present actual test output and before/after assertions, optionally inspect test schema read-only through Boost if an isolated connection is configured. Do not add a demonstration route or seed persistent development merely for screenshots.
8. Gate B handoff: implemented file list versus manifest, database schema evidence, exact observed test/check results, demo result, approved deviations and remaining risks. Stop for owner acceptance. Slice 1B is only a proposed next slice and does not begin here.

## 10. Trade-offs, questions and forbidden deviations

**Already settled:** capacity belongs here; reservation/confirmation counters and capacity mutations belong to Slice 4; GBP paid GA, one organizer, no HTTP/frontend. These are not reopened by this plan.

**Technical recommendations for approval:** bigint keys, explicit ticket code, positive signed integers/checks, restrictive FK, two schedule/order indexes, immutable schedule casts, insert-only seeds, duplicated organizer display attribution instead of an organizer table. Repeated organizer_name is a small denormalization for four catalogue rows; seed tests require one shared value. It does not provide multi-organizer management. GBP column plus check is more explicit than an implicit currency. Ticket description is nullable; covers/venue/schedules are required. No need for repository/action/DTO/value-object layers for this persistence-only boundary.

**Owner decisions required before coding (explicitly approve recommendations or request revision):**

1. **Fixed demo season and content:** approve section 7's original organizer/events, illustrative public venues, absolute dates, two GA price tiers and SVG data-URL covers. Fixed dates guarantee replay stability but eventually become entirely past. Alternative: rolling dates only when first creating records, which weakens cross-install determinism; not recommended. Venue listings are illustrative and do not imply actual bookings.
2. **IANA validation boundary:** approve model assignment membership validation plus database nonblank check, with raw SQL bypass explicitly accepted for this seeded-only write surface. If database-level membership must be guaranteed, request a revised plan for an additional lookup/function/trigger or constrained zone list; those additions are not silently authorized. No seed-time-only check masquerades as global enforcement.
3. **Sold-out fixture:** approve deferral of genuine sold-out examples until authoritative sales accounting exists. Alternatively request a separately specified sales-closed product state in a revised plan. This does not remove the final v1 Available/Sold out requirement; it prevents false claims in 1A/1B.
4. **Started/past boundary:** approve starts_at <= reference time as the proposed started/past partition for data tests, including events still in progress; no extra status column. A distinct ongoing customer presentation, if desired, needs its own decision before 1B queries/UI.

**Risks/follow-up:** PHP and PostgreSQL tzdata can evolve; test all seed identifiers on both runtimes. Raw SQL can bypass IANA validation and casts. Fixed venue/organizer/cover text is proposed content. Seed replay deliberately does not update content or mutable catalogue values, and changing natural keys can recreate entries. No accounting means no accurate remaining-capacity/sold-out result yet. Migration rollback loses catalogue data. No test of later-slice payment/capacity invariants is claimed. Persistent development receives no destructive replay; eventual development data population is an explicit operational step after implementation approval, not performed by this planning task.

Forbidden deviations without revised approval: any schema/constraint or public-contract change from this manifest; new abstraction/dependency; extra table/status/flag; inventory counters/actions/locks; fake paid orders; altered transaction/seed update semantics; new security/write boundary; controllers/routes/client code; edits to dormant auth or Slice 0 infrastructure. Routine typing and generator cleanup consistent with the manifest may proceed after Gate A.

## 11. Approval record

**Gate A — plan approval**

- Approval status/date: **Pending; awaiting explicit owner approval.**
- Owner decisions and requested revisions: Capacity approved in current instructions; implementation not approved. Section 10 decisions pending.
- Final approved plan revision: None. Proposed revision 1 dated 30 October 2026.
- Planning changes: This file added; PLANS.md receives proposal link/status while preserving the existing roadmap amendment. No application code, tests, migrations, seeds, dependencies or database data changed; read-only diagnostics only.

**Gate B — implementation acceptance**

- Files actually changed compared with approved manifest: Not applicable; implementation has not started.
- Test commands and actual results: Not run; matrix/commands above are proposals. Read-only inspection confirmed both PostgreSQL servers' versions and isolated test identity/UTC.
- Approved deviations, if any: None.
- Owner acceptance/date: Pending implementation and review.

Stop here. Approval of this document must be explicit; no Slice 1A implementation or Slice 1B planning/implementation is authorized by its creation.
