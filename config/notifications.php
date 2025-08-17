<?php

return [
    'email' => [
        'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
        'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
        'port' => $_ENV['MAIL_PORT'] ?? 587,
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@yourstore.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Your Store',
    ],
    'whatsapp' => [
        'api_url' => $_ENV['WHATSAPP_API_URL'] ?? '',
        'api_token' => $_ENV['WHATSAPP_API_TOKEN'] ?? '',
        'enabled' => !empty($_ENV['WHATSAPP_API_TOKEN']),
    ],
    'sms' => [
        'twilio_sid' => $_ENV['TWILIO_SID'] ?? '',
        'twilio_token' => $_ENV['TWILIO_TOKEN'] ?? '',
        'twilio_from' => $_ENV['TWILIO_FROM'] ?? '',
        'enabled' => !empty($_ENV['TWILIO_SID']),
    ],
    'templates' => [
        'order_confirmation' => 'emails/order_confirmation',
        'order_shipped' => 'emails/order_shipped',
        'password_reset' => 'emails/password_reset',
        'welcome' => 'emails/welcome',
        'email_verification' => 'emails/email_verification',
    ],
];