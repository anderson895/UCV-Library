<?php
// =====================================================
// Authentication Helpers
// =====================================================
// Include this file at the top of any page that needs
// to know who is logged in.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Returns true if a user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Returns true if the logged-in user is an admin
function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

// Force the visitor to be logged in.
// If not, redirect to the login page.
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

// Force the visitor to be an admin.
function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: ' . base_url('user/dashboard.php'));
        exit;
    }
}

// Force the visitor to be a regular user.
function require_user() {
    require_login();
    if (is_admin()) {
        header('Location: ' . base_url('admin/dashboard.php'));
        exit;
    }
}

// Builds a URL relative to the project root.
// Example: base_url('auth/login.php')
function base_url($path = '') {
    // Auto-detect the folder name (works even if renamed)
    $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Walk up to project root by removing the deepest folder if we're in a sub-folder
    $parts = explode('/', trim($script, '/'));
    // Known sub-folders inside the project
    $known = ['auth', 'user', 'admin', 'config', 'includes', 'assets'];
    if (!empty($parts) && in_array(end($parts), $known)) {
        array_pop($parts);
    }
    $root = '/' . implode('/', $parts);
    $root = rtrim($root, '/');
    return $root . '/' . ltrim($path, '/');
}

// Safely escape output to prevent XSS
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Stores a one-time message that displays on the next page
function flash_set($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Returns and clears the one-time flash message
function flash_get() {
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}
?>
