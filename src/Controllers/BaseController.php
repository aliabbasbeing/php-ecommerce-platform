<?php

namespace App\Controllers;

abstract class BaseController
{
    protected $request;
    protected $errors = [];
    protected $successMessage = '';

    public function __construct()
    {
        $this->request = $this->parseRequest();
    }

    /**
     * Parse incoming request
     */
    protected function parseRequest()
    {
        return [
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'get' => $_GET,
            'post' => $_POST,
            'files' => $_FILES,
            'headers' => getallheaders() ?: [],
            'body' => file_get_contents('php://input')
        ];
    }

    /**
     * Return JSON response
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Return error response
     */
    protected function errorResponse($message, $statusCode = 400, $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $this->jsonResponse($response, $statusCode);
    }

    /**
     * Return success response
     */
    protected function successResponse($data = [], $message = 'Success')
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];

        $this->jsonResponse($response, 200);
    }

    /**
     * Validate input data
     */
    protected function validate($data, $rules)
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $fieldRules = explode('|', $ruleSet);

            foreach ($fieldRules as $rule) {
                $this->validateRule($field, $value, $rule, $data);
            }
        }

        return empty($this->errors);
    }

    /**
     * Validate individual rule
     */
    protected function validateRule($field, $value, $rule, $data)
    {
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $ruleParam = $ruleParts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = ucfirst($field) . ' is required.';
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be a valid email address.';
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < $ruleParam) {
                    $this->errors[$field][] = ucfirst($field) . " must be at least {$ruleParam} characters.";
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > $ruleParam) {
                    $this->errors[$field][] = ucfirst($field) . " must not exceed {$ruleParam} characters.";
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be a number.';
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be an integer.';
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (!empty($value) && (!isset($data[$confirmField]) || $value !== $data[$confirmField])) {
                    $this->errors[$field][] = ucfirst($field) . ' confirmation does not match.';
                }
                break;

            case 'unique':
                // This would require database checking - implement in child controllers
                break;

            case 'exists':
                // This would require database checking - implement in child controllers
                break;
        }
    }

    /**
     * Get validation errors
     */
    protected function getErrors()
    {
        return $this->errors;
    }

    /**
     * Redirect with flash message
     */
    protected function redirectWithMessage($url, $type, $message)
    {
        flash($type, $message);
        redirect($url);
    }

    /**
     * Sanitize input
     */
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get current user from session
     */
    protected function getCurrentUser()
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }

    /**
     * Check if user has specific role
     */
    protected function hasRole($role)
    {
        $user = $this->getCurrentUser();
        return $user && ($user['role'] === $role || $user['role'] === 'admin');
    }

    /**
     * Require authentication
     */
    protected function requireAuth($redirectUrl = '/login')
    {
        if (!$this->isAuthenticated()) {
            if ($this->isAjax()) {
                $this->errorResponse('Authentication required', 401);
            } else {
                redirect($redirectUrl);
            }
        }
    }

    /**
     * Require specific role
     */
    protected function requireRole($role, $redirectUrl = '/')
    {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            if ($this->isAjax()) {
                $this->errorResponse('Insufficient permissions', 403);
            } else {
                redirect($redirectUrl);
            }
        }
    }

    /**
     * Upload file
     */
    protected function uploadFile($file, $destination = 'uploads/')
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Upload failed');
        }

        $allowedExtensions = config('app.upload.allowed_extensions');
        $maxSize = config('app.upload.max_size');
        
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new \Exception('File type not allowed');
        }

        if ($file['size'] > $maxSize) {
            throw new \Exception('File size too large');
        }

        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $destination . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new \Exception('Failed to move uploaded file');
        }

        return $destination . $fileName;
    }
}