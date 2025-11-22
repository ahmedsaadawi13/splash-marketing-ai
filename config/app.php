<?php
// FILE: /config/app.php

return [
    'name' => getenv('APP_NAME') ?: 'SplashMarketingAI',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => getenv('APP_DEBUG') === 'true',
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',

    'ai' => [
        'provider' => getenv('AI_PROVIDER') ?: 'openai',
        'api_key' => getenv('AI_API_KEY') ?: '',
        'model' => getenv('AI_MODEL') ?: 'gpt-4',
    ],

    'session' => [
        'lifetime' => getenv('SESSION_LIFETIME') ?: 7200,
    ],

    'security' => [
        'csrf_enabled' => getenv('CSRF_ENABLED') !== 'false',
        'password_min_length' => getenv('PASSWORD_MIN_LENGTH') ?: 8,
    ],

    'uploads' => [
        'max_size' => getenv('MAX_UPLOAD_SIZE') ?: 10485760,
        'allowed_image_types' => explode(',', getenv('ALLOWED_IMAGE_TYPES') ?: 'jpg,jpeg,png,gif,webp'),
        'allowed_csv_types' => explode(',', getenv('ALLOWED_CSV_TYPES') ?: 'csv,txt'),
    ],
];
