<?php

return [
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'your-super-secret-jwt-key',
    'jwt_expiry' => $_ENV['JWT_EXPIRY'] ?? 3600,
    'google' => [
        'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
        'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI'] ?? '',
    ],
    'password_reset_expiry' => 3600, // 1 hour
    'email_verification_expiry' => 86400, // 24 hours
    'max_login_attempts' => 5,
    'lockout_duration' => 900, // 15 minutes
];