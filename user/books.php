<?php
// =====================================================
// Browse Books (user) + Borrow action
// =====================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_user();

$user_id = $_SESSION['user_id'];

// Handle borrow request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_book_id'])) {
    $book_id = (int)$_POST['borrow_book_id'];

    // Get the book and check availability
    $stmt = mysqli_prepare($conn, "SELECT id, title, available_copies FROM books WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $book_id);
    mysqli_stmt_execute($stmt);
    $book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$book) {
        flash_set('error', 'Book not found.');
    } elseif ($book['available_copies'] < 1) {
        flash_set('error', 'Sorry, no copies of this book are available right now.');
    } else {
        // Check if user already has a non-returned copy of this book
        $stmt = mysqli_prepare($conn, "SELECT id FROM borrows WHERE user_id = ? AND book_id = ? AND status = 'borrowed' LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'ii', $user_id, $book_id);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($exists) {
            flash_set('warning', 'You already have this book borrowed. Please return it first.');
        } else {
            // Create the borrow record (7-day loan)
            $borrow_date = date('Y-m-d');
            $due_date    = date('Y-m-d', strtotime('+7 days'));

            mysqli_begin_transaction($conn);
            try {
                $stmt = mysqli_prepare($conn, "INSERT INTO borrows (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
                mysqli_stmt_bind_param($stmt, 'iiss', $user_id, $book_id, $borrow_date, $due_date);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $stmt = mysqli_prepare($conn, "UPDATE books SET available_copies = available_copies - 1 WHERE id = ? AND available_copies > 0");
                mysqli_stmt_bind_param($stmt, 'i', $book_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                mysqli_commit($conn);
                flash_set('success', 'Borrowed "' . $book['title'] . '". Please return by ' . $due_date . '.');
            } catch (Exception $ex) {
                mysqli_rollback($conn);
                flash_set('error', 'Could not borrow the book. Please try again.');
            }
        }
    }
    header('Location: books.php');
    exit;
}

// Search filter
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? ORDER BY title ASC");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $books = mysqli_stmt_get_result($stmt);
} else {
    $books = mysqli_query($conn, "SELECT * FROM books ORDER BY title ASC");
}

$page_title = 'Browse Books';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Browse Books</h1>

<div class="toolbar">
    <form class="search-form" method="get" action="books.php">
        <input type="text" name="q" placeholder="Search by title, author, category..."
               value="<?php echo e($search); ?>">
        <button type="submit" class="btn btn-sm">Search</button>
        <?php if ($search): ?>
            <a class="btn btn-sm btn-outline" href="books.php">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>ISBN</th>
                <th>Available</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($books) === 0): ?>
                <tr><td colspan="6" style="text-align:center; padding:24px;">No books found.</td></tr>
            <?php else: ?>
                <?php while ($b = mysqli_fetch_assoc($books)): ?>
                    <tr>
                        <td><strong><?php echo e($b['title']); ?></strong></td>
                        <td><?php echo e($b['author']); ?></td>
                        <td><?php echo e($b['category']); ?></td>
                        <td><?php echo e($b['isbn']); ?></td>
                        <td>
                            <?php if ($b['available_copies'] > 0): ?>
                                <span class="badge badge-success"><?php echo (int)$b['available_copies']; ?></span>
                            <?php else: ?>
                                <span class="badge badge-danger">Out of stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($b['available_copies'] > 0): ?>
                                <form method="post" style="margin:0;">
                                    <input type="hidden" name="borrow_book_id" value="<?php echo (int)$b['id']; ?>">
                                    <button class="btn btn-sm btn-success" type="submit">Borrow</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-sm" disabled style="opacity:0.5; cursor:not-allowed;">Unavailable</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
