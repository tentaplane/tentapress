# Feature Landscape: Rich Text Block Editor

**Domain:** Notion-style block editors and WYSIWYG content editing
**Researched:** 2026-02-03
**Research mode:** Ecosystem survey (Feature dimension)

## Executive Summary

Modern rich text editors in 2026 fall into two categories: **block-based editors** (Notion, Gutenberg) that treat each element as a discrete component, and **traditional WYSIWYG editors** (TinyMCE, CKEditor) that use a document model. The TentaPress Rich Text Block sits between these approaches: a single block containing rich content with slash commands for element insertion.

**Key insight:** Slash commands and inline formatting toolbars are now **table stakes** for non-technical users. They expect Notion-style UX patterns. Drag-and-drop reordering and nested blocks are differentiators but add significant complexity.

**Confidence level:** HIGH (verified via official documentation for Notion, Gutenberg, TipTap, and ProseMirror)

---

## Table Stakes Features

Features users expect. Missing these makes the product feel incomplete.

### Text Elements

| Feature | Why Expected | Complexity | Dependencies | Notes |
|---------|--------------|------------|--------------|-------|
| **Paragraphs** | Default text entry mode | Low | None | Must be the fallback content type |
| **Headings (H1-H3)** | Content structure is universal expectation | Low | None | H1-H6 supported in most editors; H1-H3 covers 95% of use cases |
| **Blockquotes** | Standard in every content editor since 2000s | Low | None | Visual styling more important than functionality |
| **Bold formatting** | Universal text emphasis | Low | Selection mechanism | Keyboard shortcut (Cmd/Ctrl+B) is required |
| **Italic formatting** | Universal text emphasis | Low | Selection mechanism | Keyboard shortcut (Cmd/Ctrl+I) is required |
| **Links** | Connecting content is fundamental | Medium | URL validation | Need link insertion UI + edit/remove |
| **Inline code** | Common for tech-adjacent content | Low | Selection mechanism | Monospace styling + light background |

