# ADR 0019 - Builder Plugin Owns Visual Canvas and Live Preview

- Status: accepted
- Date: 2026-02-23
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/planning/builder-ux/PROJECT.md
- Supersedes:
- Superseded by:

---

## Context
Pages and posts already support structured block payloads and an optional page editor experience. Teams now need a fully
visual drag-and-drop editing flow with live theme rendering, but without coupling that complexity into core blocks or
forcing it on all installations.

---

## Decision
Adopt `tentapress/builder` as an optional plugin that owns visual-canvas authoring and live preview concerns while
preserving existing payload and rendering contracts:

- Add a shared editor driver registry in `tentapress/system` for pluggable driver resolution.
- Keep `blocks` JSON as canonical payload output for the builder driver.
- Keep `page` editor document storage in `content` unchanged.
- Add short-lived, user-scoped preview snapshots for unsaved visual edits.
- Reserve `props.presentation` for builder-level presentation metadata under a strict whitelist.

---

## Options considered
### Option A
- Pros:
  - Keeps `tentapress/blocks` focused on schema + rendering primitives.
  - Preserves existing pages/posts and page-editor compatibility.
  - Allows immediate GA for teams that enable the builder plugin.
- Cons:
  - Adds cross-plugin coordination through driver registry contracts.

### Option B
- Pros:
  - Embedding visual builder logic directly into pages/posts would reduce plugin boundaries.
- Cons:
  - Forces visual builder complexity on all installs.
  - Blurs ownership between core content lifecycle and advanced authoring UX.

### Option C (optional)
- Pros:
  - Replacing blocks payloads with a new builder-native format could simplify client state.
- Cons:
  - Breaks existing rendering contracts and migration-free compatibility requirements.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Pages/posts can resolve `blocks`, `page`, and `builder` drivers consistently.
  - Live preview renders unsaved edits through the existing theme pipeline.
  - Existing payloads remain valid and deterministic after normalization.
- Negative:
  - Additional test and release overhead across multiple plugins/packages.
- Trade-offs:
  - More architecture seams in exchange for optionality and backward compatibility.

---

## Notes
Evidence:
- `packages/tentapress/system/src/Editor/EditorDriverRegistry.php`
- `plugins/tentapress/builder/src/BuilderServiceProvider.php`
- `plugins/tentapress/builder/src/Http/Admin/BuilderSnapshotController.php`
- `plugins/tentapress/builder/src/Http/Admin/BuilderPreviewController.php`
- `plugins/tentapress/pages/src/Http/Admin/StoreController.php`
- `plugins/tentapress/posts/src/Http/Admin/StoreController.php`
- `plugins/tentapress/blocks/src/Render/BlockRenderer.php`
