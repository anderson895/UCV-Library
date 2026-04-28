<?php
// =====================================================
// Admin: Manage Books (Add / Edit / Delete)
// =====================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$action = $_GET['action'] ?? 'list';
$edit_book = null;

// -------- Handle form submissions --------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['form_action'] ?? '';

    // Add new book
    if ($form_action === 'add') {
        $title    = trim($_POST['title'] ?? '');
        $author   = trim($_POST['author'] ?? '');
        $isbn     = trim($_POST['isbn'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $copies   = max(1, (int)($_POST['total_copies'] ?? 1));

        if ($title && $author) {
            $stmt = mysqli_prepare($conn, "INSERT INTO books (title, author, isbn, category, total_copies, available_copies) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssssii', $title, $author, $isbn, $category, $copies, $copies);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            flash_set('success', 'Book added successfully.');
        } else {
            flash_set('error', 'Title and author are required.');
        }
        header('Location: books.php');
        exit;
    }

    // Update existing book
    if ($form_action === 'edit') {
        $id       = (int)($_POST['id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $author   = trim($_POST['author'] ?? '');
        $isbn     = trim($_POST['isbn'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $copies   = max(1, (int)($_POST['total_copies'] ?? 1));

        // Get current borrowed count = total - available, so we keep available = copies - borrowed
        $stmt = mysqli_prepare($conn, "SELECT total_copies, available_copies FROM books WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $cur = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($cur) {
            $borrowed_now = (int)$cur['total_copies'] - (int)$cur['available_copies'];
            $new_available = max(0, $copies - $borrowed_now);

            $stmt = mysqli_prepare($conn, "UPDATE books SET title = ?, author = ?, isbn = ?, category = ?, total_copies = ?, available_copies = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'ssssiii', $title, $author, $isbn, $category, $copies, $new_available, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            flash_set('success', 'Book updated successfully.');
        }
        header('Location: books.php');
        exit;
    }

    // Delete book
    if ($form_action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = mysqli_prepare($conn, "DELETE FROM books WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        flash_set('success', 'Book deleted.');
        header('Location: books.php');
        exit;
    }
}

// -------- Load book for edit --------
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $edit_book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$edit_book) {
        flash_set('error', 'Book not found.');
        header('Location: books.php');
        exit;
    }
}

// -------- Search + list --------
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $books = mysqli_stmt_get_result($stmt);
} else {
    $books = mysqli_query($conn, "SELECT * FROM books ORDER BY id DESC");
}

$page_title = 'Manage Books';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Manage Books</h1>

<?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="card" style="max-width:600px;">
        <h2 class="card-title"><?php echo $action === 'edit' ? 'Edit Book' : 'Add New Book'; ?></h2>
        <form method="post" action="books.php">
            <input type="hidden" name="form_action" value="<?php echo $action === 'edit' ? 'edit' : 'add'; ?>">
            <?php if ($edit_book): ?>
                <input type="hidden" name="id" value="<?php echo (int)$edit_book['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" required
                       value="<?php echo e($edit_book['title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Author *</label>
                <input type="text" name="author" required
                       value="<?php echo e($edit_book['author'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>ISBN</label>
                <input type="text" name="isbn"
                       value="<?php echo e($edit_book['isbn'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category"
                       value="<?php echo e($edit_book['category'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Total Copies</label>
                <input type="number" name="total_copies" min="1"
                       value="<?php echo (int)($edit_book['total_copies'] ?? 1); ?>">
            </div>

            <button type="submit" class="btn"><?php echo $action === 'edit' ? 'Save Changes' : 'Add Book'; ?></button>
            <a href="books.php" class="btn btn-outline">Cancel</a>
        </form>
    </div>
<?php else: ?>

    <div class="toolbar">
        <a href="books.php?action=add" class="btn">+ Add New Book</a>
        <form class="search-form" method="get" action="books.php">
            <input type="text" name="q" placeholder="Search books..."
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
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>ISBN</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($books) === 0): ?>
                    <tr><td colspan="7" style="text-align:center; padding:24px;">No books found.</td></tr>
                <?php else: ?>
                    <?php while ($b = mysqli_fetch_assoc($books)): ?>
                        <tr>
                            <td>#<?php echo (int)$b['id']; ?></td>
                            <td><strong><?php echo e($b['title']); ?></strong></td>
                            <td><?php echo e($b['author']); ?></td>
                            <td><?php echo e($b['category']); ?></td>
                            <td><?php echo e($b['isbn']); ?></td>
                            <td><?php echo (int)$b['available_copies']; ?> / <?php echo (int)$b['total_copies']; ?></td>
                            <td>
                                <a class="btn btn-sm btn-gold" href="books.php?action=edit&id=<?php echo (int)$b['id']; ?>">Edit</a>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this book? This cannot be undone.');">
                                    <input type="hidden" name="form_action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                                    <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
