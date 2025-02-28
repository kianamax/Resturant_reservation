<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'customer'; // Default to customer if not specified

    if (empty($name) || empty($email) || empty($password) || ($role !== 'customer' && $role !== 'staff' && $role !== 'admin')) {
        $error = "All fields are required, and role must be Customer, Staff, or Admin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Email already exists.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (:name, :email, :password, :role, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashed_password,
                    'role' => $role
                ]);

                // Clear any existing session to ensure login is required
                session_destroy();
                session_start();

                // Redirect to login.php with a success message
                header("Location: login.php?message=Signup successful! Please sign in with your credentials.");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Signup error: " . $e->getMessage());
            $error = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Eats - Create Account</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .modern-bg { background: linear-gradient(135deg, #001f3f, #003366, #004d80); }
        .luxury-text { color: #f4c430; text-shadow: 0 2px 4px rgba(244, 196, 48, 0.5); }
        .hover-elevate { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-elevate:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); }
        .input-focus:focus { border-color: #f4c430; box-shadow: 0 0 0 3px rgba(244, 196, 48, 0.3); }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        .slide-up { animation: slideUp 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(50px); } to { opacity: 1; transform: translateY(0); } }
        .glow-effect { position: relative; overflow: hidden; }
        .glow-effect:before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(244, 196, 48, 0.1) 0%, transparent 70%); transform: translate(-50%, -50%); transition: transform 0.5s ease; }
        .glow-effect:hover:before { transform: translate(0, 0); }
    </style>
</head>
<body class="modern-bg min-h-screen font-sans flex items-center justify-center text-white">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md fade-in relative overflow-hidden border border-gray-200">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')] bg-cover bg-center opacity-10 blur-sm"></div>
        <h1 class="text-4xl font-bold luxury-text mb-6 text-center relative z-10">Create Account for Nairobi Eats</h1>
        <?php if ($error): ?>
            <div class="bg-red-100 p-3 rounded-lg text-center relative z-10 slide-up border border-red-300 text-red-800">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form action="signup.php" method="POST" class="space-y-6 relative z-10">
            <div>
                <label class="block text-gray-700 mb-2"><i class="fas fa-user mr-2"></i>Name</label>
                <input type="text" name="name" required class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 placeholder-gray-500 text-gray-900">
            </div>
            <div>
                <label class="block text-gray-700 mb-2"><i class="fas fa-envelope mr-2"></i>Email</label>
                <input type="email" name="email" required class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 placeholder-gray-500 text-gray-900">
            </div>
            <div>
                <label class="block text-gray-700 mb-2"><i class="fas fa-lock mr-2"></i>Password</label>
                <input type="password" name="password" required class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 placeholder-gray-500 text-gray-900">
            </div>
            <div>
                <label class="block text-gray-700 mb-2"><i class="fas fa-shield-alt mr-2"></i>Role</label>
                <select name="role" required class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 text-gray-900">
                    <option value="customer">Customer</option>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="w-full glow-effect bg-navy-700 text-white px-5 py-4 rounded-xl hover-elevate hover:bg-navy-800 transition-all duration-300 flex items-center justify-center text-lg" style="background-color: #003366;">
                <i class="fas fa-user-plus mr-3"></i> Create Account
            </button>
        </form>
        <p class="mt-6 text-center text-gray-600 relative z-10">Already have an account? <a href="login.php" class="text-navy-600 hover:text-navy-500 hover:underline transition-colors duration-300">Sign In</a>.</p>
    </div>
    <script>
        document.querySelectorAll('.hover-elevate').forEach(element => {
            element.addEventListener('mouseover', () => {
                element.style.transform = 'translateY(-5px)';
                element.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.2)';
            });
            element.addEventListener('mouseout', () => {
                element.style.transform = 'translateY(0)';
                element.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            });
        });

        document.querySelectorAll('.fade-in, .slide-up').forEach(element => {
            element.classList.add('animated');
        });
    </script>
</body>
</html>