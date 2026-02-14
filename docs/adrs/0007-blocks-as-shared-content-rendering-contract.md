# ADR 0007 - Blocks Plugin Provides Shared Content Rendering Contract

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Pages and posts need a reusable content model and renderer. Separate editor/rendering stacks per content type create fragmentation.

---

## Decision
Use `tentapress/blocks` as the shared content-block registry and rendering contract:

- Define a central block registry and schema/validation model.
- Expose rendering through the `tp.blocks.render` binding.
- Allow extension plugins/themes to register block types while preserving a single runtime renderer.

---

## Options considered
### Option A
- Pros:
  - Shared rendering semantics across content domains.
  - Easier extension model for custom blocks.
- Cons:
  - Core block model changes require careful compatibility management.

### Option B
- Pros:
  - Separate page/post renderers per plugin can evolve independently.
- Cons:
  - Duplicated logic and inconsistent authoring experience.

### Option C (optional)
- Pros:
  - Pure HTML field editor per content type is simpler initially.
- Cons:
  - Limited composability and weaker structured content capabilities.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Consistent block behavior in pages and posts.
  - Clear extension hook for richer block ecosystems.
- Negative:
  - Blocks plugin stability becomes critical to content workflows.
- Trade-offs:
  - Shared abstraction over domain-specific rendering stacks.

---

## Notes
Evidence:
- `plugins/tentapress/blocks/README.md`
- `plugins/tentapress/blocks/src/BlocksServiceProvider.php`
- `plugins/tentapress/pages/src/Services/PageRenderer.php`
- `plugins/tentapress/posts/src/Services/PostRenderer.php`
