<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin-login.php");
    exit();
}

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_report'])) {
    $report_type = $_POST['report_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    switch ($report_type) {
        case 'borrowing':
            $stmt = $pdo->prepare("
                SELECT 
                    b.title,
                    b.author,
                    COUNT(*) as borrow_count,
                    SUM(CASE WHEN br.status = 'overdue' THEN 1 ELSE 0 END) as overdue_count
                FROM borrowings br
                JOIN books b ON br.book_id = b.id
                WHERE br.borrow_date BETWEEN ? AND ?
                GROUP BY b.id
                ORDER BY borrow_count DESC
            ");
            break;
            
        case 'inventory':
            $stmt = $pdo->prepare("
                SELECT 
                    b.*,
                    (
                        SELECT COUNT(*) 
                        FROM borrowings br 
                        WHERE br.book_id = b.id 
                        AND br.status IN ('borrowed', 'overdue')
                    ) as currently_borrowed
                FROM books b
                ORDER BY b.quantity DESC
            ");
            break;
            
        case 'user_activity':
            $stmt = $pdo->prepare("
                SELECT 
                    u.username,
                    u.email,
                    COUNT(br.id) as total_borrows,
                    SUM(CASE WHEN br.status = 'overdue' THEN 1 ELSE 0 END) as total_overdue
                FROM users u
                LEFT JOIN borrowings br ON u.id = br.user_id
                WHERE br.borrow_date BETWEEN ? AND ?
                GROUP BY u.id
                ORDER BY total_borrows DESC
            ");
            break;
    }
    
    if (isset($stmt)) {
        if ($report_type === 'inventory') {
            $stmt->execute();
        } else {
            $stmt->execute([$start_date, $end_date]);
        }
        $report_data = $stmt->fetchAll();
        
        // Store report in database
        $content = json_encode($report_data);
        $stmt = $pdo->prepare("
            INSERT INTO reports (title, type, content, generated_by) 
            VALUES (?, ?, ?, ?)
        ");
        $title = ucfirst($report_type) . " Report - " . date('Y-m-d H:i:s');
        $stmt->execute([$title, $report_type, $content, $_SESSION['user_id']]);
    }
}

// Fetch recent reports
$stmt = $pdo->prepare("
    SELECT r.*, u.username
    FROM reports r
    JOIN users u ON r.generated_by = u.id
    ORDER BY r.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_reports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-book-reader me-2"></i>Library System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-books.php">
                            <i class="fas fa-book me-1"></i>Manage Books
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Reports</h2>

        <div class="row">
            <div class="col-md-4">
                <!-- Generate Report Form -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Generate Report</h5>
                    </div>
                    <div class="card-body">
                        <form action="reports.php" method="post">
                            <div class="mb-3">
                                <label class="form-label">Report Type</label>
                                <select name="report_type" class="form-select" required>
                                    <option value="borrowing">Borrowing Statistics</option>
                                    <option value="inventory">Inventory Status</option>
                                    <option value="user_activity">User Activity</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="generate_report" class="btn btn-primary">
                                    <i class="fas fa-file-alt me-2"></i>Generate Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <!-- Recent Reports -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Generated By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_reports as $report): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($report['title']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $report['type'] === 'borrowing' ? 'primary' : 
                                                    ($report['type'] === 'inventory' ? 'success' : 'info'); 
                                            ?>">
                                                <?php echo ucfirst(htmlspecialchars($report['type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($report['username']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($report['created_at'])); ?></td>
                                        <td>
                                            <a href="view-report.php?id=<?php echo $report['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                            <a href="download-report.php?id=<?php echo $report['id']; ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>