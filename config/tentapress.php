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
        'tentapress/custom-blocks' => 'Theme single-file custom block discovery',
        'tentapress/block-markdown-editor' => 'Markdown based rich text editor block',
        'tentapress/page-editor' => 'Notion-style page editor'

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
];
