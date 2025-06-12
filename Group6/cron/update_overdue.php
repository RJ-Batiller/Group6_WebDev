<?php
require_once __DIR__ . '/../config.php';

try {
    // Update status of overdue books
    $stmt = $pdo->prepare("
        UPDATE borrowings 
        SET status = 'overdue' 
        WHERE status = 'borrowed' 
        AND due_date < NOW()
    ");
    $stmt->execute();
    
    echo "Successfully updated overdue books\n";
} catch (Exception $e) {
    echo "Error updating overdue books: " . $e->getMessage() . "\n";
}
?> 