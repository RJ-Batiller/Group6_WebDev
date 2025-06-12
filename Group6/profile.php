<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Fetch user information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $error_message = "Error fetching user data: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $mobile_number = trim($_POST['mobile_number']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($mobile_number)) $errors[] = "Mobile number is required";
    if (!preg_match("/^09\d{9}$/", $mobile_number)) $errors[] = "Invalid mobile number format";

    // Check if email exists (excluding current user)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Email already exists";
    }

    // Password change validation
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to set a new password";
        } else {
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                $errors[] = "Current password is incorrect";
            }
            if ($new_password !== $confirm_password) {
                $errors[] = "New passwords do not match";
            }
            if (strlen($new_password) < 8) {
                $errors[] = "New password must be at least 8 characters long";
            }
        }
    }

    // If no errors, update the profile
    if (empty($errors)) {
        try {
            $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, mobile_number = ?";
            $params = [$first_name, $last_name, $email, $mobile_number];

            // Add password update if new password is provided
            if (!empty($new_password)) {
                $sql .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $_SESSION['user_id'];

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $success_message = "Profile updated successfully!";
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
            } else {
                $error_message = "Failed to update profile";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - Library Management System</title>
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
                        <a class="nav-link" href="borrowed.php">
                            <i class="fas fa-list me-1"></i> My Books
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">
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

    <div class="profile-container">
        <div class="profile-card">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-image">
                    <?php 
                    $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
                    echo $initials; 
                    ?>
                </div>
                <h3>Edit Profile</h3>
                <p class="text-muted">Update your account information</p>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required
                               value="<?php echo htmlspecialchars($user['first_name']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required
                               value="<?php echo htmlspecialchars($user['last_name']); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required
                           value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Mobile Number</label>
                    <input type="tel" name="mobile_number" class="form-control" required
                           placeholder="09123456789"
                           value="<?php echo htmlspecialchars($user['mobile_number']); ?>">
                </div>

                <hr class="section-divider">

                <h5>Change Password</h5>
                <p class="text-muted small mb-3">Leave blank if you don't want to change your password</p>

                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control">
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>