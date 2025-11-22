<?php
// FILE: /app/helpers/AIHelper.php

class AIHelper {
    /**
     * Generate email content using AI
     * This is a STUB implementation. Wire to real LLM API (OpenAI, Anthropic, etc.) in production
     */
    public static function generateEmailContent($tenantId, $params) {
        // Simulate AI token usage
        self::trackTokenUsage($tenantId, 500);

        // Extract parameters
        $purpose = isset($params['purpose']) ? $params['purpose'] : 'general';
        $audience = isset($params['audience']) ? $params['audience'] : 'subscribers';
        $tone = isset($params['tone']) ? $params['tone'] : 'professional';
        $keywords = isset($params['keywords']) ? $params['keywords'] : '';

        // Simulated AI-generated content
        $subjects = [
            'Unlock Exclusive Benefits Just for You',
            'Don\'t Miss Out: Special Offer Inside',
            'Your Journey Starts Here',
            'We Have Something Special for You',
            'Transform Your Experience Today'
        ];

        $subject = $subjects[array_rand($subjects)];

        $bodyText = self::generateEmailBodyText($purpose, $audience, $tone, $keywords);
        $bodyHtml = self::generateEmailBodyHtml($purpose, $audience, $tone, $keywords);

        // Log AI generation
        self::logAIGeneration($tenantId, 'email_content', $params);

        return [
            'subject' => $subject,
            'body_text' => $bodyText,
            'body_html' => $bodyHtml,
            'tokens_used' => 500
        ];
    }

    /**
     * Generate SMS content using AI
     */
    public static function generateSmsContent($tenantId, $params) {
        self::trackTokenUsage($tenantId, 150);

        $purpose = isset($params['purpose']) ? $params['purpose'] : 'general';
        $tone = isset($params['tone']) ? $params['tone'] : 'friendly';

        $messages = [
            "Hi {{first_name}}! We've got something special for you. Check your email for exclusive details. Reply STOP to unsubscribe.",
            "Hey there! Don't miss our limited-time offer. Visit our store today. Text STOP to opt out.",
            "{{first_name}}, your journey with us continues! Exciting updates await. Reply STOP to end messages.",
        ];

        $message = $messages[array_rand($messages)];

        self::logAIGeneration($tenantId, 'sms_content', $params);

        return [
            'message' => $message,
            'tokens_used' => 150
        ];
    }

    /**
     * Generate social media post using AI
     */
    public static function generateSocialPost($tenantId, $params) {
        self::trackTokenUsage($tenantId, 300);

        $platform = isset($params['platform']) ? $params['platform'] : 'general';
        $tone = isset($params['tone']) ? $params['tone'] : 'engaging';
        $topic = isset($params['topic']) ? $params['topic'] : '';

        $posts = [
            "🚀 Exciting news! We're revolutionizing the way you connect with your audience. Stay tuned for something amazing! #Innovation #Marketing",
            "💡 Did you know? Our platform helps thousands of businesses grow their reach every day. Join the success story! #GrowthHacking #Success",
            "🎯 Targeting the right audience has never been easier. Discover how our AI-powered tools can transform your campaigns. Learn more! #AI #Marketing",
        ];

        $post = $posts[array_rand($posts)];

        self::logAIGeneration($tenantId, 'social_post', $params);

        return [
            'post' => $post,
            'tokens_used' => 300
        ];
    }

    /**
     * Improve existing copy using AI
     */
    public static function improveCopy($tenantId, $existingCopy, $instructions) {
        self::trackTokenUsage($tenantId, 400);

        // Simulated improvement
        $improved = $existingCopy;

        if (stripos($instructions, 'shorter') !== false) {
            $words = explode(' ', $existingCopy);
            $improved = implode(' ', array_slice($words, 0, min(count($words), 50))) . '...';
        } elseif (stripos($instructions, 'friendly') !== false) {
            $improved = "Hey there! " . $existingCopy . " We can't wait to hear from you! 😊";
        } elseif (stripos($instructions, 'professional') !== false) {
            $improved = "Dear valued customer, " . $existingCopy . " Best regards, The Team";
        }

        self::logAIGeneration($tenantId, 'improve_copy', ['instructions' => $instructions]);

        return [
            'improved_copy' => $improved,
            'tokens_used' => 400
        ];
    }

