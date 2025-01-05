<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drive & Loc - Car Rental</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="#" class="text-xl font-bold">Drive & Loc</a>
            <ul class="flex space-x-4">
                <li><a href="#" class="hover:underline">Explore Vehicles</a></li>
                <li><a href="#" class="hover:underline">My Reservations</a></li>
                <li><a href="#" class="hover:underline">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Filters Section -->
    <div class="container mx-auto mt-6">
        <div class="flex justify-between items-center bg-white p-4 shadow rounded">
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <select id="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option>All</option>
                    <option>SUV</option>
                    <option>Sedan</option>
                    <option>Electric</option>
                </select>
            </div>

            <div class="flex-1 mx-4">
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" id="search" placeholder="Search by model or features" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <button class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">Apply Filters</button>
        </div>
    </div>

    <!-- Vehicles Section -->
    <div class="container mx-auto mt-8">
        <h2 class="text-2xl font-bold mb-4">Available Vehicles</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Vehicle Card -->
            <div class="bg-white shadow rounded p-4">
                <img src="https://via.placeholder.com/150" alt="Vehicle Image" class="w-full h-40 object-cover rounded">
                <h3 class="text-xl font-bold mt-2">Tesla Model S</h3>
                <p class="text-gray-600">Price: $100/day</p>
                <p class="text-green-500">Available</p>
                <button class="bg-blue-600 text-white px-4 py-2 rounded mt-2 hover:bg-blue-700">View Details</button>
            </div>

            <!-- Repeat Vehicle Card for More Cars -->
            <div class="bg-white shadow rounded p-4">
                <img src="https://via.placeholder.com/150" alt="Vehicle Image" class="w-full h-40 object-cover rounded">
                <h3 class="text-xl font-bold mt-2">BMW X5</h3>
                <p class="text-gray-600">Price: $120/day</p>
                <p class="text-green-500">Available</p>
                <button class="bg-blue-600 text-white px-4 py-2 rounded mt-2 hover:bg-blue-700">View Details</button>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex justify-center">
            <button class="bg-gray-300 text-gray-700 px-4 py-2 rounded-l">Previous</button>
            <button class="bg-blue-600 text-white px-4 py-2">1</button>
            <button class="bg-gray-300 text-gray-700 px-4 py-2">2</button>
            <button class="bg-gray-300 text-gray-700 px-4 py-2 rounded-r">Next</button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-600 text-white mt-12 p-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 Drive & Loc. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
