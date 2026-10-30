# Slice 0 — Audit and prepare the existing Laravel application

## Slice identity and approval state

- **Slice number and name:** 0 — Audit and prepare the existing Laravel application.

- **Roadmap reference:** `PLANS.md` → Slice 0.

- **Status:** Automated implementation verification complete; awaiting owner browser QA. Revision 2 approved at Gate A; Gate B remains open.

- **Proposed implementation owner/date:** Codex / 30 October 2026.

- **Owner plan approval and date:** Explicit owner approval, 30 October 2026; all four implementation decisions and enumerated test deletions approved. Keep `boost.json` ignored/untracked.

- **Implementation acceptance and date:** Pending.

- **Related approved architecture decisions:** The user identifies the v1 specification and Boost project rules as authoritative. This proposal preserves guest access, server-routed Inertia, PostgreSQL authority, a database queue, no Redis and no live payments. The architecture and roadmap still label themselves proposals; this plan does not record approval of later slices or their transaction designs.

- **Planning authorization:** Only this document may change during this pass. No application implementation, dependency installation, migration, scaffolding, runtime startup or test execution is authorized by this document's creation.

### Revision 2 review decisions

- The owner confirmed that the **original repository document's Markdown is already correctly formatted**. No formatting change to the repository document is requested. This portable copy restores Markdown syntax lost when the attachment was exported.
- The owner prefers the **full, explicitly listed unused starter-kit cleanup in Slice 0**. Do not split auth/dashboard/settings cleanup into a later slice or retain an unnecessary public route merely to reduce the diff. Individual removal targets and protected test-file deletions still require Gate A approval; no destructive schema or existing data deletion is authorized.
- **Keep `boost.json` ignored and untracked**, consistent with the starter's default. Do not modify or force-add it. Its ignored state is intentional, not a tracking defect.
- The additional revision concerns the **test database identity and execution-environment contract**. These are design clarifications; the repository database and Docker state remain unverified until implementation.

## 1. Customer-visible result and boundaries

After implementation approval and verification, a guest can open `/` and see a small, accessible Inertia foundation page. There is no login, dashboard, account/settings navigation or payment control. The existing application, Git history, Laravel Boost integration, React/TypeScript stack and useful generic components remain in place. A developer can start the environment, migrate its existing infrastructure tables, build assets and run meaningful tests against an isolated real PostgreSQL server.

This delivers the Slice 0 exit path and the authentication exclusion portion of acceptance case **A07**. It does not complete A07's eventual whole-product audit or any event, inventory or payment acceptance case. A01 belongs to Slice 1; A02–A06 belong to subsequent browsing/checkout slices; B–F and domain fault injection remain deferred.

### Actual repository audit

Read completely: `AGENTS.md`, `PLANS.md`, `docs/V1_SPEC.md`, `docs/ARCHITECTURE.md`, `docs/ACCEPTANCE_TESTS.md`, `docs/SLICE_PLAN_TEMPLATE.md` and `docs/BOOST_PROJECT_RULES.md`. Read `.ai/rules/index.md` and all twelve referenced rule files; searched their contents for setup, auth, test, PostgreSQL, queue, Inertia, migration and slice concerns. There are no additional framework rule files under `.ai/rules/boost` in this checkout. The committed rules cover application structure, controllers, requests, actions, DTOs, values, models, jobs, commands, migrations, React and tests. Their explicit invokable-controller convention takes precedence over generic resource-controller advice.

Inspected `composer.json`/lock and installed direct dependencies, `package.json`/lock, bootstrap/providers/middleware, all route files, auth/settings controllers and requests, User/model factory/seeder, infrastructure migrations, testing bootstrap and starter tests, frontend entry/layout/type/import relationships, database/queue/cache/session/auth/Inertia configuration, Vite configuration, Compose/devcontainer/CI, Git tracking and development commands. Used the Laravel, Fortify, Inertia React, Wayfinder and testing skills; the Tailwind skill applies to the proposed minimal page's existing utility styling. No convention-inference sweep is appropriate for starter scaffolding.

| Area | Observed evidence | Retain / preparation needed |
| --- | --- | --- |
| Git | Existing repository; head `eb0b9a0` (`added project rules`), earlier `f80f185` (`initial setup`); clean tracked working tree at audit start | Retain history; no initialization or second application |
| PHP/backend | `composer show --direct`: framework **13.39.0**, Inertia Laravel **3.5.2**, Boost **2.10.4**, Fortify **1.41.0**, Wayfinder **0.1.23**; PHP **8.5.9**, Composer **2.10.3** | These are inspected versions, not assumptions based on architecture text; retain framework and tool versions |
| Backend quality tools | Pest **5.3.4**, Pest Laravel plugin **5.0.1**, Pint **1.32.1**, Larastan **3.13.2**, Pao **1.1.5**, Pail **1.2.7**, Sail **1.69.0**, Tinker **3.0.2**, Chisel **0.1.1** | Retain; no broad upgrades or replacement test framework |
| Frontend lock | React/React DOM **19.3.1**, Inertia React/Vite **3.9.1**, TypeScript **5.9.3**, Tailwind **4.3.3**, Vite Plus **1.4.0**, Laravel Vite plugin **3.2.0**, Wayfinder Vite plugin **0.1.11** | Retain lockfile and Vite Plus commands; `vite` is an alias of Vite Plus core, not a stock Vite major |
| Node | Current shell Node **26.13.0**, npm **11.20.0**; devcontainer asks for floating `lts`, CI asks for Node 22 | Align supported execution on Node 22, verify locked tooling there; do not infer compatibility merely from the current shell |
| Runtime | `php artisan about --only=environment,drivers`: local/debug, UTC, pgsql, database queue/cache/session, log mail | Retain UTC and PostgreSQL-backed runtime stores; make examples and CI agree |
| Database | Boost lists default `pgsql`; configured local endpoint is loopback port 5432 and database `laravel`; `pdo_pgsql`, `pcntl`, `posix` are available | Connectivity is blocked, not proven: Boost query returned connection refused. Boost schema summary returned an empty table list; that is insufficient evidence that the database is empty or migrations never ran |
| Auth surface | Route inspection shows login/logout, password reset and confirmation, dashboard, settings/profile/security/appearance | Remove this public workflow. Registration, verification and 2FA routes are not currently registered; test that removal does not leave or reintroduce them |
| HTTP foundation | `/` named `home` is `Route::inertia`; `/up` is Laravel health; normal web middleware/CSRF present | Retain. Local Boost, Inertia devtools and framework storage routes are vendor routes, not ticketing or account features |
| Tests | Pest; global `RefreshDatabase` for Feature; SQLite `:memory:` in `phpunit.xml`; sync queue/array cache/session/mail; Fortify test helper | Replace SQLite test configuration; scope database lifecycle explicitly so subprocess queue tests can observe committed rows |
| Development | `composer dev` calls `artisan dev`; `dev:list` reports server, `queue:listen --tries=1 --timeout=0`, Pail, Vite; no scheduler; no scheduled tasks | Use a bounded `queue:work` command and add scheduler process; do not invent expiry/reconciliation tasks |
| Compose | PostgreSQL 17 Alpine, persistent `postgres_data`, loopback 5432; Redis 7 plus volume; no app/worker/scheduler/Vite services | Retain PostgreSQL version, database name and existing volume; remove Redis provisioning and add application processes |
| Devcontainer | PHP 8.5 Bookworm, PHP PostgreSQL extension/client, Node and Docker-in-Docker features; installs Redis tools/extension and globally installs Laravel installer | Reuse runtime; remove Redis installation and global application installer; supply Node consistently |
| CI | PHP 8.5, Node 22, `composer setup`, `composer ci:check`; no PostgreSQL service | Add real PostgreSQL; deterministic lockfile install and separate bootstrap/verification |
| Tracking | `.ai/rules/`, product/planning documents and devcontainer files are tracked; `AGENTS.md` and `boost.json` exist but are ignored/untracked | Track existing `AGENTS.md` without rewriting it. Preserve the starter's intentional ignore for `boost.json` and machine-specific MCP configuration |

Boost tools used: database connections, schema summary, read-only database query and version-aware documentation search. Documentation searches covered database testing, queue timeout/reservation relationships, scheduler/development processes, Fortify route exposure and Inertia shared props. Installed framework source was also inspected for `DevCommands` and the test application's bootstrap ordering. Disabling Fortify views alone does not remove its mutation routes; the proposal removes the package instead.

Read-only command results: package/version inspection, `artisan about`, `route:list --json`, `list --raw`, `dev:list`, `dev --help` and `schedule:list` succeeded. `schedule:list` reports no tasks. Docker Compose CLI is **2.40.3**, but `docker compose ps` could not access the daemon socket from this sandbox. No escalation, service startup or database mutation was attempted. No tests, build, lint, static analysis or migrations were run; their results are pending implementation.

### Explicit non-goals

No event content, Luma recreation, final branding/assets, domain tables/models/actions/DTOs/value objects, ticket selection, holds, checkout capability, idempotency ledger, capacity locks, Stripe SDK/UI/keys, webhook, email sending, reconciliation or refund code. No admin, account workflow, SPA router, Redis, deployment, publishing or customer messages. No 1,000-actor harness before capacity-changing Slice 4. No speculative abstractions or new application dependency to make a placeholder page work.

## 2. Proposed file-by-file change manifest

This is the complete intended source-file manifest. A row naming multiple files enumerates the exact files, not an open-ended directory deletion. Any additional source-file change requires a manifest revision and, if material, fresh approval. Generated/ignored artifacts are identified separately below.

### Server and configuration

