<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $book_id = $_POST['book_id'];
        $user_id = $_SESSION['user_id'];
        
        switch ($_POST['action']) {
            case 'borrow':
                // Check if user has any overdue books
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND status = 'overdue'");
                $stmt->execute([$user_id]);
                if ($stmt->fetchColumn() > 0) {
                    $_SESSION['error'] = "You have overdue books. Please return them before borrowing new ones.";
                    break;
                }
                
                // Check if book is available
                $stmt = $pdo->prepare("SELECT quantity FROM books WHERE id = ?");
                $stmt->execute([$book_id]);
                $book = $stmt->fetch();
                
                if ($book && $book['quantity'] > 0) {
                    // Get borrow_date and due_date from POST
                    $borrow_date = $_POST['borrow_date'] ?? date('Y-m-d');
                    $due_date = $_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days'));
                    // Start transaction
                    $pdo->beginTransaction();
                    try {
                        // Create borrowing record
                        $stmt = $pdo->prepare("INSERT INTO borrowings (user_id, book_id, borrow_date, due_date) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$user_id, $book_id, $borrow_date, $due_date]);
                        
                        // Update book quantity
                        $stmt = $pdo->prepare("UPDATE books SET quantity = quantity - 1 WHERE id = ?");
                        $stmt->execute([$book_id]);
                        
                        $pdo->commit();
                        $_SESSION['success'] = "Book borrowed successfully. Please return it by " . htmlspecialchars($due_date);
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $_SESSION['error'] = "Error borrowing book. Please try again.";
                    }
                } else {
                    $_SESSION['error'] = "Book is not available for borrowing.";
                }
                break;
        }
    }
    
    // Redirect back to books page
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// If no POST request, redirect to books page
header("Location: books.php");
exit();