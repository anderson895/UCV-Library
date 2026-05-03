<?php
// =====================================================
// Admin: Manage Users
// =====================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'delete') {
    $id = (int)$_POST['id'];
    if ($id === (int)$_SESSION['user_id']) {
        flash_set('error', 'You cannot delete your own account.');
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        flash_set('success', 'User deleted.');
    }
    header('Location: users.php');
    exit;
}

// Handle role change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'role') {
    $id = (int)$_POST['id'];
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    if ($id === (int)$_SESSION['user_id']) {
        flash_set('error', 'You cannot change your own role.');
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $role, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        flash_set('success', 'User role updated.');
    }
    header('Location: users.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $users = mysqli_stmt_get_result($stmt);
} else {
    $users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
}

$page_title = 'Manage Users';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Manage Users</h1>

<div class="toolbar">
    <div></div>
    <form class="search-form" method="get" action="users.php">
        <input type="text" name="q" placeholder="Search by name, username, email..."
               value="<?php echo e($search); ?>">
        <button type="submit" class="btn btn-sm">Search</button>
        <?php if ($search): ?>
            <a class="btn btn-sm btn-outline" href="users.php">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($users) === 0): ?>
                <tr><td colspan="7" style="text-align:center; padding:24px;">No users found.</td></tr>
            <?php else: ?>
                <?php while ($u = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td>#<?php echo (int)$u['id']; ?></td>
                        <td><strong><?php echo e($u['full_name']); ?></strong></td>
                        <td><?php echo e($u['username']); ?></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $u['role'] === 'admin' ? 'admin' : 'user'; ?>">
                                <?php echo e(ucfirst($u['role'])); ?>
                            </span>
                        </td>
                        <td><?php echo e(date('Y-m-d', strtotime($u['created_at']))); ?></td>
                        <td>
                            <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this user? Their borrow history will also be removed.');">
                                    <input type="hidden" name="form_action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            <?php else: ?>
                                <span style="color: var(--muted); font-size:12px;">(you)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
