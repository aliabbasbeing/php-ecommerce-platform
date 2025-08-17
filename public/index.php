<?php

require_once __DIR__ . '/../src/bootstrap.php';

// Get the request URI and parse it
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');

// Basic routing
switch ($path) {
    case '':
    case 'home':
        include __DIR__ . '/pages/homepage.php';
        break;
        
    case 'products':
        include __DIR__ . '/pages/products.php';
        break;
        
    case 'product':
        include __DIR__ . '/pages/product_detail.php';
        break;
        
    case 'search':
        include __DIR__ . '/pages/search.php';
        break;
        
    case 'cart':
        include __DIR__ . '/pages/cart.php';
        break;
        
    case 'checkout':
        include __DIR__ . '/pages/checkout.php';
        break;
        
    case 'login':
        $controller = new \App\Controllers\AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            $controller->showLogin();
        }
        break;
        
    case 'register':
        $controller = new \App\Controllers\AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->register();
        } else {
            $controller->showRegister();
        }
        break;
        
    case 'logout':
        $controller = new \App\Controllers\AuthController();
        $controller->logout();
        break;
        
    case 'forgot-password':
        $controller = new \App\Controllers\AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->forgotPassword();
        } else {
            $controller->showForgotPassword();
        }
        break;
        
    case 'reset-password':
        $controller = new \App\Controllers\AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->resetPassword();
        } else {
            $controller->showResetPassword();
        }
        break;
        
    case 'verify-email':
        $controller = new \App\Controllers\AuthController();
        $controller->verifyEmail();
        break;
        
    case 'account':
        include __DIR__ . '/account/index.php';
        break;
        
    default:
        // Handle 404
        http_response_code(404);
        include __DIR__ . '/pages/404.php';
        break;
}