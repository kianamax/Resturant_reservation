<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['name'], $_POST['email'], $_POST['role'], $_POST['restaurant_id'])) {
    $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);
    $restaurant_id = filter_var($_POST['restaurant_id'], FILTER_VALIDATE_INT);

    if ($user_id === false || empty($name) || empty($email) || empty($role) || ($role !== 'customer' && $role !== 'staff' && $role !== 'admin')) {
        header("Location: admin_dashboard.php?error=Invalid user details.");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email, role = :role, restaurant_id = :restaurant_id WHERE user_id = :user_id");
        $stmt->execute([
            'user_id' => $user_id,
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'restaurant_id' => $restaurant_id
        ]);

        header("Location: admin_dashboard.php?success=User updated successfully!");
        exit();
    } catch (PDOException $e) {
        error_log("Error updating user: " . $e->getMessage());
        header("Location: admin_dashboard.php?error=Failed to update user: " . urlencode($e->getMessage()));
        exit();
    }
}

if (isset($_GET['user_id'])) {
    $user_id = filter_var($_GET['user_id'], FILTER_VALIDATE_INT);
    if ($user_id === false) {
        header("Location: admin_dashboard.php?error=Invalid user ID.");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: admin_dashboard.php?error=User not found.");
        exit();
    }
} else {
    header("Location: admin_dashboard.php?error=No user ID provided.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Eats - Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .modern-bg { background: linear-gradient(135deg, #001f3f, #003366, #004d80); }
        .luxury-text { color: #f4c430; text-shadow: 0 2px 4px rgba(244, 196, 48, 0.5); }
        .hover-elevate { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-elevate:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); }
        .glow-card { position: relative; overflow: hidden; border: 1px solid #f4c430; }
        .glow-card:before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(244, 196, 48, 0.1) 0%, transparent 70%); transform: translate(-50%, -50%); transition: transform 0.5s ease; }
        .glow-card:hover:before { transform: translate(0, 0); }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="modern-bg min-h-screen font-sans text-white">
    <div class="container mx-auto px-4 pt-24 pb-16">
        <h1 class="text-4xl font-bold luxury-text text-navy-400 mb-8 text-center">Edit User</h1>
        <div class="glow-card bg-white p-8 rounded-3xl shadow-lg relative overflow-hidden border border-gray-200">
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')] bg-cover bg-center opacity-5 blur-sm"></div>
            <form action="edit_user.php" method="POST" class="space-y-6 relative z-10">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                <div>
                    <label class="block text-gray-600 mb-2"><i class="fas fa-user mr-2"></i>Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 placeholder-gray-500 text-gray-900">
                </div>
                <div>
                    <label class="block text-gray-600 mb-2"><i class="fas fa-envelope mr-2"></i>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 placeholder-gray-500 text-gray-900">
                </div>
                <div>
                    <label class="block text-gray-600 mb-2"><i class="fas fa-shield-alt mr-2"></i>Role</label>
                    <select name="role" required class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 text-gray-900">
                        <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                        <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 mb-2"><i class="fas fa-utensils mr-2"></i>Restaurant (for Staff, optional)</label>
                    <select name="restaurant_id" class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 text-gray-900">
                        <option value="">N/A</option>
                        <?php
                        $stmt = $pdo->query("SELECT restaurant_id, name FROM restaurants");
                        while ($restaurant = $stmt->fetch()) {
                            echo "<option value='" . htmlspecialchars($restaurant['restaurant_id']) . "' " . ($user['restaurant_id'] == $restaurant['restaurant_id'] ? 'selected' : '') . ">" . htmlspecialchars($restaurant['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="flex space-x-4 mt-4">
                    <button type="submit" class="glow-effect bg-navy-700 text-white px-4 py-2 rounded-lg hover-elevate hover:bg-navy-800 transition-all duration-300 flex items-center justify-center w-full" style="background-color: #003366;">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                    <a href="admin_dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover-elevate hover:bg-gray-700 transition-all duration-300 flex items-center justify-center w-full">
                        <i class="fas fa-arrow-left mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
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

        document.querySelectorAll('.fade-in').forEach(element => {
            element.classList.add('animated');
        });
    </script>
</body>
</html>