<?php

define('SMTP_PRIMARY_HOST', '');
define('SMTP_PRIMARY_PORT', 587);
define('SMTP_PRIMARY_ENCRYPTION', 'tls');
define('SMTP_PRIMARY_USERNAME', '');
define('SMTP_PRIMARY_PASSWORD', '');

define('SMTP_SECONDARY_HOST', '');
define('SMTP_SECONDARY_PORT', 587);
define('SMTP_SECONDARY_ENCRYPTION', 'tls');
define('SMTP_SECONDARY_USERNAME', '');
define('SMTP_SECONDARY_PASSWORD', '');

define('MAIL_FROM_ADDRESS', 'system@example.com');
define('MAIL_FROM_FRIENDLY', 'System');

define('SMTP_DEBUG_DIR', dirname(__DIR__, 2) . '/smtp_debug_logs/');