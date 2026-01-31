# Plugins

- Audience: plugin authors/agents building features in `plugins/*/*`.
- Goal: consistent, readable, adaptable plugin code with clear wiring and contracts.
- Tech: Laravel 12, PHP 8.2, Tailwind v4, Vite, Alpine.

## Standard layout (per plugin)

- `composer.json` (path repo, PSR-4 to `src/`).
- `tentapress.json` (manifest; see rules below).
- `src/`
  - `Domain/` (entities, value objects, contracts, policies).
  - `Services/` (use-case logic; stateless where possible).
  - `Http/Admin/` controllers (invokable, thin; FormRequest validation).
  - `Http/Public/` controllers for public routes.
  - `Support/` (helpers, traits, DTOs).
  - `Providers/` (if multiple providers) or root `*ServiceProvider.php`.
- `routes/admin.php` (name admin routes `tp.<area>.*`).
- `routes/web.php` (public routes, if any).
- `database/migrations/` (plugin-local tables; keep id prefix date-stamped).
- `resources/views/` (Blade; admin + public; reuse `tp-*` classes).
- `resources/lang/` (optional translations).
- `tests/` (phpunit/pest once added; include smoke tests for wiring).

## Manifest rules (`tentapress.json`)

- Required fields: `id` (`vendor/name`), `name`, `description`, `version`, `provider` (FQCN), `requires` (array).
- Admin menus (optional): `admin.menus[]` with `label`, `route`, `capability`, `position`, `parent`.
- Keep manifest as source of truth; registries will cache it. Avoid duplicates (`id` must be unique).
- Recommended: future JSON Schema validation (plan for strict fields; avoid extra keys).

## Defaults and optional plugins

- Default plugins are configured in `config/tentapress.php` and enabled by `tp:plugins defaults`.
- Optional plugins can be installed with Composer and enabled via `tp:plugins enable vendor/name`.
- `tp:plugins enable --all` only enables plugins whose providers are installed.

## Service provider duties

- Register routes: admin (`routes/admin.php`) under middleware group from system AdminMiddleware; public (`routes/web.php`) as needed.
- Bind contracts and tag extensibility points (e.g., adapters) for discovery.
- Admin menus are built centrally by the admin-shell menu builder; keep `admin.menus` in manifests as the source of truth.
- Register policies/gates for capabilities; prefer constants/enum.
- Register view namespace and config publishes (if needed).
- Defer heavy work to boot callbacks; keep `register` lightweight.

## Controllers, requests, services

- Controllers should be invokable and thin: validate with FormRequest, delegate to Services/Actions.
- Prefer response helpers (redirect with flash, JSON with status) over manual response building.
- Keep business logic in Services/Actions with clear method names and typed arguments/returns.
- Avoid `dd/dump`; use exceptions or flash errors.

## Capabilities and menus

- Define capability names centrally (enum or constants) to avoid stringly-typed checks.
- Menus are built by the admin-shell menu builder; keep menu definitions data-only in `tentapress.json`.
- Guard admin routes/controllers with capability checks; fail with clear messages.

## Extensibility patterns

- Adapters: define interfaces (e.g., deployment adapters, block renderers) and tag them in the container for discovery.
- Registries: expose read-only lists keyed by identifier; validate required methods/shape on registration.
- Blocks: define metadata (key, name, fields, view) in a registry; reject unknown block keys early.

## Validation and errors

- Validate inputs via FormRequest or Validator; return human-readable errors.
- Use `throw_if/abort_if` for guard clauses with actionable messages.
- Log operational failures (adapters, external IO) with context (plugin id, adapter id).

## Views and assets

- Blade: use Prettier Blade parser; keep directives tight (`spacesAfterControlDirective=1`).
- CSS: prefer existing `tp-*` utilities (see `resources/css/admin.css`); avoid deep specificity.
- JS: ESM modules; Alpine for sprinkles; keep entrypoints small.
- Vite: register plugin assets sparingly; avoid large globals.

## Testing (when added)

- Add `phpunit.xml`/Pest config at repo root; recommend `php artisan test` for suites and `php artisan test --filter ClassName::method` for single tests.
- Plugin smoke tests: registry discovery of manifest, menu registration, route availability, adapter contracts.
- Adapters: unit test happy path + error surfaces; prefer in-memory fakes where possible.

## Checklists

- New plugin scaffold
  - [ ] Create `composer.json` (path repo) + PSR-4 to `src/`.
  - [ ] Add `tentapress.json` with id/name/version/provider and menus/capabilities.
  - [ ] Add service provider: routes, menus, policies, bindings/tags, view namespace.
  - [ ] Add routes (`admin.php` named `tp.<plugin>.*`; `web.php` if needed).
  - [ ] Add migrations if storing data; seed defaults if needed.
  - [ ] Add controllers (invokable) + FormRequests; services/actions.
  - [ ] Add views using `tp-*` classes; reuse partials.
  - [ ] Register adapters/blocks via interfaces and container tags.
  - [ ] (When tests available) add smoke tests for registry/menu/routes/adapters.

- Review a plugin
  - [ ] Manifest complete/unique; menus/capabilities defined.
  - [ ] Routes named/namespaced correctly; middleware applied.
  - [ ] Controllers thin; validation present; logic in services/actions.
  - [ ] Capabilities/constants not hardcoded strings.
  - [ ] Adapters/blocks registered via contracts; unknown keys handled.
  - [ ] Views use existing utilities; no inline scripts unless necessary.
  - [ ] Migrations sane (ids, timestamps); seeds/factories if applicable.
  - [ ] (If tests) smoke coverage for wiring.
