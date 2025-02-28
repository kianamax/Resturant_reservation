<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch staff’s restaurant (using the restaurant_id column in users)
$stmt = $pdo->prepare("SELECT r.restaurant_id, r.name FROM users u JOIN restaurants r ON u.restaurant_id = r.restaurant_id WHERE u.user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$restaurant = $stmt->fetch();

if (!$restaurant) {
    die("Staff not assigned to a restaurant. Please contact the administrator.");
}

$restaurant_id = $restaurant['restaurant_id'];
$restaurant_name = $restaurant['name'];

// Fetch pending reservations for the staff’s restaurant
$stmt = $pdo->prepare("SELECT r.reservation_id, u.name AS customer_name, r.reservation_time, r.party_size, t.table_number 
                       FROM reservations r 
                       JOIN users u ON u.user_id = r.user_id 
                       JOIN tables t ON t.table_id = r.table_id 
                       WHERE r.restaurant_id = :restaurant_id AND r.status = 'pending'");
$stmt->execute(['restaurant_id' => $restaurant_id]);
$pending_reservations = $stmt->fetchAll();

// Fetch table statuses for the staff’s restaurant
$stmt = $pdo->prepare("SELECT table_id, table_number, capacity, status FROM tables WHERE restaurant_id = :restaurant_id");
$stmt->execute(['restaurant_id' => $restaurant_id]);
$tables = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Eats - Staff Dashboard</title>
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
        .slide-up { animation: slideUp 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(50px); } to { opacity: 1; transform: translateY(0); } }
        .pulse { animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(244, 196, 48, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(244, 196, 48, 0); } 100% { box-shadow: 0 0 0 0 rgba(244, 196, 48, 0); } }
    </style>
</head>
<body class="modern-bg min-h-screen font-sans text-white">
    <div x-data="{ sidebarOpen: false }" class="flex">
        <!-- Sidebar (Optional, for interactivity) -->
        <div x-show="sidebarOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40" @click="sidebarOpen = false"></div>
        <div x-show="sidebarOpen" class="fixed left-0 top-0 w-64 h-full bg-gray-800 p-4 z-50 transform transition-transform duration-300 ease-in-out -translate-x-full" x-transition:enter="translate-x-0" x-transition:leave="translate-x-full">
            <h2 class="text-2xl font-bold luxury-text mb-4">Nairobi Eats</h2>
            <ul class="space-y-2">
                <li><a href="staff_dashboard.php" class="text-gray-300 hover:text-navy-400 hover:underline transition-colors duration-300"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a></li>
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
                            <a href="staff_dashboard.php" class="text-gray-300 hover:text-navy-400 hover:underline transition-colors duration-300"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                            <a href="restaurants.php" class="text-gray-300 hover:text-navy-400 hover:underline transition-colors duration-300"><i class="fas fa-utensils mr-2"></i> Restaurants</a>
                            <a href="logout.php" class="text-gray-300 hover:text-red-400 hover:underline transition-colors duration-300"><i class="fas fa-sign-out-alt mr-2"></i> Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container mx-auto px-4 pt-24 pb-16 relative">
                <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')] bg-cover bg-center opacity-5 blur-sm"></div>
                <h1 class="text-3xl font-bold luxury-text text-navy-400 mb-6 text-center relative z-10">Staff Dashboard - <?php echo htmlspecialchars($restaurant_name); ?></h1>

                <!-- Pending Reservations -->
                <h2 class="text-2xl font-bold luxury-text text-white mb-4 relative z-10">Pending Reservations</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                    <?php if (!empty($pending_reservations)): ?>
                        <?php foreach ($pending_reservations as $res): ?>
                            <div class="glow-card bg-white p-6 rounded-xl shadow-lg hover-elevate transition-all duration-300">
                                <p class="text-lg luxury-text text-navy-600"><strong><?php echo htmlspecialchars($res['customer_name']); ?></strong></p>
                                <p class="text-gray-600 mb-4"><i class="fas fa-clock mr-2"></i> Time: <?php echo htmlspecialchars($res['reservation_time']); ?></p>
                                <p class="text-gray-600 mb-4"><i class="fas fa-users mr-2"></i> Party: <?php echo htmlspecialchars($res['party_size']); ?></p>
                                <p class="text-gray-600 mb-4"><i class="fas fa-chair mr-2"></i> Table: <?php echo htmlspecialchars($res['table_number']); ?></p>
                                <div class="flex space-x-4 mt-4">
                                    <button onclick="confirmReservation(<?php echo $res['reservation_id']; ?>)" class="bg-green-600 text-white px-4 py-2 rounded-lg hover-elevate hover:bg-green-700 transition-all duration-300 flex items-center justify-center w-full">
                                        <i class="fas fa-check mr-2"></i> Confirm
                                    </button>
                                    <button onclick="rejectReservation(<?php echo $res['reservation_id']; ?>)" class="bg-red-600 text-white px-4 py-2 rounded-lg hover-elevate hover:bg-red-700 transition-all duration-300 flex items-center justify-center w-full">
                                        <i class="fas fa-times mr-2"></i> Reject
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-600 text-center relative z-10 col-span-full">No pending reservations.</p>
                    <?php endif; ?>
                </div>

                <!-- Table Statuses -->
                <h2 class="text-2xl font-bold luxury-text text-white mb-4 mt-12 relative z-10">Table Statuses</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
                    <?php foreach ($tables as $table): ?>
                        <div class="glow-card bg-white p-6 rounded-xl shadow-lg hover-elevate transition-all duration-300">
                            <p class="text-lg luxury-text text-navy-600">Table <?php echo htmlspecialchars($table['table_number']); ?></p>
                            <p class="text-gray-600 mb-4"><i class="fas fa-users mr-2"></i> Capacity: <?php echo htmlspecialchars($table['capacity']); ?></p>
                            <p class="text-gray-600 mb-4"><i class="fas fa-circle mr-2 <?php echo $table['status'] === 'available' ? 'text-green-600' : 'text-red-600'; ?>"></i> Status: <?php echo htmlspecialchars($table['status']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Interactivity -->
    <script>
        function confirmReservation(reservationId) {
            if (confirm("Confirm this reservation?")) {
                fetch('confirm_reservation.php?reservation_id=' + reservationId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Reservation confirmed successfully!");
                            location.reload();
                        } else {
                            alert("Failed to confirm reservation: " . data.error);
                        }
                    })
                    .catch(error => console.error('Error confirming reservation:', error));
            }
        }

        function rejectReservation(reservationId) {
            if (confirm("Reject this reservation?")) {
                fetch('reject_reservation.php?reservation_id=' + reservationId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Reservation rejected successfully.");
                            location.reload();
                        } else {
                            alert("Failed to reject reservation: " . data.error);
                        }
                    })
                    .catch(error => console.error('Error rejecting reservation:', error));
            }
        }

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