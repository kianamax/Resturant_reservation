<?php
// dashboard.php
session_start();
require_once 'config.php'; // Include database connection

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Debugging: Check session variables
echo "User ID: " . htmlspecialchars($user_id) . "<br>";
echo "User Role: " . htmlspecialchars($user_role) . "<br>";

// Fetch user details
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found in database. Check user_id: " . htmlspecialchars($user_id));
}

// Fetch customer preferences
$preferences = [];
if ($user_role === 'customer') {
    $stmt = $pdo->prepare("SELECT preference_type, preference_value FROM customer_preferences WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $preferences = $stmt->fetchAll();
}

// Fetch recommended restaurants based on preferences
$preference_conditions = [];
$params = []; // Start with an empty params array
foreach ($preferences as $index => $pref) {
    $param_name = "pref_value_$index";
    $preference_conditions[] = "r.description LIKE :$param_name";
    $params[$param_name] = "%" . $pref['preference_value'] . "%";
}

$where_clause = !empty($preference_conditions) ? "WHERE (" . implode(" OR ", $preference_conditions) . ")" : "";
$stmt = $pdo->prepare("SELECT r.restaurant_id, r.name, r.location, r.description, r.image_url 
                       FROM restaurants r 
                       LEFT JOIN feedback f ON f.reservation_id IN (SELECT reservation_id FROM reservations WHERE restaurant_id = r.restaurant_id)
                       $where_clause
                       GROUP BY r.restaurant_id, r.name, r.location, r.description, r.image_url
                       ORDER BY COALESCE(AVG(f.rating), 0) DESC LIMIT 12");

// Only execute with the params if there are any
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute(); // Execute with no parameters if no preferences
}
$restaurants = $stmt->fetchAll();

if (empty($restaurants)) {
    echo "<p>No restaurants found. Please add restaurants to the database.</p>";
}

// Fetch reservation history
$stmt = $pdo->prepare("SELECT r.reservation_id, r.reservation_time, r.party_size, r.status, res.name 
                       FROM reservations r 
                       JOIN restaurants res ON res.restaurant_id = r.restaurant_id 
                       WHERE user_id = :user_id 
                       ORDER BY reservation_time DESC");
$stmt->execute(['user_id' => $user_id]);
$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Eats - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 50; transition: opacity 0.3s ease; }
        .modal.show { opacity: 1; }
        .modal-content { background: white; margin: 10% auto; padding: 20px; width: 90%; max-width: 500px; border-radius: 10px; transform: translateY(0); transition: transform 0.3s ease; }
        .modal-content.show { transform: translateY(-10px); }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-gray-100 min-h-screen font-sans">
    <<!-- In dashboard.php, replace the navigation bar section -->
<nav class="bg-white shadow-lg fixed top-0 w-full z-10">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <a href="index.php" class="text-2xl font-bold text-blue-600">Nairobi Eats</a>
            <div class="flex space-x-4">
                <a href="dashboard.php" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                <a href="restaurants.php" class="text-gray-700 hover:text-blue-600">Restaurants</a>
                <a href="logout.php" class="text-gray-700 hover:text-blue-600">Log Out</a>
            </div>
        </div>
    </div>
</nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 pt-24 pb-16">
        <h1 class="text-4xl font-bold text-gray-800 mb-6 fade-in">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>

        <?php if ($user_role === 'customer'): ?>
            <!-- Search and Filter Section -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8 fade-in">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Find Your Perfect Dining Spot</h2>
                <form id="searchForm" class="flex flex-col md:flex-row gap-4">
                    <input type="text" id="searchQuery" placeholder="Search restaurants..." class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <select id="filterPreference" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Preferences</option>
                        <?php foreach ($preferences as $pref): ?>
                            <option value="<?php echo htmlspecialchars($pref['preference_value']); ?>">
                                <?php echo htmlspecialchars($pref['preference_type'] . ': ' . $pref['preference_value']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </form>
            </div>

            <!-- Recommended Restaurants Section -->
            <h2 class="text-3xl font-bold text-gray-800 mb-6 fade-in">Recommended for You</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="restaurantList">
                <?php if (!empty($restaurants)): ?>
                    <?php foreach ($restaurants as $restaurant): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden card-hover">
                            <img src="<?php echo htmlspecialchars($restaurant['image_url'] ?? 'images/default.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($restaurant['name']); ?>" 
                                 class="w-full h-48 object-cover">
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($restaurant['name']); ?></h3>
                                <p class="text-gray-600 mb-2"><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($restaurant['location']); ?></p>
                                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($restaurant['description'] ?? 'No description available'); ?></p>
                                <button onclick="openReservationModal(<?php echo $restaurant['restaurant_id']; ?>)" 
                                        class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 flex items-center">
                                    <i class="fas fa-calendar-check mr-2"></i> Reserve Now
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-600">No restaurants found. Please add restaurants to the database.</p>
                <?php endif; ?>
            </div>

            <!-- Your Reservations Section -->
            <h2 class="text-3xl font-bold text-gray-800 mb-6 mt-12 fade-in">Your Reservations</h2>
            <div class="space-y-4">
                <?php if (!empty($reservations)): ?>
                    <?php foreach ($reservations as $res): ?>
                        <div class="bg-white p-4 rounded-lg shadow flex justify-between items-center card-hover">
                            <div>
                                <p class="text-gray-800"><strong><?php echo htmlspecialchars($res['name']); ?></strong> - <?php echo htmlspecialchars($res['reservation_time']); ?></p>
                                <p class="text-gray-600">Party Size: <?php echo htmlspecialchars($res['party_size']); ?>, Status: <?php echo htmlspecialchars($res['status']); ?></p>
                            </div>
                            <?php if ($res['status'] === 'pending' || $res['status'] === 'confirmed'): ?>
                                <button onclick="cancelReservation(<?php echo $res['reservation_id']; ?>)" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Cancel</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-600">You have no reservations yet.</p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <p class="text-gray-600">This dashboard is under construction for <?php echo $user_role; ?> users.</p>
        <?php endif; ?>

        <!-- Reservation Modal -->
        <div id="reservationModal" class="modal">
            <div class="modal-content">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Make a Reservation</h2>
                <form id="reservationForm" action="reserve_process.php" method="POST">
                    <input type="hidden" name="restaurant_id" id="modalRestaurantId">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2"><i class="fas fa-users mr-2"></i>Party Size</label>
                        <input type="number" name="party_size" min="1" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2"><i class="fas fa-calendar-alt mr-2"></i>Date & Time</label>
                        <input type="datetime-local" name="reservation_time" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="checkAvailability()">
                    </div>
                    <div id="availabilityMessage" class="text-gray-600 mb-4"></div>
                    <div class="flex justify-end gap-4">
                        <button type="button" onclick="closeReservationModal()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                            <i class="fas fa-check mr-2"></i>Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for Interactivity -->
    <script>
        function openReservationModal(restaurantId) {
            document.getElementById('modalRestaurantId').value = restaurantId;
            document.getElementById('reservationModal').classList.add('show');
            document.getElementById('reservationModal').style.display = 'block';
            console.log("Modal opened for restaurant ID: " + restaurantId);
        }
        function closeReservationModal() {
            document.getElementById('reservationModal').classList.remove('show');
            document.getElementById('reservationModal').style.display = 'none';
        }
        function cancelReservation(reservationId) {
            if (confirm("Are you sure you want to cancel this reservation?")) {
                window.location.href = `cancel_reservation.php?reservation_id=${reservationId}`;
            }
        }
        function checkAvailability() {
            const restaurantId = document.getElementById('modalRestaurantId').value;
            const partySize = document.querySelector('[name="party_size"]').value;
            const reservationTime = document.querySelector('[name="reservation_time"]').value;

            if (restaurantId && partySize && reservationTime) {
                fetch('check_availability.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ restaurant_id: restaurantId, party_size: partySize, reservation_time: reservationTime })
                })
                .then(response => response.json())
                .then(data => {
                    const message = document.getElementById('availabilityMessage');
                    if (data.available) {
                        message.textContent = 'Table available!';
                        message.className = 'text-green-600 mb-4';
                    } else {
                        message.textContent = 'No tables available at this time.';
                        message.className = 'text-red-600 mb-4';
                    }
                })
                .catch(error => console.error('Error checking availability:', error));
            }
        }
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const query = document.getElementById('searchQuery').value;
            const preference = document.getElementById('filterPreference').value;
            console.log('Search:', query, 'Preference:', preference);
            // Future: Implement AJAX to update restaurantList dynamically
        });
        console.log("JavaScript loaded successfully!");
    </script>
</body>
</html>