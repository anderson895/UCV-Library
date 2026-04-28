<?php
// =====================================================
// Registration Page (creates a regular user account)
// =====================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . base_url(is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$errors = [];
$old = ['username' => '', 'email' => '', 'full_name' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['username']  = trim($_POST['username'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm          = $_POST['confirm'] ?? '';

    // Validation
    if ($old['full_name'] === '') $errors[] = 'Full name is required.';
    if (strlen($old['username']) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    // Check if username/email already exists
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ss', $old['username'], $old['email']);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if ($exists) {
            $errors[] = 'That username or email is already taken.';
        }
    }

    // Save the new user
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'user')");
        mysqli_stmt_bind_param($stmt, 'ssss', $old['username'], $old['email'], $hash, $old['full_name']);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            flash_set('success', 'Account created! You can now log in.');
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Could not create account: ' . mysqli_error($conn);
            mysqli_stmt_close($stmt);
        }
    }
}

$page_title = 'Register';
include __DIR__ . '/../includes/header.php';
?>

<div class="form-wrap">
    <div class="form-card">
        <h1>Create Account</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err) echo '&bull; ' . e($err) . '<br>'; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?php echo e($old['full_name']); ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       value="<?php echo e($old['username']); ?>" required>
                <div class="form-hint">At least 3 characters</div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?php echo e($old['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="form-hint">At least 6 characters</div>
            </div>

            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" required>
            </div>

            <button type="submit" class="btn btn-block">Register</button>
        </form>

        <div class="form-foot">
            Already have an account?
            <a href="login.php">Log in here</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
