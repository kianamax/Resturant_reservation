<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['restaurant_id'])) {
    $restaurant_id = filter_var($_GET['restaurant_id'], FILTER_VALIDATE_INT);
    if ($restaurant_id === false) {
        header("Location: admin_dashboard.php?error=Invalid restaurant ID.");
        exit();
    }

    try {
        // Delete related tables and reservations first (due to foreign key constraints)
        $pdo->exec("DELETE FROM tables WHERE restaurant_id = $restaurant_id");
        $pdo->exec("DELETE FROM reservations WHERE restaurant_id = $restaurant_id");

        // Delete the restaurant
        $stmt = $pdo->prepare("DELETE FROM restaurants WHERE restaurant_id = :restaurant_id");
        $stmt->execute(['restaurant_id' => $restaurant_id]);

        header("Location: admin_dashboard.php?success=Restaurant deleted successfully!");
        exit();
    } catch (PDOException $e) {
        error_log("Error deleting restaurant: " . $e->getMessage());
        header("Location: admin_dashboard.php?error=Failed to delete restaurant: " . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: admin_dashboard.php?error=No restaurant ID provided.");
    exit();
}