| Path | Add / change / remove | Responsibility | Why needed |
| --- | --- | --- | --- |
| `routes/web.php` | Change | Retain only the named public Inertia home route | Remove dashboard group and settings include |
| `routes/settings.php` | Remove | Account settings route definitions | No account mutations/screens in v1 |
| `routes/console.php` | Change | Remove sample `inspire` command; leave no domain schedule | Avoid irrelevant starter command; scheduler may correctly have no tasks |
| `bootstrap/providers.php` | Change | Remove application Fortify provider registration | Avoid booting deleted auth classes |
| `bootstrap/app.php` | Change | Retain middleware/health/CSRF; remove `sidebar_state` cookie encryption exception | Account sidebar state no longer exists; retain appearance exception |
| `app/Providers/FortifyServiceProvider.php` | Remove | Auth views, reset action and login limiter | Fortify package and workflow removed together |
| `app/Actions/Fortify/ResetUserPassword.php` | Remove | Password-reset operation | No account recovery |
| `app/Concerns/PasswordValidationRules.php` | Remove | Account password rules | No remaining consumers after cleanup |
| `app/Concerns/ProfileValidationRules.php` | Remove | Account profile rules | No remaining consumers after cleanup |
| `app/Http/Controllers/Settings/ProfileController.php` | Remove | Profile read/update/delete | No account settings |
| `app/Http/Controllers/Settings/SecurityController.php` | Remove | Security read/password update | No account settings |
| `app/Http/Requests/Settings/PasswordUpdateRequest.php` | Remove | Password validation | Removed endpoint |
| `app/Http/Requests/Settings/ProfileDeleteRequest.php` | Remove | Account-deletion validation | Removed endpoint |
| `app/Http/Requests/Settings/ProfileUpdateRequest.php` | Remove | Profile validation | Removed endpoint |
| `app/Http/Requests/Settings/TwoFactorAuthenticationRequest.php` | Remove | Fortify 2FA state integration | Remove otherwise dangling package dependency |
| `app/Http/Middleware/HandleInertiaRequests.php` | Change | Share parent props and application name | Remove `auth.user`, user resolution and `sidebarOpen` |
| `app/Providers/AppServiceProvider.php` | Change | Preserve immutable dates/destructive-command protection; configure dev processes | Remove unused Password defaults; use installed `DevCommands` API to replace queue command and register scheduler |
| `config/fortify.php` | Remove | Fortify feature/redirect configuration | Package no longer installed |
| `config/database.php` | Change | Default to `pgsql` rather than SQLite | Examples/default runtime agree; retain unused standard connection definitions without operating their services |
| `config/queue.php` | Change | Use `pgsql` fallback for batch/failure DB selection | Avoid hidden SQLite fallback; retain database retry-after 90 and after-commit false |
| `config/inertia.php` | Change | Disable SSR for this client-rendered foundation | Avoid requiring an SSR bundle/server for clean production-asset smoke; retain the installed integration/plugin |
| `database/seeders/DatabaseSeeder.php` | Change | Empty, explicitly non-account seeding entry point | Remove automatic demo-user insertion; event seeds start in Slice 1 |

### Client

| Path | Add / change / remove | Responsibility | Why needed |
| --- | --- | --- | --- |
| `resources/js/pages/welcome.tsx` | Change | Small guest foundation page with document title, heading and honest preparation copy | Remove starter/auth links without designing event catalogue early |
| `resources/js/app.tsx` | Change | Retain Inertia/React boot, theme, tooltip/toast providers and progress; use no account layout | Remove imports and automatic auth/settings/dashboard layout selection |
| `resources/js/types/global.d.ts` | Change | Declare shared `name` prop | Remove required Auth/sidebar props and unused passwordrules extension |
| `resources/js/types/index.ts` | Change | Shared type exports | Remove auth type export |
| `resources/js/types/ui.ts` | Change | Retain generic FlashToast type | Remove account AppLayoutProps/AuthLayoutProps/AppVariant types |
| `resources/js/types/auth.ts` | Remove | User/Auth client types | No account payload contract |
| `resources/js/pages/dashboard.tsx` | Remove | Authenticated dashboard | Excluded product surface |
| `resources/js/pages/auth/login.tsx` | Remove | Login screen | No login |
| `resources/js/pages/auth/forgot-password.tsx` | Remove | Reset-link screen | No email/account recovery |
| `resources/js/pages/auth/reset-password.tsx` | Remove | Password-reset screen | No account recovery |
| `resources/js/pages/settings/appearance.tsx` | Remove | Account appearance screen | Keep automatic theme support without settings workflow |
| `resources/js/pages/settings/profile.tsx` | Remove | Profile screen | No account management |
| `resources/js/pages/settings/security.tsx` | Remove | Password/security screen | No account management |
| `resources/js/layouts/app-layout.tsx` | Remove | Account layout export | Remove dashboard layout chain |
| `resources/js/layouts/app/app-header-layout.tsx` | Remove | Account header layout | No authenticated navigation |
| `resources/js/layouts/app/app-sidebar-layout.tsx` | Remove | Account sidebar layout | No authenticated navigation |
| `resources/js/layouts/auth-layout.tsx` | Remove | Auth layout export | No auth pages |
| `resources/js/layouts/auth/auth-card-layout.tsx` | Remove | Auth card layout | No auth pages |
| `resources/js/layouts/auth/auth-simple-layout.tsx` | Remove | Auth simple layout | No auth pages |
| `resources/js/layouts/auth/auth-split-layout.tsx` | Remove | Auth split layout | No auth pages |
| `resources/js/layouts/settings/layout.tsx` | Remove | Settings navigation/layout | No settings pages |
| `resources/js/components/app-content.tsx` | Remove | Dashboard content wrapper | Account layout dependency |
| `resources/js/components/app-header.tsx` | Remove | Dashboard/search/user header | References removed route and user contract |
| `resources/js/components/app-shell.tsx` | Remove | Account sidebar state | References removed shared prop |
| `resources/js/components/app-sidebar-header.tsx` | Remove | Account sidebar heading | Account layout dependency |
| `resources/js/components/app-sidebar.tsx` | Remove | Dashboard/user navigation | References removed routes |
| `resources/js/components/nav-main.tsx` | Remove | Dashboard navigation | Remove unused account shell |
| `resources/js/components/nav-footer.tsx` | Remove | Starter shell external navigation | Remove unused account shell |
| `resources/js/components/nav-user.tsx` | Remove | Signed-in user control | References removed auth contract |
| `resources/js/components/user-info.tsx` | Remove | User identity display | References removed User type |
| `resources/js/components/user-menu-content.tsx` | Remove | Settings/logout controls | References removed routes |
| `resources/js/components/delete-user.tsx` | Remove | Account deletion dialog | Removed controller/action import |
| `resources/js/components/password-input.tsx` | Remove | Password visibility input | Account-only control |
| `resources/js/components/appearance-tabs.tsx` | Remove | Account settings theme selector | Retain theme hook, remove settings control |
| `vite.config.ts` | Change | Preserve Vite Plus/Inertia/Wayfinder/Tailwind; bind dev server to container interface with fixed port | Enable Compose HMR; permit only configured local origin, no blanket CORS bypass |

Retain generic UI primitives, breadcrumbs, heading/error/text-link helpers, logo components, hooks, navigation types, CSS/theme tokens and existing favicon assets. They do not expose an account workflow. Avoid a broad unused-component/package purge. Original product branding replaces starter identity in a later browsing slice.

### Tests

| Path | Add / change / remove | Responsibility | Why needed |
| --- | --- | --- | --- |
| `phpunit.xml` | Change | Real PostgreSQL test database; array session/cache/mail and sync default queue; test-only connection identity | Remove SQLite; require `testing`/`pgsql`/`laravel_test` and let only the documented host/port/password vary by runtime; dedicated tests opt into real database queue/cache/session |
| `tests/Pest.php` | Change | Feature base binding without blanket RefreshDatabase; remove sample helper/expectation | Infrastructure worker tests require visible commits and independent connections |
| `tests/TestCase.php` | Change | Remove Fortify helper; check resolved configuration **and actual connected PostgreSQL database/role** before migration traits run | Fail closed before destructive test setup against an unintended database, including stale `DB_URL`/config cache |
| `tests/Feature/ExampleTest.php` | Change | Guest HTML and Inertia home contract, no account props | Strengthen existing public smoke instead of adding a duplicate |
| `tests/Feature/PublicSurfaceTest.php` | Add | Negative HTTP/route coverage of excluded starter endpoints; health smoke | Prove workflow removal across methods, not merely hidden navigation |
| `tests/Feature/InfrastructureTest.php` | Add | PostgreSQL identity/connection isolation, DB sessions/cache and durable queue execution/failure | Prove project infrastructure with real committed persistence |
| `tests/Fixtures/FoundationProbeJob.php` | Add | Test-only serializable queued probe | Separate PHP worker can load it; write unique cache marker or throw intentional exception; no production probe/job |
| `tests/Feature/Auth/AuthenticationTest.php` | Remove, approval required | Starter login/logout/rate-limit tests | Contracts are deliberately removed; replaced by unavailable-endpoint tests, not skipped |
| `tests/Feature/Auth/PasswordResetTest.php` | Remove, approval required | Starter password reset tests | No email/reset workflow |
| `tests/Feature/DashboardTest.php` | Remove, approval required | Starter dashboard tests | Dashboard deliberately unavailable |
| `tests/Feature/Settings/ProfileUpdateTest.php` | Remove, approval required | Starter profile tests | No profile mutations |
| `tests/Feature/Settings/SecurityTest.php` | Remove, approval required | Starter password/2FA settings tests | No security settings/Fortify helper |

Keep `tests/Unit/ExampleTest.php` unchanged rather than silently deleting an existing test. Report it as a starter sanity test, not meaningful evidence for the foundation. No browser-test dependency is proposed; browser QA is manual.

### Development tooling, dependencies and documentation

| Path | Add / change / remove | Responsibility | Why needed |
| --- | --- | --- | --- |
| `compose.yml` | Change | PostgreSQL plus isolated test PostgreSQL, app, Vite, worker, scheduler using shared runtime | Reproducible full local topology; remove Redis service/volume definition, preserve existing PostgreSQL volume |
| `.devcontainer/Dockerfile` | Change | Shared PHP 8.5 + Composer + Node 22 runtime | Reuse current base and PHP extensions; add Node from a Node 22 Bookworm build stage, remove Redis tools/PECL extension |
| `.devcontainer/devcontainer.json` | Change | Reuse shared runtime; keep Docker-in-Docker/editor settings; forward 8000/5173 | Remove duplicate floating Node feature and global Laravel installer hook; application setup stays explicit |
| `.devcontainer/devcontainer-lock.json` | Change | Match remaining devcontainer feature set | Remove Node feature lock entry; preserve existing Docker feature pin |
| `.env.example` | Change | PostgreSQL loopback dev defaults, database queue/cache/session, worker timing and local-only credentials | No SQLite/Redis instructions or actual secrets; keep existing app name pending branding |
| `.env.testing.example` | Add | Optional developer test connection template, database `laravel_test`, loopback port 5433, test-only credentials | Distinct server/role/database; no committed populated `.env.testing` |
| `.gitignore` | Change | Track `AGENTS.md` while ignoring populated `.env.testing` | Remove only the `AGENTS.md` ignore; preserve the original `boost.json`, MCP and secret ignores |
| `AGENTS.md` | Add to Git tracking, content unchanged | Existing authoritative instructions including Boost block | Currently ignored; do not regenerate or rewrite |
| `composer.json` | Change | Remove Fortify; make setup deterministic and non-migrating; retain dev/quality entry points | No accidental database mutation from install/bootstrap; require explicit migrate |
| `composer.lock` | Change | Targeted Fortify removal and newly orphaned transitive entries only | Preserve unrelated installed versions; no broad update |
| `.github/workflows/tests.yml` | Change | PostgreSQL 17 service, PHP PostgreSQL extensions, locked installs, explicit disposable migration/build/check steps | CI must not quietly prove SQLite behavior |
| `docs/slice-plans/00-project-setup.md` | Add now; update after approval/implementation | Proposal, local runbook and approval/evidence record | The only file permitted to change during this planning pass |

