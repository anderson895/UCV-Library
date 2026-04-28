<?php
// =====================================================
// Login Page
// =====================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to the right dashboard
if (is_logged_in()) {
    header('Location: ' . base_url(is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$error = '';

// Handle the login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Look up the user safely with a prepared statement
        $stmt = mysqli_prepare($conn, "SELECT id, username, password, full_name, role FROM users WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ss', $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            // Save user info into the session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            flash_set('success', 'Welcome back, ' . $user['full_name'] . '!');
            header('Location: ' . base_url($user['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$page_title = 'Log In';
include __DIR__ . '/../includes/header.php';
?>

<div class="form-wrap">
    <div class="form-card">
        <h1>Log In</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username"
                       value="<?php echo e($_POST['username'] ?? ''); ?>"
                       autocomplete="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn-block">Log In</button>
        </form>

        <div class="form-foot">
            Don't have an account?
            <a href="register.php">Register here</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
