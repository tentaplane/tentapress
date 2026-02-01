# Improvements Priority (Build-Oriented)

Ordered by impact on product cohesion and build dependencies. Each item includes a short scope, dependencies, and a
first milestone.

1) Rework blocks interface (Notion/Gutenberg‑style, full‑screen editing)

- Goal: Make authoring feel modern, fast, and consistent across pages/posts.
- Scope: full‑screen editor shell, block list + inline editing, drag/drop, block insert, block settings sidebar,
  autosave, undo/redo.
- Dependencies: none (this drives everything else).
- Risks: large UX surface area; needs clear data model to avoid rewrites.
- Milestone: clickable editor shell + block list + basic text/image blocks saved to existing block storage.
- Suggested approach:
  - Phase 0 (align data model): keep current JSON block schema; define a small UI contract for blocks (label, icon,
    editable fields) and reuse existing block definitions.
  - Phase 1 (shell + list): build a full‑screen editor route that wraps the current editor, then replace it with a
    list view that renders blocks as cards (no inline editing yet).
  - Phase 2 (inline editing): add block‑specific forms in a right sidebar; keep block content editing within the list
    (Notion‑style focus + drag handle).
  - Phase 3 (interactions): keyboard shortcuts, drag/drop reorder, quick insert, block duplication, and undo/redo.
  - Phase 4 (polish): autosave, optimistic updates, and a compact “page outline” view.
- Progress log:
  - 2026-02-01: Locked naming conventions for actions/links. Standardized blocks to `actions[]` and `link` schema.
  - 2026-02-01: Fixed select fields to reflect stored values in the editor form.
  - 2026-02-01: Added full‑screen editor route and layout toggle for pages.
  - 2026-02-01: Styled full‑screen blocks list with card‑style rows and a cleaner editor toolbar.
  - Next: Build a Notion‑style inline block inserter and add a right‑side settings panel.

2) Build a snazzy Tailwind base theme showcasing TentaPress

- Goal: Reference theme that demonstrates blocks, layouts, and admin flow.
- Scope: landing layout, post layout, default layout, block styling tokens, consistent typography + spacing scale.
- Dependencies: block data model from #1.
- Risks: theme can drift if blocks change; keep it aligned with editor output.
- Status: In progress — new Tailwind default layout + bold SaaS block overrides landed, with demo page seeding.
- Next: polish remaining layouts (landing/post), tighten typography scale, and review block coverage.
- Milestone: public demo pages that use new blocks and look production‑ready.

3) Improve SEO offerings to include analytics JS tags

- Goal: allow operators to add analytics snippets without theme edits.
- Scope: UI to store JS tags (head/footer), safe rendering in themes, per‑site settings.
- Dependencies: theme layout hooks (head/footer).
- Risks: XSS risk; must store + output safely and document constraints.
- Milestone: settings UI + render hook for head/footer tags.

4) Build a skeleton plugin generator

- Goal: one command to scaffold a plugin with best‑practice structure.
- Scope: composer.json + tentapress.json + service provider + routes + views + assets stub.
- Dependencies: conventions from current plugins; align with #1 and #3 patterns.
- Risks: generator gets stale; keep templates minimal and updateable.
- Milestone: `tp:plugin make` that outputs a minimal working plugin.

5) Improve documentation of console commands

- Goal: single canonical reference of user‑facing CLI commands.
- Scope: list, descriptions, required args, examples, common flows (setup, themes, plugins).
- Dependencies: commands stabilized after #4.
- Risks: docs drift; include a “last updated” note or generate from command list later.
- Milestone: doc page in /docs with all core commands and examples.

6) Consider how to build a forms builder

- Goal: first‑class forms with storage + integrations.
- Scope: basic form block, submissions table, email notifications, spam protection.
- Dependencies: block editor patterns from #1; theme rendering from #2.
- Risks: product scope creep; keep MVP tight.
- Milestone: simple form block that saves submissions and emails admins.
