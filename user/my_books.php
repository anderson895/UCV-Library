<?php
// =====================================================
// My Borrowed Books (with Return action)
// =====================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_user();

$user_id = $_SESSION['user_id'];

// Handle return action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_borrow_id'])) {
    $borrow_id = (int)$_POST['return_borrow_id'];

    // Make sure this borrow record belongs to the logged-in user
    $stmt = mysqli_prepare($conn, "SELECT id, book_id, status FROM borrows WHERE id = ? AND user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ii', $borrow_id, $user_id);
    mysqli_stmt_execute($stmt);
    $borrow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$borrow) {
        flash_set('error', 'Borrow record not found.');
    } elseif ($borrow['status'] === 'returned') {
        flash_set('warning', 'This book has already been returned.');
    } else {
        $today = date('Y-m-d');
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, "UPDATE borrows SET status = 'returned', return_date = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $today, $borrow_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn, "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $borrow['book_id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($conn);
            flash_set('success', 'Book returned successfully. Thank you!');
        } catch (Exception $ex) {
            mysqli_rollback($conn);
            flash_set('error', 'Could not return the book. Please try again.');
        }
    }
    header('Location: my_books.php');
    exit;
}

// Auto-mark overdue
$today = date('Y-m-d');
$stmt = mysqli_prepare($conn, "UPDATE borrows SET status = 'overdue' WHERE user_id = ? AND status = 'borrowed' AND due_date < ?");
mysqli_stmt_bind_param($stmt, 'is', $user_id, $today);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Fetch all of this user's borrows (newest first)
$stmt = mysqli_prepare($conn, "
    SELECT br.id, br.borrow_date, br.due_date, br.return_date, br.status,
           b.title, b.author, b.isbn
    FROM borrows br
    JOIN books b ON b.id = br.book_id
    WHERE br.user_id = ?
    ORDER BY br.id DESC
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$rows = mysqli_stmt_get_result($stmt);

$page_title = 'My Books';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">My Borrowed Books</h1>

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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($rows) === 0): ?>
                <tr><td colspan="7" style="text-align:center; padding:24px;">
                    You haven't borrowed any books yet.
                    <a href="<?php echo base_url('user/books.php'); ?>">Browse books &rarr;</a>
                </td></tr>
            <?php else: ?>
                <?php while ($r = mysqli_fetch_assoc($rows)): ?>
                    <tr>
                        <td><strong><?php echo e($r['title']); ?></strong></td>
                        <td><?php echo e($r['author']); ?></td>
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
                                <form method="post" style="margin:0;" onsubmit="return confirm('Return this book?');">
                                    <input type="hidden" name="return_borrow_id" value="<?php echo (int)$r['id']; ?>">
                                    <button class="btn btn-sm btn-gold" type="submit">Return</button>
                                </form>
                            <?php else: ?>
                                <span style="color: var(--muted); font-size: 12px;">&#10003; Done</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
