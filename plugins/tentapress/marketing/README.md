# Marketing

Analytics providers, consent-managed script injection, and marketing runtime output for TentaPress.

## Plugin Details

| Field | Value |
|-------|-------|
| ID | `tentapress/marketing` |
| Version | `0.2.0` |
| Provider | `TentaPress\Marketing\MarketingServiceProvider` |

## Features

- Google Analytics 4, Plausible, Umami, and Rybbit provider support
- Custom script slots for `head`, `body-open`, and `body-close`
- Built-in consent banner and preferences flow
- Consent-gated analytics output via a single runtime manager
- Admin settings screen for providers, consent copy, and script slots

## Theme Integration

Include the shared partials in public layouts:

```blade
@include('tentapress-marketing::head')
@include('tentapress-marketing::body-open')
@include('tentapress-marketing::body-close')
@include('tentapress-marketing::consent')
```

`head` belongs inside `<head>`. `body-open` belongs immediately after `<body>`. `body-close` and `consent` belong before `</body>`.

## Trust Model

Custom scripts are stored in `tp_settings` and rendered raw. Treat them as trusted admin-managed code.

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/marketing
```
