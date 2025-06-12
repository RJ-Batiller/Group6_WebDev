<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate book ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid book ID.";
    header("Location: books.php");
    exit();
}

$book_id = (int)$_GET['id'];

try {
    // Fetch book details
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if (!$book) {
        $_SESSION['error'] = "Book not found.";
        header("Location: books.php");
        exit();
    }

    // Check if user has borrowed this book
    $stmt = $pdo->prepare("SELECT id FROM borrowings WHERE user_id = ? AND book_id = ? AND status = 'borrowed'");
    $stmt->execute([$_SESSION['user_id'], $book_id]);
    $is_borrowed = $stmt->fetch();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Details - <?php echo htmlspecialchars($book['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-book-reader me-2"></i>Library System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="books.php"><i class="fas fa-book me-1"></i> Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="borrowed.php"><i class="fas fa-list me-1"></i> My Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user me-1"></i> Profile</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container book-details-page py-5 mt-5">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="<?php echo htmlspecialchars($book['cover_image'] ?? 'assets/images/default-book.jpg'); ?>" 
                         class="img-fluid rounded shadow" alt="Book cover">
                </div>
<div class="col-md-8 text-white">
    <h2><?php echo htmlspecialchars($book['title']); ?></h2>
    <p class="text-light-emphasis">By <?php echo htmlspecialchars($book['author']); ?></p>
    <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></p>
    <p><strong>Available Quantity:</strong> <?php echo $book['quantity']; ?></p>
    <p><strong>Description:</strong></p>
    <p><?php echo nl2br(htmlspecialchars($book['description'] ?? 'No description available.')); ?></p>

    <!-- Aligned Buttons -->
    <div class="mt-4 d-flex flex-wrap gap-3 align-items-center">
        <?php if ($is_borrowed): ?>
            <form action="borrow.php" method="post">
                <input type="hidden" name="action" value="return">
                <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-undo-alt me-2"></i>Return Book
                </button>
            </form>
        <?php elseif ($book['quantity'] > 0): ?>
            <form action="borrow.php" method="post">
                <input type="hidden" name="action" value="borrow">
                <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-book-reader me-2"></i>Borrow Book
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger mt-2">This book is currently unavailable.</div>
        <?php endif; ?>

        <a href="books.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Books
        </a>
    </div>
</div>

            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
