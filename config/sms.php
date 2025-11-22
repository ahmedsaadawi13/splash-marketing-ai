<?php
// FILE: /config/sms.php

return [
    'from_phone' => getenv('SMS_FROM_PHONE') ?: '+1234567890',
    'provider' => getenv('SMS_PROVIDER') ?: 'twilio',
    'account_sid' => getenv('SMS_ACCOUNT_SID') ?: '',
    'auth_token' => getenv('SMS_AUTH_TOKEN') ?: '',
];
