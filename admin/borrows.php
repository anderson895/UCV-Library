<?php
// =====================================================
// Admin: All Borrow Records (force-return capability)
// =====================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

// Auto-flag overdue
mysqli_query($conn, "UPDATE borrows SET status = 'overdue' WHERE status = 'borrowed' AND due_date < CURDATE()");

// Force-return action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'return') {
    $borrow_id = (int)$_POST['id'];
    $stmt = mysqli_prepare($conn, "SELECT book_id, status FROM borrows WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $borrow_id);
    mysqli_stmt_execute($stmt);
    $br = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($br && $br['status'] !== 'returned') {
        $today = date('Y-m-d');
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, "UPDATE borrows SET status = 'returned', return_date = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $today, $borrow_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn, "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $br['book_id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($conn);
            flash_set('success', 'Marked as returned.');
        } catch (Exception $ex) {
            mysqli_rollback($conn);
            flash_set('error', 'Could not mark as returned.');
        }
    }
    header('Location: borrows.php');
    exit;
}

// Filter by status
$filter = $_GET['status'] ?? 'all';
$where = '';
$params = [];
$types = '';
if (in_array($filter, ['borrowed', 'returned', 'overdue'], true)) {
    $where = " WHERE br.status = ? ";
    $params[] = $filter;
    $types .= 's';
}

$sql = "
    SELECT br.id, br.borrow_date, br.due_date, br.return_date, br.status,
           u.full_name, u.username,
           b.title, b.author
    FROM borrows br
    JOIN users u ON u.id = br.user_id
    JOIN books b ON b.id = br.book_id
    $where
    ORDER BY br.id DESC
";
$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$rows = mysqli_stmt_get_result($stmt);

$page_title = 'All Borrow Records';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">All Borrow Records</h1>

<div class="toolbar">
    <div>
        <a class="btn btn-sm <?php echo $filter==='all'      ? '' : 'btn-outline'; ?>" href="borrows.php">All</a>
        <a class="btn btn-sm <?php echo $filter==='borrowed' ? '' : 'btn-outline'; ?>" href="borrows.php?status=borrowed">Borrowed</a>
        <a class="btn btn-sm <?php echo $filter==='overdue'  ? 'btn-danger' : 'btn-outline'; ?>" href="borrows.php?status=overdue">Overdue</a>
        <a class="btn btn-sm <?php echo $filter==='returned' ? 'btn-success' : 'btn-outline'; ?>" href="borrows.php?status=returned">Returned</a>
    </div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Book</th>
                <th>Borrowed</th>
                <th>Due</th>
                <th>Returned</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($rows) === 0): ?>
                <tr><td colspan="8" style="text-align:center; padding:24px;">No records found.</td></tr>
            <?php else: ?>
                <?php while ($r = mysqli_fetch_assoc($rows)): ?>
                    <tr>
                        <td>#<?php echo (int)$r['id']; ?></td>
                        <td>
                            <strong><?php echo e($r['full_name']); ?></strong><br>
                            <small style="color: var(--muted);"><?php echo e($r['username']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo e($r['title']); ?></strong><br>
                            <small style="color: var(--muted);"><?php echo e($r['author']); ?></small>
                        </td>
                        <td><?php echo e($r['borrow_date']); ?></td>
                        <td><?php echo e($r['due_date']); ?></td>
                        <td><?php echo $r['return_date'] ? e($r['return_date']) : '&mdash;'; ?></td>
                        <td>
                            <?php
                            $cls = $r['status'] === 'returned' ? 'badge-success'
                                 : ($r['status'] === 'overdue' ? 'badge-danger' : 'badge-warning');
                            ?>
                            <span class="badge <?php echo $cls; ?>"><?php echo e(ucfirst($r['status'])); ?></span>
                        </td>
                        <td>
                            <?php if ($r['status'] !== 'returned'): ?>
                                <form method="post" style="margin:0;" onsubmit="return confirm('Mark as returned?');">
                                    <input type="hidden" name="form_action" value="return">
                                    <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                    <button class="btn btn-sm btn-success" type="submit">Mark Returned</button>
                                </form>
                            <?php else: ?>
                                <span style="color: var(--muted); font-size:12px;">&#10003; Closed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
