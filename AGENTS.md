<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>

# Repository instructions — ticketing v1

This repository will contain a single Laravel application with server-routed
Inertia pages, React, TypeScript, and PostgreSQL. The approved product contract
is `docs/V1_SPEC.md`; `docs/ARCHITECTURE.md` is the proposed engineering design,
`docs/ACCEPTANCE_TESTS.md` defines the evidence, `PLANS.md` orders delivery,
`docs/SLICE_PLAN_TEMPLATE.md` specifies the per-slice approval document, and
`docs/BOOST_PROJECT_RULES.md` defines the coding conventions to record through
Laravel Boost's project-rules mechanism.

## Authority and approval

- Read `docs/V1_SPEC.md`, `docs/ARCHITECTURE.md`,
  `docs/ACCEPTANCE_TESTS.md`, `PLANS.md`, and
  `docs/SLICE_PLAN_TEMPLATE.md` completely before planning or implementing a
  slice. Read `docs/BOOST_PROJECT_RULES.md` and the applicable Laravel Boost
  `.ai/rules` via `.ai/rules/index.md`. Follow the existing `AGENTS.md`
  instructions throughout.
- Do not silently change a settled requirement, invent a feature, or reinterpret
  an invariant to make a test pass. Raise conflicts for human decision.
- `V1_SPEC.md` owns **what** the product must do. Architecture may be refined
  only without weakening that contract; material changes require approval.
- The design documents are proposals until their owner explicitly approves them.
- Implement only one **approved implementation plan** for one vertical slice
  at a time. Stop at its implementation review gate with a demo, changed-file
  summary, test evidence, and open risks.
- No live Stripe keys, live charges, deployments, publishing, or external
  customer messages without separate explicit authorization.

## Mandatory two-gate workflow for every slice

**Gate A — proposed implementation plan (no code):**

1. Read the documents above and inspect the actual repository and relevant
   current code. Do not assume the code matches an earlier plan.
2. Write a slice-specific plan using `docs/SLICE_PLAN_TEMPLATE.md`, e.g.
   `docs/slice-plans/04-atomic-holds.md`. This documentation is the only
   permitted file change during a plan-only task, unless the owner explicitly
   requests another documentation change. Do **not** scaffold, migrate,
   install dependencies, edit application code, or start implementation tests
   as a side effect of planning. Read-only inspection is allowed.
3. Present exact proposed files and class responsibilities, public contracts,
   migrations/indexes/constraints, routes, Inertia components, transaction
   boundaries and lock order, idempotency and failure handling, jobs/Stripe
   integration where applicable, test cases, verification commands, risks,
   and explicit non-goals. Mark irrelevant sections "not applicable" with a
   reason; do not silently omit them.
4. Identify any design conflict or material unresolved choice as a question.
   **Stop and wait for explicit owner approval of this slice's plan.** An
   existing roadmap entry or approval of another slice is not approval.

**Gate B — implement, verify, review:**

5. Only after Gate A approval, implement the approved plan and nothing outside
   its scope. Keep the per-slice plan updated with approved decisions.
6. If implementation needs a material deviation, **pause before making it**,
   explain impact and alternatives, and obtain explicit approval. Material
   deviations include schema or constraint changes, new dependencies,
   additional services/abstractions, changed action ownership or contracts,
   altered transaction/locking/idempotency/payment semantics, new features,
   or changed security boundaries. Routine local refactors or typing fixes
   consistent with the plan do not require approval.
7. Run applicable tests and checks. Present the implemented-file list, demo,
   results of actual verification commands, unresolved risks, and deviations.
   **Stop for owner acceptance before starting the next slice.**

The owner can request another planning revision without authorizing code.
Never interpret "looks good" on an unrelated document or a previous slice as
permission to skip either gate.

## Laravel Boost project rules

- Laravel Boost's generated framework guidelines are complementary to this
  file. If Boost already owns a `<laravel-boost-guidelines>` block inside the
  repository's `AGENTS.md`, keep that block intact and place these instructions
  **outside** it. Do not overwrite the existing starter-kit or Boost setup.
- The owner's coding preferences belong in Laravel Boost **project rules** at
  `.ai/rules/`, not duplicated as a long style guide in `AGENTS.md`.
  `docs/BOOST_PROJECT_RULES.md` is the approved rule-recording specification.
- **Before Slice 0 planning**, use Boost's `record-rule` MCP tool to create
  the rules and maintain `.ai/rules/index.md`. Present the generated files for
  owner review. This is a documentation-only setup step: no application code,
  installs, database mutations or refactoring are authorized.
- Before planning or editing a code file, consult the applicable paths in
  `.ai/rules/index.md` and read their rule files. Do not guess a missing rule
  or infer the owner's intended style from the unmodified starter kit.
- Commit `.ai/rules/` and its index to Git. A project rule never overrides
  `docs/V1_SPEC.md`, the payment/capacity invariants in this file, or the
  two-gate approval workflow. Raise any conflict for owner resolution.

## Non-negotiable engineering rules

- PostgreSQL is the only source of truth for capacity, orders, payment links,
  idempotency records, and queued jobs. Redis is not part of v1.
- Never use a browser-provided amount or a browser report of payment success
  to mark an order paid. Never allow confirmed plus held inventory to exceed
  capacity.
- The initial pending order, order items, and reserved counters must commit
  atomically. A repeated checkout request returns that same order and does not
  reset its deadline.
- Keep transactions short. Never call Stripe while holding PostgreSQL inventory
  row locks. Use an explicit, consistent lock order and deadlock retry policy.
- A payment-uncertain order keeps capacity protected. Safe Stripe cancellation
  or verified final failure is required before release; no time-only release.
- All Stripe mutations must have stable logical idempotency keys. Distinguish
  Laravel checkout idempotency from Stripe API idempotency.
- Webhook processing, confirmation, refund handling, and scheduled recovery
  must be independently retry-safe and tolerate duplicate/out-of-order events.
- Display immediate Stripe.js payment success to the customer; only verified
  backend processing changes the database order to `confirmed`.

## Verification

- Test concurrency against **real PostgreSQL using separate connections**,
  not SQLite or a transaction mock. Include 1,000-way contention against scarce
  inventory and deliberate crash/timeout injection.
- Test each meaningful state transition, repeated request, scheduler race,
  Stripe timeout, and webhook redelivery before declaring a slice finished.
- Use Stripe test mode for integration; use deterministic fakes for injected
  failures. Never test with live customer payment credentials.
- Run the focused tests, broader regression suite, TypeScript checking, build,
  and formatting/lint checks applicable to the slice. Report commands and
  observed results; never claim tests passed without running them.
- If the environment cannot run a required test, say so and leave the slice
  unverified rather than quietly substituting a weaker test.

## Scope guardrails

- No admin or organizer management, login, emails, post-purchase booking
  lookup, QR check-in, waiting room, waitlist, or Redis in v1.
- No additional per-browser active-hold limits (deferred to v2); retain basic
  HTTP rate limiting.
- Event content is seeded; browse experience resembles the structure and
  interaction of https://luma.com/venicebookclub with original branding/assets.
- No Laravel application code should be written until the user approves this
  proposed design and the first implementation slice.
