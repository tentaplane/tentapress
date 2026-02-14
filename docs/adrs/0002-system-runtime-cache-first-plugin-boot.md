# ADR 0002 - System Runtime Uses Cache-First Plugin Boot

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
System boot must stay resilient before full plugin sync/migration and must avoid runtime drift in long-running PHP processes.

---

## Decision
Boot enabled plugins from `bootstrap/cache/tp_plugins.php` first, with DB writes performed by explicit lifecycle commands.

- `PluginManager` registers providers only from cache at boot.
- `tp:plugins` actions (`sync`, `enable`, `disable`, `defaults`, `cache`) rebuild cache deliberately.
- Theme runtime state is cached in `bootstrap/cache/tp_theme.php`.
- Runtime cache refresh hooks are used for OPCache-backed environments.

---

## Options considered
### Option A
- Pros:
  - Deterministic boot order and startup behavior.
  - Avoids DB dependency during early app boot.
  - Works safely before first migration in some environments.
- Cons:
  - Requires command discipline to keep cache fresh.

### Option B
- Pros:
  - Read directly from DB on every request.
- Cons:
  - Higher boot coupling to DB availability.
  - More runtime overhead and potential drift with generated artifacts.

### Option C (optional)
- Pros:
  - Composer-only provider registration is simple.
- Cons:
  - Cannot represent dynamic enable/disable plugin state.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Runtime provider loading is stable and explicit.
  - Lifecycle commands become the clear authority for state transitions.
- Negative:
  - Stale cache risk exists if operators bypass command workflows.
- Trade-offs:
  - Operational explicitness is preferred over automatic implicit synchronization.

---

## Notes
Implemented primarily in:
- `packages/tentapress/system/src/Plugin/PluginManager.php`
- `packages/tentapress/system/src/Plugin/PluginRegistry.php`
- `packages/tentapress/system/src/Theme/ThemeManager.php`
- `packages/tentapress/system/src/Support/RuntimeCacheRefresher.php`
