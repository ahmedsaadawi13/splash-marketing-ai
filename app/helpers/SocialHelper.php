<?php
// FILE: /app/helpers/SocialHelper.php

class SocialHelper {
    private $logPath;

    public function __construct() {
        $this->logPath = __DIR__ . '/../../storage/logs/social_log.txt';
    }

    /**
     * Post to social media (simulated for this implementation)
     * In production, integrate with Facebook Graph API, Twitter API, LinkedIn API, etc.
     */
    public function post($platform, $accountId, $message, $media = null) {
        // Validate platform
        $allowedPlatforms = ['facebook', 'twitter', 'linkedin', 'instagram'];
        if (!in_array($platform, $allowedPlatforms)) {
            return [
                'success' => false,
                'error' => 'Unsupported platform'
            ];
        }

        // Prepare post data
        $postData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'platform' => $platform,
            'account_id' => $accountId,
            'message' => $message,
            'media' => $media,
            'status' => 'posted'
        ];

        // Log the post
        $this->logPost($postData);

        // Simulate random failures (2% failure rate)
        if (rand(1, 100) <= 2) {
            return [
                'success' => false,
                'error' => 'Simulated posting failure'
            ];
        }

        return [
            'success' => true,
            'post_id' => $this->generatePostId($platform),
            'url' => $this->generatePostUrl($platform, $accountId)
        ];
    }

    /**
     * Schedule a social media post
     */
    public function schedule($platform, $accountId, $message, $scheduledAt, $media = null) {
        // In production, this would integrate with social media scheduling APIs
        // For now, we'll just log it
        $scheduleData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'platform' => $platform,
            'account_id' => $accountId,
            'message' => $message,
            'scheduled_at' => $scheduledAt,
            'media' => $media,
            'status' => 'scheduled'
        ];

        $this->logPost($scheduleData);

        return [
            'success' => true,
            'scheduled_id' => $this->generatePostId($platform),
            'scheduled_at' => $scheduledAt
        ];
    }

    /**
     * Get post analytics (simulated)
     */
    public function getPostAnalytics($platform, $postId) {
        // Simulate analytics data
        return [
            'post_id' => $postId,
            'platform' => $platform,
            'impressions' => rand(100, 10000),
            'reach' => rand(50, 5000),
            'engagement' => rand(10, 500),
            'likes' => rand(5, 200),
            'comments' => rand(0, 50),
            'shares' => rand(0, 100),
            'clicks' => rand(5, 300)
        ];
    }

    /**
     * Validate message length for platform
     */
    public function validateMessageLength($platform, $message) {
        $limits = [
            'twitter' => 280,
            'facebook' => 63206,
            'linkedin' => 3000,
            'instagram' => 2200
        ];

        $limit = isset($limits[$platform]) ? $limits[$platform] : 1000;
        $length = strlen($message);

        return [
            'valid' => $length <= $limit,
            'length' => $length,
            'limit' => $limit,
            'remaining' => max(0, $limit - $length)
        ];
    }

    /**
     * Extract hashtags from message
     */
    public function extractHashtags($message) {
        preg_match_all('/#(\w+)/', $message, $matches);
        return isset($matches[1]) ? $matches[1] : [];
    }

    /**
     * Extract mentions from message
     */
    public function extractMentions($message) {
        preg_match_all('/@(\w+)/', $message, $matches);
        return isset($matches[1]) ? $matches[1] : [];
    }

    /**
     * Format message for specific platform
     */
    public function formatForPlatform($platform, $message, $includeHashtags = true) {
        $formatted = $message;

        switch ($platform) {
            case 'twitter':
                // Twitter specific formatting
                $validation = $this->validateMessageLength('twitter', $formatted);
                if (!$validation['valid']) {
                    $formatted = substr($formatted, 0, 277) . '...';
                }
                break;

            case 'instagram':
                // Instagram typically uses more hashtags
                if ($includeHashtags && strpos($formatted, '#') === false) {
                    $formatted .= "\n\n#marketing #business #growth";
                }
                break;

            case 'linkedin':
                // LinkedIn professional tone
                // Could add professional formatting here
                break;
        }

        return $formatted;
    }

    /**
     * Log post to file
     */
    private function logPost($postData) {
        $logEntry = sprintf(
            "[%s] PLATFORM: %s | ACCOUNT: %s | MESSAGE: %s | STATUS: %s\n",
            $postData['timestamp'],
            strtoupper($postData['platform']),
            $postData['account_id'],
            substr($postData['message'], 0, 100) . (strlen($postData['message']) > 100 ? '...' : ''),
            $postData['status']
        );

        // Ensure log directory exists
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($this->logPath, $logEntry, FILE_APPEND);
    }

    private function generatePostId($platform) {
        return strtoupper(substr($platform, 0, 2)) . '_' . uniqid();
    }

    private function generatePostUrl($platform, $accountId) {
        $urls = [
            'facebook' => 'https://facebook.com/posts/' . uniqid(),
            'twitter' => 'https://twitter.com/status/' . uniqid(),
            'linkedin' => 'https://linkedin.com/feed/update/urn:li:share:' . uniqid(),
            'instagram' => 'https://instagram.com/p/' . uniqid()
        ];

        return isset($urls[$platform]) ? $urls[$platform] : '#';
    }
}
