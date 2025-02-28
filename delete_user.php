<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['user_id'])) {
    $user_id = filter_var($_GET['user_id'], FILTER_VALIDATE_INT);
    if ($user_id === false) {
        header("Location: admin_dashboard.php?error=Invalid user ID.");
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);

        header("Location: admin_dashboard.php?success=User deleted successfully!");
        exit();
    } catch (PDOException $e) {
        error_log("Error deleting user: " . $e->getMessage());
        header("Location: admin_dashboard.php?error=Failed to delete user: " . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: admin_dashboard.php?error=No user ID provided.");
    exit();
}