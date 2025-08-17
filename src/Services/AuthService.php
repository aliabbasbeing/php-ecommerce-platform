<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class AuthService
{
    private $userModel;
    private $secretKey;
    private $expiry;

    public function __construct()
    {
        $this->userModel = new User();
        $this->secretKey = config('auth.jwt_secret');
        $this->expiry = config('auth.jwt_expiry');
    }

    /**
     * Authenticate user with email and password
     */
    public function authenticate($email, $password, $remember = false)
    {
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        if (!$user['is_active']) {
            throw new \Exception('Account is deactivated');
        }

        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            throw new \Exception('Invalid credentials');
        }

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Generate JWT token
        $token = $this->generateToken($user);

        // Set session
        $this->setUserSession($user);

        // Set remember me cookie if requested
        if ($remember) {
            $this->setRememberMeCookie($user['id']);
        }

        return [
            'user' => $this->hideUserSensitiveData($user),
            'token' => $token
        ];
    }

    /**
     * Register new user
     */
    public function register($data)
    {
        // Check if email already exists
        if ($this->userModel->findByEmail($data['email'])) {
            throw new \Exception('Email already registered');
        }

        // Create user
        $userId = $this->userModel->createUser($data);
        $user = $this->userModel->find($userId);

        // Send email verification
        $this->sendEmailVerification($user);

        return $this->hideUserSensitiveData($user);
    }

    /**
     * Generate JWT token
     */
    public function generateToken($user)
    {
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + $this->expiry
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * Verify JWT token
     */
    public function verifyToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \Exception('Invalid token');
        }
    }

    /**
     * Refresh token
     */
    public function refreshToken($token)
    {
        $payload = $this->verifyToken($token);
        $user = $this->userModel->find($payload['user_id']);
        
        if (!$user || !$user['is_active']) {
            throw new \Exception('User not found or inactive');
        }

        return $this->generateToken($user);
    }

    /**
     * Set user session
     */
    public function setUserSession($user)
    {
        $_SESSION['user'] = $this->hideUserSensitiveData($user);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['authenticated'] = true;
    }

    /**
     * Clear user session
     */
    public function clearUserSession()
    {
        unset($_SESSION['user']);
        unset($_SESSION['user_id']);
        unset($_SESSION['authenticated']);
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }
    }

    /**
     * Set remember me cookie
     */
    private function setRememberMeCookie($userId)
    {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store token in database (you might want to create a remember_tokens table)
        setcookie('remember_me', $token, $expires, '/', '', true, true);
    }

    /**
     * Check remember me cookie
     */
    public function checkRememberMe()
    {
        if (isset($_COOKIE['remember_me'])) {
            // Verify token and log user in
            // This would require a remember_tokens table
            return false;
        }
        return false;
    }

    /**
     * Send email verification
     */
    private function sendEmailVerification($user)
    {
        $notificationService = new NotificationService();
        $verificationUrl = url("verify-email?token={$user['email_verification_token']}");
        
        $data = [
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'verification_url' => $verificationUrl
        ];

        $notificationService->sendEmail(
            $user['email'],
            'Verify Your Email Address',
            'email_verification',
            $data
        );
    }

    /**
     * Verify email address
     */
    public function verifyEmail($token)
    {
        return $this->userModel->verifyEmail($token);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset($email)
    {
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            // Don't reveal if email exists
            return true;
        }

        $token = $this->userModel->generatePasswordResetToken($email);
        
        if ($token) {
            $notificationService = new NotificationService();
            $resetUrl = url("reset-password?token={$token}");
            
            $data = [
                'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                'reset_url' => $resetUrl,
                'expires_in' => '1 hour'
            ];

            $notificationService->sendEmail(
                $user['email'],
                'Password Reset Request',
                'password_reset',
                $data
            );
        }

        return true;
    }

    /**
     * Reset password
     */
    public function resetPassword($token, $newPassword)
    {
        return $this->userModel->resetPassword($token, $newPassword);
    }

    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            throw new \Exception('User not found');
        }

        if (!$this->userModel->verifyPassword($currentPassword, $user['password'])) {
            throw new \Exception('Current password is incorrect');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->userModel->update($userId, ['password' => $hashedPassword]);
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser()
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated()
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        $user = $this->getCurrentUser();
        return $user && ($user['role'] === $role || $user['role'] === 'admin');
    }

    /**
     * Hide sensitive user data
     */
    private function hideUserSensitiveData($user)
    {
        $hiddenFields = ['password', 'password_reset_token', 'email_verification_token'];
        
        foreach ($hiddenFields as $field) {
            unset($user[$field]);
        }
        
        return $user;
    }

    /**
     * Google OAuth login
     */
    public function authenticateWithGoogle($googleUser)
    {
        $user = $this->userModel->findByEmail($googleUser['email']);
        
        if (!$user) {
            // Create new user from Google data
            $userData = [
                'email' => $googleUser['email'],
                'first_name' => $googleUser['given_name'] ?? '',
                'last_name' => $googleUser['family_name'] ?? '',
                'password' => password_hash(uniqid(), PASSWORD_DEFAULT), // Random password
                'email_verified_at' => date('Y-m-d H:i:s'), // Google emails are verified
                'role' => 'customer'
            ];
            
            $userId = $this->userModel->create($userData);
            $user = $this->userModel->find($userId);
        }

        if (!$user['is_active']) {
            throw new \Exception('Account is deactivated');
        }

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Generate JWT token
        $token = $this->generateToken($user);

        // Set session
        $this->setUserSession($user);

        return [
            'user' => $this->hideUserSensitiveData($user),
            'token' => $token
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data)
    {
        // Remove sensitive fields
        $allowedFields = ['first_name', 'last_name', 'phone'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (empty($updateData)) {
            throw new \Exception('No valid fields to update');
        }

        $result = $this->userModel->update($userId, $updateData);
        
        if ($result) {
            // Update session data
            $user = $this->userModel->find($userId);
            $this->setUserSession($user);
        }
        
        return $result;
    }
}