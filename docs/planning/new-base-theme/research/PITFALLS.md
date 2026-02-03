# Pitfalls Research

**Domain:** CMS theme + block library visual overhaul
**Researched:** 2026-02-02
**Confidence:** LOW

## Critical Pitfalls

### Pitfall 1: Frontend/editor style drift

**What goes wrong:**
Blocks look polished on the public site but appear broken or misaligned in the editor canvas, making authors distrust the system.

**Why it happens:**
Styles are updated only in the theme layer and not mirrored in editor styles or block previews.

**How to avoid:**
Define a shared design token layer (colors, type scale, spacing) and apply the same Tailwind utilities or CSS variables to both editor and frontend renderers. Verify parity with side-by-side snapshots for every core block.

**Warning signs:**
Editor screenshots diverge from frontend, or block previews use fallback typography/colors.

**Phase to address:**
Phase 1 (Design tokens + global styles)

---

### Pitfall 2: Style updates break existing block content

**What goes wrong:**
Existing pages render with unexpected spacing, layout collapse, or typography changes that make real content look worse than the demo.

**Why it happens:**
Global utility changes or aggressive base styles assume idealized content and override block-level guardrails.

**How to avoid:**
Establish regression fixtures with real content (long headings, short cards, missing images). Limit global overrides and keep block-level constraints (min-heights, line clamps, spacing rules) explicit.

**Warning signs:**
Regression screenshots show large shifts on long or sparse content; block defaults rely on the demo content to look good.

**Phase to address:**
Phase 2 (Block library restyle + regression fixtures)

---

### Pitfall 3: Token explosion without a usage map

**What goes wrong:**
Palette and typography updates proliferate into dozens of near-duplicate tokens, making future changes unpredictable and inconsistent.

**Why it happens:**
Design decisions are made per-block without a constrained system or usage rules.

**How to avoid:**
Keep a small token set with explicit roles (base, muted, accent, critical) and map each token to block contexts. Document the mapping in a living reference.

**Warning signs:**
Multiple new colors are introduced to solve one-off block needs; no canonical mapping exists for headings, body, and accents.

**Phase to address:**
Phase 1 (Design tokens + visual system spec)

---

### Pitfall 4: Demo-first design that fails in real use

**What goes wrong:**
The story-driven homepage looks exceptional, but the default blocks feel bland or brittle when used in typical agency projects.

**Why it happens:**
Design effort concentrates on the hero page and ignores block-level composition and defaults.

**How to avoid:**
Treat block defaults as first-class. Each block needs a strong default style that stands alone without custom content or manual spacing.

**Warning signs:**
Blocks look good only when placed within the demo layout; standalone blocks appear under-designed.

**Phase to address:**
Phase 2 (Block library restyle) and Phase 3 (Demo homepage)

---

### Pitfall 5: Accessibility regressions in the new palette

**What goes wrong:**
New colors and typography reduce contrast and readability, especially in callouts, buttons, and muted text.

**Why it happens:**
Palette choices are made visually in isolation without verifying contrast in real block combinations.

**How to avoid:**
Define contrast targets for text and UI elements, check pairings used by blocks, and constrain usage to approved combinations.

**Warning signs:**
Muted text blends with backgrounds; link and button states are hard to distinguish.

**Phase to address:**
Phase 1 (Design tokens + accessibility checks)

---

### Pitfall 6: Responsive gaps in block styling

**What goes wrong:**
Blocks look polished at desktop width but stack poorly on mobile, with awkward spacing or type scale jumps.

**Why it happens:**
Design review happens primarily at one breakpoint and block styles rely on implicit defaults.

**How to avoid:**
Define responsive rules per block (stacking, spacing, typography). Review every block at key breakpoints before finalizing.

**Warning signs:**
Mobile views require manual overrides or look compressed; repeated use of "mt"/"mb" tweaks in templates.

**Phase to address:**
Phase 2 (Block library restyle + responsive review)

---

## Technical Debt Patterns

