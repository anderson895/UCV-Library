<?php
// =====================================================
// Landing Page
// =====================================================
require_once __DIR__ . '/includes/auth.php';

// Logged-in users go straight to their dashboard
if (is_logged_in()) {
    header('Location: ' . base_url(is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$page_title = 'Welcome';
include __DIR__ . '/includes/header.php';
?>

<div class="hero">
    <img src="<?php echo base_url('assets/logo.jpg'); ?>" alt="UCV Logo">
    <h1>UCV Library Management System</h1>
    <p>Borrow books, track due dates, and manage your library account &mdash; all in one place.</p>
    <a class="btn btn-gold" href="<?php echo base_url('auth/register.php'); ?>">Get Started</a>
    <a class="btn" href="<?php echo base_url('auth/login.php'); ?>" style="margin-left:8px;">Log In</a>
</div>

<div class="stats">
    <div class="stat-card">
        <div class="stat-label">Sign Up</div>
        <div class="stat-value" style="font-size:18px;">Create your free student account in seconds.</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Borrow</div>
        <div class="stat-value" style="font-size:18px;">Browse the catalog and borrow available books with one click.</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Return</div>
        <div class="stat-value" style="font-size:18px;">Track your due dates and return books before they're overdue.</div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
