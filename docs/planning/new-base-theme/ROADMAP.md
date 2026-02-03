# Roadmap: TentaPress Theme & Blocks Upgrade

## Overview

This project transforms TentaPress's default theme and 18-block library from functional scaffolding into an impressive, agency-ready visual system. The journey follows a token-first approach: establish the design foundation (palette, typography, spacing), then systematically restyle all blocks with consistent defaults, and finally prove the system with a story-driven demo composition.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [ ] **Phase 1: Token System + Global Shell** - Design foundation and site frame
- [ ] **Phase 2: Block Library Restyle** - All 18 blocks restyled with consistent defaults
- [ ] **Phase 3: Demo Composition + Patterns** - Story-driven homepage and curated patterns

## Phase Details

### Phase 1: Token System + Global Shell

**Goal**: Establish the visual foundation that enables consistent block styling

**Depends on**: Nothing (first phase)

**Requirements**: TOKN-01, TOKN-02, TOKN-03, TOKN-04, TOKN-05, SHEL-01, SHEL-02, SHEL-03, SHEL-04, SHEL-05, SHEL-06, SHEL-07

**Success Criteria** (what must be TRUE):
1. Theme CSS contains complete token system (colors, fonts, spacing) accessible via Tailwind utilities
2. Header renders with navigation that responds correctly at mobile/tablet/desktop
3. Footer renders with appropriate site metadata and links
4. Both default and landing layouts render pages with correct token-based styling
5. All color combinations used pass WCAG AA contrast requirements

**Plans**: TBD

**Research flags**: Confirm editor styling hooks and parity workflow in TentaPress

---

### Phase 2: Block Library Restyle

**Goal**: Every core block has a strong default style that stands alone and works in any context

**Depends on**: Phase 1 (tokens and layouts must exist)

**Requirements**: BLOK-01 through BLOK-20

**Success Criteria** (what must be TRUE):
1. All 18 blocks render with the new visual system (using Phase 1 tokens)
2. Each block looks intentional and polished when placed alone on a blank page
3. Blocks maintain visual consistency when placed adjacent to other blocks
4. Every block renders correctly at mobile (375px), tablet (768px), and desktop (1280px)
5. Blocks with variants have each variant styled distinctively but coherently

**Plans**: TBD

**Research flags**: Validate regression fixture approach and block preview coverage

---

### Phase 3: Demo Composition + Patterns

**Goal**: Prove the system works in real compositions and provide patterns that accelerate page building

**Depends on**: Phase 2 (blocks must be styled)

**Requirements**: DEMO-01, DEMO-02, DEMO-03, DEMO-04

**Success Criteria** (what must be TRUE):
1. Demo homepage tells a coherent story using only existing blocks (hero to CTA arc)
2. Demo showcases at least 12 of the 18 blocks in meaningful context
3. At least 5 curated patterns exist for common sections (hero, features, testimonials, CTA, etc.)
4. Demo page would impress an agency prospect evaluating TentaPress

**Plans**: TBD

---

## Progress

**Execution Order:**
Phases execute in numeric order: 1 -> 2 -> 3

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Token System + Global Shell | 0/TBD | Not started | - |
| 2. Block Library Restyle | 0/TBD | Not started | - |
| 3. Demo Composition + Patterns | 0/TBD | Not started | - |

---
*Roadmap created: 2026-02-03*
*Depth: standard*
*Total v1 requirements: 36*