No changes proposed to `boost.json`, `package.json`, `package-lock.json`, `phpstan.neon`, existing migrations, User model/factory, auth/cache/session configs or `.ai/rules/`. `boost.json` stays ignored/untracked by owner decision. No new README or other documentation file: the development runbook lives here under the current documentation authorization.

Ignored/local artifacts expected after implementation approval: developer-created `.env`/optional `.env.testing` (never overwrite an existing populated file), vendor/node_modules installed from locks, Vite build/hot/font manifests, regenerated Wayfinder actions/routes/helpers, framework package/autoload/cache files, logs and test caches. These are not committed source changes. Remove stale ignored Wayfinder output for deleted controllers/routes before regeneration; inspect generated output and imports. Do not enumerate generated scaffolding as new application classes. No Git commit or publication is performed by the plan-only pass.

## 3. Laravel class and contract design

No new production Laravel class is needed. Retain the existing `Route::inertia('/', 'welcome')->name('home')` rather than introducing a controller/action/DTO for a page with no input or domain operation. This is an explicit continuation of the starter's view-route convention; future business endpoints follow the recorded thin invokable-controller and action rules.

| Fully qualified class | Responsibility | Input contract | Return/side effects | Dependencies |
| --- | --- | --- | --- | --- |
| `App\Http\Middleware\HandleInertiaRequests` (modified) | Minimal shared application props | Laravel Request | `array\<string, mixed>` including parent shared props and string `name`; no auth/sidebar data | Inertia Middleware and application config |
| `App\Providers\AppServiceProvider` (modified) | Existing safe defaults and local dev process registration | Framework lifecycle | Preserve immutable dates and production destructive-command protection; register worker/scheduler; no Password defaults | Date, DB, installed `Illuminate\Foundation\DevCommands` |
| `Database\Seeders\DatabaseSeeder` (modified) | Future content-seed entry point | `run(): void` | No rows inserted in Slice 0 | Laravel Seeder |
| `Tests\TestCase` (modified) | Safe framework test bootstrap | `createApplication(): Application` | Return booted application only after resolved `testing`/`pgsql` configuration and live PostgreSQL database/role checks; throw before migration lifecycle otherwise | Laravel base test case, config and read-only DB identity query |
| `Tests\Fixtures\FoundationProbeJob` (new, test-only) | Observable durable queue probe | Immutable unique marker string and intentional-failure boolean | Implements ShouldQueue; success writes database-cache marker, failure throws predictable exception; no account/domain rows | Queueable framework trait and Cache; dev autoload `Tests\\` |

Request path: GET → existing web/session/CSRF middleware → Inertia view route → minimal shared props → `welcome.tsx`. Form Request, DTO, business action and domain persistence are **not applicable**, because there is no mutation or business input. A failed backend request follows Laravel's existing error handling; no success is fabricated. No custom error service or public diagnostic endpoint is added.

Retain `App\Models\User`, `Database\Factories\UserFactory` and `config/auth.php` as dormant framework scaffolding. They are not buyer identities and no v1 path creates, authenticates or exposes users. Keeping them avoids dangling default guard/provider configuration and avoids rewriting a historical migration that also provisions guest sessions. Remove the demo user seed. An existing user row is not deleted and an old session does not grant any customer account route. Their full removal is an optional separately approved schema cleanup, not an implicit part of this slice.

## 4. Routes and frontend design

| Method/path | Handler and access | Proposed behavior |
| --- | --- | --- |
| GET/HEAD `/`, name `home` | Existing Inertia view route, `web`, public | Render `welcome`, shared application name; no custom page props or client data fetching |
| GET/HEAD `/up` | Existing Laravel health route | Application boot health only; do not claim it verifies DB/worker/scheduler |
| Former auth/dashboard/settings paths | No registered application/Fortify routes | 404 for tested methods; no redirect to login and no mutation |

Negative coverage includes GET `/login`, POST `/login`, POST `/logout`, GET/POST `/register`, GET/POST `/forgot-password`, GET `/reset-password/test-token`, POST `/reset-password`, GET `/dashboard`, GET `/settings`, GET/PATCH/DELETE `/settings/profile`, GET `/settings/security`, PUT `/settings/password`, GET `/settings/appearance`, GET/POST `/user/confirm-password`, GET `/user/confirmed-password-status`, GET `/email/verify`, POST `/email/verification-notification`, GET/POST `/two-factor-challenge` and POST `/user/two-factor-authentication`. Also inspect the full route collection for any Fortify action, verification/passkey/2FA routes, not merely these examples. Laravel's test requests disable CSRF normally; verify removal by route collection plus response assertions, not a 419 from middleware.

Keep normal CSRF protection in place; there is no webhook exclusion or customer mutation in this slice. No new throttle policy is needed for a static read-only page. Removing Fortify's login limiter does not remove the database-cache rate-limiting capability needed by later checkout routes; those limits will be approved with those endpoints. Do not add per-browser hold quotas.

`resources/js/pages/welcome.tsx` owns a document title, one main landmark, heading and brief text explaining that the booking experience is being prepared. It has no mocked events, fake payment state, disabled ticketing form or backend diagnostic details. Shared props are typed through the existing Inertia declaration. No auth object is required. No page-local domain state, deferred props, polling or loading skeleton is needed. Keep existing theme support, usable contrast and semantic responsive layout; manual QA at 375px and 1440px, keyboard navigation, zoom and light/dark system preference. No final visual design is implied.

Keep Inertia/Vite integration and Wayfinder generation. The page needs no route link; any future link uses generated route functions. Explicitly configure SSR off for this client-rendered slice, retaining the plugin and optional build script rather than operating another Node server. The browser determines no capacity, price, deadline or payment status.

## 5. Database and migrations

**New/modified/removed migrations: None.** No domain schema or data backfill. Do not infer migration state from the failed database inspection, rewrite migration history or drop account tables. At implementation start, repeat Boost schema inspection and `migrate:status` against the intended local database before running pending migrations. If data/schema differs materially from these inspected source migrations, pause and revise this plan.

Retain these exact migrations and their existing table definitions:

- `database/migrations/0001_01_01_000000_create_users_table.php`: `users`, `password_reset_tokens`, `sessions`. `sessions.user_id` is nullable/indexed with no declared FK, allowing guest sessions. User and reset tables remain unused.

- `database/migrations/0001_01_01_000001_create_cache_table.php`: `cache` and `cache_locks` for database cache/locks and future basic HTTP rate limiting.

- `database/migrations/0001_01_01_000002_create_jobs_table.php`: `jobs`, `job_batches`, `failed_jobs`, preserving current queue and failure indexes. Batches are unused but deleting standard infrastructure adds no v1 value.

Because no migration changes are proposed, no new columns, SQL types, defaults, FK, unique/check constraints or indexes need approval here. Existing infrastructure counters/statuses are owned by Laravel's queue/session/cache drivers. There are no ticketing counters, order/payment statuses or new allowed domain transitions. The target clean database also has Laravel's `migrations` repository table. There must be no automatically seeded user, event, order or payment record.

### PostgreSQL configuration and isolation

- Development: keep PostgreSQL **17 Alpine**, `laravel` database and existing `postgres_data` volume. Use Compose service DNS `postgres` from application services; use loopback 5432 from the existing devcontainer/host. Use UTC for the application and PostgreSQL session/server configuration; verify with a real query. No host-wide package install or PostgreSQL major upgrade.

- Tests: separate Compose service **`postgres-test`**, PostgreSQL 17 Alpine, database **`laravel_test`**, role **`laravel_test`**, local-only password and loopback host port **5433**. Use a disposable tmpfs data directory. This avoids creating a test database by a fragile first-run-only init script on the existing development volume.

- In `phpunit.xml`, specify `APP_ENV=testing`, `DB_CONNECTION=pgsql`, `DB_DATABASE=laravel_test`, `DB_USERNAME=laravel_test`, and an empty `DB_URL`. Default `DB_HOST=127.0.0.1`, `DB_PORT=5433` for local tests; **permit explicit host/port overrides for Compose and CI**. Supply `DB_PASSWORD` as a local test-only environment value or from `.env.testing`; do not inherit the development database password by accident. The independent test server internally listens on 5432. The framework must never fall back to SQLite or the development PostgreSQL service when a test connection fails. Check the final resolved Laravel configuration, because XML environment values can interact with already-present environment variables and configuration caching.

**Exact v1 test connection contract** (same database/role in every row; never the development `laravel` database):

| Test runner | `DB_HOST` | `DB_PORT` | `DB_DATABASE` / `DB_USERNAME` | Source of test credentials and overrides |
| --- | --- | --- | --- | --- |
| Host or existing devcontainer | `127.0.0.1` | `5433` | `laravel_test` / `laravel_test` | `.env.testing` or exported test-only `DB_PASSWORD`; Compose publishes only the separate test PostgreSQL port |
| Compose app process running Pest | `postgres-test` | `5432` | `laravel_test` / `laravel_test` | Explicit `docker compose run -e ...` test overrides, including password and empty `DB_URL`; do not reuse `app`'s development `DB_HOST=postgres` |
| CI runner | `127.0.0.1` | `5432` | `laravel_test` / `laravel_test` | Dedicated ephemeral CI PostgreSQL service/role; explicit workflow environment, `CI=true`, empty `DB_URL` |
| Independently spawned test queue worker | **Exactly the parent test runner's resolved test host** | **Exactly the parent's test port** | `laravel_test` / `laravel_test` | Explicit subprocess environment carrying the parent's validated test-only credentials and `APP_ENV=testing`; clear inherited development `DB_URL` and non-test `APP_CONFIG_CACHE` |

The test role/password must come from the **test** PostgreSQL service definition and matching test-runner environment; there is no need for live credentials or a shared development role. Treat the sample local password in the Docker example as a disposable development value only. If a devcontainer's loopback cannot reach the published test port, stop and revise that runtime mapping rather than silently switching to the development service.

