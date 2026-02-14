# ADR 0014 - Pages Plugin Owns Page Lifecycle and Site Page Routing

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Pages represent static and marketing content with slug-based routing and layout control. This lifecycle differs from blog posts and should not be overloaded into post workflows.

---

## Decision
Keep page CRUD, publish state, and page route handling in `tentapress/pages`.

- Own `tp_pages` data model and page admin routes.
- Own public page rendering route pipeline.
- Integrate shared services (blocks/media/SEO/theme layout) without transferring page domain ownership.

---

## Options considered
### Option A
- Pros:
  - Clear page-specific workflow ownership.
  - Stable routing semantics for site pages.
- Cons:
  - Requires coordination with posts for shared editor concerns.

### Option B
- Pros:
  - Merge into a generic content plugin.
- Cons:
  - Blurs domain behavior and complicates routing rules.

### Option C (optional)
- Pros:
  - Theme-only page management keeps plugins smaller.
- Cons:
  - Weak consistency and poorer admin governance.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Page workflow and routing stay explicit and maintainable.
- Negative:
  - Cross-content enhancements require explicit integration contracts.
- Trade-offs:
  - Domain clarity over maximal plugin consolidation.

---

## Notes
Evidence:
- `plugins/tentapress/pages/README.md`
- `plugins/tentapress/pages/routes/admin.php`
- `plugins/tentapress/pages/routes/web.php`
