<?php

declare(strict_types=1);

return [
    'catalog' => [
        'local_path' => 'docs/catalog/first-party-plugins.json',
        'url' => 'https://raw.githubusercontent.com/tentaplane/tentapress/refs/heads/main/docs/catalog/first-party-plugins.json',
        'timeout_seconds' => 10,
        'cache_ttl_seconds' => 900,
        'require_https' => true,
    ],
    'plugin_lifecycle' => [
        'php_binary' => '',
        'composer_binary' => '',
    ],
];
