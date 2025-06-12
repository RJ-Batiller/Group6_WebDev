<?php
session_start();
require_once 'config.php';

// Only allow admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin-login.php");
    exit();
}

// Handle return action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrowing_id'])) {
    $borrowing_id = $_POST['borrowing_id'];
    // Get borrowing record
    $stmt = $pdo->prepare("SELECT * FROM borrowings WHERE id = ? AND status = 'borrowed'");
    $stmt->execute([$borrowing_id]);
    $borrowing = $stmt->fetch();
    if ($borrowing) {
        $pdo->beginTransaction();
        try {
            // Mark as returned
            $stmt = $pdo->prepare("UPDATE borrowings SET status = 'returned', return_date = NOW() WHERE id = ?");
            $stmt->execute([$borrowing_id]);
            // Increase book quantity
            $stmt = $pdo->prepare("UPDATE books SET quantity = quantity + 1 WHERE id = ?");
            $stmt->execute([$borrowing['book_id']]);
            $pdo->commit();
            $_SESSION['success'] = "Book marked as returned.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error processing return.";
        }
    } else {
        $_SESSION['error'] = "Borrowing record not found or already returned.";
    }
    header("Location: manage-books.php");
    exit();
}

header("Location: manage-books.php");
exit();
