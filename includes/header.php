<?php
// =====================================================
// Page Header (top of every page)
// =====================================================
// Pages should set $page_title before including this.

if (!isset($page_title)) {
    $page_title = 'UCV Library';
}

// Make sure auth helpers are available
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?> | UCV Library</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/style.css'); ?>">
    <link rel="icon" href="<?php echo base_url('assets/logo.jpg'); ?>">
</head>
<body>

<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="<?php echo base_url(is_admin() ? 'admin/dashboard.php' : (is_logged_in() ? 'user/dashboard.php' : 'index.php')); ?>">
            <img src="<?php echo base_url('assets/logo.jpg'); ?>" alt="UCV Logo" class="brand-logo">
            <span class="brand-text">
                <strong>UCV Library</strong>
                <small>Management System</small>
            </span>
        </a>

        <nav class="topnav">
            <?php if (is_logged_in()): ?>
                <?php if (is_admin()): ?>
                    <a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a>
                    <a href="<?php echo base_url('admin/books.php'); ?>">Books</a>
                    <a href="<?php echo base_url('admin/users.php'); ?>">Users</a>
                    <a href="<?php echo base_url('admin/borrows.php'); ?>">Borrows</a>
                <?php else: ?>
                    <a href="<?php echo base_url('user/dashboard.php'); ?>">Dashboard</a>
                    <a href="<?php echo base_url('user/books.php'); ?>">Browse Books</a>
                    <a href="<?php echo base_url('user/my_books.php'); ?>">My Books</a>
                <?php endif; ?>
                <span class="nav-user">
                    Hi, <strong><?php echo e($_SESSION['full_name'] ?? $_SESSION['username']); ?></strong>
                </span>
                <a class="nav-logout" href="<?php echo base_url('auth/logout.php'); ?>">Log Out</a>
            <?php else: ?>
                <a href="<?php echo base_url('auth/login.php'); ?>">Log In</a>
                <a class="nav-cta" href="<?php echo base_url('auth/register.php'); ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">

<?php
// Show one-time flash message if present
$flash = flash_get();
if ($flash):
?>
    <div class="alert alert-<?php echo e($flash['type']); ?>">
        <?php echo e($flash['message']); ?>
    </div>
<?php endif; ?>
