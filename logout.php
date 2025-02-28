<?php
session_start();
if (isset($_SESSION['user_id'])) {
    session_destroy(); // Destroy the session
}
header("Location: index.php?message=Logged out successfully.");
exit();
?>