<?php
// FILE: /tests/test_db_connection.php
// Test database connection

require_once __DIR__ . '/../app/core/Database.php';

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
    $db = Database::getInstance();
    $result = $db->fetchOne("SELECT 1 as test");

    if ($result && $result['test'] == 1) {
        echo "✓ Database connection successful!\n";
        exit(0);
    } else {
        echo "✗ Database connection failed: Unexpected result\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
