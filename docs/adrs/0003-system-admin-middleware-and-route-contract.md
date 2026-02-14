# ADR 0003 - System Defines a Shared Admin Middleware and Route Contract

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
All admin plugins need consistent security, auth, naming, and URL structure. Without a shared contract, plugin admin routes diverge and security controls become uneven.

---

## Decision
Define a single admin contract in system package:

- Middleware group `tp.admin` with base stack (`web`, security headers, admin auth, admin error handling).
- Route helper `AdminRoutes::group()` that enforces:
  - URL prefix: `/admin`
  - Route name prefix: `tp.`
  - Middleware: `tp.admin`
- Fine-grained capabilities enforced by `tp.can:<capability>` in plugin routes.

---

## Options considered
### Option A
- Pros:
  - Uniform admin behavior across all plugins.
  - Simplifies plugin route authoring and review.
- Cons:
  - Less freedom for plugin-specific routing conventions.

### Option B
- Pros:
  - Plugin-local middleware and prefixes allow full autonomy.
- Cons:
  - Inconsistent security posture and route naming.
  - Higher maintenance burden.

### Option C (optional)
- Pros:
  - Global middleware only, no route helper abstraction.
- Cons:
  - Less explicit and easier to misconfigure per plugin.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Shared admin UX/security assumptions become reliable.
  - Plugin scaffolding and reviews are simpler.
- Negative:
  - Plugins must align with established conventions.
- Trade-offs:
  - Convention-over-configuration for admin surfaces.

---

## Notes
Implemented primarily in:
- `packages/tentapress/system/src/Http/AdminMiddleware.php`
- `packages/tentapress/system/src/Support/AdminRoutes.php`
- `packages/tentapress/system/src/SystemServiceProvider.php`
