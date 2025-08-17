<?php

return [
    'stripe' => [
        'secret_key' => $_ENV['STRIPE_SECRET_KEY'] ?? '',
        'publishable_key' => $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '',
    ],
    'paypal' => [
        'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['PAYPAL_CLIENT_SECRET'] ?? '',
        'mode' => $_ENV['PAYPAL_MODE'] ?? 'sandbox', // sandbox or live
    ],
    'currency' => 'USD',
    'tax_rate' => 0.10, // 10%
    'shipping_cost' => 5.99,
    'free_shipping_threshold' => 50.00,
];