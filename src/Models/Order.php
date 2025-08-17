<?php

namespace App\Models;

class Order extends BaseModel
{
    protected $table = 'orders';
    protected $fillable = [
        'order_number', 'user_id', 'status', 'subtotal', 'tax_amount', 
        'shipping_amount', 'discount_amount', 'total_amount', 'currency',
        'payment_status', 'payment_method', 'payment_reference',
        'billing_address', 'shipping_address', 'notes'
    ];

    /**
     * Generate unique order number
     */
    public function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
        } while ($this->findWhere(['order_number' => $orderNumber]));
        
        return $orderNumber;
    }

    /**
     * Create order from cart
     */
    public function createFromCart($userId, $cartItems, $addresses, $paymentData)
    {
        try {
            $this->beginTransaction();

            // Calculate totals
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            $taxRate = config('payment.tax_rate', 0.10);
            $shippingCost = $subtotal >= config('payment.free_shipping_threshold', 50) 
                ? 0 : config('payment.shipping_cost', 5.99);
            
            $taxAmount = $subtotal * $taxRate;
            $total = $subtotal + $taxAmount + $shippingCost;

            // Create order
            $orderData = [
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $userId,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingCost,
                'discount_amount' => 0,
                'total_amount' => $total,
                'currency' => config('payment.currency', 'USD'),
                'payment_status' => 'pending',
                'payment_method' => $paymentData['method'] ?? null,
                'payment_reference' => $paymentData['reference'] ?? null,
                'billing_address' => json_encode($addresses['billing']),
                'shipping_address' => json_encode($addresses['shipping']),
                'notes' => $paymentData['notes'] ?? null
            ];

            $orderId = $this->create($orderData);

            // Create order items
            $orderItemModel = new OrderItem();
            foreach ($cartItems as $item) {
                $orderItemModel->create([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity']
                ]);

                // Update product stock
                $productModel = new Product();
                $productModel->updateStock($item['product_id'], $item['quantity'], 'decrease');
            }

            // Add initial status history
            $this->addStatusHistory($orderId, 'pending', 'Order created');

            $this->commit();
            return $orderId;

        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Add status history
     */
    public function addStatusHistory($orderId, $status, $notes = '')
    {
        $sql = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$orderId, $status, $notes]);
    }

    /**
     * Update order status
     */
    public function updateStatus($orderId, $status, $notes = '')
    {
        $result = $this->update($orderId, ['status' => $status]);
        if ($result) {
            $this->addStatusHistory($orderId, $status, $notes);
        }
        return $result;
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($orderId, $status, $reference = null)
    {
        $updateData = ['payment_status' => $status];
        if ($reference) {
            $updateData['payment_reference'] = $reference;
        }
        return $this->update($orderId, $updateData);
    }

    /**
     * Get order items
     */
    public function getItems($orderId)
    {
        $sql = "SELECT * FROM order_items WHERE order_id = ? ORDER BY id";
        return $this->query($sql, [$orderId]);
    }

    /**
     * Get order status history
     */
    public function getStatusHistory($orderId)
    {
        $sql = "SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC";
        return $this->query($sql, [$orderId]);
    }

    /**
     * Get orders with pagination
     */
    public function getOrdersWithPagination($filters = [], $limit = 20, $offset = 0)
    {
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE 1=1";
        $params = [];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $sql .= " AND o.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (o.order_number LIKE ? OR u.email LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Get order statistics
     */
    public function getStatistics($dateFrom = null, $dateTo = null)
    {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_orders,
                    COUNT(CASE WHEN status = 'shipped' THEN 1 END) as shipped_orders,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as average_order_value
                FROM orders
                WHERE 1=1";
        $params = [];

        if ($dateFrom) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }

        $result = $this->query($sql, $params);
        return $result[0] ?? null;
    }

    /**
     * Get recent orders
     */
    public function getRecent($limit = 10)
    {
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC 
                LIMIT ?";
        return $this->query($sql, [$limit]);
    }

    /**
     * Calculate refund amount
     */
    public function calculateRefund($orderId, $itemsToRefund = [])
    {
        $order = $this->find($orderId);
        if (!$order) return 0;

        if (empty($itemsToRefund)) {
            // Full refund
            return $order['total_amount'];
        }

        // Partial refund
        $refundAmount = 0;
        $orderItems = $this->getItems($orderId);
        
        foreach ($itemsToRefund as $refundItem) {
            foreach ($orderItems as $orderItem) {
                if ($orderItem['id'] == $refundItem['item_id']) {
                    $refundAmount += $orderItem['price'] * $refundItem['quantity'];
                    break;
                }
            }
        }

        return $refundAmount;
    }
}

class OrderItem extends BaseModel
{
    protected $table = 'order_items';
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'product_sku',
        'quantity', 'price', 'total'
    ];
}