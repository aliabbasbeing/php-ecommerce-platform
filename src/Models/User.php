<?php

namespace App\Models;

class User extends BaseModel
{
    protected $table = 'users';
    protected $fillable = [
        'email', 'password', 'first_name', 'last_name', 'phone', 'role',
        'email_verified_at', 'email_verification_token', 'password_reset_token',
        'password_reset_expires', 'is_active', 'last_login'
    ];
    protected $hidden = ['password', 'password_reset_token', 'email_verification_token'];

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        return $this->findWhere(['email' => $email]);
    }

    /**
     * Create user with hashed password
     */
    public function createUser($data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $data['email_verification_token'] = bin2hex(random_bytes(32));
        
        return $this->create($data);
    }

    /**
     * Verify password
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Update last login
     */
    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    /**
     * Generate password reset token
     */
    public function generatePasswordResetToken($email)
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + config('auth.password_reset_expiry'));
        
        $user = $this->findByEmail($email);
        if ($user) {
            $this->update($user['id'], [
                'password_reset_token' => $token,
                'password_reset_expires' => $expires
            ]);
            return $token;
        }
        
        return false;
    }

    /**
     * Reset password using token
     */
    public function resetPassword($token, $newPassword)
    {
        $user = $this->findWhere([
            'password_reset_token' => $token
        ]);
        
        if (!$user || strtotime($user['password_reset_expires']) < time()) {
            return false;
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $this->update($user['id'], [
            'password' => $hashedPassword,
            'password_reset_token' => null,
            'password_reset_expires' => null
        ]);
    }

    /**
     * Verify email
     */
    public function verifyEmail($token)
    {
        $user = $this->findWhere(['email_verification_token' => $token]);
        
        if ($user) {
            return $this->update($user['id'], [
                'email_verified_at' => date('Y-m-d H:i:s'),
                'email_verification_token' => null
            ]);
        }
        
        return false;
    }

    /**
     * Get user addresses
     */
    public function getAddresses($userId)
    {
        $sql = "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
        return $this->query($sql, [$userId]);
    }

    /**
     * Get user orders
     */
    public function getOrders($userId, $limit = 10, $offset = 0)
    {
        $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        return $this->query($sql, [$userId, $limit, $offset]);
    }

    /**
     * Get user wishlist
     */
    public function getWishlist($userId)
    {
        $sql = "SELECT w.*, p.name, p.slug, p.price, p.sale_price, p.image 
                FROM wishlists w 
                JOIN products p ON w.product_id = p.id 
                WHERE w.user_id = ? AND p.is_active = 1
                ORDER BY w.created_at DESC";
        return $this->query($sql, [$userId]);
    }

    /**
     * Get user notifications
     */
    public function getNotifications($userId, $limit = 20, $unreadOnly = false)
    {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];
        
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->query($sql, $params);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notificationId, $userId)
    {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$notificationId, $userId]);
    }

    /**
     * Get unread notification count
     */
    public function getUnreadNotificationCount($userId)
    {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
}