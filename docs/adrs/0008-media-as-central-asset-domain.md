# ADR 0008 - Media Plugin Is the Central Asset Domain

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Multiple features require image/file assets (pages, posts, blocks, SEO). Separate media handling per plugin causes duplication and inconsistent metadata/attribution handling.

---

## Decision
Use `tentapress/media` as the central asset domain:

- Own upload, metadata, attribution, variant generation, and media selection flows.
- Provide integration contract for other plugins through admin routes and media reference resolution bindings.
- Support extension plugins for stock sources and optimization providers.

---

## Options considered
### Option A
- Pros:
  - Single source of truth for media records and metadata.
  - Reusable integrations across content and SEO plugins.
- Cons:
  - Broad dependency surface and higher plugin criticality.

### Option B
- Pros:
  - Plugin-local media storage offers autonomy.
- Cons:
  - Duplicate storage/indexing models and poorer UX consistency.

### Option C (optional)
- Pros:
  - External DAM-only integration reduces internal complexity.
- Cons:
  - Harder local-first experience and weaker default portability.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Consistent media UX and data model across the platform.
  - Enables specialized provider plugins without changing consuming plugins.
- Negative:
  - Media availability becomes important for many editor paths.
- Trade-offs:
  - Centralized asset domain over distributed plugin-specific media handling.

---

## Notes
Evidence:
- `plugins/tentapress/media/README.md`
- `plugins/tentapress/media/tentapress.json`
- `plugins/tentapress/media/src/MediaServiceProvider.php`
