<?php

// error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');     // Your database host
define('DB_NAME', 'group6');        // Your database name
define('DB_USER', 'root');          // Your database username
define('DB_PASS', '');              // Your database password
define('DB_CHARSET', 'utf8mb4');    // Database charset

try {
    // Create DSN
    $dsn = "mysql:host=" . DB_HOST;
    
    // First connect without database to check if it exists
    $temp_pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    // Check if database exists
    $result = $temp_pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    
    if (!$result->rowCount()) {
        // Create database if it doesn't exist
        $temp_pdo->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    // Now connect with the database
    $pdo = new PDO(
        $dsn . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "<!-- Database connected successfully -->"; // Hidden success message
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Security Configuration
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Lax');

// Time Zone Configuration
date_default_timezone_set('Asia/Manila'); // Adjust this to your timezone
