AGENTS handbook for this repo (TentaPress Laravel monorepo)

Scope

- Audience: automation/agent contributors. Keep instructions concise and follow repo conventions.
- Stack: PHP 8.2+, Laravel 12, Bun/NPM + Vite + Tailwind v4, AlpineJS. Plugins/packages are path repositories under
  `plugins/*/*` and `packages/*/*`.
- No Cursor/Copilot rule files found.

Workspace orientation

- App roots: `app/`, `routes/`, `database/`, `public/`, `themes/`, `plugins/`, `packages/`.
- Plugins/packages are local Composer path deps; `tp:plugins sync` discovers them.
- Assets:
    - Admin entrypoints live in `plugins/tentapress/admin-shell/resources/{css,js}`.
    - Themes own their own asset entrypoints under `themes/*/*/resources/`.
- Tailwind sources:
    - Admin: `plugins/tentapress/admin-shell/resources/css/admin.css` includes cached views + plugin/package sources.
    - Theme CSS lives per theme and sources its own `views/` plus package/plugin views.

Setup and dependencies

- PHP deps: `composer install` (Laravel, local plugin packages).
- JS deps: prefer Bun (`bun install`); npm fallback OK for dev commands.
- Env: copy `.env.example` to `.env`, run `php artisan key:generate`, configure DB (sqlite in CI; use your DB locally),
  then `php artisan migrate`.
- Post-autoload hook runs `php artisan tp:plugins sync`.

Run/build commands

- Full setup: `composer setup` (composer install → copy .env if missing → key:generate → migrate --force →
  `bun --cwd plugins/tentapress/admin-shell install` → `bun --cwd plugins/tentapress/admin-shell run build`).
- Dev servers: `composer dev` (concurrently php artisan serve, queue:listen --tries=1, pail log stream, npm dev in
  admin-shell). Requires Node/NPM installed; Bun not used in this script.
- Frontend dev (core/admin): `npm --prefix plugins/tentapress/admin-shell run dev` or
  `bun --cwd plugins/tentapress/admin-shell run dev`.
- Theme dev: `bun run --cwd themes/<vendor>/<theme> dev`.
- Frontend build: `npm --prefix plugins/tentapress/admin-shell run build` or
  `bun --cwd plugins/tentapress/admin-shell run build`.
- Laravel app key: `php artisan key:generate`.
- Plugin sync: `php artisan tp:plugins sync` and optionally `php artisan tp:plugins enable --all`.
- Migrations: `php artisan migrate --force` (CI uses sqlite) or `php artisan migrate` locally.

Quality/lint/format

- PHP formatting: `./vendor/bin/pint` (config: `pint.json`, preset psr12). Check mode: `./vendor/bin/pint --test`.
- PHP refactors: `./vendor/bin/rector` (config: `rector.php`, enables Laravel code quality/collections/if-helpers sets,
  type coverage level 1, dead code level 1, php82). Removes dump helpers (`dd`, `dump`, `var_dump`).
- Blade/JS/CSS formatting: Prettier with Blade + Tailwind plugins (`.prettierrc`, width 120, semicolons, single quote,
  tabWidth 4, trailing commas, bracketSameLine).
- Blade formatting options: `.blade.format.json` uses Laravel Pint style, single quotes, PHP 8.2;
  `spacesAfterControlDirective=1`.
- EditorConfig: 4-space indent, LF, UTF-8, trim trailing whitespace (except markdown), 2-space YAML.

Testing status

- Root test runner is Pest (PHPUnit under the hood) with `phpunit.xml` + `tests/`.
- Monorepo discovery is enabled from root for `tests/`, `plugins/*/*/tests`, and `packages/*/*/tests`.
- CI does not run tests yet; run them locally from root with `composer test` or `vendor/bin/pest`.

CI expectations (`.github/workflows/ci.yml`)

