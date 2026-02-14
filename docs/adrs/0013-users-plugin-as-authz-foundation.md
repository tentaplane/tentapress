# ADR 0013 - Users Plugin Is the Authentication and Authorization Foundation

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Most admin capabilities depend on user identity, roles, and permissions. A central auth/authz foundation is required for consistent enforcement.

---

## Decision
Treat `tentapress/users` as foundational auth/authz plugin:

- Own user accounts, roles, capabilities, and admin authentication flows.
- Expose capabilities used by `tp.can:*` middleware checks in other plugins.
- Serve as dependency for plugins that require identity/capability-aware behavior.

---

## Options considered
### Option A
- Pros:
  - Single authority for identity and permission semantics.
  - Predictable capability enforcement across plugins.
- Cons:
  - High criticality; regressions have broad impact.

### Option B
- Pros:
  - Plugin-local permission models offer local autonomy.
- Cons:
  - Inconsistent role/capability semantics and fragmented auth.

### Option C (optional)
- Pros:
  - Delegate completely to external IAM provider.
- Cons:
  - Increases integration complexity for local-first installs.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Uniform authorization model across admin routes and plugin capabilities.
  - Clear dependency boundary for auth-sensitive plugins.
- Negative:
  - Requires strong backward-compatibility discipline for roles/capabilities.
- Trade-offs:
  - Central governance over distributed permission ownership.

---

## Notes
Evidence:
- `plugins/tentapress/users/README.md`
- `plugins/tentapress/users/tentapress.json`
- `packages/tentapress/system/src/Http/CanMiddleware.php`
