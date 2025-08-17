<?php

namespace App\Models;

class Cart extends BaseModel
{
    protected $table = 'cart';
    protected $fillable = ['user_id', 'session_id', 'product_id', 'quantity'];

    /**
     * Get cart items for user or session
     */
    public function getItems($userId = null, $sessionId = null)
    {
        if ($userId) {
            $sql = "SELECT c.*, p.name, p.slug, p.price, p.sale_price, p.image, p.stock_quantity, p.stock_status
                    FROM cart c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.user_id = ? AND p.is_active = 1
                    ORDER BY c.created_at DESC";
            return $this->query($sql, [$userId]);
        } elseif ($sessionId) {
            $sql = "SELECT c.*, p.name, p.slug, p.price, p.sale_price, p.image, p.stock_quantity, p.stock_status
                    FROM cart c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.session_id = ? AND c.user_id IS NULL AND p.is_active = 1
                    ORDER BY c.created_at DESC";
            return $this->query($sql, [$sessionId]);
        }
        
        return [];
    }

    /**
     * Add item to cart
     */
    public function addItem($productId, $quantity, $userId = null, $sessionId = null)
    {
        // Check if product exists and is active
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        if (!$product || !$product['is_active']) {
            throw new \Exception('Product not found or inactive');
        }

        // Check stock availability
        if (!$productModel->isInStock($productId, $quantity)) {
            throw new \Exception('Insufficient stock');
        }

        // Check if item already exists in cart
        $conditions = ['product_id' => $productId];
        if ($userId) {
            $conditions['user_id'] = $userId;
        } else {
            $conditions['session_id'] = $sessionId;
            $conditions['user_id'] = null;
        }

        $existingItem = $this->findWhere($conditions);

        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            
            // Check stock for new quantity
            if (!$productModel->isInStock($productId, $newQuantity)) {
                throw new \Exception('Insufficient stock for requested quantity');
            }
            
            return $this->update($existingItem['id'], ['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            $data = [
                'product_id' => $productId,
                'quantity' => $quantity
            ];
            
            if ($userId) {
                $data['user_id'] = $userId;
            } else {
                $data['session_id'] = $sessionId;
            }
            
            return $this->create($data);
        }
    }

    /**
     * Update item quantity
     */
    public function updateQuantity($itemId, $quantity, $userId = null, $sessionId = null)
    {
        // Verify ownership
        $conditions = ['id' => $itemId];
        if ($userId) {
            $conditions['user_id'] = $userId;
        } else {
            $conditions['session_id'] = $sessionId;
            $conditions['user_id'] = null;
        }

        $item = $this->findWhere($conditions);
        if (!$item) {
            throw new \Exception('Cart item not found');
        }

        if ($quantity <= 0) {
            return $this->removeItem($itemId, $userId, $sessionId);
        }

        // Check stock availability
        $productModel = new Product();
        if (!$productModel->isInStock($item['product_id'], $quantity)) {
            throw new \Exception('Insufficient stock');
        }

        return $this->update($itemId, ['quantity' => $quantity]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem($itemId, $userId = null, $sessionId = null)
    {
        // Verify ownership
        $conditions = ['id' => $itemId];
        if ($userId) {
            $conditions['user_id'] = $userId;
        } else {
            $conditions['session_id'] = $sessionId;
            $conditions['user_id'] = null;
        }

        $item = $this->findWhere($conditions);
        if (!$item) {
            throw new \Exception('Cart item not found');
        }

        return $this->delete($itemId);
    }

    /**
     * Clear cart
     */
    public function clearCart($userId = null, $sessionId = null)
    {
        if ($userId) {
            $sql = "DELETE FROM cart WHERE user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$userId]);
        } elseif ($sessionId) {
            $sql = "DELETE FROM cart WHERE session_id = ? AND user_id IS NULL";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$sessionId]);
        }
        
        return false;
    }

    /**
     * Get cart totals
     */
    public function getTotals($userId = null, $sessionId = null, $couponCode = null)
    {
        $items = $this->getItems($userId, $sessionId);
        
        $subtotal = 0;
        $totalQuantity = 0;
        
        foreach ($items as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $subtotal += $price * $item['quantity'];
            $totalQuantity += $item['quantity'];
        }

        $discount = 0;
        if ($couponCode) {
            $couponModel = new Coupon();
            $discount = $couponModel->calculateDiscount($couponCode, $subtotal);
        }

        $taxRate = config('payment.tax_rate', 0.10);
        $discountedSubtotal = $subtotal - $discount;
        $taxAmount = $discountedSubtotal * $taxRate;
        
        $shippingThreshold = config('payment.free_shipping_threshold', 50);
        $shippingCost = $discountedSubtotal >= $shippingThreshold 
            ? 0 : config('payment.shipping_cost', 5.99);
        
        $total = $discountedSubtotal + $taxAmount + $shippingCost;

        return [
            'items' => $items,
            'quantity' => $totalQuantity,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingCost,
            'total' => $total,
            'free_shipping_remaining' => max(0, $shippingThreshold - $discountedSubtotal)
        ];
    }

    /**
     * Merge session cart with user cart
     */
    public function mergeSessionCart($userId, $sessionId)
    {
        $sessionItems = $this->getItems(null, $sessionId);
        
        foreach ($sessionItems as $item) {
            try {
                $this->addItem($item['product_id'], $item['quantity'], $userId);
            } catch (\Exception $e) {
                // Skip items that can't be added (e.g., out of stock)
                continue;
            }
        }

        // Clear session cart
        $this->clearCart(null, $sessionId);
    }

    /**
     * Get cart item count
     */
    public function getItemCount($userId = null, $sessionId = null)
    {
        if ($userId) {
            $sql = "SELECT SUM(quantity) FROM cart WHERE user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
        } elseif ($sessionId) {
            $sql = "SELECT SUM(quantity) FROM cart WHERE session_id = ? AND user_id IS NULL";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$sessionId]);
        } else {
            return 0;
        }

        return $stmt->fetchColumn() ?: 0;
    }

    /**
     * Validate cart items (check stock, prices, etc.)
     */
    public function validateCart($userId = null, $sessionId = null)
    {
        $items = $this->getItems($userId, $sessionId);
        $errors = [];
        $updated = false;

        foreach ($items as $item) {
            $productModel = new Product();
            $product = $productModel->find($item['product_id']);

            // Check if product still exists and is active
            if (!$product || !$product['is_active']) {
                $this->removeItem($item['id'], $userId, $sessionId);
                $errors[] = "Product '{$item['name']}' is no longer available and has been removed from cart.";
                $updated = true;
                continue;
            }

            // Check stock availability
            if (!$productModel->isInStock($item['product_id'], $item['quantity'])) {
                $maxAvailable = $product['stock_quantity'];
                if ($maxAvailable > 0) {
                    $this->updateQuantity($item['id'], $maxAvailable, $userId, $sessionId);
                    $errors[] = "Quantity for '{$item['name']}' has been adjusted to {$maxAvailable} due to stock limitations.";
                } else {
                    $this->removeItem($item['id'], $userId, $sessionId);
                    $errors[] = "Product '{$item['name']}' is out of stock and has been removed from cart.";
                }
                $updated = true;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'updated' => $updated
        ];
    }
}