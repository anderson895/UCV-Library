<?php
// =====================================================
// One-Time Setup Script
// =====================================================
// Run this AFTER importing database.sql in phpMyAdmin.
// It creates the default admin and seeds sample books.
// You can safely run it multiple times.
//
// URL: http://localhost/Blessy%20Taccad%20Priete/setup.php
// =====================================================

require_once __DIR__ . '/config/database.php';

$steps = [];

// 1. Create default admin if missing
$result = mysqli_query($conn, "SELECT id FROM users WHERE username = 'admin' LIMIT 1");
if (mysqli_num_rows($result) === 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
    $u = 'admin'; $em = 'admin@ucv.edu.ph'; $name = 'System Administrator';
    mysqli_stmt_bind_param($stmt, 'ssss', $u, $em, $hash, $name);
    if (mysqli_stmt_execute($stmt)) {
        $steps[] = ['ok', 'Default admin account created (username: admin / password: admin123).'];
    } else {
        $steps[] = ['err', 'Failed to create admin: ' . mysqli_error($conn)];
    }
    mysqli_stmt_close($stmt);
} else {
    $steps[] = ['ok', 'Admin account already exists. Skipped.'];
}

// 2. Create a sample regular user if missing
$result = mysqli_query($conn, "SELECT id FROM users WHERE username = 'student' LIMIT 1");
if (mysqli_num_rows($result) === 0) {
    $hash = password_hash('student123', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'user')");
    $u = 'student'; $em = 'student@ucv.edu.ph'; $name = 'Sample Student';
    mysqli_stmt_bind_param($stmt, 'ssss', $u, $em, $hash, $name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $steps[] = ['ok', 'Sample student account created (username: student / password: student123).'];
} else {
    $steps[] = ['ok', 'Sample student already exists. Skipped.'];
}

// 3. Seed sample books if table is empty
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM books"));
if ((int)$row['c'] === 0) {
    $books = [
        ['Introduction to Programming',  'John Smith',     '978-0001', 'Computer Science', 5],
        ['Database Systems',             'Maria Cruz',     '978-0002', 'Computer Science', 3],
        ['Philippine History',           'Jose Rizal Jr.', '978-0003', 'History',          4],
        ['English Grammar',              'Ana Reyes',      '978-0004', 'Language',         6],
        ['Calculus 101',                 'Pedro Santos',   '978-0005', 'Mathematics',      4],
        ['Web Development Basics',       'Liza Tan',       '978-0006', 'Computer Science', 3],
        ['World Literature',             'Carmen Lopez',   '978-0007', 'Literature',       5],
        ['Physics for Everyone',         'Mark dela Cruz', '978-0008', 'Science',          3],
    ];
    $stmt = mysqli_prepare($conn, "INSERT INTO books (title, author, isbn, category, total_copies, available_copies) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($books as $b) {
        $title = $b[0]; $author = $b[1]; $isbn = $b[2]; $category = $b[3]; $copies = $b[4];
        mysqli_stmt_bind_param($stmt, 'ssssii', $title, $author, $isbn, $category, $copies, $copies);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);
    $steps[] = ['ok', 'Seeded ' . count($books) . ' sample books.'];
} else {
    $steps[] = ['ok', 'Books table already has data. Skipped.'];
}

$page_title = 'Setup';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup &mdash; UCV Library</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="index.php">
            <img src="assets/logo.jpg" alt="UCV Logo" class="brand-logo">
            <span class="brand-text">
                <strong>UCV Library</strong>
                <small>Setup</small>
            </span>
        </a>
    </div>
</header>

<main class="container">
    <div class="card" style="max-width:700px; margin:24px auto;">
        <h2 class="card-title">Setup Complete</h2>

        <?php foreach ($steps as $s): ?>
            <div class="setup-step <?php echo $s[0]; ?>">
                [<?php echo $s[0] === 'ok' ? 'OK' : 'ERROR'; ?>] <?php echo htmlspecialchars($s[1]); ?>
            </div>
        <?php endforeach; ?>

        <hr style="margin:18px 0; border:none; border-top:1px solid var(--border);">

        <h3 style="color: var(--maroon); margin-bottom:12px;">Default Accounts</h3>
        <p style="margin-bottom:6px;"><strong>Admin:</strong> <code>admin</code> / <code>admin123</code></p>
        <p style="margin-bottom:18px;"><strong>Student:</strong> <code>student</code> / <code>student123</code></p>

        <a class="btn" href="auth/login.php">Go to Login</a>
        <a class="btn btn-outline" href="index.php">Home</a>
    </div>

    <div class="alert alert-warning" style="max-width:700px; margin:0 auto;">
        <strong>Security tip:</strong> Delete <code>setup.php</code> after running it on a real deployment, and change the default admin password.
    </div>
</main>

<footer class="footer">
    <div class="footer-inner">
        <small>&copy; <?php echo date('Y'); ?> University of Cagayan Valley</small>
    </div>
</footer>
</body>
</html>
