# ADR 0018 - Settings Plugin Owns Site Configuration Key-Value Store

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Core site configuration values are needed across plugins. A consistent storage and admin editing model is needed to avoid ad hoc settings tables and coupling.

---

## Decision
Use `tentapress/settings` as the central site configuration plugin:

- Own `tp_settings` key-value persistence and admin update flow.
- Provide stable storage boundary consumed by other plugins.
- Keep settings management capability-gated through admin conventions.

---

## Options considered
### Option A
- Pros:
  - Single source of truth for platform/site settings.
  - Avoids duplicated settings implementations.
- Cons:
  - Generic key-value model requires naming discipline.

### Option B
- Pros:
  - Plugin-local settings tables offer local autonomy.
- Cons:
  - Fragmented config model and poor operator ergonomics.

### Option C (optional)
- Pros:
  - Environment-only configuration is simple.
- Cons:
  - Not suitable for user-managed runtime settings.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Predictable settings persistence and admin governance.
- Negative:
  - Shared store misuse can create key collisions without conventions.
- Trade-offs:
  - Centralized settings governance over per-plugin storage autonomy.

---

## Notes
Evidence:
- `plugins/tentapress/settings/README.md`
- `plugins/tentapress/settings/routes/admin.php`
- `plugins/tentapress/settings/src/Http/Admin/UpdateController.php`