Shortcuts that seem reasonable but create long-term problems.

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| One-off block overrides for the demo page | Quick polish for the story layout | Style drift and inconsistent defaults | Only for demo-specific blocks, documented and isolated |
| Custom CSS per block instead of shared utilities | Fast iteration on a single block | Harder maintenance and inconsistent system | Only during exploration, then refactor |
| Global base style overrides for headings and links | Instant visual change across site | Unintended effects in blocks and editor | Only if accompanied by regression fixtures |

## Integration Gotchas

Common mistakes when connecting to external services.

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| Tailwind v4 CSS-first config | Assuming legacy config patterns still apply | Align with existing CSS-first approach and reuse tokens/utilities |
| Admin-shell utilities | Replacing shared `tp-*` utilities with ad-hoc classes | Build on existing utilities and extend only when necessary |
| Theme + plugin boundaries | Styling blocks only in the theme package | Keep block styles inside block library where possible, with tokens shared |

## Performance Traps

Patterns that work at small scale but fail as usage grows.

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| Excessive bespoke CSS per block | CSS size grows quickly, hard to audit | Use shared utilities and tokenized patterns | Multiple themes or large block library |
| Large demo assets for first paint | Slow initial render, poor Lighthouse scores | Optimize images, use responsive sizes, avoid heavy fonts | On low-end devices or poor networks |
| Overly complex typography stack | Font loading delays and layout shifts | Limit font families and weights | When multiple pages load all weights |

## Security Mistakes

Domain-specific security issues beyond general web security.

| Mistake | Risk | Prevention |
|---------|------|------------|
| Styling user-generated HTML without guardrails | Visual phishing or misleading CTAs | Keep block styles scoped to known structures |
| Inconsistent link styling in editor vs frontend | Authors misinterpret link affordances | Enforce shared link styles in both contexts |
| Hidden focus states due to new palette | Keyboard users lose navigation cues | Maintain visible focus styles across components |

## UX Pitfalls

Common user experience mistakes in this domain.

| Pitfall | User Impact | Better Approach |
|---------|-------------|-----------------|
| Over-designed demo that masks real block behavior | Developers misjudge flexibility | Showcase blocks both in story and standalone |
| Default block spacing too tight/loose | Layout feels amateur without manual tweaking | Provide sensible default rhythm across blocks |
| Typography scale feels editorial but not utility | Long-form pages look uneven | Align scale to common content patterns |

## "Looks Done But Isn't" Checklist

Things that appear complete but are missing critical pieces.

- [ ] **Editor parity:** Verify block styles match frontend for every core block.
- [ ] **Responsive coverage:** Verify each block at mobile, tablet, desktop.
- [ ] **Accessibility:** Check contrast and focus states for new palette.
- [ ] **Standalone block defaults:** Confirm blocks look good outside the demo page.

## Recovery Strategies

When pitfalls occur despite prevention, how to recover.

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| Frontend/editor style drift | MEDIUM | Inventory shared tokens, align editor styles, re-test block previews |
| Style updates break existing content | HIGH | Add regression fixtures, roll back global overrides, re-apply per block |
| Token explosion | MEDIUM | Consolidate palette, document mapping, refactor block styles to new roles |

## Pitfall-to-Phase Mapping

How roadmap phases should address these pitfalls.

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| Frontend/editor style drift | Phase 1 | Side-by-side snapshot review for each block |
| Style updates break existing content | Phase 2 | Regression fixtures for long/short/empty content |
| Token explosion without a usage map | Phase 1 | Token map document reviewed by implementers |
| Demo-first design that fails in real use | Phase 2 + Phase 3 | Standalone block gallery vs demo page comparison |
| Accessibility regressions in the new palette | Phase 1 | Contrast checks on token pairs used by blocks |
| Responsive gaps in block styling | Phase 2 | Breakpoint review checklist completed |

## Sources

- No external sources used; based on internal experience and domain patterns (LOW confidence)

---
*Pitfalls research for: CMS theme + block library visual overhaul*
*Researched: 2026-02-02*
