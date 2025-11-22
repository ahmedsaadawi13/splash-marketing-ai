<?php
// FILE: /config/mail.php

return [
    'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'noreply@splashmarketingai.local',
    'from_name' => getenv('MAIL_FROM_NAME') ?: 'SplashMarketingAI',
    'smtp_host' => getenv('MAIL_SMTP_HOST') ?: 'smtp.mailtrap.io',
    'smtp_port' => getenv('MAIL_SMTP_PORT') ?: 2525,
    'smtp_username' => getenv('MAIL_SMTP_USERNAME') ?: '',
    'smtp_password' => getenv('MAIL_SMTP_PASSWORD') ?: '',
    'smtp_encryption' => getenv('MAIL_SMTP_ENCRYPTION') ?: 'tls',
];
