# TentaPress

An agency-first publishing platform for landing pages, campaigns, and small sites. TentaPress gives teams a faster way
to launch, a safer way for clients to edit, and a cleaner path to maintain sites over time.

| Key       | Value      |
|-----------|------------|
| Version   | 0.33.34    |
| PHP       | 8.2+       |
| Framework | Laravel 12 |
| License   | MIT        |

## Quick Start (ZIP + setup script)

This is the easiest way to get started if you are not planning to develop core code.

1. Download the latest ZIP from [GitHub Releases](https://github.com/tentaplane/tentapress/releases/latest).
2. Unzip it somewhere on your machine.
3. Open a terminal in that folder.
4. Run:

```bash
php tentapress.php setup
```

5. Follow the prompts:
    - site name
    - site URL
    - starter theme install
    - optional demo homepage
    - first admin login
6. Open your admin at `/admin` and sign in.

If your machine allows executable scripts, this also works:

```bash
./tentapress.php setup
```

## Why TentaPress works so well

TentaPress is built for teams that need to move quickly without creating long-term content chaos.

- Fast launch flow: starter setup script, starter theme, optional demo homepage.
- Structured editing: block-based content that keeps pages consistent and harder to break.
- Agency-friendly operations: plugin-driven architecture for clear ownership and controlled extension.
- Laravel-native confidence: modern framework foundations, security defaults, and familiar tooling.
- Practical publishing model: pages, posts, media, menus, users, SEO, and settings in one admin.

In short: TentaPress helps teams ship faster today without inheriting a mess tomorrow.

## What the setup script does for you

`tentapress.php setup` automates first-time installation by:

- preparing `.env` and setting `APP_NAME`, `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`
- installing PHP dependencies
- creating app key, running migrations, and linking storage
- syncing/enabling default plugins and seeding permissions
- installing/activating a starter theme (optional)
- optionally creating demo homepage content
- creating your first super admin user

## Technical Setup and Operations

### Requirements

- PHP 8.2+
- Composer
- SQLite/MySQL/PostgreSQL (SQLite is the easiest first run path)
- Optional for frontend/theme asset builds: Bun, pnpm, or npm

### Common commands

```bash
php artisan tp:plugins sync
php artisan tp:plugins list
php artisan tp:plugins enable --all

php artisan tp:themes sync
php artisan tp:themes list
php artisan tp:themes activate tentapress/tailwind

php artisan tp:users:make-admin
php artisan tp:permissions seed
php artisan tp:posts
```

### Development commands

```bash
composer dev
bun --cwd plugins/tentapress/admin-shell run dev
bun --cwd plugins/tentapress/admin-shell run build
composer test
composer test:filter -- SomeTestName
./vendor/bin/pint --dirty
```

### Testing (Pest, Monorepo)

TentaPress uses Pest at the root and auto-discovers tests across core, plugins, and packages.

- Core tests: `tests/Feature`
- Plugin tests: `plugins/<vendor>/<plugin>/tests/Feature`
- Package tests: `packages/<vendor>/<package>/tests/Feature`
- Plugin/package tests automatically run with Laravel `RefreshDatabase`.
- Example plugin feature test: `plugins/tentapress/pages/tests/Feature/HomeRedirectTest.php`.
- Example package feature test: `packages/tentapress/system/tests/Feature/PluginsListCommandTest.php`.
- Migration strategy: module feature tests run on sqlite (`:memory:`) with fresh migrations per test execution.

Run all tests from root:

```bash
composer test
```

Validate a fresh setup:

```bash
composer install
composer test
composer test:filter -- HomeRedirectTest
```

CI now runs `composer test` on pull requests and pushes to `main`.

Release archives exclude test sources via `.gitattributes` (`/tests`, `plugins/**/tests`, `packages/**/tests`).

### Third-party plugin discovery

TentaPress discovers plugin manifests (`tentapress.json`) inside allowed Composer vendor namespaces.

Add namespaces to `config/tentapress.php` (`plugin_vendor_namespaces`), then run:

```bash
php artisan tp:plugins sync
```

## Security

Please report vulnerabilities privately and do not open public exploit issues.

- Full policy: [`SECURITY.md`](SECURITY.md)
- Supported versions and disclosure process are documented there.

Operational basics:

- always run HTTPS in production
- rotate secrets and keep them out of git
- keep dependencies updated
- apply least-privilege access for admin and database credentials

## Contributing

Contributions are welcome. Keep pull requests small, readable, and documented.

- Contributor guide: [`CONTRIBUTING.md`](CONTRIBUTING.md)
- Architecture decisions: `docs/adrs/`
- Product requirement docs: `docs/prds/`

Before opening a PR:

- format changed PHP files with `./vendor/bin/pint`
- run relevant migrations and plugin sync
- update docs when behavior changes

## Vision

- Manifesto: [`docs/vision/MANIFESTO.md`](docs/vision/MANIFESTO.md)
- Product direction: [`docs/vision/VISION.md`](docs/vision/VISION.md)
