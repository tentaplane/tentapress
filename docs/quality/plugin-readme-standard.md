# Plugin README Standard

Use this structure for all first-party plugin `README.md` files.

## Required Sections

1. `# <Plugin Name>`
2. One-line description
3. `## Plugin Details`
4. `## Features`
5. `## Dependencies`
6. `## Development`

## Optional Sections (when relevant)

- `## Database`
- `## Data Model Integration`
- `## Admin Menu`
- `## Public Routes`
- `## Configuration`
- `## Asset Build`
- `## Extending`
- `## Third-Party Notices`

## Plugin Details Format

Use this table format exactly:

```md
## Plugin Details

| Field | Value |
|-------|-------|
| ID | `vendor/name` |
| Version | x.y.z |
| Provider | `Vendor\\Package\\ServiceProvider` |
```

## Development Section

Always include enable flow:

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable vendor/name
```

If assets are plugin-local, also include build commands and output paths.

## Conventions

- Keep README focused on current behavior (no roadmap items).
- Keep feature bullets user-facing and concrete.
- Keep dependencies explicit by plugin ID.
- Prefer short sections over long prose.
- Use consistent heading names across plugins.
- If the plugin is distributed independently, include `## Third-Party Notices` and ship a `THIRD_PARTY_NOTICES.md` at the plugin root.
