<?php

require_once __DIR__ . '/../src/bootstrap.php';

// Set JSON header
header('Content-Type: application/json');

// Handle CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    exit;
}

// Parse the request
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace('/api', '', $path);
$path = trim($path, '/');
$pathParts = explode('/', $path);

$endpoint = $pathParts[0] ?? '';
$action = $pathParts[1] ?? '';

try {
    switch ($endpoint) {
        case 'cart':
            require_once __DIR__ . '/endpoints/cart.php';
            break;
            
        case 'products':
            require_once __DIR__ . '/endpoints/products.php';
            break;
            
        case 'auth':
            require_once __DIR__ . '/endpoints/auth.php';
            break;
            
        case 'orders':
            require_once __DIR__ . '/endpoints/orders.php';
            break;
            
        case 'search':
            require_once __DIR__ . '/endpoints/search.php';
            break;
            
        case 'notifications':
            require_once __DIR__ . '/endpoints/notifications.php';
            break;
            
        case 'newsletter':
            require_once __DIR__ . '/endpoints/newsletter.php';
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'API endpoint not found'
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => config('app.app_debug') ? $e->getMessage() : 'Internal server error'
    ]);
}