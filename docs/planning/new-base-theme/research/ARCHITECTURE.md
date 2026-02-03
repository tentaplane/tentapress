# Architecture Research

**Domain:** CMS theme + block library visual system integration (TentaPress)
**Researched:** 2026-02-02
**Confidence:** MEDIUM (based on TentaPress codebase; no external sources)

## Standard Architecture

### System Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          Presentation Layer                             │
├─────────────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌─────────────────┐  ┌────────────────────────────┐  │
│  │ Theme Layout │  │ Theme Components│  │ Theme Block Overrides      │  │
│  │ (layouts/*)  │  │ (views/components) │ (views/blocks/*)           │  │
│  └──────┬───────┘  └────────┬────────┘  └─────────────┬──────────────┘  │
│         │                   │                         │                 │
├─────────┴───────────────────┴─────────────────────────┴─────────────────┤
│                          Rendering Layer                                │
├─────────────────────────────────────────────────────────────────────────┤
│   BlockRenderer → Variant Resolver → View Namespace (tp-theme:: / fallback) │
├─────────────────────────────────────────────────────────────────────────┤
│                          Content & Registry                             │
├─────────────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌────────────────┐  ┌─────────────────────────────┐ │
│  │ Page Content │  │ Block Registry │  │ Block Definitions (JSON)     │ │
│  │ (blocks[])   │  │ (type → def)   │  │ fields/variants/examples     │ │
│  └──────────────┘  └────────────────┘  └─────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

| Component | Responsibility | Typical Implementation |
|-----------|----------------|------------------------|
| Theme layout | Page frame, header/footer, asset includes | Blade layouts in `themes/*/views/layouts/*.blade.php` |
| Theme tokens | Palette, typography, spacing scales | Tailwind v4 `@theme` in `resources/css/theme.css` |
| Theme components | Shared UI parts (menus, buttons) | Blade components in `views/components` |
| Block overrides | Visual markup for blocks under the theme | Blade views in `themes/*/views/blocks/*` |
| Block definitions | Schema, defaults, variants, examples | JSON in `plugins/tentapress/blocks/resources/definitions/*.json` |
| Block renderer | Resolve view + variant + theme override | `plugins/tentapress/blocks/src/Render/BlockRenderer.php` |
| Page content | Ordered list of blocks with props | Stored as block array in page/post content |

## Recommended Project Structure

```
themes/
└── tentapress/
    └── tailwind/
        ├── resources/
        │   ├── css/
        │   │   └── theme.css        # Tailwind v4 @theme tokens + utilities
        │   └── js/
        │       └── theme.js         # Optional theme JS (no animations)
        ├── views/
        │   ├── layouts/             # default/landing/post shells
        │   ├── components/          # shared UI fragments (nav, buttons)
        │   └── blocks/              # block visual overrides by type/variant
        └── tentapress.json

plugins/
└── tentapress/
    └── blocks/
        ├── resources/
        │   ├── definitions/         # block JSON schemas + variants
        │   └── views/blocks/         # default block views (fallback)
        └── src/Render/BlockRenderer.php
```

### Structure Rationale

- **themes/tentapress/tailwind/views/blocks:** Theme-level visual changes are isolated here so the block system stays stable and upgrades are safe.
- **plugins/tentapress/blocks/resources/definitions:** Block schema and examples stay centralized; visual work consumes these props without behavior changes.

## Architectural Patterns

### Pattern 1: Theme Override First, Plugin Fallback

**What:** Render block views from the active theme when present; otherwise use plugin defaults.
**When to use:** Any visual overhaul that should not change block behavior or editor schema.
**Trade-offs:** Fast iteration in theme, but requires consistent naming between block type and view path.

**Example:**
```php
// BlockRenderer resolves tp-theme::blocks.hero.default, else tentapress-blocks::blocks.hero.default
```

### Pattern 2: Token-First Styling (Design System at CSS Layer)

**What:** Centralize palette, typography, and spacing in Tailwind v4 `@theme` so all blocks consume shared tokens.
**When to use:** Visual overhaul where the same styles must appear across multiple blocks.
**Trade-offs:** Requires disciplined use of tokens; ad-hoc utilities become harder to audit.

**Example:**
```css
@theme {
  --font-display: "Clash Grotesk";
  --color-brand: #0b3b2e;
  --radius-card: 18px;
}
```

### Pattern 3: Variant-Scoped Views

**What:** Each block variant maps to a dedicated Blade view instead of branching logic inside one file.
**When to use:** Blocks with multiple visual layouts (hero, CTA, features).
**Trade-offs:** More files, but easier to keep variants visually distinct and maintainable.

## Data Flow

### Request Flow

```
Visitor requests page
    ↓
Page model loads blocks[]
    ↓
BlockRenderer resolves type + variant
    ↓
Theme override view (tp-theme::) or plugin fallback
    ↓
Rendered HTML + theme CSS/JS
```

### Key Data Flows

1. **Block rendering:** `blocks[]` → block definition lookup → variant view resolution → Blade render.
2. **Theme styling:** `resources/css/theme.css` tokens → utilities/classes in block views → consistent visual language.
3. **Demo homepage:** Landing layout → ordered block list (story sequence) → block views render with example props.

### Suggested Build Order

1. **Design tokens + typography baseline** in `resources/css/theme.css` (unblocks all block styling).
2. **Layout shells** (`views/layouts/*.blade.php`) to establish page frame and global spacing.
3. **Shared components** (`views/components/*`) used across blocks (buttons, section headers).
4. **Block overrides by priority** (hero, features, CTA, testimonials, logo-cloud, FAQ, stats, etc.).
5. **Story-driven homepage composition** using landing layout + block ordering.
6. **Cross-block consistency pass** (spacing scale, color usage, typography rhythm).

## Scaling Considerations

| Scale | Architecture Adjustments |
|-------|--------------------------|
| 0-1k pages | Theme-only overrides are sufficient; keep views flat. |
| 1k-10k pages | Introduce partials/components for repeated patterns (cards, buttons). |
| 10k+ pages | Add linting/audits for token usage and block view consistency. |

### Scaling Priorities

1. **First bottleneck:** Visual drift between blocks → fix with shared components and strict token usage.
2. **Second bottleneck:** Variant sprawl → document variant intent and prune unused variants.

## Anti-Patterns

### Anti-Pattern 1: Editing Plugin Block Views Directly

**What people do:** Change `plugins/tentapress/blocks/resources/views/blocks/*` for theme visuals.
**Why it's wrong:** Couples visual changes to core plugin updates and makes upgrades risky.
**Do this instead:** Override in `themes/tentapress/tailwind/views/blocks/*`.

### Anti-Pattern 2: Hardcoding Demo Content Inside Views

**What people do:** Bake copy/images into Blade templates for the demo homepage.
**Why it's wrong:** Breaks editor-driven content and blocks reuse.
**Do this instead:** Use block props/defaults and page content to drive demo narrative.

### Anti-Pattern 3: Mixing Layout Concerns Inside Block Views

**What people do:** Add global header/section spacing inside individual blocks.
**Why it's wrong:** Causes inconsistent spacing and makes blocks less portable.
**Do this instead:** Keep layout spacing in layouts/sections, blocks focus on internal composition.

## Integration Points

### External Services

| Service | Integration Pattern | Notes |
|---------|---------------------|-------|
| None | N/A | Visual overhaul is theme + block views only. |

### Internal Boundaries

| Boundary | Communication | Notes |
|----------|---------------|-------|
| Theme ↔ Block renderer | View namespace (`tp-theme::`) | Theme overrides chosen before plugin fallback. |
| Block definitions ↔ Views | Props + variants | JSON schema drives editor and block props. |
| Layouts ↔ Blocks | Slot/section composition | Layouts wrap block output without modifying block data. |

## Sources

- `plugins/tentapress/blocks/src/Render/BlockRenderer.php`
- `plugins/tentapress/blocks/resources/definitions/hero.json`
- `plugins/tentapress/blocks/README.md`
- `themes/tentapress/tailwind/README.md`

---
*Architecture research for: CMS theme + block library visual integration*
*Researched: 2026-02-02*
