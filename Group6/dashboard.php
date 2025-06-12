<?php
    session_start();
    require_once 'config.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Fetch user information
    try {
        $user_id = $_SESSION['user_id'];
        $is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
        // Get user details
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            session_destroy();
            header("Location: login.php");
            exit();
        }

        // Get borrowed books count
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM borrowings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_borrowed = $stmt->fetch()['total'];

        // Get recently borrowed books (limit 5)
        $stmt = $pdo->prepare("
            SELECT b.*, br.borrow_date, br.due_date, br.status 
            FROM borrowings br 
            JOIN books b ON br.book_id = b.id 
            WHERE br.user_id = ? 
            ORDER BY br.borrow_date DESC 
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $borrowed_books = $stmt->fetchAll();

    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Library Management System</title>
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="books.php">
                            <i class="fas fa-book me-1"></i> Books
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="borrowed.php">
                            <i class="fas fa-list me-1"></i> My borrowed Books
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

    <div class="dashboard-container">
        <div class="row">
            <!-- Left Column - Profile and Stats -->
            <div class="col-md-4">
                <!-- Profile Section -->
                <div class="stats-card">
                    <div class="profile-section">
                        <div class="profile-image">
                            <?php 
                            $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                            echo $initials; 
                            ?>
                        </div>
                        <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <p class="text-muted">
                            Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                        </p>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="stats-card">
                    <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i> Your Stats</h5>
                    <div class="row text-center">
                        <div class="col-12">
                            <h4><?php echo $total_borrowed; ?></h4>
                            <small class="text-muted">Books Borrowed</small>
                        </div>
                    </div>
                </div>

                <?php if ($is_admin): ?>
                <!-- Admin Quick Links -->
                <div class="stats-card bg-light border border-warning">
                    <h5 class="mb-3 text-warning"><i class="fas fa-user-shield me-2"></i> Admin Panel</h5>
                    <div class="d-grid gap-2">
                        <a href="admin-dashboard.php" class="btn btn-warning"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</a>
                        <a href="manage-books.php" class="btn btn-warning"><i class="fas fa-book me-2"></i>Manage Books</a>
                        <a href="manage-users.php" class="btn btn-warning"><i class="fas fa-users me-2"></i>Manage Users</a>
                        <a href="admin-borrowings.php" class="btn btn-warning"><i class="fas fa-undo-alt me-2"></i>Manage Returns</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column - Main Content -->
            <div class="col-md-8">
                <!-- Welcome Message -->
                <div class="stats-card">
                    <h2>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
                    <p class="text-muted">Here's your recent activity</p>
                </div>

                <!-- Currently Borrowed Books -->
                <div class="stats-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-book-reader me-2"></i> Recently Borrowed Books</h5>
                        <a href="borrowed.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <?php if (!empty($borrowed_books)): ?>
                        <?php foreach ($borrowed_books as $book): ?>
                            <div class="book-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6><?php echo htmlspecialchars($book['title']); ?></h6>
                                        <small class="text-muted">
                                            Due: <?php echo date('M d, Y', strtotime($book['due_date'])); ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $book['status'] === 'overdue' ? 'danger' : 'success'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($book['status'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No books currently borrowed</p>
                            <a href="books.php" class="btn btn-primary">Browse Books</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple animation for cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stats-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>