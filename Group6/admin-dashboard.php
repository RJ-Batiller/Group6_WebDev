<?php
require_once 'config.php';
session_start();

// Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin-login.php');
    exit();
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT id, username, email, first_name, last_name, status, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $error = "Failed to fetch users: " . $e->getMessage();
}

// Recommend: show pending users for approval
$pendingUsers = array_filter($users, function($u) { return $u['status'] === 'pending'; });

// Top 5 active users (by borrow count) — fixed LEFT JOIN here
try {
    $topUsersStmt = $pdo->query("
        SELECT u.id, u.username, u.first_name, u.last_name, COUNT(b.id) as borrow_count 
        FROM users u 
        LEFT JOIN borrowings b ON u.id = b.user_id 
        GROUP BY u.id 
        ORDER BY borrow_count DESC 
        LIMIT 5
    ");
    $topUsers = $topUsersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $topUsers = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">    
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
                    <li class="nav-item"><a class="nav-link" href="admin-borrowings.php"><i class="fas fa-list me-1"></i> Manage Borrowings</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="admin-container" style="margin-top: 80px;">
        <div class="admin-header d-flex justify-content-between align-items-center">
            <h2><i class="fas fa-user-shield me-2"></i>Admin Dashboard</h2>
        </div>
        <div class="user-info">
            <h4>Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</h4>
            <p class="text-muted">You have full administrative privileges</p>
            <div class="d-flex gap-3">
                <span class="text-muted">Role: <span class="badge bg-danger">Admin</span></span>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <!-- All Users Card -->
                <div class="admin-card mb-4">
                    <div class="admin-card-header bg-primary text-white p-3">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            <td><span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'pending' ? 'warning' : 'secondary'); ?>"><?php echo htmlspecialchars(ucfirst($user['status'])); ?></span></td>
                                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <!-- Recommendations Card -->
                <div class="admin-card mb-4">
                    <div class="admin-card-header bg-warning text-dark p-3">
                        <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Pending User Approvals</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pendingUsers)): ?>
                            <div class="text-muted">No pending users.</div>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($pendingUsers as $pending): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($pending['username']); ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Top Active Users Card -->
                <div class="admin-card">
                    <div class="admin-card-header bg-success text-white p-3">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 5 Active Users</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($topUsers)): ?>
                            <div class="text-muted">No data available.</div>
                        <?php else: ?>
                            <ol class="mb-0">
                                <?php foreach ($topUsers as $top): ?>
                                    <li><?php echo htmlspecialchars($top['first_name'] . ' ' . $top['last_name']); ?> <span class="text-muted">(<?php echo $top['borrow_count']; ?> borrows)</span></li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
