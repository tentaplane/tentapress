# ADR 0006 - Admin Shell Is the Shared Admin UI Platform Plugin

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Core and plugin admin experiences need a consistent layout, navigation, and asset baseline. Duplicating shell concerns across plugins increases drift and maintenance cost.

---

## Decision
Treat `tentapress/admin-shell` as the single shared admin UI platform:

- Own the dashboard shell, navigation construction, and common admin partials.
- Ship common admin assets from plugin-local build artifacts.
- Provide shared CSS utility conventions (`tp-*`) and JavaScript behaviors used by other plugins.

---

## Options considered
### Option A
- Pros:
  - Centralized admin UX and lower duplication.
  - Plugin teams focus on domain screens instead of shell infrastructure.
- Cons:
  - Admin-shell changes can affect many plugins.

### Option B
- Pros:
  - Each plugin can fully control its own admin shell.
- Cons:
  - Inconsistent UX and repeated implementation effort.

### Option C (optional)
- Pros:
  - Put shell in system package only.
- Cons:
  - Less modular release cadence than plugin-based shell ownership.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Unified admin look-and-feel and predictable navigation behavior.
- Negative:
  - Admin-shell plugin is a high-impact dependency for admin UX.
- Trade-offs:
  - Platform centralization over plugin-level UI autonomy.

---

## Notes
Evidence:
- `plugins/tentapress/admin-shell/README.md`
- `plugins/tentapress/admin-shell/tentapress.json`
- `plugins/tentapress/admin-shell/src/AdminShellServiceProvider.php`
