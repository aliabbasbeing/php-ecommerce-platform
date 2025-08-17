<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class NotificationService
{
    private $emailService;
    private $whatsappService;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->whatsappService = new WhatsAppService();
    }

    /**
     * Send email notification
     */
    public function sendEmail($to, $subject, $template, $data = [])
    {
        return $this->emailService->send($to, $subject, $template, $data);
    }

    /**
     * Send WhatsApp notification
     */
    public function sendWhatsApp($to, $message, $template = null, $data = [])
    {
        if (config('notifications.whatsapp.enabled')) {
            return $this->whatsappService->send($to, $message, $template, $data);
        }
        return false;
    }

    /**
     * Send order confirmation notification
     */
    public function sendOrderConfirmation($order, $user)
    {
        $data = [
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'order_number' => $order['order_number'],
            'total_amount' => number_format($order['total_amount'], 2),
            'order_url' => url("account/orders/{$order['id']}")
        ];

        // Send email
        $this->sendEmail(
            $user['email'],
            "Order Confirmation - {$order['order_number']}",
            'order_confirmation',
            $data
        );

        // Send WhatsApp if phone number is available
        if (!empty($user['phone'])) {
            $message = "Your order {$order['order_number']} has been confirmed! Total: \$" . number_format($order['total_amount'], 2);
            $this->sendWhatsApp($user['phone'], $message);
        }

        // Create in-app notification
        $this->createNotification($user['id'], 'order_placed', 'Order Placed', 
            "Your order {$order['order_number']} has been successfully placed.", $data);
    }

    /**
     * Send order status update notification
     */
    public function sendOrderStatusUpdate($order, $user, $status, $message = '')
    {
        $statusMessages = [
            'processing' => 'is being processed',
            'shipped' => 'has been shipped',
            'delivered' => 'has been delivered',
            'cancelled' => 'has been cancelled'
        ];

        $statusMessage = $statusMessages[$status] ?? 'status has been updated';
        
        $data = [
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'order_number' => $order['order_number'],
            'status' => ucfirst($status),
            'status_message' => $statusMessage,
            'message' => $message,
            'order_url' => url("account/orders/{$order['id']}")
        ];

        // Send email
        $this->sendEmail(
            $user['email'],
            "Order Update - {$order['order_number']}",
            'order_status_update',
            $data
        );

        // Send WhatsApp
        if (!empty($user['phone'])) {
            $whatsappMessage = "Order {$order['order_number']} {$statusMessage}.";
            if ($message) {
                $whatsappMessage .= " Note: {$message}";
            }
            $this->sendWhatsApp($user['phone'], $whatsappMessage);
        }

        // Create in-app notification
        $this->createNotification($user['id'], 'order_' . $status, 'Order ' . ucfirst($status), 
            "Your order {$order['order_number']} {$statusMessage}.", $data);
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail($user)
    {
        $data = [
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'email' => $user['email'],
            'shop_url' => url(),
            'account_url' => url('account')
        ];

        return $this->sendEmail(
            $user['email'],
            'Welcome to ' . config('app.app_name'),
            'welcome',
            $data
        );
    }

    /**
     * Send abandoned cart reminder
     */
    public function sendAbandonedCartReminder($user, $cartItems)
    {
        $data = [
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'cart_items' => $cartItems,
            'cart_url' => url('cart'),
            'item_count' => count($cartItems)
        ];

        return $this->sendEmail(
            $user['email'],
            'You left something in your cart',
            'abandoned_cart',
            $data
        );
    }

    /**
     * Send low stock alert to admin
     */
    public function sendLowStockAlert($products)
    {
        $adminUsers = $this->getAdminUsers();
        
        $data = [
            'products' => $products,
            'admin_url' => url('admin/products')
        ];

        foreach ($adminUsers as $admin) {
            $this->sendEmail(
                $admin['email'],
                'Low Stock Alert',
                'low_stock_alert',
                $data
            );
        }
    }

    /**
     * Send new order notification to admin
     */
    public function sendNewOrderNotificationToAdmin($order, $customer)
    {
        $adminUsers = $this->getAdminUsers();
        
        $data = [
            'order_number' => $order['order_number'],
            'customer_name' => $customer['first_name'] . ' ' . $customer['last_name'],
            'customer_email' => $customer['email'],
            'total_amount' => number_format($order['total_amount'], 2),
            'order_url' => url("admin/orders/{$order['id']}")
        ];

        foreach ($adminUsers as $admin) {
            $this->sendEmail(
                $admin['email'],
                "New Order - {$order['order_number']}",
                'new_order_admin',
                $data
            );

            // Create in-app notification for admin
            $this->createNotification($admin['id'], 'new_order', 'New Order', 
                "New order {$order['order_number']} received from {$customer['first_name']} {$customer['last_name']}", $data);
        }
    }

    /**
     * Create in-app notification
     */
    public function createNotification($userId, $type, $title, $message, $data = [])
    {
        $sql = "INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, ?, ?, ?, ?)";
        $stmt = DB->prepare($sql);
        return $stmt->execute([
            $userId,
            $type,
            $title,
            $message,
            json_encode($data)
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId)
    {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?";
        $stmt = DB->prepare($sql);
        return $stmt->execute([$notificationId, $userId]);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $limit = 20, $unreadOnly = false)
    {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];
        
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = DB->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount($userId)
    {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0";
        $stmt = DB->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    /**
     * Send bulk notification
     */
    public function sendBulkNotification($userIds, $subject, $template, $data = [])
    {
        $userModel = new \App\Models\User();
        $results = [];
        
        foreach ($userIds as $userId) {
            $user = $userModel->find($userId);
            if ($user && $user['is_active']) {
                try {
                    $this->sendEmail($user['email'], $subject, $template, $data);
                    $results[$userId] = 'success';
                } catch (\Exception $e) {
                    $results[$userId] = 'failed';
                }
            }
        }
        
        return $results;
    }

    /**
     * Schedule notification
     */
    public function scheduleNotification($userId, $type, $title, $message, $scheduledAt, $data = [])
    {
        // This would require a scheduled_notifications table
        // For now, just create immediate notification
        return $this->createNotification($userId, $type, $title, $message, $data);
    }

    /**
     * Get admin users
     */
    private function getAdminUsers()
    {
        $userModel = new \App\Models\User();
        return $userModel->findAll(['role' => 'admin', 'is_active' => 1]);
    }

    /**
     * Process notification queue
     */
    public function processQueue()
    {
        // This would process any queued notifications
        // Useful for bulk operations or scheduled notifications
        return true;
    }

    /**
     * Send newsletter
     */
    public function sendNewsletter($subject, $template, $data = [], $userFilters = [])
    {
        $userModel = new \App\Models\User();
        $conditions = array_merge(['is_active' => 1], $userFilters);
        $users = $userModel->findAll($conditions);
        
        $results = [];
        foreach ($users as $user) {
            $personalizedData = array_merge($data, [
                'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                'unsubscribe_url' => url("unsubscribe?email={$user['email']}")
            ]);
            
            try {
                $this->sendEmail($user['email'], $subject, $template, $personalizedData);
                $results[$user['id']] = 'success';
            } catch (\Exception $e) {
                $results[$user['id']] = 'failed';
            }
        }
        
        return $results;
    }
}