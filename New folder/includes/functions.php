<?php
// includes/functions.php

/**
 * Sanitize user input
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Redirect utility
 */
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit;
}

/**
 * Generate a safe slug
 */
function generateSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    return $slug;
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display flash message
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        
        $alertType = ($type === 'error') ? 'danger' : 'success';
        
        echo "<div class='alert alert-{$alertType} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
              
        unset($_SESSION['flash']);
    }
}

/**
 * Get Settings
 */
function getSettings($pdo) {
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    return $stmt->fetch();
}

/**
 * Get Cart Count
 */
function getCartCount($pdo) {
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        if (!isset($_SESSION['cart_session_id'])) {
            return 0;
        }
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE session_id = ? AND user_id IS NULL");
        $stmt->execute([$_SESSION['cart_session_id']]);
    }
    return $stmt->fetchColumn() ?: 0;
}

/**
 * Format Currency
 */
function formatCurrency($amount, $currency = '$') {
    return $currency . number_format($amount, 2);
}
?>
