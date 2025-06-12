<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin-login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $user_id = $_POST['user_id'];
        
        switch ($_POST['action']) {
            case 'activate':
                $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['success'] = "User activated successfully.";
                break;
                
            case 'suspend':
                $stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['success'] = "User suspended successfully.";
                break;
                
            case 'delete':
                // Check if user has any active borrowings
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND status = 'borrowed'");
                $stmt->execute([$user_id]);
                if ($stmt->fetchColumn() == 0) {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $_SESSION['success'] = "User deleted successfully.";
                } else {
                    $_SESSION['error'] = "Cannot delete user with active borrowings.";
                }
                break;
        }
        
        header("Location: manage-users.php");
        exit();
    }
}

// === FIXED QUERY ===
// Fetch all users including pending ones (no status filter)
$stmt = $pdo->prepare("
    SELECT 
        u.*,
        r.name as role_name,
        (SELECT COUNT(*) FROM borrowings WHERE user_id = u.id AND status = 'borrowed') as active_borrowings,
        (SELECT COUNT(*) FROM borrowings WHERE user_id = u.id AND status = 'overdue') as overdue_books
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.id != ?
    ORDER BY u.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
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
                    <li class="nav-item"><a class="nav-link active" href="manage-books.php"><i class="fas fa-book me-1"></i> Manage Books</a></li>
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
        <div class="admin-header d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0 text-gray-800"><i class="fas fa-users me-2"></i>Manage Users</h2>
            <div class="text-end">
                <p class="mb-0 text-gray-600">Total Users: <strong><?php echo count($users); ?></strong></p>
            </div>
        </div>

        <?php if (isset($_SESSION['success']) && strpos($_SESSION['success'], 'Admin account created') === false): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Users Table Card -->
        <div class="admin-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-user-friends me-2"></i>User Management</h5>
                    <span class="badge bg-light text-dark"><?php echo count($users); ?> records</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Borrowings</th>
                                <th>Overdue</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-user-slash fa-2x mb-2"></i><br>
                                        No users found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                        <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php 
                                            $role_name = $user['role_name'] ?? 'unknown';
                                            $badge_class = ($role_name === 'admin') ? 'danger' : 'primary';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>">
                                            <?php echo ucfirst(htmlspecialchars($role_name)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $user['status'] === 'active' ? 'success' : 
                                                ($user['status'] === 'suspended' ? 'danger' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['active_borrowings'] > 0): ?>
                                            <span class="badge bg-primary"><?php echo $user['active_borrowings']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['overdue_books'] > 0): ?>
                                            <span class="badge bg-danger"><?php echo $user['overdue_books']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap">
                                            <?php if ($user['status'] === 'pending' || $user['status'] === 'suspended'): ?>
                                                <form action="manage-users.php" method="post" class="me-1 mb-1">
                                                    <input type="hidden" name="action" value="activate">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn btn-success btn-sm btn-action">
                                                        <i class="fas fa-check me-1"></i>Activate
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($user['status'] === 'active'): ?>
                                                <form action="manage-users.php" method="post" class="me-1 mb-1">
                                                    <input type="hidden" name="action" value="suspend">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm btn-action">
                                                        <i class="fas fa-ban me-1"></i>Suspend
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($user['active_borrowings'] == 0): ?>
                                                <form action="manage-users.php" method="post" class="me-1 mb-1">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm btn-action" 
                                                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
