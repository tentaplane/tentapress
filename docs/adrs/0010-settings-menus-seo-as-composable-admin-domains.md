# ADR 0010 - Settings, Menus, and SEO Are Composable Admin Domains

- Status: accepted
- Date: 2026-02-14
- Decision owners: TentaPress maintainers
- Related PRD(s): docs/project-status.md
- Supersedes:
- Superseded by:

---

## Context
Site configuration, navigation, and metadata management are cross-cutting concerns. Bundling them into page/post plugins would increase coupling and reduce composability.

---

## Decision
Keep `settings`, `menus`, and `seo` as separate admin-domain plugins that integrate through shared contracts:

- `settings`: key-value site/platform settings domain.
- `menus`: navigation structures plus theme location assignment.
- `seo`: metadata editing and content-level SEO overlays.

All three use admin route conventions (`tp.*`, `/admin`, capability middleware) and integrate with content/themes where needed.

---

## Options considered
### Option A
- Pros:
  - Better separation of concerns and clearer ownership.
  - Reusable cross-plugin integration points.
- Cons:
  - More plugin packages and integration wiring.

### Option B
- Pros:
  - Fold each concern into pages/posts for fewer plugins.
- Cons:
  - Heavier content plugins and weaker modularity.

### Option C (optional)
- Pros:
  - Single monolithic admin settings plugin is simple to locate.
- Cons:
  - Lower maintainability and less bounded contexts.

---

## Consequences
What changes as a result of this decision?

- Positive:
  - Independent delivery cadence for major admin concerns.
  - Cleaner plugin boundaries and clearer permission scopes.
- Negative:
  - Integration testing across plugins is required.
- Trade-offs:
  - Modular boundaries over reduced package count.

---

## Notes
Evidence:
- `plugins/tentapress/settings/README.md`
- `plugins/tentapress/menus/README.md`
- `plugins/tentapress/seo/README.md`
- `plugins/tentapress/settings/routes/admin.php`
- `plugins/tentapress/menus/routes/admin.php`
- `plugins/tentapress/seo/routes/admin.php`
