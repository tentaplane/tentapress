<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Plugins
    |--------------------------------------------------------------------------
    |
    | These plugins are enabled automatically on first sync and when running
    | `php artisan tp:plugins defaults`.
    |
    */
    'default_plugins' => [
        'tentapress/admin-shell',
        'tentapress/blocks',
        'tentapress/media',
        'tentapress/pages',
        'tentapress/posts',
        'tentapress/menus',
        'tentapress/seo',
        'tentapress/settings',
        'tentapress/system-info',
        'tentapress/themes',
        'tentapress/users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Optional Plugins
    |--------------------------------------------------------------------------
    |
    | These are kept in the monorepo but are not required by default. Install
    | them with `composer require vendor/name` and then enable them.
    |
    */
    'optional_plugins' => [
        'tentapress/import' => 'Import tooling',
        'tentapress/export' => 'Export tooling',
        'tentapress/static-deploy' => 'Static deployments',
        'tentapress/headless-api' => 'Headless REST API endpoints',
        'tentapress/custom-blocks' => 'Theme single-file custom block discovery',
        'tentapress/block-markdown-editor' => 'Markdown based rich text editor block',
        'tentapress/page-editor' => 'Notion-style page editor',
        'tentapress/builder' => 'Visual drag-and-drop page and post builder',
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin/Theme Vendor Namespaces
    |--------------------------------------------------------------------------
    |
    | Vendor namespaces to scan under /vendor for tentapress.json manifests.
    | Add third-party namespaces here to allow discovery when installed via Composer.
    |
    */
    'plugin_vendor_namespaces' => [
        'tentapress',
        'acme',
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin Catalog
    |--------------------------------------------------------------------------
    |
    | Plugin catalog source configuration for first-party plugin discovery.
    |
    */
    'catalog' => [
        'local_path' => 'docs/catalog/first-party-plugins.json',
        'url' => 'https://github.com/tentaplane/tentapress/blob/main/docs/catalog/first-party-plugins.json',
        'timeout_seconds' => 5,
        'cache_ttl_seconds' => 900,
        'require_https' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin Lifecycle Binaries
    |--------------------------------------------------------------------------
    |
    | TentaPress will auto-detect usable php and composer binaries for
    | install/update jobs. Set these only if you need to override detection.
    |
    */
    'plugin_lifecycle' => [
        'php_binary' => '',
        'composer_binary' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    |
    | Configure the media URL generator for front-end usage. The default
    | "local" driver uses Storage::disk()->url(). Other drivers can be added.
    |
    */
    'media' => [
        'url_driver' => env('TP_MEDIA_URL_DRIVER', 'local'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blaze
    |--------------------------------------------------------------------------
    |
    | Blaze can optimize anonymous Blade components. Keep this disabled by
    | default and enable incrementally with explicit paths.
    |
    */
    'blaze' => [
        'enabled' => (bool) env('TP_BLAZE_ENABLED', false),
        'debug' => (bool) env('TP_BLAZE_DEBUG', false),
        'active_theme_components' => [
            'compile' => true,
            'memo' => false,
            'fold' => false,
        ],
        'paths' => [],
    ],
];
