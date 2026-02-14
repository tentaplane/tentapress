# ADR 0009 - Pages and Posts Own Separate Public Content Route Domains

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
TentaPress needs both static-page and blog-post capabilities with clear URL semantics and manageable routing precedence.

---

## Decision
Keep pages and posts as separate plugins with distinct public route domains:

- Pages plugin owns slug-based page rendering (including home fallback behavior).
- Posts plugin owns blog index/detail under blog-prefixed routes.
- Both consume shared editing/rendering building blocks (blocks/media/SEO) without collapsing domain ownership.

---

## Options considered
### Option A
- Pros:
  - Clear domain boundaries and route ownership.
  - Independent evolution of page and post workflows.
- Cons:
  - Two plugin surfaces to maintain.

### Option B
- Pros:
  - Single content plugin could reduce package count.
- Cons:
  - Blurs UX, model, and route responsibilities.

### Option C (optional)
- Pros:
  - Put posts under generic page model.
- Cons:
  - Weak semantics for blog-specific lifecycle needs.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Routing and domain logic remain explicit.
  - Shared components can be reused without forcing a single content model.
- Negative:
  - Cross-content features must coordinate across two plugins.
- Trade-offs:
  - Domain clarity over maximal consolidation.

---

## Notes
Evidence:
- `plugins/tentapress/pages/routes/web.php`
- `plugins/tentapress/posts/routes/web.php`
- `plugins/tentapress/pages/README.md`
- `plugins/tentapress/posts/README.md`
