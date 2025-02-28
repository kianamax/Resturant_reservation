<?php
// signup_process.php
session_start();
require_once 'config.php'; // Include the config file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic validation
    if (strlen($password) < 6) {
        $_SESSION['signup_error'] = "Password must be at least 6 characters.";
        header("Location: signup.php");
        exit();
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['signup_error'] = "Email already registered.";
        header("Location: signup.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'customer')");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashed_password
    ]);

    // Log the user in after signup
    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['user_role'] = 'customer';
    header("Location: login.php"); // Redirect to dashboard (to be created)
    exit();
}
?>