# PROJECT: TentaPress Theme & Blocks Upgrade

## What This Is

A visual overhaul of TentaPress's default theme and block library to create an impressive, inspirational first
experience that showcases what the system is capable of.

**Core value:** When someone installs TentaPress, they should see something that makes them excited to build with it —
not bare-bones scaffolding they need to escape from.

## The Problem

The current theme (`themes/tentapress/tailwind`) and blocks (`plugins/tentapress/blocks`) are functional but
uninspiring:

- Minimal styling — white cards, subtle borders, gray text
- No brand identity — just Tailwind defaults with a custom font
- Doesn't demonstrate the system's flexibility
- Feels like a starting point you have to fix, not a foundation you can build on

## The Solution

Create a cohesive visual identity for TentaPress and apply it across the theme and all 18 existing blocks:

1. **Brand Identity** — color palette and typography that conveys "modern agency tool"
2. **Theme Upgrade** — Tailwind CSS v4 theme with the new identity
3. **Block Restyling** — all 18 blocks redesigned with bold, modern aesthetics
4. **Demo Page** — one page showcasing all blocks working together

## Design Direction

**Personality:** Notion's warmth and approachability + Framer's boldness and energy

- Friendly but makes a statement
- Professional without being sterile
- Modern, confident, trustworthy

**Visual References:** Dropbox.com, Slack.com

- Bold colors, confident typography
- Generous whitespace
- Subtle depth with shadows and gradients
- Clean without being boring

**Constraints:**

- Gradients and shadows — yes, but not overdone
- No animations
- Simple enough to customize or strip back
- Should impress without overwhelming

## Brand Context

TentaPress is part of the TentaPlane family:

- **TentaPlane** — umbrella organization
- **TentaPress** — open source CMS (this project)
- **TentaHQ, TentaCDN, TentaMail, TentaForms** — future products

The "Tenta" prefix suggests reach, connection, extensibility (tentacles).

**Product positioning:** "The fastest way for agencies to ship and manage small sites"

- Agency owners, developers, project managers
- Landing pages, brochure sites, small content sites
- Speed without chaos, client-safe editing, easy to run

## Technical Scope

**Theme location:** `themes/tentapress/tailwind/`

- `resources/css/theme.css` — Tailwind v4 config
- Theme layouts and partials

**Blocks location:** `plugins/tentapress/blocks/`

- 18 block definitions in `resources/definitions/*.json`
- 18 block templates in `resources/views/blocks/*.blade.php`

**Blocks to restyle:**

1. hero
2. content
3. features
4. cta
5. testimonial
6. logo-cloud
7. stats
8. faq
9. quote
10. gallery
11. image
12. buttons
13. newsletter
14. timeline
15. table
16. divider
17. embed
18. map

**Demo page:** Location TBD — will showcase all blocks in a cohesive layout

## Requirements

### Validated

- ✓ Plugin architecture for blocks — existing
- ✓ Theme architecture with per-theme Vite builds — existing
- ✓ Block variant system — existing
- ✓ 18 block types defined — existing

### Active

- [ ] Brand color palette defined
- [ ] Typography system selected (fonts, scale)
- [ ] Theme CSS updated with brand identity
- [ ] Hero block restyled
- [ ] Content block restyled
- [ ] Features block restyled
- [ ] CTA block restyled
- [ ] Testimonial block restyled
- [ ] Logo-cloud block restyled
- [ ] Stats block restyled
- [ ] FAQ block restyled
- [ ] Quote block restyled
- [ ] Gallery block restyled
- [ ] Image block restyled
- [ ] Buttons block restyled
- [ ] Newsletter block restyled
- [ ] Timeline block restyled
- [ ] Table block restyled
- [ ] Divider block restyled
- [ ] Embed block restyled
- [ ] Map block restyled
- [ ] Demo page created showcasing all blocks

### Out of Scope

- New block types — focus is on existing 18
- Animation/motion design — explicitly excluded
- Multiple theme variants — one polished theme
- Admin UI changes — frontend theme only

## Key Decisions

| Decision                       | Rationale                                          | Outcome                                |
|--------------------------------|----------------------------------------------------|----------------------------------------|
| Notion + Framer personality    | Balances approachability with boldness             | Pending — guides all visual choices    |
| Dropbox/Slack visual reference | Bold, modern SaaS aesthetic without being overdone | Pending — reference for implementation |
| No animations                  | Keep it simple, reduce complexity                  | Confirmed                              |
| Restyle existing blocks only   | Showcase capability without scope creep            | Confirmed                              |

## Success Criteria

1. A new user installing TentaPress sees an impressive, cohesive visual identity
2. The demo page demonstrates all 18 blocks working together beautifully
3. An agency could show this to a client and feel proud
4. The code remains simple enough to customize without major surgery

---
*Last updated: 2026-02-02 after initialization*
