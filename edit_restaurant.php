<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restaurant_id'], $_POST['name'], $_POST['location'], $_POST['contact_number'], $_POST['description'])) {
    $restaurant_id = filter_var($_POST['restaurant_id'], FILTER_VALIDATE_INT);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
    $contact_number = filter_var($_POST['contact_number'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

    if ($restaurant_id === false || empty($name) || empty($location)) {
        header("Location: admin_dashboard.php?error=Invalid restaurant details.");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE restaurants SET name = :name, location = :location, contact_number = :contact_number, description = :description WHERE restaurant_id = :restaurant_id");
        $stmt->execute([
            'restaurant_id' => $restaurant_id,
            'name' => $name,
            'location' => $location,
            'contact_number' => $contact_number,
            'description' => $description
        ]);

        header("Location: admin_dashboard.php?success=Restaurant updated successfully!");
        exit();
    } catch (PDOException $e) {
        error_log("Error updating restaurant: " . $e->getMessage());
        header("Location: admin_dashboard.php?error=Failed to update restaurant: " . urlencode($e->getMessage()));
        exit();
    }
}

if (isset($_GET['restaurant_id'])) {
    $restaurant_id = filter_var($_GET['restaurant_id'], FILTER_VALIDATE_INT);
    if ($restaurant_id === false) {
        header("Location: admin_dashboard.php?error=Invalid restaurant ID.");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE restaurant_id = :restaurant_id");
    $stmt->execute(['restaurant_id' => $restaurant_id]);
    $restaurant = $stmt->fetch();

    if (!$restaurant) {
        header("Location: admin_dashboard.php?error=Restaurant not found.");
        exit();
    }
} else {
    header("Location: admin_dashboard.php?error=No restaurant ID provided.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Eats - Edit Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .modern-bg { background: linear-gradient(135deg, #001f3f, #003366, #004d80); }
        .luxury-text { color: #f4c430; text-shadow: 0 2px 4px rgba(244, 196, 48, 0.5); }
        .hover-elevate { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-elevate:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="modern-bg min-h-screen font-sans text-white">
    <div class="container mx-auto px-4 pt-24 pb-16">
        <h1 class="text-4xl font-bold luxury-text text-navy-400 mb-8 text-center">Edit Restaurant</h1>
        <div class="bg-gray-900 p-8 rounded-3xl shadow-2xl relative overflow-hidden border border-gray-200">
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')] bg-cover bg-center opacity-5 blur-sm"></div>
            <form action="edit_restaurant.php" method="POST" class="space-y-6 relative z-10">
                <input type="hidden" name="restaurant_id" value="<?php echo htmlspecialchars($restaurant['restaurant_id']); ?>">
                <div>
                    <label class="block text-gray-300 mb-2"><i class="fas fa-utensils mr-2"></i>Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($restaurant['name']); ?>" required class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 placeholder-gray-500 text-gray-900">
                </div>
                <div>
                    <label class="block text-gray-300 mb-2"><i class="fas fa-map-marker-alt mr-2"></i>Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($restaurant['location']); ?>" required class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 placeholder-gray-500 text-gray-900">
                </div>
                <div>
                    <label class="block text-gray-300 mb-2"><i class="fas fa-phone mr-2"></i>Contact Number</label>
                    <input type="text" name="contact_number" value="<?php echo htmlspecialchars($restaurant['contact_number']); ?>" class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 placeholder-gray-500 text-gray-900">
                </div>
                <div>
                    <label class="block text-gray-300 mb-2"><i class="fas fa-info-circle mr-2"></i>Description</label>
                    <textarea name="description" class="w-full px-5 py-4 bg-gray-50 border border-gray-300 rounded-xl input-focus hover-elevate focus:outline-none transition-all duration-300 placeholder-gray-500 text-gray-900" rows="4"><?php echo htmlspecialchars($restaurant['description']); ?></textarea>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="submit" class="glow-effect bg-navy-700 text-white px-5 py-4 rounded-xl hover-elevate hover:bg-navy-800 transition-all duration-300 flex items-center text-lg" style="background-color: #003366;">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                    <a href="admin_dashboard.php" class="bg-gray-600 text-white px-5 py-4 rounded-xl hover-elevate hover:bg-gray-700 transition-all duration-300 flex items-center text-lg">
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