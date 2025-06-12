<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $quantity = intval($_POST['quantity']);

    if ($title && $author && $quantity > 0) {
        $stmt = $pdo->prepare("INSERT INTO requests (user_id, title, author, quantity, request_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $title, $author, $quantity]);
        $message = "Your request has been submitted!";
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Request a Book</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Book Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Author</label>
                <input type="text" name="author" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" required min="1">
            </div>
            <button type="submit" class="btn btn-success">Submit Request</button>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