- Triggers on push to main and PRs.
- Steps: checkout → setup PHP 8.2 → composer install (no scripts) → create sqlite db → prepare `.env` (APP_ENV=testing,
  sqlite) → migrate → key:generate → `./vendor/bin/pint --test` → `tp:plugins sync` + `tp:plugins enable --all` +
  migrate → setup Bun → `bun --cwd plugins/tentapress/admin-shell install --frozen-lockfile` →
  `bun --cwd plugins/tentapress/admin-shell run build`.
- CI omits rector and any tests; keep Pint passing.

Conventions: PHP/Laravel

- Use `declare(strict_types=1);` and typed properties/returns. Prefer `final` and `readonly` where sensible.
- Namespaces follow PSR-4 under `App\`, `TentaPress\...` in packages/plugins. Keep one class per file.
- Constructors use property promotion; favor dependency injection.
- Collections: lean on Laravel Collection APIs; Rector enforces `whereLike` usage via `WhereToWhereLikeRector`.
- Error handling: prefer domain exceptions; use Laravel helpers (`abort_if`, `throw_if`) with clear messages. Avoid
  dumping (`dd`/`dump`)—Rector will remove.
- Validation: use FormRequests or validator; return human messages.
- Controllers: RESTful names `XxxController`; routes in `routes/` or plugin package routes.
- Middleware: register aliases/groups (see `packages/tentapress/system/src/Http/AdminMiddleware.php`).
- Config/data: migrations for schema; seeds/factories available if added.

Conventions: Blade/Frontend

- Blade files live under `resources/views` or package/plugin `resources/views`; use Prettier Blade parser. Keep
  directives compact; `spacesAfterControlDirective=1`.
- Components: follow existing partials; avoid inline scripts in Blade when possible.
- JS: ESM imports; AlpineJS used in `plugins/tentapress/admin-shell/resources/js/admin.js`. Prefer small modules, no
  globals.
- CSS: Tailwind v4 CSS-first. Use `@import "tailwindcss"` and `@source` entries to include templates/JS. Admin UI kit
  uses `tp-*` utility classes in `plugins/tentapress/admin-shell/resources/css/admin.css`; reuse those utilities before
  adding custom styles.
- Assets build with Vite; keep entrypoints small and tree-shakeable.

Imports and ordering

- PHP: group `use` statements, alphabetical, no unused imports. Keep native types before classes when mixing.
- JS/CSS: use ESM; order: node modules → aliases → relative. For CSS, prefer Tailwind utilities; avoid deep specificity.

Naming

- Classes: StudlyCase; interfaces `SomethingInterface` only if needed; events/jobs/listeners descriptive.
- Methods: verbs for actions (`syncPlugins`, `registerRoutes`); boolean accessors `is/has/should`.
- Variables: descriptive, avoid abbreviations; collections plural.
- Blade/CSS: `tp-` prefix for admin UI utilities; keep consistent with existing tokens.

Error handling & logging

- Avoid `dd/dump`. Use exceptions or responses. Log with Laravel logger; keep messages actionable.
- For user-facing errors, use `tp_notice_*` flash keys which render as admin toasts.

Performance & security

- Use eager loading where needed; avoid N+1.
- Validate and authorize requests; check admin middleware for protected areas.
- Avoid storing secrets in repo; `.env` is required locally, excluded from git.

Monorepo/plugin notes

- Composer path repos under `plugins/*/*` and `packages/*/*`; symlinked. Keep namespaces aligned with folder names.
- After modifying plugin/package manifests, run `php artisan tp:plugins sync`.
- Plugin assets/views live within each plugin under `resources/`.

Adding tests (future guidance)

- Preferred: feature tests via Pest.
- Place tests in `plugins/<vendor>/<plugin>/tests/Feature` and `packages/<vendor>/<package>/tests/Feature`.
- Run all tests from root with `composer test` or a single filter:
  `composer test:filter -- MyFeatureTest`.
- Keep sqlite-friendly tests for CI; seed data via factories.

Pre-PR checklist

- Format PHP with Pint; format Blade/JS/CSS with Prettier.
- Run rector if touching PHP architecture-heavy areas (`./vendor/bin/rector`).
- Ensure migrations run (`php artisan migrate`); sync plugins (`tp:plugins sync`).
- Build frontend (`bun --cwd plugins/tentapress/admin-shell run build` or
  `npm --prefix plugins/tentapress/admin-shell run build`).
- Run tests from root (`composer test`) and filter with `composer test:filter -- SomeTestName` when iterating.

Known gaps to respect

- CI does not run tests yet; if enabling tests in CI, use root Pest command (`composer test`).
- CI skips rector; run locally before pushing major refactors.
- No ESLint/Stylelint; rely on Prettier + Tailwind conventions.

House rules

- Branching: branch per task, avoid pushing to main; suggested pattern `agent/<issue-id>-<slug>`.
- Keep diffs small; one task per PR.
- Ask before adding dependencies where possible.
- Do not remove user changes; avoid destructive git commands.
- `scripts/` is local-only (ignored) and should not be committed.

Quick command reference

- Install PHP deps: `composer install`
- Sync plugins: `php artisan tp:plugins sync`
- Enable all plugins: `php artisan tp:plugins enable --all`
- Migrate: `php artisan migrate --force`
- Test (root Pest): `composer test`
- Test single filter: `composer test:filter -- SomeTestName`
- Format PHP: `./vendor/bin/pint --test` (check) / `./vendor/bin/pint`
- Rector: `./vendor/bin/rector`
- Frontend install: `bun install` (preferred) or `npm install`
- Frontend dev (core/admin): `npm --prefix plugins/tentapress/admin-shell run dev` or
  `bun --cwd plugins/tentapress/admin-shell run dev`
- Theme dev: `bun run --cwd themes/<vendor>/<theme> dev`
- Frontend build: `npm --prefix plugins/tentapress/admin-shell run build` or
  `bun --cwd plugins/tentapress/admin-shell run build`
- Full setup shortcut: `composer setup`

If something is missing

- Prefer following nearest similar file as precedent.
- When introducing tests or new tooling, document the exact commands here and wire them into CI.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines
should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an
expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.17
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- rector/rector (RECTOR) - v2
- alpinejs (ALPINEJS) - v3
- prettier (PRETTIER) - v3
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that
domain—don't wait until you're stuck.

- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles,
  restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors,
  typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards,
  buttons, or any visual/UI changes.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling
  files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature
  tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run
  `bun --cwd plugins/tentapress/admin-shell run build`, `bun --cwd plugins/tentapress/admin-shell run dev`, or
  `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available
  parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the
  correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel
  or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the
  remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an
  array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example:
  `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`,
  not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally
  complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd and will be available at: `https?://[kebab-case-project-dir].test`. Use the
  `get-absolute-url` tool to generate valid URLs for the user.
- You must not run any commands to make the site available via HTTP(S). It is always available through Laravel Herd.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list
  available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the
  correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries
  or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing
  them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other
  things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you
  should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both
  validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config
  files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be
  used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to
  use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit`
  to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run
  `bun --cwd plugins/tentapress/admin-shell run build` or ask the user to run
  `bun --cwd plugins/tentapress/admin-shell run dev` or `composer run dev`.

Commit conventions

- Always use Conventional Commits (`feat`, `fix`, `chore`, `build`, `refactor`, `docs`, `test`), with scope when
  relevant (e.g. `feat(admin-shell): ...`).
- If you touch a plugin, bump its `tentapress.json` and README version using SemVer aligned to the commit type (major
  for breaking, minor for `feat`, patch for `fix`/`chore`/`build`/`refactor`/`docs`/`test`).

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console
  configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column.
  Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing
  conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected
  style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples.
  Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.
  </laravel-boost-guidelines>
