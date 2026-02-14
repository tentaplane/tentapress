# ADR 0005 - System Uses a Single Active Theme Activation Contract

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Theme activation affects view lookup, optional theme service providers, and admin theme metadata. A stable active-theme contract is required for consistent rendering.

---

## Decision
Adopt a single-active-theme model:

- Active theme ID is stored in `tp_settings` under `active_theme`.
- Active theme runtime data is materialized into `bootstrap/cache/tp_theme.php`.
- Theme views are registered under namespace `tp-theme`.
- Optional theme provider registration is supported from manifest metadata.

---

## Options considered
### Option A
- Pros:
  - Deterministic theme behavior and clear fallback logic.
  - Simple admin activation semantics.
- Cons:
  - No multi-theme runtime for a single site instance.

### Option B
- Pros:
  - DB-only active theme lookups simplify artifact management.
- Cons:
  - More runtime coupling and less deterministic boot in constrained environments.

### Option C (optional)
- Pros:
  - Multi-active or conditional theme routing is flexible.
- Cons:
  - Substantially more complexity than current product needs.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Theme activation, cache, and rendering behavior stay coherent.
  - Theme metadata (layouts/menu locations/provider) has a consistent source.
- Negative:
  - Activation flow must keep settings and cache in sync.
- Trade-offs:
  - Operational simplicity over advanced multi-theme orchestration.

---

## Notes
Implemented primarily in:
- `packages/tentapress/system/src/Theme/ThemeRegistry.php`
- `packages/tentapress/system/src/Theme/ThemeManager.php`
- `packages/tentapress/system/src/Console/ThemesCommand.php`
