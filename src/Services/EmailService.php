<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    private $config;

    public function __construct()
    {
        $this->config = config('notifications.email');
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    /**
     * Configure PHPMailer
     */
    private function configure()
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port = $this->config['port'];

            // Default from address
            $this->mailer->setFrom($this->config['from_address'], $this->config['from_name']);

            // Character set
            $this->mailer->CharSet = 'UTF-8';

        } catch (Exception $e) {
            throw new \Exception("Mailer configuration failed: " . $e->getMessage());
        }
    }

    /**
     * Send email
     */
    public function send($to, $subject, $template, $data = [])
    {
        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Recipients
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->renderTemplate($template, $data);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);

            return $this->mailer->send();

        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email with attachment
     */
    public function sendWithAttachment($to, $subject, $template, $data = [], $attachments = [])
    {
        try {
            // Add attachments
            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    $this->mailer->addAttachment($attachment['path'], $attachment['name'] ?? '');
                } else {
                    $this->mailer->addAttachment($attachment);
                }
            }

            return $this->send($to, $subject, $template, $data);

        } catch (Exception $e) {
            error_log("Email with attachment failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Render email template
     */
    private function renderTemplate($template, $data = [])
    {
        $templatePath = __DIR__ . "/../../templates/emails/{$template}.php";
        
        if (!file_exists($templatePath)) {
            // Fallback to simple template
            return $this->renderSimpleTemplate($data);
        }

        // Extract data for use in template
        extract($data);
        
        // Start output buffering
        ob_start();
        include $templatePath;
        $content = ob_get_clean();
        
        // Wrap in layout
        return $this->wrapInLayout($content, $data);
    }

    /**
     * Wrap content in email layout
     */
    private function wrapInLayout($content, $data = [])
    {
        $layoutPath = __DIR__ . "/../../templates/emails/layout.php";
        
        if (!file_exists($layoutPath)) {
            return $content;
        }

        extract($data);
        ob_start();
        include $layoutPath;
        return ob_get_clean();
    }

    /**
     * Simple template fallback
     */
    private function renderSimpleTemplate($data)
    {
        $html = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
        
        if (isset($data['title'])) {
            $html .= '<h2>' . htmlspecialchars($data['title']) . '</h2>';
        }
        
        if (isset($data['message'])) {
            $html .= '<p>' . nl2br(htmlspecialchars($data['message'])) . '</p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Test email configuration
     */
    public function testConfiguration()
    {
        try {
            $this->mailer->SMTPDebug = SMTP::DEBUG_CONNECTION;
            $this->mailer->addAddress($this->config['from_address']);
            $this->mailer->Subject = 'Test Email Configuration';
            $this->mailer->Body = 'This is a test email to verify configuration.';
            
            return $this->mailer->send();
        } catch (Exception $e) {
            throw new \Exception("Email test failed: " . $e->getMessage());
        }
    }

    /**
     * Get email statistics
     */
    public function getStatistics($dateFrom = null, $dateTo = null)
    {
        // This would require an email_logs table to track sent emails
        // For now, return mock data
        return [
            'sent' => 0,
            'failed' => 0,
            'opened' => 0,
            'clicked' => 0
        ];
    }
}

class WhatsAppService
{
    private $config;
    private $apiUrl;
    private $apiToken;

    public function __construct()
    {
        $this->config = config('notifications.whatsapp');
        $this->apiUrl = $this->config['api_url'];
        $this->apiToken = $this->config['api_token'];
    }

    /**
     * Send WhatsApp message
     */
    public function send($to, $message, $template = null, $data = [])
    {
        if (!$this->config['enabled']) {
            return false;
        }

        try {
            $payload = [
                'to' => $this->formatPhoneNumber($to),
                'message' => $message
            ];

            if ($template && !empty($data)) {
                $payload['template'] = $template;
                $payload['data'] = $data;
            }

            $response = $this->makeApiCall('/send', $payload);
            return $response['success'] ?? false;

        } catch (\Exception $e) {
            error_log("WhatsApp sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send template message
     */
    public function sendTemplate($to, $template, $data = [])
    {
        if (!$this->config['enabled']) {
            return false;
        }

        try {
            $payload = [
                'to' => $this->formatPhoneNumber($to),
                'template' => $template,
                'data' => $data
            ];

            $response = $this->makeApiCall('/send-template', $payload);
            return $response['success'] ?? false;

        } catch (\Exception $e) {
            error_log("WhatsApp template sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Make API call
     */
    private function makeApiCall($endpoint, $data)
    {
        $url = rtrim($this->apiUrl, '/') . $endpoint;
        
        $options = [
            'http' => [
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiToken
                ],
                'method' => 'POST',
                'content' => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new \Exception('API call failed');
        }

        return json_decode($response, true);
    }

    /**
     * Format phone number
     */
    private function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if not present (assuming US +1)
        if (strlen($phone) === 10) {
            $phone = '1' . $phone;
        }
        
        return $phone;
    }

    /**
     * Get WhatsApp status
     */
    public function getStatus()
    {
        if (!$this->config['enabled']) {
            return false;
        }

        try {
            $response = $this->makeApiCall('/status', []);
            return $response['status'] ?? 'unknown';
        } catch (\Exception $e) {
            return 'error';
        }
    }
}