<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's borrowed books
$stmt = $pdo->prepare("
    SELECT 
        b.id as borrowing_id,
        books.*,
        b.borrow_date,
        b.due_date,
        b.return_date,
        b.status
    FROM borrowings b
    JOIN books ON b.book_id = books.id
    WHERE b.user_id = ?
    ORDER BY b.borrow_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$borrowed_books = $stmt->fetchAll();

// Group books by status
$current_books = array_filter($borrowed_books, function($book) {
    return $book['status'] == 'borrowed';
});

$returned_books = array_filter($borrowed_books, function($book) {
    return $book['status'] == 'returned';
});

$overdue_books = array_filter($borrowed_books, function($book) {
    return $book['status'] == 'overdue';
});
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Borrowed Books - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Fixed Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-book-reader me-2"></i>Library System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="books.php">
                            <i class="fas fa-book me-1"></i> Books
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="borrowed.php">
                            <i class="fas fa-list me-1"></i> My Borrowed Books
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-1"></i> Profile
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="borrowed-container">
    <div class="page-header">
        <h2><i class="fas fa-book-reader me-2"></i>My Borrowed Books</h2>
    </div>
        <?php if (!empty($current_books)): ?>
        <div class="card book-card">
            <div class="book-card-header primary">
                <h5 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Currently Borrowed</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Borrowed Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($current_books as $book): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($book['cover_image'] ?? 'assets/images/default-book.jpg'); ?>" 
                                             alt="Book cover" class="book-cover me-3">
                                        <div>
                                            <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($book['author']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($book['borrow_date'])); ?></td>
                                <td>
                                    <?php 
                                    $due_date = new DateTime($book['due_date']);
                                    $now = new DateTime();
                                    $days_left = $due_date->diff($now)->days;
                                    $is_overdue = $now > $due_date;
                                    
                                    echo date('M d, Y', strtotime($book['due_date']));
                                    if ($is_overdue) {
                                        echo ' <span class="badge bg-danger">Overdue</span>';
                                    } elseif ($days_left <= 3) {
                                        echo ' <span class="badge bg-warning">Due soon</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary">Borrowed</span>
                                </td>
                                <td>
                                    <form action="borrow.php" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="return">
                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-undo-alt me-1"></i>Return
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($overdue_books)): ?>
        <div class="card book-card">
            <div class="book-card-header danger">
                <h5 class="card-title mb-0"><i class="fas fa-exclamation-circle me-2"></i>Overdue Books</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Borrowed Date</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overdue_books as $book): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($book['cover_image'] ?? 'assets/images/default-book.jpg'); ?>" 
                                             alt="Book cover" class="book-cover me-3">
                                        <div>
                                            <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($book['author']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($book['borrow_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($book['due_date'])); ?></td>
                                <td>
                                    <span class="badge bg-danger">
                                        <?php 
                                        $due_date = new DateTime($book['due_date']);
                                        $now = new DateTime();
                                        echo $due_date->diff($now)->days . ' days';
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="borrow.php" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="return">
                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-undo-alt me-1"></i>Return Now
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($returned_books)): ?>
        <div class="card book-card">
            <div class="book-card-header success">
                <h5 class="card-title mb-0"><i class="fas fa-check-circle me-2"></i>Return History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Borrowed Date</th>
                                <th>Returned Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($returned_books as $book): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($book['cover_image'] ?? 'assets/images/default-book.jpg'); ?>" 
                                             alt="Book cover" class="book-cover me-3">
                                        <div>
                                            <strong><?php echo htmlspecialchars($book['title']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($book['author']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($book['borrow_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($book['return_date'])); ?></td>
                                <td>
                                    <span class="badge bg-success">Returned</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($borrowed_books)): ?>
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h4 class="text-muted">You haven't borrowed any books yet</h4>
            <p class="text-muted">Browse our collection to find books to borrow</p>
            <a href="books.php" class="btn btn-primary">
                <i class="fas fa-search me-2"></i>Browse Books
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>