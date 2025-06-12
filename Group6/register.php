<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// At the very top of the file, add this debug line
echo "Page loaded<br>";

// Database connection test
try {
    require_once 'config.php';
    
    // Test connection
    if (!isset($pdo)) {
        throw new Exception('Database connection not established. Check config.php');
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $test = $pdo->query('SELECT 1');
    
    if (!$test) {
        throw new Exception('Could not execute test query');
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; margin: 10px; border: 1px solid red;'>";
    echo "Database Error: " . $e->getMessage();
    echo "</div>";
}

session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $birthday = $_POST['birthday'] ?? '';
    $mobile_number = trim($_POST['mobile_number'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $terms_accepted = isset($_POST['terms_accepted']);

    // Validation
    if (empty($first_name)) $errors['first_name'] = "First name is required";
    if (empty($last_name)) $errors['last_name'] = "Last name is required";
    if (empty($username)) $errors['username'] = "Username is required";
    if (empty($email)) $errors['email'] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format";
    if (empty($birthday)) $errors['birthday'] = "Birthday is required";
    if (empty($mobile_number)) $errors['mobile_number'] = "Mobile number is required";
    if (!preg_match("/^09\d{9}$/", $mobile_number)) $errors['mobile_number'] = "Invalid mobile number format (e.g., 09123456789)";
    if (empty($gender)) $errors['gender'] = "Gender is required";
    if (empty($password)) $errors['password'] = "Password is required";
    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match";
    if (!$terms_accepted) $errors['terms'] = "You must accept the terms and conditions";

    // Check if username or email already exists
    if (empty($errors['username']) && empty($errors['email'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors['exists'] = "Username or email already exists";
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, first_name, last_name, birthday, mobile_number, gender, status, terms_accepted) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$username, $email, $hashedPassword, $first_name, $last_name, $birthday, $mobile_number, $gender, $terms_accepted])) {
                $success = "Registration successful! You can now login.";
                $_SESSION['success'] = $success;
                header("Location: login.php");
                exit();
            } else {
                $errors['db'] = "Registration failed. Please try again.";
            }
        } catch (PDOException $e) {
            $errors['db'] = "Registration failed. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Registration - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
<div class="register-container">
            <div class="auth-header">
                <h3>Create Account</h3>
                <p>Join our library community</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errors['exists']) || isset($errors['db'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    if (isset($errors['exists'])) echo htmlspecialchars($errors['exists']);
                    if (isset($errors['db'])) echo htmlspecialchars($errors['db']);
                    ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" required 
                                   value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                            <?php if (isset($errors['first_name'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['first_name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" required
                                   value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                            <?php if (isset($errors['last_name'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['last_name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                        <input type="text" name="username" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" required
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['username']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['email']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Birthday</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="date" name="birthday" class="form-control <?php echo isset($errors['birthday']) ? 'is-invalid' : ''; ?>" required
                               value="<?php echo isset($_POST['birthday']) ? htmlspecialchars($_POST['birthday']) : ''; ?>">
                        <?php if (isset($errors['birthday'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['birthday']); ?>
                            </div>
                        <?php endif; ?>
            </div>
        </div>

        <div class="mb-3">
                    <label class="form-label">Mobile Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" name="mobile_number" class="form-control <?php echo isset($errors['mobile_number']) ? 'is-invalid' : ''; ?>" required
                               placeholder="09123456789"
                               value="<?php echo isset($_POST['mobile_number']) ? htmlspecialchars($_POST['mobile_number']) : ''; ?>">
                        <?php if (isset($errors['mobile_number'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['mobile_number']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
        </div>

        <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                        <select name="gender" class="form-control <?php echo isset($errors['gender']) ? 'is-invalid' : ''; ?>" required>
                            <option value="">Select gender</option>
                            <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                        <?php if (isset($errors['gender'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['gender']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback d-block">
                            <?php echo htmlspecialchars($errors['password']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" required>
                    <button class="btn btn-outline-secondary toggle-password" type="button" id="toggleConfirmPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="invalid-feedback d-block">
                            <?php echo htmlspecialchars($errors['confirm_password']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="terms_accepted" class="form-check-input <?php echo isset($errors['terms']) ? 'is-invalid' : ''; ?>" id="terms" required>
            <label class="form-check-label" for="terms">
                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
            </label>
            <?php if (isset($errors['terms'])): ?>
                <div class="invalid-feedback">
                    <?php echo htmlspecialchars($errors['terms']); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </div>
    </form>

    <a href="login.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>By registering and logging in with us, you are consenting to our collection, storage, and use of your personal information in accordance with these Terms and Conditions. We securely hold your data such as your name, email, birthday, mobile number, and gender to provide you with personalized and improved library services.</p>
        <p>Your information is maintained responsibly and will only be used for purposes related to your account, communication about our services, and compliance with legal requirements. We do not share or sell your personal data to third parties.</p>
        <p>Logging in with us means you agree to allow us to store and process your information for as long as necessary to deliver our services and uphold legal obligations. You also agree to abide by our community guidelines and terms while using our platform.</p>
        <p>If you have questions or concerns about how your data is handled, please contact our support team for assistance.</p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Password toggle for Password field
document.getElementById("togglePassword").addEventListener("click", function () {
    const passwordField = document.getElementById("password");
    const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
    passwordField.setAttribute("type", type);
    this.querySelector("i").classList.toggle("fa-eye");
    this.querySelector("i").classList.toggle("fa-eye-slash");
});

// Password toggle for Confirm Password field
document.getElementById("toggleConfirmPassword").addEventListener("click", function () {
    const confirmPasswordField = document.getElementById("confirm_password");
    const type = confirmPasswordField.getAttribute("type") === "password" ? "text" : "password";
    confirmPasswordField.setAttribute("type", type);
    this.querySelector("i").classList.toggle("fa-eye");
    this.querySelector("i").classList.toggle("fa-eye-slash");
});
</script>
</body>
</html>
