<?php
// FILE: /public/index.php

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// Set timezone
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'UTC');

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/core/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/helpers/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Error handling
if (getenv('APP_DEBUG') === 'true') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Initialize router
$router = new Router();

// ===================================
// AUTHENTICATION ROUTES
// ===================================
$router->get('/login', ['AuthController', 'showLogin']);
$router->post('/login', ['AuthController', 'login']);
$router->get('/register', ['AuthController', 'showRegister']);
$router->post('/register', ['AuthController', 'register']);
$router->get('/logout', ['AuthController', 'logout']);

// ===================================
// DASHBOARD
// ===================================
$router->get('/', ['DashboardController', 'index']);
$router->get('/dashboard', ['DashboardController', 'index']);

// ===================================
// CONTACTS
// ===================================
$router->get('/contacts', ['ContactController', 'index']);
$router->get('/contacts/create', ['ContactController', 'create']);
$router->post('/contacts', ['ContactController', 'store']);
$router->get('/contacts/import', ['ContactController', 'import']);
$router->post('/contacts/import', ['ContactController', 'processImport']);
$router->get('/contacts/export', ['ContactController', 'export']);

// ===================================
// CAMPAIGNS
// ===================================
$router->get('/campaigns', ['CampaignController', 'index']);
$router->get('/campaigns/create', ['CampaignController', 'create']);
$router->post('/campaigns', ['CampaignController', 'store']);
$router->get('/campaigns/{id}', ['CampaignController', 'show']);
$router->post('/campaigns/{id}/send', ['CampaignController', 'send']);

// ===================================
// TRACKING
// ===================================
$router->get('/track/open', ['TrackingController', 'trackOpen']);
$router->get('/track/click', ['TrackingController', 'trackClick']);

// ===================================
// UNSUBSCRIBE
// ===================================
$router->get('/unsubscribe', function() {
    $contactId = $_GET['contact'] ?? null;
    $token = $_GET['token'] ?? null;

    if ($contactId && $token) {
        $contactModel = new Contact();
        $contact = $contactModel->findById($contactId);

        if ($contact) {
            $expectedToken = md5($contact['id'] . $contact['email'] . 'secret_salt');
            if (hash_equals($expectedToken, $token)) {
                $contactModel->unsubscribe($contactId, $contact['tenant_id']);
                echo "You have been successfully unsubscribed.";
                return;
            }
        }
    }

    echo "Invalid unsubscribe link.";
});

// ===================================
// REST API ROUTES
// ===================================
$router->post('/api/contacts/upsert', ['ApiController', 'upsertContact']);
$router->post('/api/events/track', ['ApiController', 'trackEvent']);
$router->post('/api/campaigns/create', ['ApiController', 'createCampaign']);
$router->get('/api/campaigns/{id}/stats', ['ApiController', 'getCampaignStats']);

// ===================================
// 404 Handler
// ===================================
$router->notFound(function() {
    http_response_code(404);
    echo '<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 100px; }
        h1 { font-size: 48px; color: #333; }
        p { font-size: 18px; color: #666; }
        a { color: #4CAF50; text-decoration: none; }
    </style>
</head>
<body>
    <h1>404</h1>
    <p>Page Not Found</p>
    <p><a href="/">Return to Dashboard</a></p>
</body>
</html>';
});

// Dispatch the router
$router->dispatch();
