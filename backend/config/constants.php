<?php
define('APP_NAME', 'Fair Football Judgement System');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE', 'Africa/Dar_es_Salaam');

// Session name
define('SESSION_NAME', 'ffjs_session');

// Token expiry (in seconds) — 1 hour
define('RESET_TOKEN_EXPIRY', 3600);

// Allowed roles
define('ROLES', ['administrator', 'referee', 'manager', 'viewer']);

// Set timezone
date_default_timezone_set(APP_TIMEZONE);