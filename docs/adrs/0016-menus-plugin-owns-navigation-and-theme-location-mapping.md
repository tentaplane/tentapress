# ADR 0016 - Menus Plugin Owns Navigation and Theme Location Mapping

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Navigation composition and theme slot assignment are cross-cutting concerns used by multiple content types and themes.

---

## Decision
Use `tentapress/menus` as the dedicated navigation domain:

- Own menu and menu-item persistence.
- Own mapping of menus to theme-defined `menu_locations`.
- Provide admin UX and capabilities for controlled navigation management.

---

## Options considered
### Option A
- Pros:
  - Theme-aware navigation management is centralized.
  - Reusable by any content or theme implementation.
- Cons:
  - Requires integration with settings/pages/posts/themes.

### Option B
- Pros:
  - Theme-local menu configuration can be simple.
- Cons:
  - No central admin model and weaker cross-theme portability.

### Option C (optional)
- Pros:
  - Put menus in pages plugin.
- Cons:
  - Increases coupling between content and navigation domains.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Navigation ownership and permission boundaries are clear.
- Negative:
  - Theme contracts must stay stable for location mapping.
- Trade-offs:
  - Dedicated navigation domain over convenience coupling.

---

## Notes
Evidence:
- `plugins/tentapress/menus/README.md`
- `plugins/tentapress/menus/routes/admin.php`
- `themes/tentapress/tailwind/tentapress.json`
