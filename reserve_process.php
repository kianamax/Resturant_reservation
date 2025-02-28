<?php
// reserve_process.php
session_start();
require_once 'config.php'; // Include database connection

// Debugging: Check if session is set
error_log("Session User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set'));

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    error_log("User not authenticated, redirecting to login.php");
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Debugging: Log received POST data
error_log("Received POST data: " . print_r($_POST, true));

// Check if the request is a POST and contains required fields
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restaurant_id'], $_POST['party_size'], $_POST['reservation_time'])) {
    $restaurant_id = filter_var($_POST['restaurant_id'], FILTER_VALIDATE_INT);
    $party_size = filter_var($_POST['party_size'], FILTER_VALIDATE_INT);
    $reservation_time = filter_var($_POST['reservation_time'], FILTER_SANITIZE_STRING);

    // Debugging: Log validated data
    error_log("Validated Data - User ID: $user_id, Restaurant ID: $restaurant_id, Party Size: $party_size, Reservation Time: $reservation_time");

    // Validate inputs
    if ($restaurant_id === false || $party_size === false || $party_size < 1 || empty($reservation_time)) {
        error_log("Validation failed: Invalid reservation details. Restaurant ID: $restaurant_id, Party Size: $party_size, Reservation Time: $reservation_time");
        header("Location: dashboard.php?error=Invalid reservation details.");
        exit();
    }

    // Convert reservation_time to a proper datetime format if needed
    try {
        $reservation_time = date('Y-m-d H:i:s', strtotime($reservation_time));
        error_log("Converted Reservation Time: $reservation_time");
    } catch (Exception $e) {
        error_log("Time conversion error: " . $e->getMessage());
        header("Location: dashboard.php?error=Invalid reservation time format.");
        exit();
    }

    // Verify user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    if ($stmt->fetchColumn() == 0) {
        error_log("User not found for ID: $user_id");
        header("Location: dashboard.php?error=User not found.");
        exit();
    }

    // Verify restaurant exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM restaurants WHERE restaurant_id = :restaurant_id");
    $stmt->execute(['restaurant_id' => $restaurant_id]);
    if ($stmt->fetchColumn() == 0) {
        error_log("Restaurant not found for ID: $restaurant_id");
        header("Location: dashboard.php?error=Restaurant not found.");
        exit();
    }

    // Find an available table
    $stmt = $pdo->prepare("SELECT table_id FROM tables WHERE restaurant_id = :restaurant_id AND capacity >= :party_size AND status = 'available' AND table_id NOT IN (SELECT table_id FROM reservations WHERE reservation_time = :reservation_time AND status IN ('pending', 'confirmed')) LIMIT 1");
    $stmt->execute(['restaurant_id' => $restaurant_id, 'party_size' => $party_size, 'reservation_time' => $reservation_time]);
    $table = $stmt->fetch();

    error_log("Table query result: " . print_r($table, true));

    if ($table) {
        $table_id = $table['table_id'];

        // Start a transaction to ensure data consistency
        $pdo->beginTransaction();
        error_log("Starting transaction for table_id: $table_id");

        try {
            // Insert the reservation with the table_id
            $stmt = $pdo->prepare("INSERT INTO reservations (user_id, restaurant_id, table_id, reservation_time, party_size, status) VALUES (:user_id, :restaurant_id, :table_id, :reservation_time, :party_size, 'pending')");
            $stmt->execute([
                'user_id' => $user_id,
                'restaurant_id' => $restaurant_id,
                'table_id' => $table_id,
                'reservation_time' => $reservation_time,
                'party_size' => $party_size
            ]);

            // Get the last inserted reservation ID for debugging
            $reservation_id = $pdo->lastInsertId();
            error_log("Reservation successfully inserted. Reservation ID: $reservation_id");

            // Update table status to 'reserved'
            $stmt = $pdo->prepare("UPDATE tables SET status = 'reserved' WHERE table_id = :table_id");
            $stmt->execute(['table_id' => $table_id]);

            $pdo->commit();
            error_log("Transaction committed successfully.");

            // Redirect with success message
            header("Location: dashboard.php?success=Reservation made successfully!");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Reservation or table update error: " . $e->getMessage());
            header("Location: dashboard.php?error=Failed to make reservation: " . urlencode($e->getMessage()));
            exit();
        }
    } else {
        // No available tables
        error_log("No available tables found for restaurant $restaurant_id, party size $party_size, time $reservation_time");
        header("Location: dashboard.php?error=No available tables at this time.");
        exit();
    }
} else {
    // Invalid request
    error_log("Invalid POST request or missing fields: " . print_r($_POST, true));
    header("Location: dashboard.php?error=Invalid reservation request.");
    exit();
}