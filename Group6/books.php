<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$availability = $_GET['availability'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

try {
    $stmt = $pdo->query("SELECT DISTINCT category FROM books ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
        $search_term = "%$search%";
        $params = array_merge($params, [$search_term, $search_term, $search_term]);
    }

    if (!empty($category)) {
        $where_conditions[] = "category = ?";
        $params[] = $category;
    }

    if ($availability === 'available') {
        $where_conditions[] = "quantity > 0";
    } elseif ($availability === 'unavailable') {
        $where_conditions[] = "quantity = 0";
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    $count_sql = "SELECT COUNT(*) FROM books $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_books = $stmt->fetchColumn();
    $total_pages = ceil($total_books / $per_page);

    $sql = "SELECT * FROM books $where_clause ORDER BY title LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $params[] = $per_page;
    $params[] = $offset;
    $stmt->execute($params);
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

function getPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Books - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
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
                <li class="nav-item"><a class="nav-link" href="borrowed.php"><i class="fas fa-list me-1"></i> My Borrowed Books</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user me-1"></i> Profile</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="books-container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-5" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-5" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search and Filters -->
    <div class="card search-card mb-4 mt-5">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by title, author, or ISBN"
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="availability" class="form-select">
                        <option value="">All Books</option>
                        <option value="available" <?php echo $availability === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="unavailable" <?php echo $availability === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Books Grid -->
    <?php if (empty($books)): ?>
        <div class="text-center py-5">
            <i class="fas fa-book-open fa-4x text-muted mb-4"></i>
            <h4 class="text-muted">No books found</h4>
            <p class="text-muted">Try adjusting your search filters</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php foreach ($books as $book): ?>
                <div class="col">
                    <div class="card h-100 book-card position-relative">
                        <span class="badge <?php echo $book['quantity'] > 0 ? 'bg-success' : 'bg-danger'; ?> availability-badge">
                            <?php echo $book['quantity'] > 0 ? 'Available' : 'Unavailable'; ?>
                        </span>
                        <div class="d-flex justify-content-center align-items-center" style="height:220px; background:#f1f3f5;">
                            <img src="<?php echo htmlspecialchars($book['cover_image'] ?? 'assets/images/default-book.jpg'); ?>" 
                                 class="book-cover" alt="Book cover" style="max-height:210px; max-width:90%; object-fit:cover; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.07);">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-dark mb-1"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="card-text text-muted mb-1">By <?php echo htmlspecialchars($book['author']); ?></p>
                            <p class="card-text"><small class="text-muted">Category: <?php echo htmlspecialchars($book['category']); ?></small></p>
                        </div>
                        <div class="card-footer bg-transparent d-flex flex-column gap-2">
                            <a href="book-details.php?id=<?php echo $book['id']; ?>" class="btn btn-info text-white">
                                <i class="fas fa-info-circle me-2"></i>View Details
                            </a>
                            <?php
                            $stmt = $pdo->prepare("SELECT id FROM borrowings WHERE user_id = ? AND book_id = ? AND status = 'borrowed'");
                            $stmt->execute([$_SESSION['user_id'], $book['id']]);
                            $is_borrowed = $stmt->fetch();
                            ?>
                            <?php if ($is_borrowed): ?>
                                <form action="borrow.php" method="post">
                                    <input type="hidden" name="action" value="return">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-undo-alt me-2"></i>Return Book
                                    </button>
                                </form>
                            <?php elseif ($book['quantity'] > 0): ?>
                                <!-- Always show borrow/return date fields for available books -->
                                <form action="borrow.php" method="post">
                                    <input type="hidden" name="action" value="borrow">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <div class="mb-2">
                                        <label for="borrow_date_<?php echo $book['id']; ?>" class="form-label mb-1">Borrow Date</label>
                                        <input type="date" name="borrow_date" id="borrow_date_<?php echo $book['id']; ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="due_date_<?php echo $book['id']; ?>" class="form-label mb-1">Return Date</label>
                                        <input type="date" name="due_date" id="due_date_<?php echo $book['id']; ?>" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-book-reader me-2"></i>Borrow Book
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Book navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo getPaginationUrl($page - 1); ?>">
                            <i class="fas fa-chevron-left me-1"></i> Previous
                        </a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo getPaginationUrl($i); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo getPaginationUrl($page + 1); ?>">
                            Next <i class="fas fa-chevron-right ms-1"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
