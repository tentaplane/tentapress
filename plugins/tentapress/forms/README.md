# Forms

Forms block and submission targets for TentaPress.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/forms` |
| Version | 0.1.0 |
| Provider | `TentaPress\Forms\FormsServiceProvider` |

## Features

- Registers a `forms/signup` block in the block registry.
- Renders configurable form fields (email, text, textarea, checkbox, select, hidden).
- Supports provider configuration props for Mailchimp and TentaForms.

## Dependencies

- `tentapress/blocks`

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/forms
```
