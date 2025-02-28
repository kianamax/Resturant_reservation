<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['reservation_id'])) {
    $reservation_id = filter_var($_GET['reservation_id'], FILTER_VALIDATE_INT);
    if ($reservation_id === false) {
        echo json_encode(['success' => false, 'error' => 'Invalid reservation ID']);
        exit();
    }

    try {
        // Check if the reservation belongs to the staff’s restaurant using the new restaurant_id column
        $stmt = $pdo->prepare("SELECT r.restaurant_id, r.table_id FROM reservations r JOIN users u ON r.restaurant_id = u.restaurant_id WHERE r.reservation_id = :reservation_id AND u.user_id = :user_id AND r.status = 'pending'");
        $stmt->execute(['reservation_id' => $reservation_id, 'user_id' => $_SESSION['user_id']]);
        $reservation = $stmt->fetch();

        if (!$reservation) {
            echo json_encode(['success' => false, 'error' => 'Reservation not found or not authorized']);
            exit();
        }

        $restaurant_id = $reservation['restaurant_id'];
        $table_id = $reservation['table_id'];

        // Update reservation status to 'cancelled' and table status back to 'available'
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = :reservation_id");
        $stmt->execute(['reservation_id' => $reservation_id]);

        $stmt = $pdo->prepare("UPDATE tables SET status = 'available' WHERE table_id = :table_id");
        $stmt->execute(['table_id' => $table_id]);

        $pdo->commit();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error rejecting reservation: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No reservation ID provided']);
}
exit();