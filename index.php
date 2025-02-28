<?php
// Include the header
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nairobi Eats - Discover the Best Restaurants in Nairobi</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

 <!-- Hero Section -->
<section class="bg-cover bg-center h-screen relative" style="background-image: url('images/food.jpg');">
    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    <!-- Content -->
    <div class="container mx-auto px-4 h-full flex items-center justify-center relative z-10">
        <div class="text-center">
            <h1 class="text-5xl font-bold text-white mb-4 drop-shadow-lg">Discover the Best Restaurants in Nairobi</h1>
            <p class="text-xl text-white mb-8 drop-shadow-lg">Book a table at your favorite restaurant in just a few clicks.</p>
            <form action="search.php" method="GET" class="flex justify-center">
                <input type="text" name="query" placeholder="Search for restaurants..." class="w-96 px-4 py-3 rounded-l-lg focus:outline-none">
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-r-lg hover:bg-blue-700">Search</button>
            </form>
        </div>
    </div>
</section>

    <!-- Featured Restaurants Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Featured Restaurants</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Restaurant Card 1 -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="images/carny.jpg" alt="Restaurant 1" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2">The Carnivore</h3>
                        <p class="text-gray-600 mb-4">Experience the best of Kenyan BBQ at this iconic restaurant.</p>
                        <a href="#" class="text-blue-600 hover:underline">Book Now</a>
                    </div>
                </div>

                <!-- Restaurant Card 2 -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="images/talisman.jpg" alt="Restaurant 2" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2">Talisman Restaurant</h3>
                        <p class="text-gray-600 mb-4">A cozy spot offering a fusion of African and European cuisine.</p>
                        <a href="#" class="text-blue-600 hover:underline">Book Now</a>
                    </div>
                </div>

                <!-- Restaurant Card 3 -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <img src="images/java.jpg" alt="Restaurant 3" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2">Java House</h3>
                        <p class="text-gray-600 mb-4">Known for its coffee, breakfast, and casual dining.</p>
                        <a href="#" class="text-blue-600 hover:underline">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call-to-Action Section -->
    <section class="bg-blue-600 py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Ready to Reserve Your Table?</h2>
            <p class="text-xl text-white mb-8">Join thousands of happy diners and book your table today.</p>
            <a href="signup.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100">Sign Up Now</a>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

</body>
</html>