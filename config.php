<?php
// config.php

// Database configuration
define('DB_HOST', 'localhost');         // Database host (usually localhost for local development)
define('DB_NAME', 'restaurant_reserve'); // Database name
define('DB_USER', 'root');              // Default MySQL username (update for your server)
define('DB_PASS', '');                  // Default MySQL password (update for your server)

// Establish database connection using PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Return results as associative arrays
            PDO::ATTR_EMULATE_PREPARES => false // Use real prepared statements
        ]
    );
} catch (PDOException $e) {
    // Display a generic error message to avoid leaking sensitive info
    die("Database connection failed. Please try again later.");
}

// Optional: Other global configurations (e.g., site URL, timezone)
define('SITE_URL', 'http://localhost/nairobi_eats'); // Update with your actual site URL
date_default_timezone_set('Africa/Nairobi'); // Set timezone to Nairobi
?>