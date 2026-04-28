<?php
// =====================================================
// Admin Dashboard
// =====================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Auto-flag overdue
mysqli_query($conn, "UPDATE borrows SET status = 'overdue' WHERE status = 'borrowed' AND due_date < CURDATE()");

// Stats
$total_books   = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM books"))['c'];
$total_copies  = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_copies) AS c FROM books"))['c'] ?? 0);
$total_users   = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role = 'user'"))['c'];
$active_borrow = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM borrows WHERE status IN ('borrowed','overdue')"))['c'];
$overdue       = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM borrows WHERE status = 'overdue'"))['c'];

// Recent borrows
$recent = mysqli_query($conn, "
    SELECT br.id, br.borrow_date, br.due_date, br.status,
           u.full_name, b.title
    FROM borrows br
    JOIN users u ON u.id = br.user_id
    JOIN books b ON b.id = br.book_id
    ORDER BY br.id DESC
    LIMIT 8
");

$page_title = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Admin Dashboard</h1>

<div class="stats">
    <div class="stat-card">
        <div class="stat-label">Total Book Titles</div>
        <div class="stat-value"><?php echo $total_books; ?></div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Total Copies</div>
        <div class="stat-value"><?php echo $total_copies; ?></div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Registered Users</div>
        <div class="stat-value"><?php echo $total_users; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Borrows</div>
        <div class="stat-value"><?php echo $active_borrow; ?></div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Overdue</div>
        <div class="stat-value"><?php echo $overdue; ?></div>
    </div>
</div>

<div class="card">
    <h2 class="card-title">Quick Actions</h2>
    <a class="btn" href="<?php echo base_url('admin/books.php'); ?>">Manage Books</a>
    <a class="btn btn-gold" href="<?php echo base_url('admin/users.php'); ?>">Manage Users</a>
    <a class="btn btn-outline" href="<?php echo base_url('admin/borrows.php'); ?>">View All Borrows</a>
</div>

<div class="card">
    <h2 class="card-title">Recent Borrow Activity</h2>
    <?php if (mysqli_num_rows($recent) === 0): ?>
        <p>No borrow activity yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Book</th>
                        <th>Borrowed</th>
                        <th>Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = mysqli_fetch_assoc($recent)): ?>
                        <tr>
                            <td><?php echo e($r['full_name']); ?></td>
                            <td><?php echo e($r['title']); ?></td>
                            <td><?php echo e($r['borrow_date']); ?></td>
                            <td><?php echo e($r['due_date']); ?></td>
                            <td>
                                <?php
                                $cls = $r['status'] === 'returned' ? 'badge-success'
                                     : ($r['status'] === 'overdue' ? 'badge-danger' : 'badge-warning');
                                ?>
                                <span class="badge <?php echo $cls; ?>"><?php echo e(ucfirst($r['status'])); ?></span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
