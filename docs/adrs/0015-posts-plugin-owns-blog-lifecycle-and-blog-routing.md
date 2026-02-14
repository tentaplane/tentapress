# ADR 0015 - Posts Plugin Owns Blog Lifecycle and Blog Routing

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Blog content needs a dedicated index/detail route space and post-specific publishing semantics that differ from static pages.

---

## Decision
Keep blog lifecycle and public blog routes in `tentapress/posts`.

- Own `tp_posts` model and post admin flows.
- Own `/blog` and `/blog/{slug}` public route domain.
- Reuse shared services (blocks/media/SEO/users) while preserving post domain ownership.

---

## Options considered
### Option A
- Pros:
  - Clear blog feature ownership and extensibility.
  - Predictable public route boundaries.
- Cons:
  - Requires coordination with pages for shared editor behavior.

### Option B
- Pros:
  - Fold posts into pages plugin for fewer packages.
- Cons:
  - Weaker semantics for blog workflows and discovery.

### Option C (optional)
- Pros:
  - External blog engine integration only.
- Cons:
  - Less cohesive first-party editing experience.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Post lifecycle and blog delivery remain explicit and testable.
- Negative:
  - Extra integration points across plugins are required.
- Trade-offs:
  - Domain separation over reduced package count.

---

## Notes
Evidence:
- `plugins/tentapress/posts/README.md`
- `plugins/tentapress/posts/routes/admin.php`
- `plugins/tentapress/posts/routes/web.php`
