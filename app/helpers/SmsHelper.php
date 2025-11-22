<?php
// FILE: /app/helpers/SmsHelper.php

class SmsHelper {
    private $logPath;

    public function __construct() {
        $this->logPath = __DIR__ . '/../../storage/logs/sms_log.txt';
    }

    /**
     * Send SMS (simulated for this implementation)
     * In production, integrate with Twilio, Nexmo, AWS SNS, etc.
     */
    public function send($to, $message, $from = null) {
        // Default from number
        if (!$from) {
            $config = require __DIR__ . '/../../config/sms.php';
            $from = $config['from_phone'];
        }

        // Validate phone number format
        $to = $this->normalizePhoneNumber($to);
        if (!$to) {
            return [
                'success' => false,
                'error' => 'Invalid phone number format'
            ];
        }

        // Check message length (160 characters for single SMS)
        $messageLength = strlen($message);
        $segments = ceil($messageLength / 160);

        // Prepare SMS data
        $smsData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'from' => $from,
            'message' => $message,
            'length' => $messageLength,
            'segments' => $segments,
            'status' => 'sent'
        ];

        // Log the SMS
        $this->logSms($smsData);

        // Simulate random failures (3% failure rate)
        if (rand(1, 100) <= 3) {
            return [
                'success' => false,
                'error' => 'Simulated delivery failure'
            ];
        }

        return [
            'success' => true,
            'message_id' => $this->generateMessageId(),
            'segments' => $segments
        ];
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk($recipients, $message, $from = null) {
        $results = [];

        foreach ($recipients as $recipient) {
            $personalizedMessage = $this->replaceVariables($message, $recipient);

            $result = $this->send(
                $recipient['phone'],
                $personalizedMessage,
                $from
            );

            $results[] = [
                'phone' => $recipient['phone'],
                'success' => $result['success'],
                'message_id' => isset($result['message_id']) ? $result['message_id'] : null,
                'segments' => isset($result['segments']) ? $result['segments'] : 1,
                'error' => isset($result['error']) ? $result['error'] : null
            ];
        }

        return $results;
    }

    /**
     * Replace template variables with contact data
     */
    public function replaceVariables($message, $contact) {
        $variables = [
            '{{first_name}}' => isset($contact['first_name']) ? $contact['first_name'] : '',
            '{{last_name}}' => isset($contact['last_name']) ? $contact['last_name'] : '',
            '{{full_name}}' => isset($contact['full_name']) ? $contact['full_name'] : '',
            '{{phone}}' => isset($contact['phone']) ? $contact['phone'] : '',
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

        return str_replace(array_keys($variables), array_values($variables), $message);
    }

    /**
     * Normalize phone number to E.164 format
     */
    private function normalizePhoneNumber($phone) {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Ensure it starts with +
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }

        // Basic validation
        if (strlen($phone) < 10 || strlen($phone) > 16) {
            return false;
        }

        return $phone;
    }

    /**
     * Calculate SMS segments
     */
    public function calculateSegments($message) {
        $length = strlen($message);

        // Check if message contains unicode characters
        if (preg_match('/[^\x00-\x7F]/', $message)) {
            // Unicode message: 70 chars per segment
            return ceil($length / 70);
        } else {
            // Standard GSM: 160 chars per segment
            return ceil($length / 160);
        }
    }

    /**
     * Log SMS to file
     */
    private function logSms($smsData) {
        $logEntry = sprintf(
            "[%s] TO: %s | FROM: %s | MESSAGE: %s | SEGMENTS: %d | STATUS: %s\n",
            $smsData['timestamp'],
            $smsData['to'],
            $smsData['from'],
            substr($smsData['message'], 0, 50) . (strlen($smsData['message']) > 50 ? '...' : ''),
            $smsData['segments'],
            $smsData['status']
        );

        // Ensure log directory exists
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($this->logPath, $logEntry, FILE_APPEND);
    }

    private function generateMessageId() {
        return 'SMS' . strtoupper(uniqid());
    }
}
