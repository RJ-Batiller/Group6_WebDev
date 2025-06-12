<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($first_name) || empty($last_name)) {
        $error = "All fields are required.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Username or email already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $first_name, $last_name]);
                
                // Remove admin from users table if exists
                $stmt = $pdo->prepare("DELETE FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);

                $success = 'Admin account created successfully. <a href="admin-login.php" class="alert-link">Login here</a>.';
            }
        } catch (PDOException $e) {
            error_log("Admin Register error: " . $e->getMessage());
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Admin - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .btn-toggle-password {
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <div class="auth-header">
            <h3>Register Admin</h3>
            <p>Create a new admin account</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <form method="post" action="" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" minlength="6" required>
                    <span class="input-group-text btn-toggle-password" role="button" tabindex="0" id="togglePassword" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </span>
                    <div class="invalid-feedback">
                        Please enter a password (at least 6 characters).
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="6" required>
                    <span class="input-group-text btn-toggle-password" role="button" tabindex="0" id="toggleConfirmPassword" aria-label="Toggle confirm password visibility">
                        <i class="fas fa-eye"></i>
                    </span>
                    <div class="invalid-feedback" id="confirmPasswordFeedback">
                        Please confirm your password.
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Register Admin</button>
                <a href="admin-login.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    'use strict';

    var form = document.querySelector('.needs-validation');
    var password = document.getElementById('password');
    var confirm_password = document.getElementById('confirm_password');
    var confirmPasswordFeedback = document.getElementById('confirmPasswordFeedback');

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (password.value !== confirm_password.value) {
            confirm_password.setCustomValidity("Passwords do not match");
            confirmPasswordFeedback.textContent = "Passwords do not match.";
        } else {
            confirm_password.setCustomValidity("");
        }

        form.classList.add('was-validated');
    }, false);

    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const pwd = password;
        const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
        pwd.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
        const pwd = confirm_password;
        const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
        pwd.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
})();
</script>
</body>
</html>
