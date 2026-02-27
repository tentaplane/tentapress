# Forms

Forms block and submission targets for TentaPress.

## Plugin Details

| Field    | Value                                   |
|----------|-----------------------------------------|
| ID       | `tentapress/forms`                      |
| Version  | 0.4.0                                   |
| Provider | `TentaPress\Forms\FormsServiceProvider` |

## Features

- Registers a `forms/signup` block in the block registry.
- Renders configurable form fields (email, text, textarea, checkbox, select, hidden).
- Submits to a local endpoint with CSRF protection, honeypot, and minimum elapsed-time spam checks.
- Supports provider configuration props for Mailchimp, TentaForms, and Kit.

## Dependencies

- `tentapress/blocks`

## Public Routes

| Method | Path                      | Name              |
|--------|---------------------------|-------------------|
| POST   | `/forms/submit/{formKey}` | `tp.forms.submit` |

## Data Handling

- Submission payloads are forwarded to destination providers.
- PII is not stored in plugin database tables in MVP.
- Logs store metadata and optional hashed email for diagnostics.

## For Site Owners (No Code)

You can connect a signup form to Kit in a few minutes:

1. Open the page in the editor and add the **Form** block.
2. In the block settings, choose **Provider = Kit**.
3. Paste your **Kit API Key** and **Kit Form ID**.
4. Optional: add a **Kit Tag ID** if you want new subscribers auto-tagged.
5. Publish the page and submit a test entry to confirm it works.

Where to find these values in Kit:

- **API Key**: Kit account settings -> API.
- **Form ID**: the numeric ID for the form receiving subscribers.
- **Tag ID**: optional numeric ID for the tag to auto-apply.

If something fails, the form will show a friendly error and your visitor's typed values are kept so they can retry.

## Quick Provider Notes

- **Mailchimp**: use Action URL (+ optional list/GDPR fields).
- **TentaForms**: use form ID (+ environment).
- **Kit**: use API Key + Form ID (+ optional Tag ID).

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/forms
php artisan tp:forms:migrate-newsletter --dry-run
php artisan tp:forms:migrate-newsletter
```
