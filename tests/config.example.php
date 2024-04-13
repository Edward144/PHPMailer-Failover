<?php

//Define SMTP services
define('MAILER_FAILOVER_SMTP', [
    'smtp_service_1' => [
        'host' => 'localhost',
        'port' => 1025,
        'username' => 'user1',
        'password' => 'pass',
        'encryption' => 'tls'
    ],
    'smtp_service_2' => [
        'host' => '127.0.0.1',
        'port' => 1025,
        'username' => 'user2',
        'password' => 'pass',
        'encryption' => 'tls'
    ],
]);

//Define debug location and level
define('MAILER_FAILOVER_DEBUG_LEVEL', 2);
define('MAILER_FAILOVER_DEBUG_LOCATION', __DIR__ . '/smtp_debug_logs/');

//Define default from address
define('MAILER_FAILOVER_FROM', 'system@example.com');
define('MAILER_FAILOVER_FROM_FRIENDLY', 'Example System');