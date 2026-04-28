<?php
// =====================================================
// Logout - clears the session and redirects home
// =====================================================
require_once __DIR__ . '/../includes/auth.php';

$_SESSION = [];
session_destroy();

// Start a fresh session just to set a flash message
session_start();
flash_set('info', 'You have been logged out.');

header('Location: ' . base_url('auth/login.php'));
exit;
?>
