# Forms

Forms block and submission targets for TentaPress.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/forms` |
| Version | 0.3.0 |
| Provider | `TentaPress\Forms\FormsServiceProvider` |

## Features

- Registers a `forms/signup` block in the block registry.
- Renders configurable form fields (email, text, textarea, checkbox, select, hidden).
- Submits to a local endpoint with CSRF protection, honeypot, and minimum elapsed-time spam checks.
- Supports provider configuration props for Mailchimp and TentaForms.

## Dependencies

- `tentapress/blocks`

## Public Routes

| Method | Path | Name |
|-------|------|------|
| POST | `/forms/submit/{formKey}` | `tp.forms.submit` |

## Data Handling

- Submission payloads are forwarded to destination providers.
- PII is not stored in plugin database tables in MVP.
- Logs store metadata and optional hashed email for diagnostics.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/forms
php artisan tp:forms:migrate-newsletter --dry-run
php artisan tp:forms:migrate-newsletter
```
