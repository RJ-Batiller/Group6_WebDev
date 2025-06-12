<?php
require_once 'config.php';

try {
    // Test database connection
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>Database Connection: SUCCESS ✅</h2>";

    // Count total books
    $stmt = $pdo->query("SELECT COUNT(*) FROM books");
    $total_books = $stmt->fetchColumn();
    echo "<h3>Total Books: {$total_books}</h3>";

    // Count books by category
    $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM books GROUP BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Books by Category:</h3>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>{$category['category']}: {$category['count']} books</li>";
    }
    echo "</ul>";

    // Display some sample books
    echo "<h3>Sample Books:</h3>";
    $stmt = $pdo->query("SELECT * FROM books LIMIT 5");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Title</th><th>Author</th><th>Category</th><th>Quantity</th></tr>";
    foreach ($books as $book) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($book['title']) . "</td>";
        echo "<td>" . htmlspecialchars($book['author']) . "</td>";
        echo "<td>" . htmlspecialchars($book['category']) . "</td>";
        echo "<td>" . htmlspecialchars($book['quantity']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch(PDOException $e) {
    echo "<h2>Database Connection: FAILED ❌</h2>";
    echo "Error: " . $e->getMessage();
}
?> 