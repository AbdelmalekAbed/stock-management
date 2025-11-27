<?php
/**
 * Security utilities - CSRF protection, session management, rate limiting
 */

require_once __DIR__ . '/config.php';

class Security {
    
    /**
     * Initialize secure session
     */
    public static function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            
            // Check session timeout
            if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_LIFETIME)) {
                self::destroySession();
                return false;
            }
            
            // Update last activity time
            $_SESSION['LAST_ACTIVITY'] = time();
            
            // Regenerate session ID periodically to prevent session fixation
            if (!isset($_SESSION['CREATED'])) {
                $_SESSION['CREATED'] = time();
            } else if (time() - $_SESSION['CREATED'] > 1800) { // Regenerate every 30 minutes
                session_regenerate_id(true);
                $_SESSION['CREATED'] = time();
            }
            
            // Prevent session hijacking
            if (!isset($_SESSION['USER_AGENT'])) {
                $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            } else if ($_SESSION['USER_AGENT'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
                self::destroySession();
                return false;
            }
        }
        return true;
    }
    
    /**
     * Destroy session completely
     */
    public static function destroySession() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Get CSRF token input field HTML
     */
    public static function getCSRFField() {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Check if request has valid CSRF token
     */
    public static function checkCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[CSRF_TOKEN_NAME] ?? '';
            if (!self::validateCSRFToken($token)) {
                self::logError('CSRF token validation failed');
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }
    }
    
    /**
     * Rate limiting for login attempts
     */
    public static function checkLoginAttempts($identifier) {
        $key = 'login_attempts_' . md5($identifier);
        $lockoutKey = 'login_lockout_' . md5($identifier);
        
        // Check if currently locked out
        if (isset($_SESSION[$lockoutKey]) && time() < $_SESSION[$lockoutKey]) {
            $remainingTime = $_SESSION[$lockoutKey] - time();
            return [
                'allowed' => false,
                'message' => "Too many login attempts. Please try again in " . ceil($remainingTime / 60) . " minutes."
            ];
        }
        
        // Check number of attempts
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
        }
        
        $attempts = $_SESSION[$key];
        
        if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
            // Lock out the user
            $_SESSION[$lockoutKey] = time() + LOGIN_LOCKOUT_TIME;
            return [
                'allowed' => false,
                'message' => "Too many login attempts. Account locked for " . (LOGIN_LOCKOUT_TIME / 60) . " minutes."
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Record failed login attempt
     */
    public static function recordFailedLogin($identifier) {
        $key = 'login_attempts_' . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
        }
        
        $_SESSION[$key]['count']++;
        $_SESSION[$key]['last_attempt'] = time();
    }
    
    /**
     * Reset login attempts after successful login
     */
    public static function resetLoginAttempts($identifier) {
        $key = 'login_attempts_' . md5($identifier);
        $lockoutKey = 'login_lockout_' . md5($identifier);
        
        unset($_SESSION[$key]);
        unset($_SESSION[$lockoutKey]);
    }
    
    /**
     * Sanitize input
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
            return $data;
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number
     */
    public static function validatePhone($phone) {
        return preg_match('/^[0-9]{10}$/', $phone);
    }
    
    /**
     * Validate uploaded image
     */
    public static function validateImageUpload($file) {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file['error']) || is_array($file['error'])) {
            $errors[] = 'Invalid file upload.';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check for upload errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'No file was uploaded.';
                return ['valid' => false, 'errors' => $errors];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'File exceeds maximum size limit.';
                return ['valid' => false, 'errors' => $errors];
            default:
                $errors[] = 'Unknown upload error.';
                return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File size exceeds ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB limit.';
        }
        
        // Check file type
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);
        
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];
        
        if (!array_key_exists($mimeType, $allowedMimes)) {
            $errors[] = 'Invalid file type. Only JPG, PNG, and GIF images are allowed.';
        }
        
        // Check file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMAGE_TYPES)) {
            $errors[] = 'Invalid file extension.';
        }
        
        // Additional security: check image dimensions
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = 'File is not a valid image.';
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors];
        }
        
        return ['valid' => true, 'extension' => $ext];
    }
    
    /**
     * Generate secure filename
     */
    public static function generateSecureFilename($prefix, $extension) {
        return $prefix . '_' . time() . '_' . bin2hex(random_bytes(2)) . '.' . $extension;
    }
    
    /**
     * Force HTTPS redirect
     */
    public static function forceHTTPS() {
        if (FORCE_HTTPS && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $redirect);
            exit();
        }
    }
    
    /**
     * Log security events
     */
    public static function logError($message, $context = []) {
        if (!LOG_ERRORS) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $logMessage = "[$timestamp] [$ip] $message";
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . json_encode($context);
        }
        $logMessage .= " | User-Agent: $userAgent\n";
        
        error_log($logMessage, 3, LOG_FILE);
    }
    
    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

// Initialize session on every request
Security::initSession();

// Force HTTPS if configured
Security::forceHTTPS();
