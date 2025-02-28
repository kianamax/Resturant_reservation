<?php
session_start();
require_once 'config.php';

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'customer';

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=Email and password are required.");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($role === 'admin' && $email === 'admin@nairobieats.com' && $password === 'admin') {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_role'] = 'admin';
                header("Location: admin_dashboard.php");
            } elseif ($role === $user['role']) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_role'] = $user['role'];
                if ($user['role'] === 'customer') {
                    header("Location: dashboard.php");
                } elseif ($user['role'] === 'staff') {
                    header("Location: staff_dashboard.php");
                }
            } else {
                header("Location: login.php?error=Invalid role or credentials for this user.");
            }
            exit();
        } else {
            header("Location: login.php?error=Invalid email or password.");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: login.php?error=Database error. Please try again later.");
        exit();
    }
} else {
    header("Location: login.php?error=Invalid request.");
    exit();
}
?>