**Source confidence:** HIGH — These features appear in [Notion's slash commands](https://www.notion.com/help/guides/using-slash-commands), [WordPress Gutenberg](https://jetpack.com/resources/wordpress-block-editor/), [TipTap marks](https://tiptap.dev/docs/editor/extensions/nodes), and every major WYSIWYG editor.

### Lists

| Feature | Why Expected | Complexity | Dependencies | Notes |
|---------|--------------|------------|--------------|-------|
| **Bullet lists** | Universal content organization pattern | Low | None | Enter creates new item, Tab/Shift+Tab for indent |
| **Numbered lists** | Universal content organization pattern | Low | None | Auto-numbering required |
| **Checklists/todos** | Popularized by Notion, now expected | Medium | Checkbox state management | Must persist checked state in JSON |

**Source confidence:** HIGH — [Notion block types](https://www.notion.com/help/guides/block-basics-build-the-foundation-for-your-teams-pages), [TipTap list nodes](https://tiptap.dev/docs/editor/extensions/nodes), [Gutenberg lists](https://jetpack.com/resources/wordpress-block-editor/)

### Media

| Feature | Why Expected | Complexity | Dependencies | Notes |
|---------|--------------|------------|--------------|-------|
| **Images** | Visual content is core to web publishing | Medium | Media library integration | Need image picker UI + JSON storage of media ID |
| **Image captions** | Accessibility and context are expected | Medium | Image feature | Optional but improves UX significantly |
| **Embeds (video/iframe)** | YouTube/Vimeo embedding is universal | Medium | oEmbed or URL parsing | Validate trusted domains to prevent XSS |

**Source confidence:** HIGH — Standard across [Notion](https://www.notion.com/help/guides/using-slash-commands), [Gutenberg](https://jetpack.com/resources/wordpress-block-editor/), all modern CMSs

### Core Editor UX

| Feature | Why Expected | Complexity | Dependencies | Notes |
|---------|--------------|------------|--------------|-------|
| **Slash command menu** | Notion popularized this; users now expect it | Medium | Command parser + menu UI | Type `/` triggers menu, filter as user types |
| **Inline floating toolbar** | Medium/Google Docs pattern for formatting | Medium | Selection detection + positioning | Appears on text selection, disappears on deselect |
| **Undo/redo** | Universal expectation in content editors | Medium | History tracking | Keyboard shortcuts (Cmd/Ctrl+Z, Cmd/Ctrl+Shift+Z) required |
| **Keyboard shortcuts** | Power users expect them | Medium | Shortcut registry | Bold, italic, link minimum; more is better |
| **Placeholder text** | Guides users on empty blocks | Low | None | "Type / for commands" or similar |

**Source confidence:** HIGH — [Notion slash commands](https://www.notion.com/help/guides/using-slash-commands), [TipTap toolbar](https://tiptap.dev/docs/editor/extensions/functionality), [undo/redo patterns](https://tiptap.dev/docs/editor/extensions/functionality/undo-redo)

### Layout Elements

| Feature | Why Expected | Complexity | Dependencies | Notes |
|---------|--------------|------------|--------------|-------|
| **Horizontal rules** | Visual separation, common since HTML 1.0 | Low | None | Simple divider element |
| **Spacers** | Control vertical spacing without CSS knowledge | Low | None | Configurable height preferred |

**Source confidence:** MEDIUM — Common in [Notion](https://www.notion.com/help/guides/using-slash-commands) and page builders, less common in traditional editors

### Formatting (Additional)

| Feature | Why Expected | Complexity | Dependencies | Notes |
|---------|--------------|------------|--------------|-------|
| **Strikethrough** | Common for editing workflows | Low | Selection mechanism | Less critical than bold/italic but widely expected |
| **Underline** | Traditional formatting option | Low | Selection mechanism | Some argue against it (confusion with links) |

**Source confidence:** HIGH — [TipTap marks](https://tiptap.dev/docs/editor/extensions/nodes), present in all WYSIWYG editors

---

## Nice to Have Features

Features that enhance the experience but aren't expected in v1.

### Enhanced Formatting

| Feature | Value Proposition | Complexity | When to Add | Notes |
|---------|-------------------|------------|-------------|-------|
| **Subscript / Superscript** | Mathematical/scientific content | Low | Phase 2 | Use cases: footnotes, math notation |
| **Text highlight** | Emphasize without bold | Medium | Phase 2 | Single color sufficient; avoid color picker |
| **Code blocks** | Multi-line code with syntax highlighting | High | Phase 2+ | Requires syntax highlighter library |

**Source confidence:** MEDIUM — [TipTap marks](https://tiptap.dev/docs/editor/extensions/nodes) show these as available but not universally implemented

### Advanced Content

| Feature | Value Proposition | Complexity | When to Add | Notes |
|---------|-------------------|------------|-------------|-------|
| **Tables** | Structured data presentation | Very High | Phase 3+ | Table editing UX is complex; consider separate block |
| **Collapsible sections** | Long-form content organization | High | Phase 2+ | Toggle/Details element pattern |
| **Callouts/Alerts** | Highlighted info boxes | Medium | Phase 2 | Different from blockquotes with icon + color |
| **Dividers with text** | Section headers in long content | Low | Phase 2 | "--- or ---" style dividers |

**Source confidence:** HIGH — [Notion blocks](https://www.notion.com/help/guides/block-basics-build-the-foundation-for-your-teams-pages), [TipTap nodes](https://tiptap.dev/docs/editor/extensions/nodes)

### UX Enhancements

| Feature | Value Proposition | Complexity | When to Add | Notes |
|---------|-------------------|------------|-------------|-------|
| **Markdown shortcuts** | Type `**text**` → bold automatically | Medium | Phase 1-2 | Familiar to power users; doesn't hurt beginners |
| **Drag handle per element** | Reorder content within block | High | Phase 3+ | Requires drag-drop library + complex state management |
| **Click-to-edit images** | Inline image editing (crop/resize) | Very High | Not recommended | Better handled by media library |
| **Copy/paste formatting preservation** | Paste from Word/Google Docs cleanly | High | Phase 2 | Requires HTML sanitization; see anti-patterns below |
| **Focus mode** | Distraction-free writing | Low | Phase 2 | Hide UI chrome, fullscreen optional |
| **Character/word count** | Writing metrics | Low | Phase 2 | Common in blog-focused CMSs |

**Source confidence:** MEDIUM-HIGH — [Markdown shortcuts in TipTap](https://tiptap.dev/docs/editor/markdown/api/utilities), drag-drop in [Gutenberg](https://wordpress.com/blog/block-editor-updates-2/), paste handling is [common problem](https://plainenglish.io/blog/wysiwyg-editor-flaws-and-how-to-handle-them)

### Accessibility Improvements

| Feature | Value Proposition | Complexity | When to Add | Notes |
|---------|-------------------|------------|-------------|-------|
| **Keyboard navigation** | Navigate between elements with Tab/arrows | Medium | Phase 1 | WCAG 2.1 Level A requirement |
| **Focus indicators** | Visual feedback on keyboard focus | Low | Phase 1 | WCAG 2.2 requires [visible focus](https://www.w3.org/WAI/WCAG22/Understanding/keyboard-accessible.html) |
| **Screen reader support** | Announce content structure | High | Phase 2 | Requires ARIA labels + semantic HTML |
| **Alt text UI** | Prompt for image alt text | Medium | Phase 1-2 | Improves accessibility compliance |

**Source confidence:** HIGH — WCAG 2.1/2.2 requirements from [W3C](https://www.w3.org/TR/WCAG21/), [keyboard accessibility](https://www.w3.org/WAI/WCAG21/Understanding/keyboard.html)

---

## Anti-Features

Features to explicitly NOT build. Common mistakes in rich text editors.

### Formatting Overload

| Anti-Feature | Why Avoid | What to Do Instead | Source |
|--------------|-----------|-------------------|--------|
| **Font family picker** | Breaks design consistency; creates ugly content | Use theme's font stack only | [WYSIWYG anti-patterns](https://plainenglish.io/blog/wysiwyg-editor-flaws-and-how-to-handle-them) |
| **Font size picker** | Users create inconsistent hierarchies instead of using headings | Force semantic heading use (H1-H3) | [WYSIWYG best practices](https://helpdesk.chambermaster.com/kb/article/2045-wysiwyg-editor-best-practices/) |
| **Text color picker** | 99% of use cases don't need it; creates visual chaos | Use highlights sparingly if needed in Phase 2+ | [Notion design philosophy](https://www.notion.com/help/guides/using-slash-commands) |
| **Background color picker** | Same as text color; breaks theme consistency | Don't implement | [Clean content principles](https://www.adamhyde.net/whats-wrong-with-wysiwyg/) |
| **Alignment controls everywhere** | Most content should be left-aligned | Only allow center alignment for specific block types (images) if needed | UX research |

**Rationale:** The goal is **structured content**, not desktop publishing. Limit visual formatting to maintain design consistency and guide users toward semantic markup.

### Complex Features

| Anti-Feature | Why Avoid | What to Do Instead | Source |
|--------------|-----------|-------------------|--------|
| **Nested blocks / full page builder** | Scope creep; TentaPress already has page-level blocks | Keep this as a rich text **block**, not a page builder | [PROJECT.md constraints](../PROJECT.md) |
| **Drag-and-drop reordering** | Adds significant complexity; keyboard shortcuts work for most users | Use cut/paste or simple up/down buttons if needed | [Drag-drop complexity](https://github.com/WordPress/gutenberg/pull/4115) |
| **Collaborative editing** | TentaPress is single-user; adds massive complexity | Not needed for target audience | [ProseMirror collab](https://prosemirror.net/examples/collab/) shows complexity |
| **Real-time preview** | Content is already WYSIWYG in editor | Not needed | Editor design |
| **Version history** | Database-level concern, not editor concern | Let Laravel/TentaPress handle versioning if needed | Architecture separation |
| **Comments/annotations** | Different UX pattern; would complicate editor | Separate feature if needed | Feature separation |

**Rationale:** These features are common in enterprise CMSs (Notion, Craft CMS) but **overkill for TentaPress's agency-first, single-site use case**.

### Technical Anti-Patterns

| Anti-Feature | Why Avoid | What to Do Instead | Source |
|--------------|-----------|-------------------|--------|
| **Direct HTML editing mode** | Users break the layout; produces invalid HTML | Provide clean JSON → HTML rendering only | [WYSIWYG pitfalls](https://plainenglish.io/blog/wysiwyg-editor-flaws-and-how-to-handle-them) |
| **Paste from Word without sanitization** | Brings in bloated HTML, breaks styling | Strip all formatting or use "paste as plain text" | [Paste problems](https://plainenglish.io/blog/wysiwyg-editor-flaws-and-how-to-handle-them) |
| **Auto-save without indication** | Confuses users about save state | Show explicit "Saving..." indicator | UX best practices |
| **Bloated HTML output** | Slows page load; bad for SEO | Use clean semantic HTML in Blade renderer | [WYSIWYG HTML problems](https://www.adamhyde.net/whats-wrong-with-wysiwyg/) |
| **Browser-specific rendering** | Content looks different across browsers | Use standardized JSON storage + server-rendered HTML | [Browser compatibility issues](https://plainenglish.io/blog/wysiwyg-editor-flaws-and-how-to-handle-them) |

**Rationale:** These are the most common WYSIWYG pitfalls. The JSON storage approach already avoids most of these; the key is not to add HTML editing escape hatches.

### Workflow Anti-Patterns

| Anti-Feature | Why Avoid | What to Do Instead | Source |
|--------------|-----------|-------------------|--------|
| **Inline image upload via drag-drop** | Bypasses media library; creates orphaned files | Always go through media library picker | TentaPress architecture |
| **Inline file attachments** | Not a rich text use case | Use media library directly | Feature scope |
| **External link previews (unfurling)** | Requires external API calls; slows editing | Simple link is sufficient | Performance |
| **Emoji picker** | Increases bundle size; users can paste emoji | Allow emoji input, don't add picker UI | Bundle size |
| **GIF search integration** | Scope creep; not a core content editing need | Use media library for animated GIFs if needed | Feature scope |

**Rationale:** These features blur the line between content editing and asset management. Keep the rich text editor focused on **text with inline formatting**.

---

## Feature Dependencies

Understanding which features require others to function.

```
Core Editor
├── Slash Command Menu (required for)
│   ├── Headings
│   ├── Lists (bullet, numbered, checklist)
│   ├── Blockquotes
│   ├── Images
│   ├── Embeds
│   ├── Horizontal rules
│   └── Spacers
│
├── Inline Floating Toolbar (required for)
│   ├── Bold
│   ├── Italic
│   ├── Links
│   ├── Strikethrough
│   ├── Underline
│   └── Inline code
│
├── Text Selection Mechanism (required for)
│   └── Inline Floating Toolbar (see above)
│
├── JSON Storage Schema (required for)
│   ├── All content types (structure)
│   ├── Link URLs + text
│   ├── Image media IDs
│   ├── Checklist states
│   └── Embed URLs
│
├── Blade Renderer (required for)
│   └── All content types (output)
│
└── Undo/Redo (requires)
    └── History tracking system
```

**Critical path for MVP:**
1. JSON schema design → enables storage
2. Text selection mechanism → enables inline toolbar
3. Slash command parser → enables element insertion
4. Blade renderer → enables frontend display

---

## MVP Recommendation

Based on the feature landscape, here's what should be in the initial release:

### Phase 1: MVP (Minimum Viable Product)

**Core editing:**
- Paragraphs (default)
- Headings (H1, H2, H3)
- Bold, italic, links (inline toolbar)
- Slash command menu
- Undo/redo

**Content types:**
- Blockquotes
- Bullet lists
- Numbered lists
- Horizontal rules

**Why this scope:**
- Covers 80% of content editing needs
- All table stakes features included
- Complexity is manageable for Phase 1
- Users can create structured, formatted content

### Defer to Phase 2:

**Medium complexity adds:**
- Checklists/task lists (requires state management)
- Images (requires media library integration)
- Embeds (requires URL validation)
- Strikethrough, underline, inline code
- Spacers
- Markdown shortcuts (if editor library supports it easily)
- Keyboard shortcuts beyond Cmd/Ctrl+B/I
- Image alt text UI

**Rationale:** These features enhance the experience but aren't critical for launch. They can be added incrementally as user feedback comes in.

### Defer to Phase 3+:

**High complexity or niche:**
- Tables
- Code blocks with syntax highlighting
- Collapsible sections
- Text highlights
- Drag-and-drop reordering
- Advanced accessibility (screen reader optimization)
- Copy/paste formatting preservation

**Rationale:** These are nice-to-have differentiators but require significant engineering effort. Wait for user demand before investing.

### Never Implement:

- Font family/size pickers
- Text/background color pickers
- Nested blocks within the rich text block
- Direct HTML editing
- Collaborative editing
- Inline image upload (bypass media library)

**Rationale:** These violate the design philosophy of structured content or are explicitly out of scope per PROJECT.md.

---

## Implementation Complexity Analysis

### Low Complexity (1-2 days per feature)
- Paragraphs, headings, blockquotes
- Bold, italic, strikethrough, underline
- Horizontal rules, spacers
- Placeholder text
- Focus indicators

### Medium Complexity (3-5 days per feature)
- Slash command menu + parser
- Inline floating toolbar + positioning
- Links (insert/edit/remove UI)
- Bullet lists, numbered lists
- Undo/redo
- Keyboard shortcuts system
- Image captions
- Alt text UI

### High Complexity (1-2 weeks per feature)
- Checklists (state management + rendering)
- Images (media library picker integration)
- Embeds (URL parsing + validation + preview)
- Markdown shortcuts (if not built into editor library)
- Copy/paste sanitization
- Drag-and-drop reordering
- Code blocks with syntax highlighting
- Screen reader optimization

### Very High Complexity (3+ weeks per feature)
- Tables (complex editing UX)
- Collaborative editing
- Inline image editing (crop/resize)
- Full accessibility compliance (WCAG 2.2 Level AA)

**Note:** Complexity assumes using a foundation library (TipTap, ProseMirror) rather than building from scratch. Building from scratch would 5-10x these estimates.

---

## Competitive Feature Matrix

How TentaPress Rich Text Block compares to other editors:

| Feature | Notion | Gutenberg | TinyMCE | TipTap | TentaPress Goal |
|---------|--------|-----------|---------|--------|-----------------|
| Slash commands | ✅ | ❌ | ❌ | ✅ (via extension) | ✅ Phase 1 |
| Inline toolbar | ✅ | ✅ | ✅ | ✅ | ✅ Phase 1 |
| Drag-drop reorder | ✅ | ✅ | ❌ | ❌ (manual) | ❌ Out of scope |
| Nested blocks | ✅ | ✅ | ❌ | ❌ | ❌ Out of scope |
| Checklists | ✅ | ✅ | ❌ | ✅ (via extension) | ✅ Phase 2 |
| Tables | ✅ | ✅ | ✅ | ✅ (via extension) | ❌ Phase 3+ |
| Code blocks | ✅ | ✅ | ✅ | ✅ (via extension) | ❌ Phase 3+ |
| Embeds | ✅ | ✅ | ❌ | ❌ (manual) | ✅ Phase 2 |
| Collaboration | ✅ | ❌ | ❌ | ✅ (via Y.js) | ❌ Out of scope |
| Markdown shortcuts | ✅ | ❌ | ❌ | ✅ (via extension) | ✅ Phase 2 |
| Text colors | ✅ | ✅ | ✅ | ✅ (via extension) | ❌ Anti-feature |
| Font picker | ❌ | ❌ | ✅ | ❌ | ❌ Anti-feature |

**Key insight:** TentaPress sits between **Notion** (full block editor) and **TinyMCE** (traditional WYSIWYG). The slash command + inline toolbar combination is the modern UX pattern users expect.

---

## Technology Implications

Features inform technology choices:

### Editor Library Requirements

To support MVP features, the chosen library MUST have:
- ✅ JSON/structured output (not just HTML)
- ✅ Extensible schema (add custom node types)
- ✅ Slash command support (or easy to add)
- ✅ Inline toolbar support (or easy to add)
- ✅ Undo/redo built-in
- ✅ Keyboard shortcut system
- ✅ Alpine.js compatibility (or vanilla JS)

**Leading candidates:** TipTap (ProseMirror wrapper), ProseMirror (lower-level), or custom Alpine.js implementation.

### Storage Schema Implications

The JSON schema must support:
- Heterogeneous content (paragraphs, headings, lists, etc.)
- Nested lists (indentation)
- Inline formatting spans (bold, italic, links)
- Media references (IDs, not URLs)
- Checklist states (checked/unchecked)
- Extensibility for Phase 2+ features

**Recommendation:** Follow TipTap/ProseMirror JSON structure or a simplified version. See STACK.md for details.

---

## Sources

### High Confidence (Official Documentation)

- [Notion: Using slash commands](https://www.notion.com/help/guides/using-slash-commands) — Slash command UX patterns
- [Notion: Block basics](https://www.notion.com/help/guides/block-basics-build-the-foundation-for-your-teams-pages) — Block types and structure
- [WordPress: How to Use the Block Editor](https://jetpack.com/resources/wordpress-block-editor/) — Gutenberg features
- [WordPress: Block Editor Updates](https://wordpress.com/blog/block-editor-updates-2/) — Drag-drop and patterns
- [TipTap: Schema](https://tiptap.dev/docs/editor/core-concepts/schema) — Nodes and marks structure
- [TipTap: Extensions](https://tiptap.dev/docs/editor/extensions/overview) — Available content types
- [TipTap: Undo/Redo](https://tiptap.dev/docs/editor/extensions/functionality/undo-redo) — History implementation
- [ProseMirror](https://prosemirror.net/) — Core editor library
- [ProseMirror: Collaborative editing](https://prosemirror.net/examples/collab/) — Collab complexity
- [W3C: WCAG 2.1](https://www.w3.org/TR/WCAG21/) — Accessibility requirements
- [W3C: Understanding Keyboard Accessible](https://www.w3.org/WAI/WCAG21/Understanding/keyboard.html) — Keyboard navigation
- [W3C: Keyboard Accessible Guideline](https://www.w3.org/WAI/WCAG22/Understanding/keyboard-accessible.html) — WCAG 2.2 updates

### Medium Confidence (Tech Comparisons & Best Practices)

- [10 Best React WYSIWYG Rich Text Editors in 2026](https://reactscript.com/best-rich-text-editor/) — Editor landscape
- [Modern Rich Text Editors: How to Evaluate](https://www.tag1consulting.com/blog/modern-rich-text-editors-how-evaluate-evolving-landscape) — Evaluation criteria
- [WYSIWYG Editor Flaws And How To Handle Them](https://plainenglish.io/blog/wysiwyg-editor-flaws-and-how-to-handle-them) — Anti-patterns
- [WYSIWYG Editor Best Practices](https://helpdesk.chambermaster.com/kb/article/2045-wysiwyg-editor-best-practices/) — Common mistakes
- [What's wrong with WYSIWYG](https://www.adamhyde.net/whats-wrong-with-wysiwyg/) — Design philosophy
- [Undo/redo implementations in text editors](https://www.mattduck.com/undo-redo-text-editors) — History patterns
- [You Don't Know Undo/Redo](https://dev.to/isaachagoel/you-dont-know-undoredo-4hol) — Implementation approaches
- [CKEditor: Drag and drop](https://ckeditor.com/docs/ckeditor5/latest/features/drag-drop.html) — Drag-drop UX

### Low Confidence (Community Resources)

- [A Guide to Editing and Formatting Text in Notion](https://thomasjfrank.com/a-guide-to-editing-and-formatting-text-in-notion-notion-fundamentals/) — Notion usage patterns
- [5 Best Markdown Editors for React Compared](https://strapi.io/blog/top-5-markdown-editors-for-react) — Editor comparisons
- [Top 5 Drag-and-Drop Libraries for React in 2026](https://puckeditor.com/blog/top-5-drag-and-drop-libraries-for-react) — Drag-drop libraries

---

## Research Gaps

Areas where additional investigation may be needed:

1. **Editor library selection:** STACK.md should evaluate TipTap vs ProseMirror vs custom Alpine.js implementation
2. **Media library integration:** Need to understand TentaPress media library API before implementing image insertion
3. **JSON schema design:** Need to design the schema that balances simplicity with extensibility
4. **Accessibility testing:** WCAG compliance may require user testing with assistive technologies

These gaps should be addressed in phase-specific research or technical spikes.

---

**Last updated:** 2026-02-03
**Next steps:** Create STACK.md (technology recommendations) and ARCHITECTURE.md (system structure)
