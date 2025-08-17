<?php

use App\Models\Cart;
use App\Models\Product;

$cartModel = new Cart();
$productModel = new Product();

$userId = $_SESSION['user_id'] ?? null;
$sessionId = session_id();

switch ($action) {
    case 'count':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $count = $cartModel->getItemCount($userId, $sessionId);
            echo json_encode([
                'success' => true,
                'count' => $count
            ]);
        }
        break;
        
    case 'items':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $totals = $cartModel->getTotals($userId, $sessionId);
            echo json_encode([
                'success' => true,
                'data' => $totals
            ]);
        }
        break;
        
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $productId = $input['product_id'] ?? 0;
            $quantity = $input['quantity'] ?? 1;
            
            if (!$productId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Product ID is required'
                ]);
                break;
            }
            
            try {
                $cartModel->addItem($productId, $quantity, $userId, $sessionId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Product added to cart'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $cartItemId = $input['cart_item_id'] ?? 0;
            $quantity = $input['quantity'] ?? 1;
            
            if (!$cartItemId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cart item ID is required'
                ]);
                break;
            }
            
            try {
                $cartModel->updateQuantity($cartItemId, $quantity, $userId, $sessionId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Cart updated'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }
        break;
        
    case 'remove':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $cartItemId = $input['cart_item_id'] ?? 0;
            
            if (!$cartItemId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cart item ID is required'
                ]);
                break;
            }
            
            try {
                $cartModel->removeItem($cartItemId, $userId, $sessionId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Item removed from cart'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }
        break;
        
    case 'clear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $cartModel->clearCart($userId, $sessionId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Cart cleared'
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Cart action not found'
        ]);
        break;
}