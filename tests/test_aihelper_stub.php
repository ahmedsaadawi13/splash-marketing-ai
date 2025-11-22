<?php
// FILE: /tests/test_aihelper_stub.php
// Test AI helper stub functions

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/AIHelper.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

try {
    echo "Testing AI Helper functions...\n\n";

    // Test email content generation
    $emailResult = AIHelper::generateEmailContent(1, [
        'purpose' => 'promotion',
        'audience' => 'customers',
        'tone' => 'professional'
    ]);

    if (isset($emailResult['subject']) && isset($emailResult['body_html'])) {
        echo "✓ Email content generation works\n";
        echo "  Subject: " . substr($emailResult['subject'], 0, 50) . "...\n";
    } else {
        echo "✗ Email content generation failed\n";
        exit(1);
    }

    // Test SMS content generation
    $smsResult = AIHelper::generateSmsContent(1, [
        'purpose' => 'reminder',
        'tone' => 'friendly'
    ]);

    if (isset($smsResult['message'])) {
        echo "✓ SMS content generation works\n";
        echo "  Message: " . substr($smsResult['message'], 0, 50) . "...\n";
    } else {
        echo "✗ SMS content generation failed\n";
        exit(1);
    }

    // Test social post generation
    $socialResult = AIHelper::generateSocialPost(1, [
        'platform' => 'twitter',
        'topic' => 'product launch'
    ]);

    if (isset($socialResult['post'])) {
        echo "✓ Social post generation works\n";
        echo "  Post: " . substr($socialResult['post'], 0, 50) . "...\n";
    } else {
        echo "✗ Social post generation failed\n";
        exit(1);
    }

    echo "\n✓ All AI Helper tests passed!\n";
    exit(0);

} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
