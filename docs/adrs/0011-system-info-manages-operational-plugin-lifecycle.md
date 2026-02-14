# ADR 0011 - System Info Plugin Owns Operational Plugin Lifecycle UI

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Operators need in-admin diagnostics and plugin lifecycle actions (sync/enable/disable/install/update) without direct shell access.

---

## Decision
Use `tentapress/system-info` as the admin operational control plane for diagnostics and plugin lifecycle tasks:

- Provide diagnostics views/downloads for system state.
- Expose admin actions that orchestrate plugin lifecycle operations.
- Use guarded routes and capability checks for operational safety.
- Serialize install/update workflows to avoid overlapping Composer operations.

---

## Options considered
### Option A
- Pros:
  - Operators can perform core maintenance from admin UI.
  - Central place for operational observability and controls.
- Cons:
  - Elevated responsibility and security sensitivity.

### Option B
- Pros:
  - CLI-only operations reduce admin complexity.
- Cons:
  - Less accessible operations for non-shell operators.

### Option C (optional)
- Pros:
  - Split diagnostics and lifecycle operations into different plugins.
- Cons:
  - More fragmentation of operational workflows.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Admin users can inspect and maintain plugin runtime state in one place.
- Negative:
  - Requires careful access control and operational safeguards.
- Trade-offs:
  - Operational convenience over narrower surface area.

---

## Notes
Evidence:
- `plugins/tentapress/system-info/README.md`
- `plugins/tentapress/system-info/routes/admin.php`
- `plugins/tentapress/system-info/src/Http/Admin/Plugins`