- `Tests\TestCase::createApplication()` calls the framework bootstrap, then performs a **read-only preflight before any migration trait can run**: `APP_ENV=testing`; `database.default=pgsql`; resolved `database.connections.pgsql` has an empty `url`/`DB_URL`, `database=laravel_test`, `username=laravel_test`, and the host/port tuple from the approved matrix above (CI's loopback:5432 exception only in CI). On a fresh PostgreSQL connection, query `current_database()` and `current_user` and require both to equal `laravel_test`. Fail with an explicit exception if anything mismatches, the connection is unavailable, or cached configuration changes the resolved identity. Do not run migrations as part of this check. Repeat the actual-database/role assertion in the infrastructure tests.

- A valid `APP_CONFIG_CACHE` is not an excuse to bypass the preflight: inspect the **resolved** configuration after bootstrap, not merely environment strings. The negative test injects an invalid cached database selection and proves the guard rejects it before the migration trait executes. The child worker must use the same preflight-equivalent explicit test identity and must never inherit a development config-cache path. This mechanism protects PHPUnit tests; it is **not** a blanket safeguard for arbitrary `php artisan migrate:fresh` commands.

- Remove the global Feature `RefreshDatabase` trait. Explicitly use `DatabaseMigrations` only in `InfrastructureTest.php`, so its rows are committed and visible to subprocesses. Other Slice 0 HTTP tests use array sessions and need no database migration. Later feature tests must opt into the appropriate lifecycle deliberately.

- Keep default test queue `sync` and stores `array` for unrelated HTTP tests. Infrastructure tests explicitly select `database` queue/cache/session and pgsql failure storage. They must not use Queue::fake or wrap cross-process visibility checks in an outer transaction.

- CI provisions its own PostgreSQL 17 service with database/role `laravel_test`, health check and an ephemeral runner database. It runs the same suite against real PostgreSQL rather than SQLite. The CI workflow's host/port/password tuple must match the matrix, not the local Compose defaults.

Migration rollback is Laravel's existing destructive down behavior; it drops infrastructure tables/data. Demonstrate up/down/up only on the disposable test server, never an existing development volume. **Before any manually invoked test `migrate:fresh`, explicitly inspect the intended Compose service and the resolved connection's database/user; do not rely on the PHPUnit-only guard to protect the CLI.** `migrate:fresh` is not part of normal developer setup. Never run `docker compose down -v` as cleanup. Removing Redis's Compose definition does not authorize deleting an existing Redis volume or touching another project's service.

## 6. State transitions and transaction protocol

Domain state transitions, global inventory lock order, hold/payment idempotency, deadlock retries and Stripe transaction boundaries are **not applicable**: Slice 0 adds no domain mutation, capacity or payment path. Their detailed design remains a Slice 4/6/7 prerequisite; the architecture's illustrative transactions are not implemented now.

Normal framework session/cache writes and database-queue reservation/deletion/failure writes use Laravel's installed drivers. Do not introduce custom transactions around them or replace queue locking. The test suite explicitly verifies committed visibility and distinct PostgreSQL backend connections, without claiming that this proves future oversell resistance.

Queue probe lifecycle: persist serialized test job → see committed `jobs` row → independent PHP worker reserves/executes it → successful marker persists and job is deleted, or retries exhaust and one failed-job row persists. Keep worker runtime timeout **60 seconds**, queue retry-after **90 seconds**, five maximum attempts. Scheduler and HTTP do not coordinate any domain work yet. All external work, including asset/font downloads during build, occurs outside application database transactions; there are no Stripe calls.

## 7. Idempotency, failures, queue and external integrations

Request/Stripe idempotency keys, conflicting payloads, attempt identities, cancellation and customer known/declined/ambiguous payment states are **not applicable**. There is no checkout/payment button or corresponding database state. Do not add placeholder payment abstractions or feature flags.

### Development topology and commands

Reuse `.devcontainer/Dockerfile` as the common development image for PHP 8.5, Composer and Node 22. Retain PostgreSQL extension/client, pcntl and existing useful PHP extensions. Supply Node 22 from a matching Bookworm Node image stage; remove Redis packages/extension and duplicate Node devcontainer feature. Keep the Docker feature pin. Verify image build and UID/write permissions for the existing `vscode` user; do not apply broad permission changes. No production Docker image or additional web proxy is needed for this local slice.

Compose services share the repository mount and working directory, common environment and image:

| Service | Process/configuration | Readiness/behavior |
| --- | --- | --- |
| `postgres` | Existing persistent PostgreSQL 17; local credentials supplied through example/environment defaults | Existing pg_isready health check; no volume replacement |
| `postgres-test` | Isolated PostgreSQL 17, test DB/role, tmpfs | Health check; start explicitly for tests, not a production service |
| `app` | `php artisan serve --host=0.0.0.0 --port=8000 --no-interaction` | Wait for healthy development DB; loopback host port 8000; `/up` health check |
| `vite` | `npm run dev -- --host=0.0.0.0 --port=5173 --strictPort` | Same PHP-capable image because Wayfinder invokes Artisan; loopback host port 5173; dev/HMR origin agrees with APP_URL |
| `worker` | `php artisan queue:work database --sleep=1 --tries=5 --timeout=60 --backoff=5 --no-interaction` | Healthy DB prerequisite; stop grace period exceeds timeout; logs visible in Compose |
| `scheduler` | `php artisan schedule:work --no-interaction` | Healthy DB prerequisite; no tasks due is expected until later slices |

Worker `--backoff=5` is a foundation default, not approval of later payment retry semantics. Slice 6 must add increasing job-specific delays, trusted verification and independent recovery. Retain database queue `after_commit=false`: there is no transactional dispatch now, and the eventual webhook must commit enqueue before acknowledging. Do not change that boundary preemptively.

Support the existing devcontainer workflow too: from inside it, Compose supplies PostgreSQL servers on loopback and `composer run dev` supplies HTTP, queue worker, scheduler, Pail and Vite. Configure `DevCommands` using the installed API: exclude the default `queue` command; register named `worker` with the bounded command above and named `scheduler`. Preserve existing server/logs/Vite defaults. Never run these native processes simultaneously with the equivalent Compose app/worker/scheduler/Vite services.

`composer setup` should install Composer dependencies, copy `.env.example` only if `.env` is absent, generate an app key only when blank, run `npm ci` and build assets. It must no longer run migrations or overwrite a valid app key. Remove obsolete SQLite create-project hooks; no app recreation is supported. `composer ci:check` retains JS checks, TS checks and backend test/lint/static analysis entry points. CI separates installs, key setup, explicit disposable migration and checks rather than relying on the old mutating setup script.

Bootstrap failures must be visible: DB unavailable → migration/test/worker command fails; absent dependencies/key → explicit setup step, no service-start auto-migration; worker exits → Compose/process logs and restart behavior, no claim that process liveness proves fulfillment. Stopping/restarting the dev worker preserves queued rows in development PostgreSQL. Scheduler starts cleanly with no work. Domain crash/timeout injection and 1,000-way contention are deferred because no business operation exists to inject into.

### Dependencies and observability

- **Dependencies to add:** None. Use existing Laravel, Compose, Pest, Vite Plus and Larastan tooling.

- **Dependency to remove, approval required:** direct `laravel/fortify`. `composer why laravel/fortify` showed only this starter project requires it. Review a targeted lock diff and remove only newly orphaned transitive packages; preserve packages shared with Laravel/other dependencies. Do not run an unrestricted dependency update. Report the exact removed lock entries at Gate B; pause if resolution needs unrelated version changes.

- Keep Sail installed but do not run `sail:install` or replace the custom Compose setup. Keep Chisel, Tinker, Pail, Pao and generic JS dependencies; no speculative slimming.

- Keep log mail without sending email, current logs and `/up`. Use CLI DB queries, Compose process status and logs for infrastructure inspection. Do not add a monitoring product/admin endpoint.

- No secrets/card/client-secret/payment payloads in logs or committed environment files. Build currently uses a Bunny font helper; clean builds may need outbound font access. Do not silently remove the font or alter dependencies if the environment blocks it; report a blocked check and seek a specific revision if necessary.

## 8. Test-first verification matrix

Tests below are proposed, not executed. Use the project's Pest `test()` style and Artisan test generation only after approval. No browser automation plugin/download is proposed.

| Scenario | Test file/type | Setup or injected failure | Expected durable result |
| --- | --- | --- | --- |
| Guest can open foundation | `tests/Feature/ExampleTest.php`, HTTP | Plain GET to named home | 200, `welcome` Inertia component, name prop, no auth/sidebar props, no redirect/login requirement |
| Inertia protocol works | Same file, HTTP | Request home with Inertia headers | Inertia response headers/component and typed prop contract; not merely a Blade/static 200 |
| Removed account surface stays absent (A07 portion) | `tests/Feature/PublicSurfaceTest.php`, HTTP dataset + route inspection | Exact methods/paths in section 4; no session/account needed | 404, no redirects, no Fortify actions or account route registrations; no account or mail side effects |
| Boot health available | Same file, HTTP | GET `/up` | 200; explicitly only boot health |
| Real PostgreSQL and safe identity | `tests/Feature/InfrastructureTest.php`, integration | Run using each documented runtime tuple, with a real test server | Driver pgsql; resolved connection URL empty, host/port matches allowed topology, actual `current_database()` and `current_user` both `laravel_test`, UTC; infrastructure tables available, no seeded users |
| Independent committed visibility | Same file, two PostgreSQL PDO connections | Distinct backend PIDs; insert unique cache-key row in explicit transaction A; read B before/after commit | B sees no uncommitted row, then exactly one committed value; rollback variant leaves none; clean unique markers |
| Guest database session works without account | Same file, HTTP + PostgreSQL | Select database sessions/cache and request home | Successful response, persisted guest session with null user_id, no user insertion |
| Database cache supports future basic limiting | Same file, integration | Use database store; unique limiter key at configured test limit | First allowed attempt works, next exceeds limit, cleanup resets it; Redis never involved |
| Job is durably queued and independently consumed | Same file + `tests/Fixtures/FoundationProbeJob.php` | Set database queue/cache; dispatch probe, inspect jobs before starting separate `queue:work --stop-when-empty` PHP process | Exactly one queued row before worker, worker exit 0, marker visible after worker, jobs row gone, no failed row |
| Failed execution is diagnosable | Same files, process integration | Probe throws; worker five attempts with test-only zero backoff | No success marker, job eventually removed, one failed_jobs row with stable exception and UUID; worker completes; no production retry-delay change |
| PostgreSQL unavailable fails visibly | Same integration file, isolated connection | Clone test connection with unreachable port and short connection timeout; never replace default connection | Expected connection exception; no queue fallback or row persisted; main test connection remains usable |
| DB queue enqueue failure is not success | Same integration file | Point test queue's DB connection at that unreachable clone and dispatch probe | Exception surfaces, zero marker/job on working DB; no sync fallback |
| Migration replay remains usable | CLI on disposable test server | Explicit down/up or fresh migration followed by second migrate | All retained migrations succeed; second run reports nothing to migrate; no demo user |
| Test configuration cannot silently select dev DB | `tests/Feature/InfrastructureTest.php`, subprocess bootstrap checks | Inject invalid cached `DB_DATABASE`, `DB_URL`/resolved URL, wrong host/port, and wrong role into separate test bootstraps; do not point any test at a real development DB | Each bootstrap throws an explicit safety error before migration traits or any destructive database operation; subprocess exits nonzero, migration records unchanged, temporary cache removed in finally |
| Test worker cannot inherit development settings | `tests/Feature/InfrastructureTest.php`, process integration | Parent runner uses `postgres-test` or loopback test tuple while dev `.env` points at `postgres`; launch the independent worker with explicit verified test credentials and no inherited `DB_URL`/config cache | Worker consumes test jobs only; marker and `jobs` changes appear on `laravel_test`, with no development database access |
| Full clean environment | Compose + manual browser | Fresh checkout/runtime, installed locks, explicit migration/build | Home renders, CSS/JS load, no console/import/manifest errors; worker/scheduler start; no Redis/auth navigation |

The queued fixture must be a named dev-autoloadable class, not an anonymous serialized job or production feature. Assert real `jobs`/`failed_jobs` rows and marker results. A separate PHP subprocess receives **explicitly resolved and validated** `APP_ENV=testing`, app key, `DB_CONNECTION=pgsql`, test database/role/host/port/password, and database queue/cache settings; clear inherited `DB_URL` and any non-test `APP_CONFIG_CACHE`, and use the same repo/vendor. Do **not** assume `phpunit.xml` environment values automatically apply to `php artisan queue:work` launched in another process. Bound worker process duration and fail with captured output on timeout. `DatabaseMigrations` avoids parent rollback transactions hiding queue rows. Keep all fixtures and migrations confined to `laravel_test`. Negative bootstrap checks run within the focused infrastructure command below; each expected child failure is asserted by a passing parent test. Temporary config caches are runtime artifacts, never committed fixtures containing environment values.

The connection-visibility test is a small infrastructure prerequisite, not a substitute for the acceptance contract's 1,000 simultaneous hold actors. Stripe tests, scheduler domain races, duplicate webhooks, response-loss cut points, crash-safe capacity release and payment correctness remain unverified until their approved slices.

### Exact intended verification commands and exit evidence

Run only after Gate A. Inside a supported PHP 8.5/Node 22 environment at the repository root, unless a Compose prefix is shown. A required failed/unavailable check leaves Slice 0 unverified. Do not count a skipped test or a killed long-running process as a passing test.

1. Recheck audit and source scope:

```bash

  git status --short

  composer show --direct

  php artisan about --only=environment,drivers --no-interaction

  docker compose config --quiet

```

  Expected: recorded framework/dependencies retained except approved removal; pgsql/database stores; valid Compose exit 0. Use sanitized config output, never a full secret dump.

2. Compose bootstrap from a checkout with no installed dependencies (leave an existing `.env` intact):

```bash

  docker compose build app vite worker scheduler

  docker compose up -d postgres postgres-test

  docker compose run --rm --no-deps app composer setup

  docker compose run --rm app php artisan migrate:status --no-interaction

  docker compose run --rm app php artisan migrate --no-interaction

  docker compose run --rm app php artisan migrate --no-interaction

  docker compose up -d app vite worker scheduler

  docker compose ps

  docker compose logs --tail=40 worker scheduler

```

  Expected: build/install/build-assets exit 0, healthy databases, successful explicit pending migration, second migrate reports nothing pending; HTTP/Vite/worker/scheduler remain running. Before the development migration, inspect Boost schema/status; a fresh database may report no migration table yet. An initial `migrate:status` nonzero for a genuinely fresh database is recorded as a bootstrap precondition, not a migration failure. Do not proceed against unexpected existing data/schema.

3. From devcontainer/host tooling using loopback test port 5433 (or use the Compose alternative below), focused then full checks:

```bash

  php artisan config:clear --no-interaction

  php artisan test --compact tests/Feature/ExampleTest.php

  php artisan test --compact tests/Feature/PublicSurfaceTest.php

  php artisan test --compact tests/Feature/InfrastructureTest.php

  php artisan test --compact

  vendor/bin/pint --dirty --format agent

  composer lint:check

  composer types:check

  php artisan wayfinder:generate --with-form --no-interaction

  npm run check

  npm run types:check

  npm run build

  composer ci:check

  git diff --check

```

  Expected: each exit 0, test counts/assertions recorded with no unexpected skips; Larastan level 7 and JS checks clean; no stale auth/settings controller imports; production manifest/page assets generated. Pint formats modified PHP; do not use `--test --format agent`. A format/lint failure outside the manifest is reported for scope review, not fixed by an unapproved repository-wide rewrite.

  Compose alternative for the test runner:

```bash

  docker compose run --rm -e APP_ENV=testing -e DB_CONNECTION=pgsql -e DB_HOST=postgres-test -e DB_PORT=5432 -e DB_DATABASE=laravel_test -e DB_USERNAME=laravel_test -e DB_PASSWORD=secret -e DB_URL= app php artisan test --compact

```

  Apply that same runtime prefix to focused test commands if not using host tooling. The child worker must receive these exact test connection values **explicitly**, not via incidental `.env` inheritance. CI uses loopback port 5432 for its isolated PostgreSQL service, set explicitly in workflow environment. The resolved database/role guard is mandatory across environments. `secret` here is the example disposable local-test password only; match the real approved test service configuration if it differs.

4. On the **disposable test server only**, demonstrate infrastructure migration replay and seed no-op. **Before executing the destructive command**, inspect the separate `postgres-test` service's connection and database/role; confirm the command resolves to `laravel_test` / `laravel_test`, never `postgres` / `laravel`. Stop if anything differs. The PHPUnit guard does not run for these Artisan commands.

```bash

  docker compose exec -T postgres-test psql -U laravel_test -d laravel_test -Atc 'SELECT current_database(), current_user'

  docker compose run --rm -e APP_ENV=testing -e DB_HOST=postgres-test -e DB_PORT=5432 -e DB_DATABASE=laravel_test -e DB_USERNAME=laravel_test -e DB_PASSWORD=secret -e DB_URL= app php artisan db:show --database=pgsql --no-interaction

  docker compose run --rm -e APP_ENV=testing -e DB_HOST=postgres-test -e DB_PORT=5432 -e DB_DATABASE=laravel_test -e DB_USERNAME=laravel_test -e DB_PASSWORD=secret -e DB_URL= app php artisan migrate:fresh --seed --no-interaction

  docker compose run --rm -e APP_ENV=testing -e DB_HOST=postgres-test -e DB_PORT=5432 -e DB_DATABASE=laravel_test -e DB_USERNAME=laravel_test -e DB_PASSWORD=secret -e DB_URL= app php artisan migrate --no-interaction

```

  Expected: preflight shows the **test** database and role explicitly before any destructive command; all checks exit 0, all retained infrastructure tables and migration records are present, zero users after seed, no event/order/payment tables. Use the agreed local test-only password above; adjust commands consistently if approved defaults change. Infrastructure tests also exercise clean migration rollback/recreation on this server. Never substitute the development server here.

5. Process/route/manual checks:

```bash

  php artisan dev:list --no-interaction

  php artisan schedule:list --no-interaction

  php artisan schedule:run --no-interaction

  php artisan queue:failed --no-interaction

  php artisan route:list --json --no-interaction

  git ls-files AGENTS.md .ai/rules PLANS.md docs

  git check-ignore boost.json

  git status --short

```

  Expected: native process list contains server/Vite/logs/worker/scheduler, no unbounded default queue listener; scheduler correctly reports no tasks; no unexpected failed development jobs; no Fortify/dashboard/settings route; governance/rules and `AGENTS.md` tracked with the Boost guideline block preserved; `git check-ignore boost.json` confirms it stays ignored. For already tracked files do not stage unrelated owner changes. Explicitly add only the existing ignored `AGENTS.md` after approval; do **not** force-add `boost.json`. No automatic commit.

  Resolve the project URL through Boost `get-absolute-url` before sharing or opening it for the owner. In the browser inspect home at 375px/1440px, theme, title and lack of account controls; inspect recent browser logs with Boost. Stop Vite, ensure its hot-file cleanup, and reload using the built assets to prove manifest delivery too; restart Vite afterwards. Check `/up` separately. Record observed browser outcomes, not inferred success from an HTTP test. No public URL or deployment is needed.

CI should execute the same lock installs and checks: `composer install --no-interaction --prefer-dist`, `npm ci`, create CI-local `.env`/blank key once, `php artisan key:generate --no-interaction`, explicit migrate against its disposable test database, Wayfinder generation, `npm run build`, `composer ci:check` and `git diff --check`. Keep existing pinned GitHub action revisions. Report local execution separately from an actually observed CI run; do not claim hosted CI passed without observing it.

## 9. Implementation order and review demo

1. Obtain Gate A approval, including the removal/dependency decisions below. Record it here; recheck working tree, installed versions and live schema/migration status. Do not infer approval from a roadmap entry.

2. Add meaningful public-surface regression tests and prepare PostgreSQL test bootstrap/fixtures, using `php artisan make:test --pest ... --no-interaction` for new test files. Record expected failures before cleanup where the isolated test environment permits; rerun a test after each change to it. Do not run old SQLite tests as substitute evidence.

3. Align runtime image, Compose/test PostgreSQL and CI test configuration. Preserve the development database volume. Verify PostgreSQL health/connectivity and migration isolation before any destructive test setup.

4. Remove the approved auth routes/provider/controllers/requests/client layouts and dependent imports. Remove Fortify with a targeted lock change after its references are gone. Remove only the approved obsolete tests; add negative surface coverage. Retain dormant schema/model/provider compatibility, remove account seed.

5. Simplify home/shared props/app boot; keep generic components/theme and typed route tooling. Configure client rendering and container Vite access.

6. Configure worker/scheduler process paths and explicit setup/migration steps. Track the existing `AGENTS.md` without altering content; keep Boost rules/docs intact and preserve `boost.json`'s intentional ignore. Resolve only routine typing/formatting fixes within this manifest.

7. Run focused tests and full applicable verification above; inspect actual diff against every proposed removal and applicable rule. Record failed/blocked checks precisely, including environment limitations.

8. Present Gate B demo and stop. Do not plan or implement Slice 1 as an automatic continuation.

Owner demo: start either the Compose process stack or the existing devcontainer's native dev processes, open the resolved home URL, show the real Inertia page and no account entry points, show removed routes returning 404, show PostgreSQL/migration status and distinct test database, demonstrate a passing real database-queue probe in the test suite, and show worker/scheduler logs. Reload with built assets and no Vite. There is no ticketing demo at this gate.

Final implementation review must include actual changed-file list versus this manifest, exact dependency/lock removals, retained account scaffolding rationale, commands/exit codes/test counts, browser observations, PostgreSQL identity/connection evidence, pending failures/risks and deviations. Acceptance requires the clean-environment path to be demonstrated; inability to access Docker/PostgreSQL leaves that exit criterion blocked.

## 10. Trade-offs, questions and forbidden deviations

### Decisions requested with Gate A

**Owner direction already given during revision:** keep the full unused starter-kit cleanup in this setup slice, and leave `boost.json` ignored. These clarify the proposed scope; they do **not** constitute Gate A approval of the implementation, specific test deletions, dependency removal or infrastructure changes.

1. **Approve removing Fortify and the listed auth/settings/dashboard source and test files?** This permanently removes login/logout/reset/confirmation and account mutation routes from this application until deliberately reintroduced. No account migration/data deletion is included. Existing tests are protected by AGENTS.md's instruction, “Do not delete tests or test files without approval”; this manifest requests that specific approval rather than treating cleanup as implicit permission.

2. **Approve retaining dormant User/model factory/auth config and existing users/password-reset schema?** Recommended because the historical migration also creates guest sessions, live migration state could not be verified and v1 forbids an account workflow rather than requiring a destructive schema purge. Alternative: remove all account persistence with an approved migration and guard/session design; that is a material scope/schema revision and must be planned first.

3. **Approve reusing the devcontainer image for Compose app/Vite/worker/scheduler and using an isolated PostgreSQL test service?** This provides the requested reproducible topology while preserving current tooling and development data. Alternative: devcontainer plus database-only Compose and native processes; fewer services but not a complete standalone Compose start path. Do not install/re-scaffold Sail or add production infrastructure without approval.

4. **Approve Node 22 alignment and SSR disabled for this client-rendered foundation?** CI already selects Node 22. The audit shell's Node 26 and floating devcontainer LTS are inconsistent; a clean image/build verifies compatibility. If the owner wants SSR or Node 26 standardized, revise runtime/CI and verification before coding; do not upgrade locked JS packages by default.

Governance basis is already supplied by the owner's request: use the approved v1 specification and project rules. No repeat approval or re-recording of those conventions is requested. Source-document “proposed”/“draft” labels do not authorize later implementation; this Gate A applies only to Slice 0.

### Risks and alternatives

- **Database and Docker unknowns:** connection refused and socket access denied prevented live schema/server/version inspection. Do not equate Boost's empty summary with an empty database. Runtime bootstrap/verification must be repeated after approval in an environment with usable Docker/PostgreSQL. No data wipe is authorized to resolve a startup problem.

- **Auth cleanup completeness:** hiding links or disabling Fortify views is insufficient; remove package routes, provider hooks, TS imports, shared props and stale generated route files as a unit. Negative route tests and clean dependency installs guard reintroduction.

- **Unused account persistence:** deliberate residual scaffolding, not a delivered account feature. Verify no seed/user mutation or auth props. Reassess only with a separately approved need; do not turn this into speculative user-domain refactoring.

- **Test isolation:** migration traits and separate workers must use the isolated server. Enforce both the approved connection topology and the actual PostgreSQL database/role with read-only preflight, not only the `DB_*` environment strings. Cached configuration/inherited `DB_URL` can otherwise redirect connections. The PHPUnit guard does not cover manually invoked migrations, so each destructive test-only CLI command needs a separate verified preflight. Do not add parallel Pest workers yet; approval of a separate-DB-per-worker setup belongs with future concurrency work.

- **Ignored Boost configuration:** `boost.json` is intentionally excluded from Git as generated by the starter setup. Keep that ignore intact; the committed `.ai/rules/` and the existing `AGENTS.md` carry the shared project conventions. No change to Boost installation or local configuration is required in Slice 0.

- **Future contention:** PostgreSQL default connection limits are not evidence for 1,000 truly concurrent hold actors. Slice 4 must budget processes/connections/resources and approve its real contention harness; the small two-connection probe does not satisfy B01.

- **Build/runtime reproducibility:** Node image/tag patches and external font delivery can change. Preserve package locks and report exact built runtime versions; do not substitute a different bundler or dependency upgrade if builds fail. Devcontainer feature locks remain committed.

- **Existing unrelated quality failures:** checks have not run in this plan-only pass. Report failures and propose minimal scope amendments rather than silently expanding this cleanup.

- **Process health:** `/up` checks boot, not PostgreSQL or payment recovery. Empty scheduler operation is intentional. Durable worker probes demonstrate foundation transport only; five payment retries, increasing backoff, webhook acknowledgement and reconciliation still require Slice 6/7 design/testing.

- **Documentation drift:** architecture says the project has no app/runtime and refers to illustrative Laravel 13 assumptions. This audit corrects the planning basis here; no permission to edit those documents was given. Request a later documentation revision if required rather than changing them now.

Forbidden deviations: new app/repository initialization; unlisted account/schema deletion; new dependency/service abstraction; Redis; domain scaffolding; extra base directories beyond this approved manifest; payment/auth/security-boundary reinterpretation; live credentials/charges; deploying/publishing; editing other product documents or rules during this planning pass. Any material implementation deviation pauses for an explained owner decision. No hold/payment semantics are settled by framework infrastructure defaults in this plan.

## 11. Approval record

### Gate A — plan approval

- **Approval status/date:** Approved explicitly by owner, 30 October 2026.

- **Owner decisions and requested revisions:** Explicit Gate A approval received for revision 2, including Fortify and enumerated source/test deletions, dormant user/schema retention, shared runtime/Compose and isolated PostgreSQL tests, Node 22 and SSR disabled. `boost.json` must stay ignored/untracked. Owner requires implementation to stop on a blocker or material deviation.

- **Final approved plan revision:** Revision 2, including the owner's explicit instruction to keep `boost.json` ignored and untracked.

- **Planning pass changes:** Revision 2 modifies only this proposed planning document, based on the uploaded revision 1. No application code, dependency install, migrations, tests, runtime startup or scaffolding performed as part of this revision.

### Gate B — plan approval

- **Approval status/date:** Approved explicitly by owner, 30 October 2026.

- **Files actually changed compared with the approved manifest:** 81 paths: 29 modified, 46 deleted, 5 new files and existing `AGENTS.md` newly tracked without content edits. All paths match the approved manifest. `boost.json` remains untouched, ignored and untracked. Exact inventory is recorded below.

- **Test commands and actual results:** See the historical checkpoint and final automated-verification record below. Automated checks pass; browser QA remains pending owner observations.

- **Approved deviations, if any:** Owner explicitly approved narrowly scoped Vite Plus formatting exclusions for the existing failing governance Markdown. Exact filenames below; no application-source exclusion, lint/TypeScript disablement, new dependency or governance-document reformatting.

- **Owner acceptance/date:** Pending.

Stop at this proposed plan. A completed document is not implementation authorization; implementation completion will also require a separate owner acceptance before the next slice.

### Implementation checkpoint — 30 October 2026

Implementation stopped when the repository-wide frontend formatting check found failures outside the approved change scope. No excluded document or project-rule formatting has been changed. This is not a completed Slice 0 handoff and no Gate B acceptance is requested yet.

Observed commands and outcomes:

| Command | Actual result |
| --- | --- |
| `git status --short --untracked-files=all`, `composer show --direct`, `docker compose config --quiet` | Existing state inspected; Compose configuration valid. Original untracked revision 2 plan preserved and updated with approval/checkpoint. |
| `docker compose ps` | Sandbox daemon access initially denied; approved escalation succeeded, showing no running project containers. |
| `docker volume ls --filter label=com.docker.compose.project=booking-engine` | No existing project volumes listed. |
| `docker compose up -d postgres` | PostgreSQL image pulled; persistent `booking-engine_postgres_data` created without replacing/deleting any existing volume. |
| Boost database query/schema summary | Development identity `laravel` / `laravel`, PostgreSQL 17.11, UTC, no application tables before migration. |
| `php artisan migrate:status --no-interaction` | Initial sandbox socket access failed; escalated read-only command reported migration table absent, consistent with empty schema. |
| `php artisan make:test --pest PublicSurfaceTest --no-interaction`, `php artisan make:test --pest InfrastructureTest --no-interaction` | Test files created. |
| `docker compose up -d postgres-test` | Separate PostgreSQL test service started with tmpfs storage and test-only database/role. |
| `php artisan test --compact tests/Feature/ExampleTest.php tests/Feature/PublicSurfaceTest.php tests/Feature/InfrastructureTest.php` (before cleanup) | 43 tests, 20 passed / 23 failed, 120 assertions. Failures exposed remaining auth props/routes, account seeding, pre-resolved starter limiter, and a missing Inertia version header in the new test. No skips reported. |
| `composer remove laravel/fortify --no-scripts --no-interaction --minimal-changes --no-update-with-dependencies` | Exit 0; 15 removals, zero installs/updates. Network-restricted advisory lookup could not complete; this is not a successful dependency audit. |
| `composer dump-autoload --no-interaction` | Exit 0; package discovery succeeds without Fortify. |
| Same focused three-file test command after cleanup/header correction | Exit 0; **43 passed, 169 assertions**, no skips reported. Real PostgreSQL visibility/rollback, guest session, database limiter, separate worker success/five-failure execution, unavailable connection/enqueue and seven unsafe cached-configuration cases passed. |
| `docker compose build app vite worker scheduler` | Exit 0; shared image built. Build output captured at `/tmp/booking-engine-slice0-build.log` for this session. |
| `docker compose run --rm --no-deps app composer setup` | Exit 0; existing environment/key preserved by conditional setup, Composer locked install, `npm ci` and asset build completed without migrations. npm reported zero vulnerabilities. Existing optional Fontaine font-fallback warning remains; no dependency added. Output at `/tmp/booking-engine-slice0-setup.log`. |
| `docker compose run --rm --no-deps app sh -c 'php -v \| head -1; node --version; npm --version; test -w storage; test -w bootstrap/cache'` | Exit 0; image PHP 8.5.12, Node 22.23.4, npm 10.9.9; application directories writable. |
| `php artisan migrate --no-interaction` (development) | Exit 0; only the three existing infrastructure migrations applied to verified empty development schema. No seed, destructive migration, account/domain record insertion or data deletion. |
| Same development migrate command a second time | Exit 0; nothing to migrate. |
| `php artisan dev:list --no-interaction` | Bounded worker, scheduler, server, Pail and Vite registered; default queue listener excluded. |
| `php artisan schedule:list --no-interaction`, `php artisan schedule:run --no-interaction`, `php artisan queue:failed --no-interaction` | Exit 0; no scheduled tasks/ready work and no failed development jobs. |
| `docker compose up -d app vite worker scheduler` | Exit 0; services started; development PostgreSQL container recreated for environment changes using the same persistent volume. Final process health/browser checks pending. |
| `vendor/bin/pint --dirty --format agent` | Modified tracked PHP formatted. New untracked test/fixture formatting still needs explicit verification. |
| `composer lint:check` | Sandbox parallel-worker socket restriction initially failed; escalated rerun passed. |
| `composer types:check` | Failed at default 128 MB PHP memory limit. |
| `composer types:check -- --memory-limit=512M` | Exit 0; no PHPStan errors. Making CI/runtime memory allowance consistent remains pending. |
| `git add AGENTS.md`, `git check-ignore boost.json`, `git ls-files boost.json` | Sandbox index write initially denied; escalated tracking succeeded. `boost.json` remains ignored with no tracked entry. |
| Semantic comparison of old/new Composer locks | Exactly the 15 listed packages removed; no retained package version changed. Composer reclassified shared packages still needed by dev tooling into packages-dev. |
| `docker compose run --rm --no-deps app npm run check` | Exit 1; formatting issues in 24 files (listed below). Output at `/tmp/booking-engine-slice0-js-check.log`. Implementation stopped for owner decision. |
| `git diff --check` | Found an extra blank line in changed `resources/js/types/ui.ts`; corrected. Final rerun pending. |

Removed Composer packages: `bacon/bacon-qr-code`, `dasprid/enum`, `laravel/fortify`, `laravel/passkeys`, `paragonie/constant_time_encoding`, `pragmarx/google2fa`, `spomky-labs/cbor-php`, `spomky-labs/pki-framework`, `symfony/polyfill-php81`, `symfony/property-access`, `symfony/property-info`, `symfony/serializer`, `symfony/type-info`, `web-auth/cose-lib`, `web-auth/webauthn-lib`.

**Formatting blocker:** `npm run check` flags every existing `.ai/rules/*.md` file (13 including index), `AGENTS.md`, `PLANS.md`, and five authoritative `docs/*.md` files. These are outside the approved formatting-change scope, with AGENTS content explicitly unchanged. It also flags four in-scope files: `.devcontainer/devcontainer-lock.json`, `compose.yml`, this slice plan, and `resources/js/pages/welcome.tsx`. Those four can be formatted within the manifest, but fixing them alone cannot make the required full check pass.

**Requested decision before resuming:** approve explicitly excluding existing governance Markdown (`.ai/rules/**/*.md`, `AGENTS.md`, `PLANS.md`, authoritative `docs/*.md`) from Vite Plus formatting in the already-listed `vite.config.ts`, while preserving code lint/type checks and formatting the changed in-scope files; alternatively authorize reformatting those existing documents. No such exclusion or out-of-scope formatting has been applied.

Remaining checks include standalone TypeScript/full regression/aggregate CI checks, focused reruns after final formatting, final route/process/DB inspections, explicit disposable-server migration replay and clean-environment demonstration. This session has no installed browser executable or browser automation tool; responsive/theme visual QA must be supplied by the owner or an approved browser environment. No browser-test dependency has been installed. No Slice 1 work started.

### Resumed implementation and final automated verification — 30 October 2026

The owner approved only narrowly scoped `fmt.ignorePatterns` exclusions, requested all remaining checks, and directed that browser QA remain pending until their observations arrive. The prior checkpoint above is historical; its formatting blocker is resolved. Gate B remains open.

**Exact additional formatting exclusions:** these 21 literal filenames are present individually in `vite.config.ts`. No new wildcard excludes future documents or any application source. Existing starter ignore entries remain unchanged. No lint ignore or TypeScript setting was weakened.

```text
.ai/rules/actions.md
.ai/rules/app.md
.ai/rules/console-commands.md
.ai/rules/controllers.md
.ai/rules/data.md
.ai/rules/index.md
.ai/rules/jobs.md
.ai/rules/js.md
.ai/rules/migrations.md
.ai/rules/models.md
.ai/rules/requests.md
.ai/rules/tests.md
.ai/rules/values.md
AGENTS.md
PLANS.md
docs/ACCEPTANCE_TESTS.md
docs/ARCHITECTURE.md
docs/BOOST_PROJECT_RULES.md
docs/SLICE_PLAN_TEMPLATE.md
docs/V1_SPEC.md
docs/slice-plans/00-project-setup.md
```

Only `.devcontainer/devcontainer-lock.json`, `compose.yml`, `resources/js/pages/welcome.tsx` and `vite.config.ts` were passed to the Vite formatter. Governance Markdown was not reformatted; this plan was updated with implementation evidence.

Routine fixes within the approved manifest, revealed by the remaining checks:

- Added `export {}` to `resources/js/types/global.d.ts` so removing the auth import does not turn its Inertia module augmentation into an ambient replacement. This resolved the four TypeScript errors without modifying generic hooks/utilities or weakening type checks.
- Enforced PHPUnit's fixed test identity/stores in both server and environment channels. Inherited Compose server variables had otherwise selected database sessions for read-only HTTP tests after an infrastructure-test rollback. Infrastructure tests still explicitly select database stores; workers receive explicit resolved test credentials. The guard still verifies allowed topology and live database/role before migrations.
- Set `composer types:check` to `phpstan analyse --memory-limit=512M`, giving the same sufficient process limit to local and CI checks after the observed 128 MB exhaustion. No runtime memory/configuration dependency added.
- Set Vite HMR host to `localhost`; the generated browser-facing hot URL now uses the published local address instead of `0.0.0.0`.
- Added `--no-reload` to Compose's `artisan serve` command. Inspection of the installed ServeCommand showed that its default reload mode strips inherited DB overrides when `.env` exists, making HTTP requests use loopback inside the container. The flag preserves Compose's `DB_HOST=postgres`. No `.env` was rewritten. Restart the Compose app after environment changes; the native devcontainer path keeps its existing serve behavior.

#### Final commands and observed results

All commands below exited 0 unless a failure is explicitly documented. Tests ran sequentially against the disposable test server, never concurrently with another migration/test run. Full command output for the Compose test/CI sequence is in `/tmp/booking-engine-slice0-final-checks.log` for this session; the durable summary is this record.

Node 22 frontend verification used the shared runtime:

```bash
docker compose run --rm --no-deps app npm exec vp -- fmt .devcontainer/devcontainer-lock.json compose.yml resources/js/pages/welcome.tsx vite.config.ts
docker compose run --rm --no-deps app npm run check
docker compose run --rm --no-deps app npm run types:check
docker compose run --rm --no-deps app npm run build
```

Results: formatting passed for 33 files; lint passed for 23 files, no warnings/errors; `tsc --noEmit` passed; asset build passed (623 modules). The build still prints the existing optional Fontaine optimized-font-fallback notice; no package was added and every tested built asset serves successfully.

PHP formatting:

```bash
vendor/bin/pint --dirty --format agent
vendor/bin/pint tests/Feature/InfrastructureTest.php tests/Feature/PublicSurfaceTest.php tests/Fixtures/FoundationProbeJob.php --format agent
```

Both passed. The explicit second command includes new untracked PHP files that a Git-dirty formatter might omit.

For each command in the following list, the exact Compose test runtime prefix was:

```bash
docker compose run --rm --no-deps -e APP_ENV=testing -e DB_CONNECTION=pgsql -e DB_HOST=postgres-test -e DB_PORT=5432 -e DB_DATABASE=laravel_test -e DB_USERNAME=laravel_test -e DB_PASSWORD=secret -e DB_URL= app
```

| Command after that prefix | Actual final result |
| --- | --- |
| `php artisan config:clear --no-interaction` | Configuration cache cleared; no database mutation |
| `php artisan test --compact tests/Feature/ExampleTest.php` | 2 passed, 17 assertions |
| `php artisan test --compact tests/Feature/PublicSurfaceTest.php` | 27 passed, 84 assertions |
| `php artisan test --compact tests/Feature/InfrastructureTest.php` | 14 passed, 68 assertions |
| `php artisan test --compact` | 44 passed, 170 assertions |
| `composer lint:check` | Passed |
| `composer types:check` | Passed, no PHPStan errors |
| `composer ci:check` | JS formatting/lint, TypeScript, PHP formatting/static analysis and full tests passed; 44 tests / 170 assertions |

No final tests were skipped. One of the 44 tests is the deliberately retained starter unit sanity test; the other 43 are the public/foundation/infrastructure evidence. The host/devcontainer connection path was separately verified with `php artisan test --compact`: 44 passed, 170 assertions on loopback port 5433.

Intermediate failures are not omitted: the first resumed TypeScript command found four declaration errors, fixed above; initial Compose regression runs failed two home tests because inherited server environment selected database sessions after schema rollback, fixed above. An initial real HTTP request failed with development PostgreSQL loopback connection refused inside the app container, fixed with `--no-reload`. An immediate readiness curl during app restart returned connection error 7; subsequent healthy-process and HTTP checks passed. These are resolved failures, not pending or skipped checks.

Disposable-server preflight and migration replay:

```bash
docker compose exec -T postgres-test psql -U laravel_test -d laravel_test -Atc 'SELECT current_database(), current_user'
```

Result `laravel_test|laravel_test`; an additional read-only query reported UTC. With the explicit test runtime prefix above, `php artisan db:show --database=pgsql --no-interaction` confirmed host `postgres-test`, port 5432, empty URL and matching role/database before destructive replay. Then `php artisan migrate:fresh --seed --no-interaction` succeeded, followed by `php artisan migrate --no-interaction` reporting nothing pending. The test database had the nine retained infrastructure/migration tables, zero users and no domain tables. No such destructive command ran against development PostgreSQL.

Clean bootstrap was verified from `/tmp/booking-engine-slice0-clean`, a source snapshot excluding `.env`, vendor, node_modules, generated route files, compiled assets and database/log/session data. This copies the existing application for verification; it does not scaffold or initialize a second application/repository. Before setup, vendor, node_modules and `.env` were absent.

```bash
docker run --rm --mount type=bind,src=/tmp/booking-engine-slice0-clean,dst=/workspaces/booking-engine --workdir /workspaces/booking-engine --env XDEBUG_MODE=off booking-engine-dev composer setup
```

Result: clean Composer lock install (140 packages), `npm ci`, automatic local app key creation and asset build succeeded without migration or development database access. A follow-up temporary-container check confirmed a nonempty generated key, built manifest and `php artisan route:list --name=home --no-interaction` booting the public Inertia route. Output is captured at `/tmp/booking-engine-slice0-clean-setup.log` for this session. The actual development `.env` and key were never copied into this snapshot or overwritten.

Other completed verification:

| Exact command / check | Observed result |
| --- | --- |
| `docker compose config --quiet` | Valid configuration |
| `docker compose ps` | App and both PostgreSQL services healthy; worker, scheduler and Vite running |
| `docker compose logs --tail=20 worker scheduler vite` | Scheduler runs with no tasks expected; Vite serves; no reported worker failure |
| `docker compose run --rm --no-deps app php artisan about --only=environment,drivers --no-interaction` | Laravel 13.39.0, image PHP 8.5.12, local/UTC; pgsql and database cache/session/queue |
| `docker compose run --rm --no-deps app php artisan migrate:status --no-interaction` | All three existing development migrations ran |
| `docker compose run --rm --no-deps app php artisan dev:list --no-interaction` | server, worker, scheduler, Pail and Vite; no default unbounded queue listener |
| `docker compose run --rm --no-deps app php artisan schedule:list --no-interaction` | No scheduled tasks, as intended |
| `docker compose run --rm --no-deps app php artisan schedule:run --no-interaction` | No tasks ready, exit 0 |
| `docker compose run --rm --no-deps app php artisan queue:failed --no-interaction` | No failed development jobs |
| `docker compose run --rm --no-deps app php artisan route:list --json --no-interaction` | Public home/health and retained vendor tooling/storage routes; no account, dashboard or settings routes |
| `docker compose run --rm --no-deps app php artisan wayfinder:generate --with-form --no-interaction` | Typed routes/actions regenerated; no stale Fortify/settings action files found |
| `docker compose run --rm --no-deps app composer audit --no-interaction` | No security vulnerability advisories found; supersedes the prior network-blocked advisory attempt |
| `git diff --check` | Passed |
| `git check-ignore boost.json`, `git ls-files boost.json` | Still ignored; no tracked entry |
| `git ls-files AGENTS.md .ai/rules PLANS.md docs` | Existing governance and rule files tracked, including newly tracked AGENTS; this new plan awaits normal staging/commit |
| Diff against `.ai/rules/`, `PLANS.md` and authoritative product documents | No changes |
| Status paths versus approved manifest | 81 changed paths, no unlisted files |

**PostgreSQL isolation confirmed:** development service remains `postgres` / `laravel` / `laravel` with its persistent volume; tests use a different server `postgres-test` / `laravel_test` / `laravel_test` with tmpfs and host port 5433. Independent worker and connection tests passed on both documented Compose and host test paths. Read-only development checks after all tests reported three migration records, zero users, zero jobs and zero failed jobs. The existing migrations, dormant User/factory/auth configuration and development volume remain intact.

**Real HTTP evidence:** with Vite stopped and its generated hot file cleared, home returned 200 and contained Inertia component `welcome` with only `errors` and `name` props; a versioned X-Inertia request returned 200 plus the Inertia header; all 10 manifest-listed built assets returned success. `/up` returned 200; GET `/login`, `/register`, `/dashboard` and `/settings` returned 404. Vite was restarted; home and its `/@vite/client` resource then returned success. This is HTTP evidence, not a claim that a browser rendered the page correctly.

#### Actual file inventory

Every path below is in the approved file-by-file manifest. No commit has been created.

| Path | Actual change |
| --- | --- |
| `.devcontainer/Dockerfile` | Modified |
| `.devcontainer/devcontainer-lock.json` | Modified |
| `.devcontainer/devcontainer.json` | Modified |
| `.env.example` | Modified |
| `.github/workflows/tests.yml` | Modified |
| `.gitignore` | Modified |
| `AGENTS.md` | Existing file newly tracked; contents unchanged |
| `app/Actions/Fortify/ResetUserPassword.php` | Deleted |
| `app/Concerns/PasswordValidationRules.php` | Deleted |
| `app/Concerns/ProfileValidationRules.php` | Deleted |
| `app/Http/Controllers/Settings/ProfileController.php` | Deleted |
| `app/Http/Controllers/Settings/SecurityController.php` | Deleted |
| `app/Http/Middleware/HandleInertiaRequests.php` | Modified |
| `app/Http/Requests/Settings/PasswordUpdateRequest.php` | Deleted |
| `app/Http/Requests/Settings/ProfileDeleteRequest.php` | Deleted |
| `app/Http/Requests/Settings/ProfileUpdateRequest.php` | Deleted |
| `app/Http/Requests/Settings/TwoFactorAuthenticationRequest.php` | Deleted |
| `app/Providers/AppServiceProvider.php` | Modified |
| `app/Providers/FortifyServiceProvider.php` | Deleted |
| `bootstrap/app.php` | Modified |
| `bootstrap/providers.php` | Modified |
| `compose.yml` | Modified |
| `composer.json` | Modified |
| `composer.lock` | Modified |
| `config/database.php` | Modified |
| `config/fortify.php` | Deleted |
| `config/inertia.php` | Modified |
| `config/queue.php` | Modified |
| `database/seeders/DatabaseSeeder.php` | Modified |
| `phpunit.xml` | Modified |
| `resources/js/app.tsx` | Modified |
| `resources/js/components/app-content.tsx` | Deleted |
| `resources/js/components/app-header.tsx` | Deleted |
| `resources/js/components/app-shell.tsx` | Deleted |
| `resources/js/components/app-sidebar-header.tsx` | Deleted |
| `resources/js/components/app-sidebar.tsx` | Deleted |
| `resources/js/components/appearance-tabs.tsx` | Deleted |
| `resources/js/components/delete-user.tsx` | Deleted |
| `resources/js/components/nav-footer.tsx` | Deleted |
| `resources/js/components/nav-main.tsx` | Deleted |
| `resources/js/components/nav-user.tsx` | Deleted |
| `resources/js/components/password-input.tsx` | Deleted |
| `resources/js/components/user-info.tsx` | Deleted |
| `resources/js/components/user-menu-content.tsx` | Deleted |
| `resources/js/layouts/app-layout.tsx` | Deleted |
| `resources/js/layouts/app/app-header-layout.tsx` | Deleted |
| `resources/js/layouts/app/app-sidebar-layout.tsx` | Deleted |
| `resources/js/layouts/auth-layout.tsx` | Deleted |
| `resources/js/layouts/auth/auth-card-layout.tsx` | Deleted |
| `resources/js/layouts/auth/auth-simple-layout.tsx` | Deleted |
| `resources/js/layouts/auth/auth-split-layout.tsx` | Deleted |
| `resources/js/layouts/settings/layout.tsx` | Deleted |
| `resources/js/pages/auth/forgot-password.tsx` | Deleted |
| `resources/js/pages/auth/login.tsx` | Deleted |
| `resources/js/pages/auth/reset-password.tsx` | Deleted |
| `resources/js/pages/dashboard.tsx` | Deleted |
| `resources/js/pages/settings/appearance.tsx` | Deleted |
| `resources/js/pages/settings/profile.tsx` | Deleted |
| `resources/js/pages/settings/security.tsx` | Deleted |
| `resources/js/pages/welcome.tsx` | Modified |
| `resources/js/types/auth.ts` | Deleted |
| `resources/js/types/global.d.ts` | Modified |
| `resources/js/types/index.ts` | Modified |
| `resources/js/types/ui.ts` | Modified |
| `routes/console.php` | Modified |
| `routes/settings.php` | Deleted |
| `routes/web.php` | Modified |
| `tests/Feature/Auth/AuthenticationTest.php` | Deleted |
| `tests/Feature/Auth/PasswordResetTest.php` | Deleted |
| `tests/Feature/DashboardTest.php` | Deleted |
| `tests/Feature/ExampleTest.php` | Modified |
| `tests/Feature/Settings/ProfileUpdateTest.php` | Deleted |
| `tests/Feature/Settings/SecurityTest.php` | Deleted |
| `tests/Pest.php` | Modified |
| `tests/TestCase.php` | Modified |
| `vite.config.ts` | Modified |
| `.env.testing.example` | Added |
| `docs/slice-plans/00-project-setup.md` | Added |
| `tests/Feature/InfrastructureTest.php` | Added |
| `tests/Feature/PublicSurfaceTest.php` | Added |
| `tests/Fixtures/FoundationProbeJob.php` | Added |

#### Owner browser QA — approved

Exact local URL, resolved through Laravel Boost: **http://localhost:8000/**. App and Vite are running; a devcontainer client must forward ports 8000 and 5173. Do not run another native `composer dev` stack over the same Compose ports.

Please record observed results for:

1. **Desktop:** at roughly 1440px, the welcome heading and preparation text render clearly, without account controls or horizontal overflow. Check light/dark system preference and keyboard focus behavior.
2. **Mobile:** at roughly 375px and with browser zoom, text remains readable and the page fits the viewport.
3. **Console/network:** reload with developer tools open; record any JavaScript, hydration, module-import, HMR or asset-loading errors. No browser logs have yet been supplied; Boost's browser-log tool reported no log file, which is not a passing console check.
4. **Inertia rendering:** the visible React page appears (not a blank shell), document title becomes `Welcome - Laravel`, and the initial HTML contains Inertia page component `welcome`. Reload preserves the same guest page. Automated HTTP protocol/asset checks passed; visual rendering still needs this observation.
5. **Authentication absent:** directly open `/login`, `/register`, `/dashboard` and `/settings`; each should show 404 without a sign-in redirect. Automated tests cover the remaining mutation/password/verification/2FA endpoints.

**Browser observations:** Pending owner response. **Gate B acceptance:** Open. **Hosted GitHub CI run:** Not triggered/observed; local `composer ci:check` passed. **Remaining warning:** Existing optional Fontaine font-fallback notice only; no additional dependency proposed. **Slice 1:** Not started.
