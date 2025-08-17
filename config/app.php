<?php

return [
    'app_name' => 'E-commerce Platform',
    'app_env' => $_ENV['APP_ENV'] ?? 'production',
    'app_debug' => $_ENV['APP_DEBUG'] ?? false,
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => 'UTC',
    'locale' => 'en',
    'currency' => 'USD',
    'currency_symbol' => '$',
    'pagination' => [
        'per_page' => 20,
        'admin_per_page' => 50,
    ],
    'upload' => [
        'max_size' => $_ENV['UPLOAD_MAX_SIZE'] ?? 10485760, // 10MB
        'allowed_extensions' => explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,gif,pdf'),
        'path' => '/uploads/',
    ],
    'cache' => [
        'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
        'ttl' => $_ENV['CACHE_TTL'] ?? 3600,
    ],
    'session' => [
        'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 120,
        'encrypt' => $_ENV['SESSION_ENCRYPT'] ?? true,
    ],
];