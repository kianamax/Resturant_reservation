<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch total users
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();

// Fetch total restaurants
$stmt = $pdo->query("SELECT COUNT(*) FROM restaurants");
$total_restaurants = $stmt->fetchColumn();

// Fetch total reservations
$stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
$total_reservations = $stmt->fetchColumn();

// Fetch average rating from feedback (handle NULL case)
$stmt = $pdo->query("SELECT COALESCE(AVG(rating), 0) FROM feedback");
$avg_rating = $stmt->fetchColumn();

// Fetch list of users with restaurant names (if applicable)
$stmt = $pdo->query("SELECT u.user_id, u.name, u.email, u.role, u.restaurant_id, r.name AS restaurant_name 
                     FROM users u 
                     LEFT JOIN restaurants r ON u.restaurant_id = r.restaurant_id");
$users = $stmt->fetchAll();

// Fetch list of restaurants
$stmt = $pdo->query("SELECT restaurant_id, name, location FROM restaurants");
$restaurants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Eats - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .modern-bg { background: linear-gradient(135deg, #001f3f, #003366, #004d80); }
        .luxury-text { color: #f4c430; text-shadow: 0 2px 4px rgba(244, 196, 48, 0.5); }
        .hover-elevate { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-elevate:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); cursor: pointer; }
        .glow-card { position: relative; overflow: hidden; border: 1px solid #f4c430; }
        .glow-card:before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(244, 196, 48, 0.1) 0%, transparent 70%); transform: translate(-50%, -50%); transition: transform 0.5s ease; }
        .glow-card:hover:before { transform: translate(0, 0); }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        .slide-up { animation: slideUp 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(50px); } to { opacity: 1; transform: translateY(0); } }
        .pulse { animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(244, 196, 48, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(244, 196, 48, 0); } 100% { box-shadow: 0 0 0 0 rgba(244, 196, 48, 0); } }
        .chart-container { max-width: 100%; margin: auto; }
    </style>
</head>
<body class="modern-bg min-h-screen font-sans text-white">
    <div x-data="{ sidebarOpen: false }" class="flex">
        <!-- Sidebar (Optional, for interactivity) -->
        <div x-show="sidebarOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40" @click="sidebarOpen = false"></div>
        <div x-show="sidebarOpen" class="fixed left-0 top-0 w-64 h-full bg-gray-800 p-4 z-50 transform transition-transform duration-300 ease-in-out -translate-x-full" x-transition:enter="translate-x-0" x-transition:leave="translate-x-full">
            <h2 class="text-2xl font-bold luxury-text mb-4">Nairobi Eats</h2>
            <ul class="space-y-2">
                <li><a href="admin_dashboard.php" class="text-gray-300 hover:text-navy-400 hover:underline transition-colors duration-300"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a></li>
                <li><a href="restaurants.php" class="text-gray-300 hover:text-navy-400 hover:underline transition-colors duration-300"><i class="fas fa-utensils mr-2"></i> Restaurants</a></li>
                <li><a href="logout.php" class="text-gray-300 hover:text-red-400 hover:underline transition-colors duration-300"><i class="fas fa-sign-out-alt mr-2"></i> Log Out</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <nav class="bg-gray-800 shadow-lg fixed top-0 w-full z-20">
                <div class="container mx-auto px-4">
                    <div class="flex justify-between items-center py-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-white p-2 rounded-lg hover:bg-gray-700 transition-colors duration-300">
                            <i class="fas fa-bars"></i>
                        </button>
                        <a href="index.php" class="text-2xl font-bold luxury-text text-navy-400">Nairobi Eats</a>
                        <div class="flex space-x-4">
                            <a href="admin_dashboard.php" class="text-gray-300 hover:text-navy-400 hover:underline transition-colors duration-300"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                            <a href="restaurants.php" class="text-gray-300 hover:text-navy-400 hover:underline transition-colors duration-300"><i class="fas fa-utensils mr-2"></i> Restaurants</a>
                            <a href="logout.php" class="text-gray-300 hover:text-red-400 hover:underline transition-colors duration-300"><i class="fas fa-sign-out-alt mr-2"></i> Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container mx-auto px-4 pt-24 pb-16 relative">
                <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')] bg-cover bg-center opacity-5 blur-sm"></div>
                <h1 class="text-3xl font-bold luxury-text text-navy-400 mb-6 text-center relative z-10">Admin Dashboard</h1>

                <!-- Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 relative z-10">
                    <div class="glow-card bg-white p-6 rounded-xl shadow-lg hover-elevate transition-all duration-300">
                        <p class="text-lg luxury-text text-navy-600"><i class="fas fa-users mr-2 pulse"></i>Total Users</p>
                        <p class="text-2xl font-bold text-navy-800"><?php echo htmlspecialchars($total_users); ?></p>
                    </div>
                    <div class="glow-card bg-white p-6 rounded-xl shadow-lg hover-elevate transition-all duration-300">
                        <p class="text-lg luxury-text text-navy-600"><i class="fas fa-utensils mr-2 pulse"></i>Total Restaurants</p>
                        <p class="text-2xl font-bold text-navy-800"><?php echo htmlspecialchars($total_restaurants); ?></p>
                    </div>
                    <div class="glow-card bg-white p-6 rounded-xl shadow-lg hover-elevate transition-all duration-300">
                        <p class="text-lg luxury-text text-navy-600"><i class="fas fa-calendar-check mr-2 pulse"></i>Total Reservations</p>
                        <p class="text-2xl font-bold text-navy-800"><?php echo htmlspecialchars($total_reservations); ?></p>
                    </div>
                    <div class="glow-card bg-white p-6 rounded-xl shadow-lg hover-elevate transition-all duration-300">
                        <p class="text-lg luxury-text text-navy-600"><i class="fas fa-star mr-2 pulse"></i>Average Rating</p>
                        <p class="text-2xl font-bold text-navy-800"><?php echo number_format($avg_rating, 1); ?>/5</p>
                    </div>
                </div>

                <!-- Users Management -->
                <h2 class="text-2xl font-bold luxury-text text-white mb-4 relative z-10">Users</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <div class="glow-card bg-white p-6 rounded-xl shadow-lg hover-elevate transition-all duration-300">
                                <p class="text-lg luxury-text text-navy-600"><strong><?php echo htmlspecialchars($user['name']); ?></strong></p>
                                <p class="text-gray-600 mb-4"><i class="fas fa-envelope mr-2"></i> Email: <?php echo htmlspecialchars($user['email']); ?></p>
                                <p class="text-gray-600 mb-4"><i class="fas fa-shield-alt mr-2"></i> Role: <?php echo htmlspecialchars($user['role']); ?></p>
                                <p class="text-gray-600 mb-4"><i class="fas fa-utensils mr-2"></i> Restaurant: <?php echo $user['restaurant_name'] ? htmlspecialchars($user['restaurant_name']) : 'N/A'; ?></p>
                                <div class="flex space-x-4 mt-4">
                                    <a href="edit_user.php?user_id=<?php echo htmlspecialchars($user['user_id']); ?>" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover-elevate hover:bg-yellow-700 transition-all duration-300 flex items-center justify-center w-full">
                                        <i class="fas fa-edit mr-2"></i> Edit
                                    </a>
                                    <a href="delete_user.php?user_id=<?php echo htmlspecialchars($user['user_id']); ?>" onclick="return confirm('Are you sure you want to delete this user?')" class="bg-red-600 text-white px-4 py-2 rounded-lg hover-elevate hover:bg-red-700 transition-all duration-300 flex items-center justify-center w-full">
                                        <i class="fas fa-trash mr-2"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-600 text-center relative z-10 col-span-full">No users found.</p>
                    <?php endif; ?>
                </div>

                <!-- Restaurants Management -->
                <h2 class="text-2xl font-bold luxury-text text-white mb-4 mt-12 relative z-10">Restaurants</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                    <?php if (!empty($restaurants)): ?>
                        <?php foreach ($restaurants as $restaurant): ?>
                            <div class="glow-card bg-white p-6 rounded-xl shadow-lg hover-elevate transition-all duration-300">
                                <p class="text-lg luxury-text text-navy-600"><strong><?php echo htmlspecialchars($restaurant['name']); ?></strong></p>
                                <p class="text-gray-600 mb-4"><i class="fas fa-map-marker-alt mr-2"></i> Location: <?php echo htmlspecialchars($restaurant['location']); ?></p>
                                <div class="flex space-x-4 mt-4">
                                    <a href="edit_restaurant.php?restaurant_id=<?php echo htmlspecialchars($restaurant['restaurant_id']); ?>" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover-elevate hover:bg-yellow-700 transition-all duration-300 flex items-center justify-center w-full">
                                        <i class="fas fa-edit mr-2"></i> Edit
                                    </a>
                                    <a href="delete_restaurant.php?restaurant_id=<?php echo htmlspecialchars($restaurant['restaurant_id']); ?>" onclick="return confirm('Are you sure you want to delete this restaurant?')" class="bg-red-600 text-white px-4 py-2 rounded-lg hover-elevate hover:bg-red-700 transition-all duration-300 flex items-center justify-center w-full">
                                        <i class="fas fa-trash mr-2"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-600 text-center relative z-10 col-span-full">No restaurants found.</p>
                    <?php endif; ?>
                </div>

                <!-- Analytics (Optional, using Chart.js) -->
                <h2 class="text-2xl font-bold luxury-text text-white mb-4 mt-12 relative z-10">Reservation Analytics</h2>
                <div class="glow-card bg-white p-6 rounded-xl shadow-lg relative z-10 chart-container">
                    <canvas id="reservationChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Interactivity -->
    <script>
        document.querySelectorAll('.hover-elevate').forEach(element => {
            element.addEventListener('mouseover', () => {
                element.style.transform = 'translateY(-5px)';
                element.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.2)';
                element.style.cursor = 'pointer'; // Ensure pointer cursor on hover
            });
            element.addEventListener('mouseout', () => {
                element.style.transform = 'translateY(0)';
                element.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            });
        });

        document.querySelectorAll('.fade-in, .slide-up').forEach(element => {
            element.classList.add('animated');
        });

        // Chart.js for Reservation Analytics
        const ctx = document.getElementById('reservationChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Reservations',
                    data: [12, 19, 15, 22, 18, 25],
                    backgroundColor: '#f4c430',
                    borderColor: '#003366',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true, ticks: { color: '#000' } },
                    x: { ticks: { color: '#000' } }
                },
                plugins: { legend: { labels: { color: '#000' } } },
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>