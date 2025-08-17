<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Models\User;

class AuthController extends BaseController
{
    private $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        if ($this->authService->isAuthenticated()) {
            redirect('/account');
        }

        include __DIR__ . '/../../templates/pages/login.php';
    }

    /**
     * Handle login
     */
    public function login()
    {
        if ($this->request['method'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
        }

        // Validate CSRF token
        if (!verify_csrf_token($this->request['post']['csrf_token'] ?? '')) {
            $this->errorResponse('Invalid CSRF token', 403);
        }

        // Validate input
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ];

        if (!$this->validate($this->request['post'], $rules)) {
            if ($this->isAjax()) {
                $this->errorResponse('Validation failed', 422, $this->getErrors());
            } else {
                $_SESSION['old_input'] = $this->request['post'];
                flash('error', 'Please check your input and try again.');
                redirect('/login');
            }
        }

        try {
            $remember = !empty($this->request['post']['remember']);
            $result = $this->authService->authenticate(
                $this->request['post']['email'],
                $this->request['post']['password'],
                $remember
            );

            if ($this->isAjax()) {
                $this->successResponse($result, 'Login successful');
            } else {
                flash('success', 'Welcome back!');
                $redirectUrl = $_SESSION['intended_url'] ?? '/account';
                unset($_SESSION['intended_url']);
                redirect($redirectUrl);
            }

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->errorResponse($e->getMessage(), 401);
            } else {
                flash('error', $e->getMessage());
                $_SESSION['old_input'] = $this->request['post'];
                redirect('/login');
            }
        }
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        if ($this->authService->isAuthenticated()) {
            redirect('/account');
        }

        include __DIR__ . '/../../templates/pages/register.php';
    }

    /**
     * Handle registration
     */
    public function register()
    {
        if ($this->request['method'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
        }

        // Validate CSRF token
        if (!verify_csrf_token($this->request['post']['csrf_token'] ?? '')) {
            $this->errorResponse('Invalid CSRF token', 403);
        }

        // Validate input
        $rules = [
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|confirmed'
        ];

        if (!$this->validate($this->request['post'], $rules)) {
            if ($this->isAjax()) {
                $this->errorResponse('Validation failed', 422, $this->getErrors());
            } else {
                $_SESSION['old_input'] = $this->request['post'];
                flash('error', 'Please check your input and try again.');
                redirect('/register');
            }
        }

        // Check if email already exists
        $userModel = new User();
        if ($userModel->findByEmail($this->request['post']['email'])) {
            if ($this->isAjax()) {
                $this->errorResponse('Email already registered', 409);
            } else {
                flash('error', 'Email already registered. Please use a different email.');
                $_SESSION['old_input'] = $this->request['post'];
                redirect('/register');
            }
        }

        try {
            $userData = [
                'first_name' => $this->sanitize($this->request['post']['first_name']),
                'last_name' => $this->sanitize($this->request['post']['last_name']),
                'email' => filter_var($this->request['post']['email'], FILTER_SANITIZE_EMAIL),
                'password' => $this->request['post']['password'],
                'phone' => $this->sanitize($this->request['post']['phone'] ?? ''),
                'role' => 'customer'
            ];

            $user = $this->authService->register($userData);

            if ($this->isAjax()) {
                $this->successResponse($user, 'Registration successful. Please check your email to verify your account.');
            } else {
                flash('success', 'Registration successful! Please check your email to verify your account.');
                redirect('/login');
            }

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->errorResponse($e->getMessage(), 400);
            } else {
                flash('error', $e->getMessage());
                $_SESSION['old_input'] = $this->request['post'];
                redirect('/register');
            }
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $this->authService->clearUserSession();
        
        if ($this->isAjax()) {
            $this->successResponse([], 'Logged out successfully');
        } else {
            flash('success', 'You have been logged out.');
            redirect('/');
        }
    }

    /**
     * Verify email address
     */
    public function verifyEmail()
    {
        $token = $this->request['get']['token'] ?? '';
        
        if (empty($token)) {
            flash('error', 'Invalid verification token.');
            redirect('/login');
        }

        try {
            $result = $this->authService->verifyEmail($token);
            
            if ($result) {
                flash('success', 'Email verified successfully! You can now log in.');
            } else {
                flash('error', 'Invalid or expired verification token.');
            }
            
        } catch (\Exception $e) {
            flash('error', 'Email verification failed.');
        }

        redirect('/login');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        include __DIR__ . '/../../templates/pages/forgot_password.php';
    }

    /**
     * Handle forgot password
     */
    public function forgotPassword()
    {
        if ($this->request['method'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
        }

        // Validate CSRF token
        if (!verify_csrf_token($this->request['post']['csrf_token'] ?? '')) {
            $this->errorResponse('Invalid CSRF token', 403);
        }

        $email = filter_var($this->request['post']['email'] ?? '', FILTER_SANITIZE_EMAIL);
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($this->isAjax()) {
                $this->errorResponse('Valid email address is required', 400);
            } else {
                flash('error', 'Please enter a valid email address.');
                redirect('/forgot-password');
            }
        }

        try {
            $this->authService->sendPasswordReset($email);
            
            if ($this->isAjax()) {
                $this->successResponse([], 'Password reset instructions have been sent to your email.');
            } else {
                flash('success', 'If an account with that email exists, password reset instructions have been sent.');
                redirect('/login');
            }

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->errorResponse('Failed to send password reset email', 500);
            } else {
                flash('error', 'Failed to send password reset email. Please try again.');
                redirect('/forgot-password');
            }
        }
    }

    /**
     * Show reset password form
     */
    public function showResetPassword()
    {
        $token = $this->request['get']['token'] ?? '';
        
        if (empty($token)) {
            flash('error', 'Invalid reset token.');
            redirect('/forgot-password');
        }

        include __DIR__ . '/../../templates/pages/reset_password.php';
    }

    /**
     * Handle reset password
     */
    public function resetPassword()
    {
        if ($this->request['method'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
        }

        // Validate CSRF token
        if (!verify_csrf_token($this->request['post']['csrf_token'] ?? '')) {
            $this->errorResponse('Invalid CSRF token', 403);
        }

        $rules = [
            'token' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|confirmed'
        ];

        if (!$this->validate($this->request['post'], $rules)) {
            if ($this->isAjax()) {
                $this->errorResponse('Validation failed', 422, $this->getErrors());
            } else {
                flash('error', 'Please check your input and try again.');
                redirect('/reset-password?token=' . ($this->request['post']['token'] ?? ''));
            }
        }

        try {
            $result = $this->authService->resetPassword(
                $this->request['post']['token'],
                $this->request['post']['password']
            );

            if ($result) {
                if ($this->isAjax()) {
                    $this->successResponse([], 'Password reset successfully');
                } else {
                    flash('success', 'Password reset successfully! You can now log in with your new password.');
                    redirect('/login');
                }
            } else {
                if ($this->isAjax()) {
                    $this->errorResponse('Invalid or expired reset token', 400);
                } else {
                    flash('error', 'Invalid or expired reset token.');
                    redirect('/forgot-password');
                }
            }

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->errorResponse($e->getMessage(), 400);
            } else {
                flash('error', $e->getMessage());
                redirect('/reset-password?token=' . ($this->request['post']['token'] ?? ''));
            }
        }
    }

    /**
     * Handle Google OAuth callback
     */
    public function googleCallback()
    {
        // This would handle Google OAuth callback
        // Requires Google API client library
        try {
            // Verify Google token and get user data
            // $googleUser = $this->verifyGoogleToken($token);
            // $result = $this->authService->authenticateWithGoogle($googleUser);
            
            flash('success', 'Google login successful!');
            redirect('/account');
            
        } catch (\Exception $e) {
            flash('error', 'Google login failed. Please try again.');
            redirect('/login');
        }
    }

    /**
     * Refresh JWT token
     */
    public function refreshToken()
    {
        $token = $this->request['headers']['Authorization'] ?? '';
        $token = str_replace('Bearer ', '', $token);

        if (empty($token)) {
            $this->errorResponse('Token required', 401);
        }

        try {
            $newToken = $this->authService->refreshToken($token);
            $this->successResponse(['token' => $newToken], 'Token refreshed');
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 401);
        }
    }
}