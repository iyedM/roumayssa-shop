<?php
/**
 * Security Library
 * Provides CSRF protection, input validation, sanitization, and session security
 */

// ============================================
// Session Security
// ============================================

/**
 * Initialize secure session
 */
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Start session
        session_start();
        
        // Regenerate session ID periodically to prevent fixation
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

/**
 * Check if session is expired (2 hours timeout)
 */
function isSessionExpired() {
    if (isset($_SESSION['last_activity'])) {
        $timeout = 7200; // 2 hours in seconds
        if (time() - $_SESSION['last_activity'] > $timeout) {
            return true;
        }
    }
    $_SESSION['last_activity'] = time();
    return false;
}

/**
 * Destroy session securely
 */
function destroySession() {
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

// ============================================
// CSRF Protection
// ============================================

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field HTML
 */
function csrfTokenField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Verify CSRF token from POST request
 */
function verifyCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validateCsrfToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed. Please refresh the page and try again.');
        }
    }
}

// ============================================
// Input Sanitization
// ============================================

/**
 * Sanitize string input
 */
function sanitizeString($input) {
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
}

/**
 * Sanitize email
 */
function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Sanitize phone number (remove non-digits)
 */
function sanitizePhone($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
}

/**
 * Sanitize integer
 */
function sanitizeInt($input) {
    return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Sanitize float
 */
function sanitizeFloat($input) {
    return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Sanitize URL
 */
function sanitizeUrl($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

// ============================================
// Input Validation
// ============================================

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (exactly 8 digits for Tunisia)
 */
function validatePhone($phone) {
    $cleaned = sanitizePhone($phone);
    return preg_match('/^[0-9]{8}$/', $cleaned);
}

/**
 * Validate required field
 */
function validateRequired($value) {
    return !empty(trim($value));
}

/**
 * Validate string length
 */
function validateLength($value, $min = 0, $max = PHP_INT_MAX) {
    $length = mb_strlen($value);
    return $length >= $min && $length <= $max;
}

/**
 * Validate integer
 */
function validateInt($value) {
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

/**
 * Validate float
 */
function validateFloat($value) {
    return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
}

/**
 * Validate URL
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// ============================================
// Combined Validation Functions
// ============================================

/**
 * Validate and sanitize customer name
 */
function validateCustomerName($name, &$error) {
    $name = sanitizeString($name);
    
    if (!validateRequired($name)) {
        $error = "Le nom est requis.";
        return false;
    }
    
    if (!validateLength($name, 3, 150)) {
        $error = "Le nom doit contenir entre 3 et 150 caractères.";
        return false;
    }
    
    return $name;
}

/**
 * Validate and sanitize customer phone
 */
function validateCustomerPhone($phone, &$error) {
    $phone = sanitizePhone($phone);
    
    if (!validateRequired($phone)) {
        $error = "Le numéro de téléphone est requis.";
        return false;
    }
    
    if (!validatePhone($phone)) {
        $error = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
        return false;
    }
    
    return $phone;
}

/**
 * Validate and sanitize customer address
 */
function validateCustomerAddress($address, &$error) {
    $address = sanitizeString($address);
    
    if (!validateRequired($address)) {
        $error = "L'adresse est requise.";
        return false;
    }
    
    if (!validateLength($address, 10, 500)) {
        $error = "L'adresse doit contenir entre 10 et 500 caractères.";
        return false;
    }
    
    return $address;
}

/**
 * Validate and sanitize email
 */
function validateEmailInput($email, &$error) {
    $email = sanitizeEmail($email);
    
    if (!validateRequired($email)) {
        $error = "L'email est requis.";
        return false;
    }
    
    if (!validateEmail($email)) {
        $error = "Format d'email invalide.";
        return false;
    }
    
    return $email;
}

// ============================================
// XSS Protection
// ============================================

/**
 * Escape output for HTML context
 */
function escapeHtml($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output for JavaScript context
 */
function escapeJs($string) {
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Escape output for URL context
 */
function escapeUrl($string) {
    return rawurlencode($string);
}

// ============================================
// Admin Authentication Helpers
// ============================================

/**
 * Check if user is authenticated as admin
 */
function isAdminAuthenticated() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require admin authentication (redirect if not authenticated)
 */
function requireAdmin($redirectUrl = '/admin/login.php') {
    if (!isAdminAuthenticated()) {
        header("Location: $redirectUrl");
        exit;
    }
    
    // Check session expiration
    if (isSessionExpired()) {
        destroySession();
        header("Location: $redirectUrl?expired=1");
        exit;
    }
}

?>
