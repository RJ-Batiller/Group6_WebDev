<?php
session_start();
require_once 'config.php';

// Only allow admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin-login.php");
    exit();
}

// Handle status update BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrowing_id'], $_POST['status'])) {
    $borrowing_id = $_POST['borrowing_id'];
    $status = $_POST['status'];
    $return_date = ($status === 'returned') ? date('Y-m-d H:i:s') : null;
    try {
        if ($status === 'returned') {
            $stmt = $pdo->prepare("UPDATE borrowings SET status = ?, return_date = ? WHERE id = ?");
            $stmt->execute([$status, $return_date, $borrowing_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE borrowings SET status = ?, return_date = NULL WHERE id = ?");
            $stmt->execute([$status, $borrowing_id]);
        }
        $_SESSION['success'] = 'Borrowing status updated.';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error updating status.';
    }
    header('Location: admin-borrowings.php');
    exit();
}

// Fetch all borrowings with user and book info
$stmt = $pdo->query("SELECT b.id, u.username, u.first_name, u.last_name, bk.title, b.borrow_date, b.due_date, b.return_date, b.status FROM borrowings b JOIN users u ON b.user_id = u.id JOIN books bk ON b.book_id = bk.id ORDER BY b.borrow_date DESC");
$borrowings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Borrowings - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href='style.css' rel='stylesheet'>
</head>
<body>
    <!-- Fixed Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="admin-dashboard.php">
                <i class="fas fa-user-shield me-2"></i>Library Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="admin-dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage-books.php"><i class="fas fa-book me-1"></i> Manage Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage-users.php"><i class="fas fa-users me-1"></i> Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin-borrowings.php"><i class="fas fa-list me-1"></i> Manage Borrowings</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="admin-container py-4" style="margin-top: 80px;">
        <div class="admin-header mb-4">
            <h2>All Borrowings</h2>
        </div>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <div class="card admin-card">
            <div class="card-body p-0">
                <table class="table table-bordered table-hover admin-table mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Book</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($borrowings)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No borrowings found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($borrowings as $b): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($b['username'] . ' (' . $b['first_name'] . ' ' . $b['last_name'] . ')'); ?></td>
                                <td><?php echo htmlspecialchars($b['title']); ?></td>
                                <td><?php echo htmlspecialchars($b['borrow_date']); ?></td>
                                <td><?php echo htmlspecialchars($b['due_date']); ?></td>
                                <td><?php echo $b['return_date'] ? htmlspecialchars($b['return_date']) : '-'; ?></td>
                                <td>
                                    <span class="badge <?php
                                        if ($b['status'] === 'returned') echo 'bg-success';
                                        elseif ($b['status'] === 'overdue') echo 'bg-danger';
                                        else echo 'bg-warning text-dark';
                                    ?>">
                                        <?php echo htmlspecialchars(ucfirst($b['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="admin-borrowings.php" method="post" style="display:inline;">
                                        <input type="hidden" name="borrowing_id" value="<?php echo $b['id']; ?>">
                                        <select name="status" class="form-select form-select-sm d-inline w-auto" style="min-width:120px;display:inline-block;">
                                            <option value="borrowed" <?php if($b['status']==='borrowed') echo 'selected'; ?>>Borrowed</option>
                                            <option value="overdue" <?php if($b['status']==='overdue') echo 'selected'; ?>>Overdue</option>
                                            <option value="returned" <?php if($b['status']==='returned') echo 'selected'; ?>>Returned</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm ms-1 btn-action">Save</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
