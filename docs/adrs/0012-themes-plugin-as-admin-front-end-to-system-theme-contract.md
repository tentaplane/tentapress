# ADR 0012 - Themes Plugin Is the Admin Front-End to the System Theme Contract

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Theme discovery/activation behavior is implemented in system package, but users need an admin UX to inspect and activate themes safely.

---

## Decision
Use `tentapress/themes` as the dedicated admin interface for the system theme contract:

- List discovered themes and metadata.
- Trigger sync and activation through established system services.
- Surface theme screenshots/layout metadata for operator decisions.

User and capability enforcement for these routes remain aligned to admin middleware and `manage_themes` capability.

---

## Options considered
### Option A
- Pros:
  - Clear separation between core theme runtime logic and admin presentation.
  - Allows theme management UX evolution without changing system internals.
- Cons:
  - Additional plugin boundary to maintain.

### Option B
- Pros:
  - Put all theme UI directly in system package.
- Cons:
  - Couples core runtime package to admin UI concerns.

### Option C (optional)
- Pros:
  - Rely on CLI-only theme management.
- Cons:
  - Poor admin usability.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Theme operations are visible and accessible in admin.
  - Runtime core remains decoupled from UI implementation details.
- Negative:
  - Requires synchronization between system and themes plugin assumptions.
- Trade-offs:
  - Better modularity over single-package simplification.

---

## Notes
Evidence:
- `plugins/tentapress/themes/README.md`
- `plugins/tentapress/themes/routes/admin.php`
- `plugins/tentapress/themes/src/Http/Admin`
