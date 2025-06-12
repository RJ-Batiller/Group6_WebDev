<?php
session_start();
require_once 'config.php';

// Clear the remember token in database if user_id exists
if (!empty($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        error_log("Error clearing remember token: " . $e->getMessage());
    }
}

// Clear the remember_token cookie if set
if (isset($_COOKIE['remember_token'])) {
    // Use secure flag only if HTTPS is used
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('remember_token', '', time() - 3600, '/', '', $secure, true);
}

// Save role before destroying session
$role = $_SESSION['role'] ?? null;

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect based on role (admin or general user)
if ($role === 'admin') {
    header("Location: admin-login.php");
} else {
    header("Location: login.php");
}
exit();
