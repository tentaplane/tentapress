# ADR 0001 - Plugin and Theme Manifest Discovery

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
TentaPress needs a consistent way to discover first-party and third-party extensions without hardcoding package lists. Discovery must work in monorepo development and Composer-installed environments.

---

## Decision
Use filesystem manifest discovery via `tentapress.json` as the canonical extension contract for plugins and themes.

For plugins:
- Scan `plugins/*/*` plus configured vendor namespaces under `vendor/<namespace>/*/*`.
- Parse each manifest through `PluginManifest`.
- Treat `id` (`vendor/name`) and `provider` as required fields.

For themes:
- Scan `themes/*/*`.
- Parse through `ThemeManifest`.
- Accept `type: theme` for vendor themes and ignore non-theme manifests.

---

## Options considered
### Option A
- Pros:
  - Minimal coupling between core and extension packages.
  - Works with path repositories and published Composer packages.
  - Keeps extension metadata explicit and versioned.
- Cons:
  - Requires correct manifest authoring.
  - Duplicate IDs are possible and must be managed operationally.

### Option B
- Pros:
  - Could infer plugins from Composer metadata only.
  - Fewer manifest files to maintain.
- Cons:
  - Insufficient for theme metadata and admin integration metadata.
  - Harder to support non-Composer extension metadata like admin menus/assets.

### Option C (optional)
- Pros:
  - Manual registry table edits would be simple to reason about.
- Cons:
  - Error-prone and not source-controlled.
  - Poor developer and deployment ergonomics.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Extension onboarding is deterministic and source-driven.
  - Plugin/theme metadata is unified across discovery, admin, and runtime.
- Negative:
  - Manifest schema mistakes surface at runtime/command time.
- Trade-offs:
  - Flexibility and extensibility are favored over strict compile-time guarantees.

---

## Notes
Implemented primarily in:
- `packages/tentapress/system/src/Plugin/PluginRegistry.php`
- `packages/tentapress/system/src/Theme/ThemeRegistry.php`
- `packages/tentapress/system/src/Plugin/PluginManifest.php`
- `packages/tentapress/system/src/Theme/ThemeManifest.php`
