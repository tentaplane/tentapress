# ADR 0017 - SEO Plugin Owns Content Metadata Governance

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
SEO metadata is a cross-content concern requiring consistent admin controls without hard-coding SEO logic into each content plugin.

---

## Decision
Use `tentapress/seo` as the dedicated metadata governance plugin:

- Own SEO settings and content-level SEO edit surfaces.
- Integrate with pages/posts editing flows through explicit route/view integrations.
- Keep rendering expectations in themes while SEO plugin governs metadata authoring.

---

## Options considered
### Option A
- Pros:
  - Clear ownership of SEO policy and metadata fields.
  - Reduced duplication across page/post workflows.
- Cons:
  - Requires integration maintenance with consuming plugins and themes.

### Option B
- Pros:
  - Inline SEO fields in pages/posts reduce package count.
- Cons:
  - Metadata governance fragments across domains.

### Option C (optional)
- Pros:
  - Theme-only metadata control avoids plugin integration.
- Cons:
  - Weak editorial governance and inconsistent admin UX.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - SEO behavior is centrally governed and reusable.
- Negative:
  - Tight integration expectations with editor and frontend rendering layers.
- Trade-offs:
  - Central governance over local convenience.

---

## Notes
Evidence:
- `plugins/tentapress/seo/README.md`
- `plugins/tentapress/seo/routes/admin.php`
- `plugins/tentapress/seo/src/SeoServiceProvider.php`
