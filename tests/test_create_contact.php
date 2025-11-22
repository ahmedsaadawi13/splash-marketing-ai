<?php
// FILE: /tests/test_create_contact.php
// Test creating a contact

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Contact.php';

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
    $contactModel = new Contact();

    $testData = [
        'email' => 'test_' . time() . '@example.com',
        'first_name' => 'Test',
        'last_name' => 'User',
        'status' => 'subscribed'
    ];

    $contactId = $contactModel->upsert($testData, 1); // Tenant ID 1

    if ($contactId) {
        echo "✓ Contact created successfully (ID: $contactId)\n";

        // Verify
        $contact = $contactModel->findById($contactId, 1);
        if ($contact && $contact['email'] === $testData['email']) {
            echo "✓ Contact verified successfully\n";
            exit(0);
        } else {
            echo "✗ Contact verification failed\n";
            exit(1);
        }
    } else {
        echo "✗ Contact creation failed\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
    exit(1);
}