    /**
     * Generate A/B testing variants
     */
    public static function generateAIVariants($tenantId, $baseCopy, $count = 3) {
        self::trackTokenUsage($tenantId, 200 * $count);

        $variants = [];

        for ($i = 0; $i < $count; $i++) {
            $prefixes = ['Discover', 'Unlock', 'Explore', 'Experience', 'Transform'];
            $suffixes = ['today', 'now', 'right away', 'instantly', 'immediately'];

            $variants[] = $prefixes[array_rand($prefixes)] . ' ' . $baseCopy . ' ' . $suffixes[array_rand($suffixes)] . '!';
        }

        self::logAIGeneration($tenantId, 'generate_variants', ['count' => $count]);

        return [
            'variants' => $variants,
            'tokens_used' => 200 * $count
        ];
    }

    /**
     * Generate subject line suggestions
     */
    public static function generateSubjectLines($tenantId, $emailContent, $count = 5) {
        self::trackTokenUsage($tenantId, 250);

        $subjects = [
            'Your Exclusive Invitation Awaits',
            'Limited Time: Special Offer Inside',
            'Don\'t Miss This Opportunity',
            'We Have News You\'ll Love',
            'Here\'s What You\'ve Been Waiting For',
            'Your Journey to Success Starts Now',
            'Unlock Premium Benefits Today'
        ];

        shuffle($subjects);
        $suggestions = array_slice($subjects, 0, $count);

        self::logAIGeneration($tenantId, 'subject_lines', ['count' => $count]);

        return [
            'suggestions' => $suggestions,
            'tokens_used' => 250
        ];
    }

    /**
     * Track AI token usage for tenant
     */
    private static function trackTokenUsage($tenantId, $tokens) {
        $db = Database::getInstance();
        $sql = "UPDATE tenant_usage
                SET ai_tokens_used_this_month = ai_tokens_used_this_month + :tokens,
                    updated_at = NOW()
                WHERE tenant_id = :tenant_id";

        $db->execute($sql, [
            'tokens' => $tokens,
            'tenant_id' => $tenantId
        ]);
    }

    /**
     * Log AI generation activity
     */
    private static function logAIGeneration($tenantId, $type, $params) {
        $db = Database::getInstance();
        $sql = "INSERT INTO activity_logs (tenant_id, entity_type, entity_id, action, description, created_at)
                VALUES (:tenant_id, 'ai', NULL, 'ai_generated_content', :description, NOW())";

        $description = "Generated $type with params: " . json_encode($params);

        $db->execute($sql, [
            'tenant_id' => $tenantId,
            'description' => substr($description, 0, 500)
        ]);
    }

    /**
     * Helper to generate email body text
     */
    private static function generateEmailBodyText($purpose, $audience, $tone, $keywords) {
        return "Hi {{first_name}},\n\n" .
               "We're excited to share something special with you!\n\n" .
               "As a valued member of our community, you deserve the best. " .
               "That's why we've crafted this exclusive message just for you.\n\n" .
               ($keywords ? "Keywords: $keywords\n\n" : "") .
               "Don't miss out on this opportunity to elevate your experience.\n\n" .
               "Best regards,\n" .
               "The Team\n\n" .
               "Click here to unsubscribe: {{unsubscribe_link}}";
    }

    /**
     * Helper to generate email body HTML
     */
    private static function generateEmailBodyHtml($purpose, $audience, $tone, $keywords) {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .button { display: inline-block; padding: 12px 24px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Something Special for You</h1>
        </div>
        <div class="content">
            <p>Hi {{first_name}},</p>
            <p>We\'re excited to share something special with you!</p>
            <p>As a valued member of our community, you deserve the best. That\'s why we\'ve crafted this exclusive message just for you.</p>
            ' . ($keywords ? '<p><strong>Focus:</strong> ' . htmlspecialchars($keywords) . '</p>' : '') . '
            <p>Don\'t miss out on this opportunity to elevate your experience.</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="#" class="button">Learn More</a>
            </p>
            <p>Best regards,<br>The Team</p>
        </div>
        <div class="footer">
            <p><a href="{{unsubscribe_link}}">Unsubscribe</a> | <a href="#">View in browser</a></p>
        </div>
    </div>
</body>
</html>';
    }
}
