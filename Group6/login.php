<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
session_start();

// If user is already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Show error from previous login attempt
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            // Query user by username or email
            $stmt = $pdo->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.username = :identifier1 OR u.email = :identifier2 
                LIMIT 1
            ");
            $stmt->execute([':identifier1' => $username, ':identifier2' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Check user status
                if ($user['status'] === 'suspended') {
                    $error = "Your account has been suspended. Please contact the administrator.";
                } elseif ($user['status'] === 'pending') {
                    $error = "Your account is pending approval. Please wait for administrator approval.";
                } else {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['role'] = $user['role_name'];
                    $_SESSION['role_id'] = $user['role_id'];

                    // Redirect by role
                    if ($user['role_name'] === 'admin') {
                        header("Location: manage-books.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid username/email or password.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Login failed: " . $e->getMessage(); // Show actual error for debugging
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="auth-header">
                <h3>Welcome Back</h3>
                <p>Sign in to your account</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <form method="post" action="" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control<?php echo !empty($error) && empty($username) ? ' is-invalid' : ''; ?>" placeholder="Enter your username or email" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                        <?php if (!empty($error) && empty($username)): ?>
                            <div class="invalid-feedback">Username or email is required</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control<?php echo !empty($error) && empty($password) ? ' is-invalid' : ''; ?>" placeholder="Enter your password" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if (!empty($error) && empty($password)): ?>
                            <div class="invalid-feedback">Password is required</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                    <a href="register.php" class="btn btn-outline-secondary">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </a>
                </div>
                <div class="text-center mt-4">
                    <span class="form-text">Forgot Password? Contact an admin for password reset at: <strong>batillerarjay4@gmail.com</strong></span>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })();

    // Password toggle
    document.getElementById("togglePassword").addEventListener("click", function () {
        const passwordField = document.getElementById("password");
        const icon = this.querySelector("i");

        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    });
    </script>
</body>
</html>
