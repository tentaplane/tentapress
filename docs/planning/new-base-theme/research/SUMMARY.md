# Project Research Summary

**Project:** TentaPress Theme & Blocks Upgrade
**Domain:** CMS theme + block library visual design system
**Researched:** 2026-02-02
**Confidence:** MEDIUM

## Executive Summary

This project is a visual overhaul of a CMS theme and its block library, aimed at delivering a confident, agency-ready first impression. Experts build these by locking a design token system first, then applying consistent block styling and templates, and finally proving cohesion with a story-driven demo composition that uses only existing blocks.

The recommended approach is to keep the stack unchanged (Tailwind v4, Vite, Alpine), implement token-first styling in the theme, and use theme-level block overrides that map cleanly to existing block definitions. Prioritize consistent defaults across all core blocks, responsive behavior, and editor/frontend parity before investing in higher-level patterns or demo polish.

Key risks include editor/frontend drift, style regressions against real content, and token sprawl. Mitigate by keeping a constrained token set with a documented usage map, validating block styling against real content fixtures, and verifying parity at each breakpoint before finalizing demo content.

## Key Findings

### Recommended Stack

The research confirms the existing Tailwind v4 + Vite + Alpine stack is the right fit for a design system refresh. The priority is disciplined token usage and consistent block styling rather than new frameworks or component libraries.

**Core technologies:**
- Tailwind CSS v4.1.18: utility-first styling with CSS-variable tokens for consistent block design.
- Vite v7.3.1: fast iteration for theme and block styling changes.
- Alpine.js v3.15.7: minimal interactivity without framework overhead.

### Expected Features

The MVP is a cohesive, token-driven visual system that restyles all core blocks and ships with a complete site shell and a story-driven demo. Differentiators come from curated patterns, distinctive typography, and a signature palette, while new block types and heavy animations are explicitly out of scope.

**Must have (table stakes):**
- Cohesive design tokens (palette, typography, spacing) — users expect consistent global styling.
- Consistent styling across core blocks — blocks must feel like one system.
- Responsive defaults + accessibility — usable on mobile and with proper focus/contrast.
- Header/footer template parts + base templates — complete site frame.
- Editor/frontend style parity — confidence for authors.

**Should have (competitive):**
- Story-driven demo homepage — showcases real compositions.
- Curated block pattern set — speeds page building with consistent layouts.
- Distinctive typography pairing and calibrated type scale — visible design intent.
- Signature color system — immediate identity beyond defaults.
- Opinionated component styling (buttons, cards, quotes) — fast aesthetic proof.

**Defer (v2+):**
- Multiple full theme presets — high maintenance and surface area.
- Advanced layout templates (microsites/funnels) — only after demand is proven.

### Architecture Approach

The architecture should stay theme-first: implement design tokens in `themes/tentapress/tailwind/resources/css/theme.css`, override block views in the theme, and allow plugin block views to remain behaviorally stable. The block renderer already supports theme override with plugin fallback, so changes stay visual and upgrade-safe.

**Major components:**
1. Theme layouts/components — page frame, shared UI parts, and global spacing rhythm.
2. Theme block overrides — per-block, per-variant Blade views in the theme.
3. Block renderer + definitions — resolve type/variant to views without schema changes.

### Critical Pitfalls

1. **Editor/frontend style drift** — share tokens and verify parity per block.
2. **Style updates breaking real content** — use regression fixtures with long/short/missing content.
3. **Token explosion without a usage map** — keep a small token set with explicit roles.
4. **Demo-first design that fails in real use** — ensure strong standalone block defaults.
5. **Accessibility regressions in the new palette** — verify contrast/focus states in real combinations.

## Implications for Roadmap

Based on research, suggested phase structure:

### Phase 1: Token System + Global Shell
**Rationale:** Tokens and layout shells unblock all downstream block styling and prevent early drift.
**Delivers:** Theme tokens, typography baseline, header/footer, core layouts, editor parity checks.
**Addresses:** Design tokens, templates, header/footer, responsive/accessibility defaults.
**Avoids:** Token explosion, editor/frontend drift, accessibility regressions.

### Phase 2: Block Library Restyle
**Rationale:** Blocks are the product; consistent defaults must exist before demo polish.
**Delivers:** Restyled block overrides for all core blocks, shared components, responsive review, regression fixtures.
**Uses:** Tailwind token system and theme override pattern.
**Implements:** Variant-scoped views per block type.

### Phase 3: Demo Composition + Patterns
**Rationale:** Demo and patterns prove the system once block defaults are strong.
**Delivers:** Story-driven homepage using core blocks, curated pattern set, optional guidelines section.
**Avoids:** Demo-only polish that masks weak block defaults.

### Phase Ordering Rationale

- Tokens and layout shells are hard dependencies for consistent block styling.
- Block restyling must precede demo composition to avoid demo-only design.
- Pattern work is most effective after block defaults are stable and validated.

### Research Flags

Phases likely needing deeper research during planning:
- **Phase 1:** Confirm editor styling hooks and parity workflow in TentaPress.
- **Phase 2:** Validate regression fixture approach and block preview coverage.

Phases with standard patterns (skip research-phase):
- **Phase 3:** Demo composition and curated patterns are conventional and well-scoped.

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | Clear alignment with existing tech and official releases. |
| Features | MEDIUM | Grounded in CMS theme expectations, but needs product input on priorities. |
| Architecture | MEDIUM | Based on codebase conventions, no external validation. |
| Pitfalls | LOW | Based on internal experience, no external sources. |

**Overall confidence:** MEDIUM

### Gaps to Address

- Editor styling pipeline specifics in TentaPress: confirm how theme tokens propagate to editor previews.
- Regression fixture strategy: define representative content sets and a lightweight review workflow.
- Token usage map ownership: decide where documentation lives and how it is enforced.

## Sources

### Primary (HIGH confidence)
- https://github.com/tailwindlabs/tailwindcss/releases/tag/v4.1.18 — Tailwind CSS version guidance.
- https://github.com/vitejs/vite/releases/tag/v7.3.1 — Vite version guidance.
- https://github.com/alpinejs/alpine/releases/tag/v3.15.7 — Alpine.js version guidance.
- https://github.com/tailwindlabs/tailwindcss-typography/releases/tag/v0.5.19 — Typography plugin compatibility.
- https://github.com/tailwindlabs/tailwindcss-forms/releases/tag/v0.5.11 — Forms plugin compatibility.
- https://github.com/tailwindlabs/prettier-plugin-tailwindcss/releases/tag/v0.7.2 — Tailwind class ordering tooling.

### Secondary (MEDIUM confidence)
- https://developer.wordpress.org/themes/getting-started/what-is-a-theme/ — theme responsibilities and templates.
- https://developer.wordpress.org/block-editor/how-to-guides/themes/global-settings-and-styles/ — global styles and presets.
- https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/ — block style variations.
- https://developer.wordpress.org/block-editor/reference-guides/block-api/block-patterns/ — block patterns guidance.
- `plugins/tentapress/blocks/src/Render/BlockRenderer.php` — block rendering behavior.
- `plugins/tentapress/blocks/resources/definitions/hero.json` — example block definition.
- `themes/tentapress/tailwind/README.md` — theme structure guidance.

### Tertiary (LOW confidence)
- Internal pitfall analysis (no external sources).

---
*Research completed: 2026-02-02*
*Ready for roadmap: yes*
