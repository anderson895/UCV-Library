<?php
// =====================================================
// User Dashboard
// =====================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_user();

$user_id = $_SESSION['user_id'];

// Count borrowed (currently checked out)
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM borrows WHERE user_id = ? AND status = 'borrowed'");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$active_borrows = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];
mysqli_stmt_close($stmt);

// Count total returned
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM borrows WHERE user_id = ? AND status = 'returned'");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$returned = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];
mysqli_stmt_close($stmt);

// Total available books
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(available_copies) AS c FROM books"));
$available = (int)($row['c'] ?? 0);

// Recent borrows (last 5)
$stmt = mysqli_prepare($conn, "
    SELECT b.title, b.author, br.borrow_date, br.due_date, br.return_date, br.status
    FROM borrows br
    JOIN books b ON b.id = br.book_id
    WHERE br.user_id = ?
    ORDER BY br.id DESC
    LIMIT 5
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$recent = mysqli_stmt_get_result($stmt);

$page_title = 'My Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Welcome, <?php echo e($_SESSION['full_name']); ?>!</h1>

<div class="stats">
    <div class="stat-card">
        <div class="stat-label">Currently Borrowed</div>
        <div class="stat-value"><?php echo $active_borrows; ?></div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Books Returned</div>
        <div class="stat-value"><?php echo $returned; ?></div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Books Available in Library</div>
        <div class="stat-value"><?php echo $available; ?></div>
    </div>
</div>

<div class="card">
    <h2 class="card-title">Quick Actions</h2>
    <a class="btn" href="<?php echo base_url('user/books.php'); ?>">Browse Books</a>
    <a class="btn btn-gold" href="<?php echo base_url('user/my_books.php'); ?>">View My Borrowed Books</a>
</div>

<div class="card">
    <h2 class="card-title">Recent Activity</h2>
    <?php if (mysqli_num_rows($recent) === 0): ?>
        <p>You haven't borrowed any books yet. <a href="<?php echo base_url('user/books.php'); ?>">Browse books &rarr;</a></p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Borrowed</th>
                        <th>Due</th>
                        <th>Returned</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($recent)): ?>
                        <tr>
                            <td><?php echo e($row['title']); ?></td>
                            <td><?php echo e($row['author']); ?></td>
                            <td><?php echo e($row['borrow_date']); ?></td>
                            <td><?php echo e($row['due_date']); ?></td>
                            <td><?php echo $row['return_date'] ? e($row['return_date']) : '&mdash;'; ?></td>
                            <td>
                                <?php
                                $cls = $row['status'] === 'returned' ? 'badge-success'
                                     : ($row['status'] === 'overdue' ? 'badge-danger' : 'badge-warning');
                                ?>
                                <span class="badge <?php echo $cls; ?>"><?php echo e(ucfirst($row['status'])); ?></span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
