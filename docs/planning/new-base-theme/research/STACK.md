# Stack Research

**Domain:** CMS theme + block library visual refresh (design system quality)
**Researched:** 2026-02-02
**Confidence:** MEDIUM

## Recommended Stack

### Core Technologies

| Technology | Version | Purpose | Why Recommended |
|------------|---------|---------|-----------------|
| Tailwind CSS | v4.1.18 | Utility-first styling system | Fits the existing Tailwind-based theme and enables a token-driven system via CSS variables and utilities without adding JS complexity. Confidence: HIGH. |
| Vite | v7.3.1 | Asset bundler for CSS/JS | Standard modern build tool for Laravel themes, fast dev feedback while iterating on visual systems and block styling. Confidence: MEDIUM. |
| Alpine.js | v3.15.7 | Lightweight interactivity where needed | Keeps small interactive affordances consistent with the current stack without adopting heavier component frameworks. Confidence: MEDIUM. |

### Supporting Libraries

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| @tailwindcss/typography | v0.5.19 | Opinionated prose defaults | Use for rich text blocks and long-form content to keep typography consistent across blocks. Confidence: HIGH. |
| @tailwindcss/forms | v0.5.11 | Baseline form styling | Use if any blocks include inputs (newsletter, contact, search) to avoid inconsistent default form styles. Confidence: HIGH. |
| prettier-plugin-tailwindcss | v0.7.2 | Sorts Tailwind class order | Use in dev tooling to keep class strings readable and reduce diff noise when polishing many blocks. Confidence: HIGH. |

### Development Tools

| Tool | Purpose | Notes |
|------|---------|-------|
| Prettier | Formatting | Pair with `prettier-plugin-tailwindcss` for consistent class ordering. |
| Tailwind CSS IntelliSense | Editor hints | Speeds up iteration on tokens and utility usage during the visual overhaul. |

## Installation

```bash
# Core
npm install tailwindcss@4.1.18 vite@7.3.1 alpinejs@3.15.7

# Supporting
npm install @tailwindcss/typography@0.5.19 @tailwindcss/forms@0.5.11

# Dev dependencies
npm install -D prettier prettier-plugin-tailwindcss@0.7.2
```

## Alternatives Considered

| Recommended | Alternative | When to Use Alternative |
|-------------|-------------|-------------------------|
| Tailwind CSS | UnoCSS | Use if build-time performance and on-demand utilities are the primary constraint and the team already uses UnoCSS. |
| @tailwindcss/typography | Custom prose CSS | Use if you want fully bespoke editorial styles and have time to hand-tune all content variants. |

## What NOT to Use

| Avoid | Why | Use Instead |
|-------|-----|-------------|
| Component libraries that impose their own visual system (e.g., DaisyUI, Flowbite) | They fight the bespoke design system goal and introduce inconsistent component tokens across blocks. | Custom Tailwind tokens + utility composition. |
| Bootstrap-based themes | Bootstrap’s component defaults conflict with utility-first token systems and make consistent block styling harder to maintain. | Tailwind CSS v4 with CSS variables. |

## Stack Patterns by Variant

**If the overhaul is limited to a single theme and block set:**
- Use Tailwind CSS v4 with CSS variables for tokens.
- Because it keeps scope tight while still giving a coherent, reusable design system.

**If the same tokens must power multiple themes or plugins:**
- Keep Tailwind CSS v4 but add a dedicated token export step (design tokens to CSS variables) before build.
- Because cross-package consistency matters more than raw iteration speed.

## Version Compatibility

| Package A | Compatible With | Notes |
|-----------|-----------------|-------|
| tailwindcss@4.1.18 | @tailwindcss/typography@0.5.19 | Both maintained by Tailwind Labs; verify plugin behavior against v4 in this repo before rollout. |
| tailwindcss@4.1.18 | @tailwindcss/forms@0.5.11 | Both maintained by Tailwind Labs; verify plugin behavior against v4 in this repo before rollout. |

## Sources

- https://github.com/tailwindlabs/tailwindcss/releases/tag/v4.1.18 — Tailwind CSS latest release (HIGH)
- https://github.com/vitejs/vite/releases/tag/v7.3.1 — Vite latest release (HIGH)
- https://github.com/alpinejs/alpine/releases/tag/v3.15.7 — Alpine.js latest release (HIGH)
- https://github.com/tailwindlabs/tailwindcss-typography/releases/tag/v0.5.19 — Typography plugin latest release (HIGH)
- https://github.com/tailwindlabs/tailwindcss-forms/releases/tag/v0.5.11 — Forms plugin latest release (HIGH)
- https://github.com/tailwindlabs/prettier-plugin-tailwindcss/releases/tag/v0.7.2 — Prettier plugin latest release (HIGH)

---
*Stack research for: CMS theme + block library visual refresh (design system quality)*
*Researched: 2026-02-02*
