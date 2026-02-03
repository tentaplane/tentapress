# NAMING CONVENTIONS

This project uses **CamelCase** for all brands and product names.

### Goals
- Keep naming consistent across code, docs, marketing, and repositories
- Make the product family easy to recognise and extend
- Avoid accidental drift (e.g. Tentapress vs TentaPress vs TENTAPRESS)

---

## Brand architecture

### Organisation / umbrella
- **TentaPlane** is the organisation name and the umbrella brand.

### Product names (suite)
- **TentaPlane** - the central product / control plane (primary product)
- **TentaPress** - the open source CMS (under the TentaPlane organisation)
- **TentaHQ** - hosting platform (future)
- **TentaCDN** - associated content delivery network for hosting platform (future)
- **TentaMail** - email marketing platform (future)
- **TentaForms** - standalone form builder, first-party module (future)

---

## Canonical names

When writing documentation, UI copy, or marketing:
- Always use **TentaPlane**, **TentaPress**, **TentaHQ**, **TentaMail**, **TentaCDN** or **TentaForms**
- Avoid variations like: `Tentaplane`, `TENTAPLANE`, `Tenta Plane`, `Tenta-Plane`

**Rule of thumb:** if it’s a brand or product, it’s CamelCase.

---

## Repository naming (GitHub)
All open source repositories live under the **tentaplane** GitHub organisation.

### Canonical repo names
- `tentaplane/tentapress` - OSS CMS (canonical)
- Additional repos (future) should follow the pattern:
    - `tentaplane/<kebab-case-project-name>`

Examples:
- `tentaplane/tentaplane-docs`
- `tentaplane/tentapress-plugins`
- `tentaplane/tentapress-starter`

**Note:** GitHub repos are typically kebab-case/lowercase for convenience; this does not change product branding, which remains CamelCase in docs and UI.

---

## Package naming (Composer / PHP)
### Composer package naming
Composer packages must be lowercase:
- `tentaplane/tentapress`
- `tentaplane/tentapress-<module>`
- `tentaplane/tentapress-plugin-<name>` (if we choose to distinguish plugins explicitly)

Examples:
- `tentaplane/tentapress-forms`
- `tentaplane/tentapress-seo`

### PHP namespaces
Use PascalCase namespaces aligned to product names:
- `TentaPlane\TentaPress\...`

Examples:
- `TentaPlane\TentaPress\Core`
- `TentaPlane\TentaPress\Plugins`
- `TentaPlane\TentaPress\Publishing`

### Laravel package/service provider naming
- `TentaPressServiceProvider`
- `TentaPressFormsServiceProvider`

---

## App and configuration identifiers

### App name (display)
- `APP_NAME="TentaPress"` for the OSS CMS
- `APP_NAME="TentaPlane"` for the central product (if separate app/repo)

### Slugs / identifiers (URLs, internal IDs)
Use lowercase kebab-case for:
- URL paths
- internal identifiers
- feature keys / flags

Examples:
- `press.tentaplane.com`
- `/admin/sites`
- `feature.offboarding-export`

---

## UI copy rules
- Use product names as proper nouns:
    - ✅ “Publish with **TentaPress**”
    - ✅ “Manage sites in **TentaPlane**”
    - ❌ “Publish with Tentapress”
    - ❌ “Manage sites in tentaplane”

- When referring to the open source project:
    - “**TentaPress (open source)**” or “**TentaPress OSS**”

---

## Extensions and plugins (naming)
Plugins should use:
- Product prefix in the **display name**
- Clear purpose in the suffix

Examples:
- **TentaPress Forms**
- **TentaPress SEO**
- **TentaPress Redirects**

Composer package examples:
- `tentaplane/tentapress-forms`
- `tentaplane/tentapress-seo`
- `tentaplane/tentapress-redirects`

---

## Decisions
- Branding: **CamelCase** for product names everywhere
- OSS location: **tentaplane/tentapress** is the canonical repository for the CMS
- Technical identifiers: lowercase/kebab-case for URLs and package names where required

---

## Changes to naming
If a naming rule needs to change:
- Propose it via an ADR (docs/adrs)
- Update this document and apply the change consistently across repos/docs