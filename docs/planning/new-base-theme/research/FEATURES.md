# Feature Research

**Domain:** CMS theme + block library visual design system (Tailwind-based)
**Researched:** 2026-02-02
**Confidence:** MEDIUM

## Feature Landscape

### Table Stakes (Users Expect These)

Features users assume exist. Missing these = product feels incomplete.

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| Cohesive design tokens (palette, typography scale, spacing scale) | Modern themes expose consistent global styles and presets | MEDIUM | Implement via theme-level style system (e.g., palette, font sizes, spacing, layout). |
| Consistent styling across core blocks | Block libraries are judged on coherence across headings, text, buttons, media, and layout blocks | MEDIUM | Define default block styles for all core blocks used in demo content. |
| Responsive layout defaults | Themes must render well on mobile and desktop without manual tweaks | MEDIUM | Use layout constraints, spacing scale, and predictable stack behavior. |
| Accessible color contrast and focus states | Theme defaults must be usable out of the box | LOW | Ensure text/background contrast and focus rings for interactive elements. |
| Header/footer template parts | Users expect a complete site frame, not a single page | MEDIUM | Provide global header/footer with navigation and footer metadata. |
| Content templates (home, page, post) | CMS themes are expected to include base templates | MEDIUM | Use block templates that show typical content types. |
| Editor/front-end style parity | Visuals should match between editor and rendered page | MEDIUM | Ensure editor styles inherit the same tokens and block styles. |

### Differentiators (Competitive Advantage)

Features that set the product apart. Not required, but valuable.

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| Story-driven demo homepage composed of core blocks | Demonstrates real-world flexibility without new block types | MEDIUM | Use a narrative sequence: hero → problem → solution → proof → CTA. |
| Curated block pattern set aligned to the design system | Accelerates building attractive pages with consistent design | MEDIUM | Provide a small, high-quality set of patterns (hero, features, testimonials, pricing, CTA). |
| Distinctive typography pairing + calibrated type scale | Developers quickly see design intentionality | LOW | Pair display + body fonts; define consistent heading sizes and line-heights. |
| Signature color system (primary, neutral, accent, state) | Immediate visual identity beyond generic defaults | LOW | Include semantic tokens and accessible contrasts. |
| Opinionated component styling (buttons, cards, quotes) | Helps evaluate aesthetic flexibility fast | MEDIUM | Use block styles/variants for buttons, quotes, callouts, media blocks. |
| Developer-facing theme guidelines page (in-theme demo section) | Shows how to combine blocks to achieve common layouts | LOW | Implement as part of demo homepage or a dedicated demo page. |

### Anti-Features (Commonly Requested, Often Problematic)

Features that seem good but create problems.

| Feature | Why Requested | Why Problematic | Alternative |
|---------|---------------|-----------------|-------------|
| New custom block types | Appears to increase capabilities | Expands scope, adds maintenance and behavior changes | Use core blocks + patterns + block styles. |
| Heavy animations or parallax | Looks “modern” | Performance risk, conflicts with “no animations” constraint | Use static composition, strong typography, and imagery. |
| Too many visual variants per block | Feels flexible | Confuses users and dilutes identity | Offer 1-2 well-designed styles per block. |
| Image-dependent layouts for core sections | Looks polished in demos | Fails with real content; brittle without assets | Ensure typography-first layouts with optional imagery. |
| Overriding user editor controls | Prevents misuse | Frustrates advanced users and limits flexibility | Provide defaults while keeping common controls available. |

## Feature Dependencies

```
Design tokens (palette/typography/spacing)
    └──requires──> Block styles for core blocks
                       └──requires──> Demo homepage and patterns

Templates (home/page/post)
    └──requires──> Header/footer template parts

Block patterns
    └──enhances──> Demo homepage narrative
```

### Dependency Notes

- **Design tokens require Block styles for core blocks:** tokens alone do not guarantee coherent block rendering.
- **Block styles require Demo homepage and patterns:** demo content is the primary proof of the styling system.
- **Templates require Header/footer template parts:** users expect a complete site frame around content templates.
- **Block patterns enhance Demo homepage narrative:** patterns allow consistent sections to be reused and shown in multiple contexts.

## MVP Definition

### Launch With (v1)

Minimum viable product — what's needed to validate the concept.

- [ ] Cohesive design tokens (palette, typography, spacing) — establishes identity and baseline styling.
- [ ] Core block styling pass — ensures every core block used in demo content looks intentional.
- [ ] Story-driven demo homepage — demonstrates the block library in real compositions.
- [ ] Responsive defaults + accessibility pass — ensures viability on mobile and for real users.
- [ ] Header/footer template parts + base templates — provides a complete site shell.

### Add After Validation (v1.x)

Features to add once core is working.

- [ ] Additional block patterns (industry-specific variants) — when feedback shows gaps in typical layouts.
- [ ] Optional style variations (light/darker or warm/cool) — when users ask for quicker theming options.

### Future Consideration (v2+)

Features to defer until product-market fit is established.

- [ ] Multiple full theme presets — increases surface area and maintenance.
- [ ] Advanced layout templates (microsites, funnels) — only after strong demand is validated.

## Feature Prioritization Matrix

| Feature | User Value | Implementation Cost | Priority |
|---------|------------|---------------------|----------|
| Design tokens (palette, typography, spacing) | HIGH | MEDIUM | P1 |
| Core block styling pass | HIGH | MEDIUM | P1 |
| Story-driven demo homepage | HIGH | MEDIUM | P1 |
| Responsive + accessibility defaults | HIGH | LOW | P1 |
| Header/footer template parts | MEDIUM | MEDIUM | P1 |
| Block patterns set | MEDIUM | MEDIUM | P2 |
| Optional style variations | MEDIUM | MEDIUM | P2 |
| Developer guidelines section | MEDIUM | LOW | P2 |
| Multiple theme presets | LOW | HIGH | P3 |

**Priority key:**
- P1: Must have for launch
- P2: Should have, add when possible
- P3: Nice to have, future consideration

## Competitor Feature Analysis

| Feature | Competitor A | Competitor B | Our Approach |
|---------|--------------|--------------|--------------|
| Global style presets (palette/typography/spacing) | WordPress block themes via theme.json | Gutenberg default themes | Use Tailwind tokens + theme settings to mirror global presets. |
| Block patterns library | WordPress Pattern Directory themes | Gutenberg patterns | Provide a curated, smaller set tailored to demo narrative. |
| Block style variations | WordPress block styles | Gutenberg core styles | Offer minimal, high-quality variants for key blocks. |
| Story-driven demo page | Some premium themes | Many marketing-focused themes | Make this a first-class, developer-facing showcase. |

## Sources

- https://developer.wordpress.org/themes/getting-started/what-is-a-theme/ (theme responsibilities, templates, presentation)
- https://developer.wordpress.org/block-editor/how-to-guides/themes/global-settings-and-styles/ (global settings/presets, block styles via theme.json)
- https://developer.wordpress.org/block-editor/reference-guides/block-api/block-styles/ (block style variations)
- https://developer.wordpress.org/block-editor/reference-guides/block-api/block-patterns/ (block patterns and categories)

---
*Feature research for: CMS theme + block library visual design system*
*Researched: 2026-02-02*
