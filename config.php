<?php
/**
 * Configuration file - Load environment variables
 */

// Load environment variables from .env file
function loadEnv($filePath = __DIR__ . '/.env') {
    if (!file_exists($filePath)) {
        // Use defaults if .env doesn't exist
        return false;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Set environment variable
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
    return true;
}

// Load .env file
loadEnv();

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'gestion_des_stocks');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Security Configuration
define('SESSION_LIFETIME', (int)(getenv('SESSION_LIFETIME') ?: 3600)); // 1 hour
define('CSRF_TOKEN_NAME', getenv('CSRF_TOKEN_NAME') ?: 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', (int)(getenv('MAX_LOGIN_ATTEMPTS') ?: 5));
define('LOGIN_LOCKOUT_TIME', (int)(getenv('LOGIN_LOCKOUT_TIME') ?: 900)); // 15 minutes

// File Upload Configuration
define('MAX_FILE_SIZE', (int)(getenv('MAX_FILE_SIZE') ?: 5242880)); // 5MB
define('ALLOWED_IMAGE_TYPES', explode(',', getenv('ALLOWED_IMAGE_TYPES') ?: 'jpg,jpeg,png,gif'));

// Environment
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN));
define('FORCE_HTTPS', filter_var(getenv('FORCE_HTTPS'), FILTER_VALIDATE_BOOLEAN));

// Error Logging
define('LOG_ERRORS', filter_var(getenv('LOG_ERRORS') ?: 'true', FILTER_VALIDATE_BOOLEAN));
define('LOG_FILE', getenv('LOG_FILE') ?: __DIR__ . '/logs/error.log');

// PHP Configuration for Production
if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// Enable error logging
if (LOG_ERRORS) {
    ini_set('log_errors', '1');
    ini_set('error_log', LOG_FILE);
    
    // Create logs directory if it doesn't exist
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
}

// Secure session configuration (only if session not started)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', FORCE_HTTPS ? '1' : '0');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
}

// Timezone
date_default_timezone_set('Africa/Tunis');
