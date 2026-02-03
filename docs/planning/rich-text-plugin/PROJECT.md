# Rich Text Block Plugin

## What This Is

A TentaPress plugin that adds a "Rich Text" block type with a Notion-style editing experience. Designed for non-technical users (clients, content editors) who need rich content creation without learning Markdown syntax. Users get slash commands to insert elements and an inline floating toolbar for text formatting.

## Core Value

Non-technical users can create rich, structured content without any syntax knowledge — just type and format.

## Requirements

### Validated

- ✓ TentaPress plugin architecture — existing
- ✓ Block registration via BlockRegistry — existing
- ✓ JSON storage in page/post blocks column — existing
- ✓ Media library for image selection — existing
- ✓ Alpine.js for interactivity — existing
- ✓ Blade rendering for frontend — existing

### Active

- [ ] Slash command menu (type / to insert elements)
- [ ] Inline floating toolbar (select text → format options)
- [ ] Headings (H1, H2, H3) via slash command
- [ ] Paragraphs as default content
- [ ] Blockquotes via slash command
- [ ] Bullet lists via slash command
- [ ] Numbered lists via slash command
- [ ] Checklists/task lists via slash command
- [ ] Images via slash command → media library picker
- [ ] Embeds (video/iframe) via slash command
- [ ] Horizontal rules via slash command
- [ ] Spacers via slash command
- [ ] Bold formatting (inline toolbar + keyboard shortcut)
- [ ] Italic formatting (inline toolbar + keyboard shortcut)
- [ ] Links (inline toolbar)
- [ ] Strikethrough formatting (inline toolbar)
- [ ] Underline formatting (inline toolbar)
- [ ] Inline code formatting (inline toolbar)
- [ ] JSON output format for structured storage
- [ ] Blade renderer to convert JSON → HTML on frontend
- [ ] Block registration in TentaPress BlockRegistry

### Out of Scope

- Nested/draggable blocks within the editor — complexity not needed for simple rich text
- Text colors / highlight colors — keeps the editor clean and focused
- Tables — can be added in v2 if needed
- File attachments — use media library directly
- Collaborative editing — single-user CMS
- Direct image upload in editor — use existing media library workflow

## Context

**Technical Environment:**
- TentaPress CMS with plugin architecture
- Laravel 12, PHP 8.2+
- Alpine.js 3.15 for frontend interactivity
- Tailwind CSS v4 for styling
- Vite for asset bundling
- Blocks stored as JSON in Eloquent models
- BlockRegistry singleton for block type registration
- BlockRenderer for frontend HTML generation

**Existing Block System:**
- Blocks plugin at `plugins/tentapress/blocks/`
- Block types registered via `BlockRegistry::register()`
- Each block type has: type key, schema, admin view, render view
- Admin views in `resources/views/blocks/`
- Theme can override block render views
- Existing Markdown block for developer-friendly editing

**User Context:**
- Non-technical users who find Markdown syntax frustrating
- Need quick content creation without training
- Familiar with Notion/Google Docs style editors

## Constraints

- **Tech stack**: Must use Alpine.js (no React/Vue) — consistent with TentaPress
- **Storage**: JSON format in blocks column — consistent with existing blocks
- **Plugin structure**: Follow TentaPress plugin conventions (tentapress.json manifest, ServiceProvider)
- **Dependencies**: Minimize new JS dependencies — keep bundle size reasonable
- **Media**: Integrate with existing media library — no separate upload flow
- **Rendering**: Server-side Blade rendering — no client-side hydration on frontend

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| JSON storage over HTML | Structured data more portable, easier to render consistently | — Pending |
| No nested blocks | Simpler UX, faster to build, covers 90% of use cases | — Pending |
| Media library integration | Consistent with rest of TentaPress, no duplicate upload logic | — Pending |
| Research editor library | Need to find best Alpine.js-compatible option | — Pending |

---
*Last updated: 2026-02-03 after initialization*
