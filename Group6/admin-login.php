<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
session_start();

// If admin is already logged in, redirect to admin-dashboard to prevent re-login
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin-dashboard.php");
    exit();
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            // Use admins table for admin login
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :identifier1 OR email = :identifier2 LIMIT 1");
            $stmt->execute([':identifier1' => $username, ':identifier2' => $username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['first_name'] = $admin['first_name'];
                $_SESSION['last_name'] = $admin['last_name'];
                $_SESSION['role'] = 'admin';
                header("Location: admin-dashboard.php"); // Redirect to admin dashboard
                exit();
            } else {
                $error = "Invalid admin credentials.";
            }
        } catch (PDOException $e) {
            error_log("Admin Login error: " . $e->getMessage());
            $error = "Login failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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
                <h3>Admin Login</h3>
                <p>Sign in as administrator</p>
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
                        <input
                            type="text"
                            name="username"
                            class="form-control<?php echo !empty($error) && empty($username) ? ' is-invalid' : ''; ?>"
                            placeholder="Enter admin username"
                            value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                            required
                        >
                        <?php if (!empty($error) && empty($username)): ?>
                            <div class="invalid-feedback">Username or email is required</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control<?php echo !empty($error) && empty($password) ? ' is-invalid' : ''; ?>"
                            placeholder="Enter your password"
                            required
                        >
                        <span class="input-group-text btn-toggle-password" role="button" tabindex="0" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </span>
                        <?php if (!empty($error) && empty($password)): ?>
                            <div class="invalid-feedback">Password is required</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                    <a href="admin-register.php" class="btn btn-outline-secondary">
                        <i class="fas fa-user-plus me-2"></i>Register Admin
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    })()

    // Password toggle
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    </script>
</body>
</html>
