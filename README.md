# README

## Getting Started

- Clone the `tentaplane/tentapress` and change directory into the clone files.
- Run our setup command:

```bash
php tentapress.php setup
```

or

```bash
./tentapress.php setup
```

This runs the full setup flow (composer install, migrations, plugins sync) and then prompts you to create your first
super admin user.

## Distribution notes

Release source archives intentionally exclude development-only paths (plugins, themes, docs, vendor, node_modules, etc.)
via `.gitattributes export-ignore`. Use the repo clone for full development; use release archives for trimmed
distribution.

## Third-party plugin discovery

When plugins/themes are installed via Composer, TentaPress scans `vendor/<namespace>` for `tentapress.json` manifests.
To allow third-party namespaces, add them to `config/tentapress.php` under `plugin_vendor_namespaces`, then run:

```bash
php artisan tp:plugins sync
```

---

## What this is

An agency-first, WordPress-adjacent platform for building, publishing, and centrally managing landing pages and small
sites - quickly, safely, and with minimal operational overhead.

- **Fast to ship** - templates and constrained sections
- **Client-safe editing** - content changes without layout breakage
- **Central management** - one console for many sites
- **Easy to run** - PHP-based, no Docker required, SQLite-first
- **Easy to extend** - modular by design, plugins as packages
- **Easy to leave** - exports and offboarding are first-class

Read the guiding principles in `MANIFESTO.md` and the longer-term direction in `VISION.md`.

## What this is not

- A full replacement for every WordPress site and plugin on day one
- A blank-canvas page builder
- A platform that requires Docker or Kubernetes to evaluate
- A “zip upload” plugin system for arbitrary, untrusted PHP code
