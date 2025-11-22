<?php
// FILE: /app/helpers/MailerHelper.php

class MailerHelper {
    private $logPath;

    public function __construct() {
        $this->logPath = __DIR__ . '/../../storage/logs/email_log.txt';
    }

    /**
     * Send email (simulated for this implementation)
     * In production, integrate with SMTP, SendGrid, Amazon SES, etc.
     */
    public function send($to, $subject, $bodyHtml, $bodyText = null, $from = null, $fromName = null) {
        // Default from address
        if (!$from) {
            $config = require __DIR__ . '/../../config/mail.php';
            $from = $config['from_email'];
            $fromName = $config['from_name'];
        }

        // Prepare email data
        $emailData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'from' => $from,
            'from_name' => $fromName,
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'status' => 'sent'
        ];

        // Log the email
        $this->logEmail($emailData);

        // Simulate random failures (2% failure rate)
        if (rand(1, 100) <= 2) {
            return [
                'success' => false,
                'error' => 'Simulated delivery failure'
            ];
        }

        return [
            'success' => true,
            'message_id' => $this->generateMessageId()
        ];
    }

    /**
     * Send bulk emails
     */
    public function sendBulk($recipients, $subject, $bodyHtml, $bodyText = null, $from = null, $fromName = null) {
        $results = [];

        foreach ($recipients as $recipient) {
            $personalizedSubject = $this->replaceVariables($subject, $recipient);
            $personalizedHtml = $this->replaceVariables($bodyHtml, $recipient);
            $personalizedText = $bodyText ? $this->replaceVariables($bodyText, $recipient) : null;

            $result = $this->send(
                $recipient['email'],
                $personalizedSubject,
                $personalizedHtml,
                $personalizedText,
                $from,
                $fromName
            );

            $results[] = [
                'email' => $recipient['email'],
                'success' => $result['success'],
                'message_id' => isset($result['message_id']) ? $result['message_id'] : null,
                'error' => isset($result['error']) ? $result['error'] : null
            ];
        }

        return $results;
    }

    /**
     * Replace template variables with contact data
     */
    public function replaceVariables($content, $contact) {
        $variables = [
            '{{first_name}}' => isset($contact['first_name']) ? $contact['first_name'] : '',
            '{{last_name}}' => isset($contact['last_name']) ? $contact['last_name'] : '',
            '{{full_name}}' => isset($contact['full_name']) ? $contact['full_name'] : '',
            '{{email}}' => isset($contact['email']) ? $contact['email'] : '',
            '{{phone}}' => isset($contact['phone']) ? $contact['phone'] : '',
            '{{country}}' => isset($contact['country']) ? $contact['country'] : '',
            '{{unsubscribe_link}}' => $this->generateUnsubscribeLink($contact),
        ];

        // Add custom attributes if available
        if (isset($contact['attributes_json']) && $contact['attributes_json']) {
            $attributes = json_decode($contact['attributes_json'], true);
            if ($attributes) {
                foreach ($attributes as $key => $value) {
                    $variables["{{custom.$key}}"] = $value;
                }
            }
        }

        return str_replace(array_keys($variables), array_values($variables), $content);
    }

    /**
     * Generate tracking pixel for open tracking
     */
    public function injectTrackingPixel($html, $recipientId) {
        $trackingUrl = $this->getBaseUrl() . "/track/open?rid=" . $recipientId;
        $pixel = '<img src="' . htmlspecialchars($trackingUrl) . '" width="1" height="1" style="display:none;" />';

        // Inject before closing body tag
        if (stripos($html, '</body>') !== false) {
            $html = str_ireplace('</body>', $pixel . '</body>', $html);
        } else {
            $html .= $pixel;
        }

        return $html;
    }

    /**
     * Wrap links for click tracking
     */
    public function wrapLinksForTracking($html, $recipientId) {
        // Simple regex to find links (in production, use proper HTML parser)
        return preg_replace_callback(
            '/<a\s+href="([^"]+)"/i',
            function($matches) use ($recipientId) {
                $originalUrl = $matches[1];
                if (strpos($originalUrl, 'unsubscribe') !== false || strpos($originalUrl, 'track/') !== false) {
                    return $matches[0]; // Don't track unsubscribe or tracking links
                }
                $trackingUrl = $this->getBaseUrl() . "/track/click?rid=" . $recipientId . "&url=" . urlencode($originalUrl);
                return '<a href="' . htmlspecialchars($trackingUrl) . '"';
            },
            $html
        );
    }

    /**
     * Log email to file
     */
    private function logEmail($emailData) {
        $logEntry = sprintf(
            "[%s] TO: %s | FROM: %s <%s> | SUBJECT: %s | STATUS: %s\n",
            $emailData['timestamp'],
            $emailData['to'],
            $emailData['from_name'],
            $emailData['from'],
            $emailData['subject'],
            $emailData['status']
        );

        // Ensure log directory exists
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($this->logPath, $logEntry, FILE_APPEND);
    }

    private function generateMessageId() {
        return '<' . uniqid() . '@splashmarketingai.local>';
    }

    private function generateUnsubscribeLink($contact) {
        $token = md5($contact['id'] . $contact['email'] . 'secret_salt');
        return $this->getBaseUrl() . "/unsubscribe?contact=" . $contact['id'] . "&token=" . $token;
    }

    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        return $protocol . '://' . $host;
    }
}
