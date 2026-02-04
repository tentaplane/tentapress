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

## Console Commands

TentaPress provides several Artisan commands for managing your installation.

### Plugin Management

```bash
php artisan tp:plugins sync              # Discover plugins and rebuild cache
php artisan tp:plugins list              # List all plugins with status
php artisan tp:plugins enable <id>       # Enable a plugin (e.g., tentapress/export)
php artisan tp:plugins disable <id>      # Disable a plugin
php artisan tp:plugins enable --all      # Enable all discovered plugins
php artisan tp:plugins defaults          # Enable default plugins from config
php artisan tp:plugins cache             # Rebuild plugin cache
php artisan tp:plugins clear-cache       # Clear plugin cache
```

### Theme Management

```bash
php artisan tp:themes sync               # Discover themes and rebuild cache
php artisan tp:themes list               # List all themes with status
php artisan tp:themes activate <id>      # Activate a theme (e.g., tentapress/tailwind)
php artisan tp:themes cache              # Rebuild theme cache
php artisan tp:themes clear-cache        # Clear theme cache
```

### User Management

```bash
php artisan tp:users:make-admin          # Create an admin user interactively
php artisan tp:permissions seed          # Seed default roles and capabilities
```

### Content Management

```bash
php artisan tp:posts                     # Publish scheduled posts whose date has passed
```

### Development

```bash
composer dev                             # Run PHP server + queue + logs + Vite
bun --cwd plugins/tentapress/admin-shell run dev   # Vite dev server only
bun --cwd plugins/tentapress/admin-shell run build # Build frontend assets
./vendor/bin/pint                        # Format PHP code
./vendor/bin/pint --dirty                # Format only changed files
```

---

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

Read the guiding principles in [`docs/vision/MANIFESTO.md`](docs/vision/MANIFESTO.md) and the longer-term direction in [`docs/vision/VISION.md`](docs/vision/VISION.md).

## What this is not

- A full replacement for every WordPress site and plugin on day one
- A blank-canvas page builder
- A platform that requires Docker or Kubernetes to evaluate
- A “zip upload” plugin system for arbitrary, untrusted PHP code
