<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header("Location: login.php?error=Access denied.");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_id'], $_POST['table_number'], $_POST['capacity'], $_POST['status'])) {
    $table_id = filter_var($_POST['table_id'], FILTER_VALIDATE_INT);
    $table_number = filter_var($_POST['table_number'], FILTER_SANITIZE_STRING);
    $capacity = filter_var($_POST['capacity'], FILTER_VALIDATE_INT);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

    if ($table_id === false || empty($table_number) || $capacity === false || $capacity < 1 || !in_array($status, ['available', 'reserved', 'occupied'])) {
        header("Location: staff_dashboard.php?error=Invalid table details.");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE tables SET table_number = :table_number, capacity = :capacity, status = :status WHERE table_id = :table_id AND restaurant_id = (SELECT restaurant_id FROM users WHERE user_id = :user_id)");
        $stmt->execute([
            'table_id' => $table_id,
            'table_number' => $table_number,
            'capacity' => $capacity,
            'status' => $status,
            'user_id' => $_SESSION['user_id']
        ]);

        header("Location: staff_dashboard.php?success=Table updated successfully!");
        exit();
    } catch (PDOException $e) {
        error_log("Error updating table: " . $e->getMessage());
        header("Location: staff_dashboard.php?error=Failed to update table: " . urlencode($e->getMessage()));
        exit();
    }
}

header("Location: staff_dashboard.php?error=Invalid request.");
exit();
?>