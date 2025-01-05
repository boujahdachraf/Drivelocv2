<?php
require_once '../config/Session.php';
require_once '../config/database.php';

$session = Session::getInstance();

// Check if user is admin
if (!$session->isLoggedIn() || !$session->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [
    'total_vehicles' => 0,
    'active_reservations' => 0,
    'total_categories' => 0,
    'total_reviews' => 0
];

try {
    // Get total vehicles
    $stmt = $db->query("SELECT COUNT(*) as count FROM vehicles");
    $stats['total_vehicles'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get active reservations
    $stmt = $db->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'confirmed'");
    $stats['active_reservations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get total categories
    $stmt = $db->query("SELECT COUNT(*) as count FROM categories");
    $stats['total_categories'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get total reviews
    $stmt = $db->query("SELECT COUNT(*) as count FROM reviews WHERE is_deleted = 0");
    $stats['total_reviews'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get recent reservations
    $stmt = $db->query("
        SELECT r.*, u.username, CONCAT(v.brand, ' ', v.model) as vehicle_name
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN vehicles v ON r.vehicle_id = v.id
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $recent_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Drive & Loc</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="index.php" class="font-bold text-xl">Admin Panel</a>
                        <div class="ml-10 flex space-x-4">
                            <a href="vehicles.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Véhicules</a>
                            <a href="categories.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Catégories</a>
                            <a href="reservations.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Réservations</a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <span class="mr-4">Bienvenue, <?= htmlspecialchars($session->get('username')) ?></span>
                        <a href="../index.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Retour au site</a>
                        <a href="../logout.php" class="ml-4 px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700">Déconnexion</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-2">Total Véhicules</h3>
                    <p class="text-3xl font-bold text-blue-600"><?= $stats['total_vehicles'] ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-2">Réservations Actives</h3>
                    <p class="text-3xl font-bold text-green-600"><?= $stats['active_reservations'] ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-2">Total Catégories</h3>
                    <p class="text-3xl font-bold text-purple-600"><?= $stats['total_categories'] ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-2">Total Avis</h3>
                    <p class="text-3xl font-bold text-yellow-600"><?= $stats['total_reviews'] ?></p>
                </div>
            </div>

            <!-- Recent Reservations -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-xl font-bold mb-4">Réservations Récentes</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Véhicule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date début</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date fin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recent_reservations as $reservation): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($reservation['username']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($reservation['vehicle_name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($reservation['pickup_date']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($reservation['return_date']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            switch($reservation['status']) {
                                                case 'confirmed':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'pending':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'cancelled':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?= htmlspecialchars($reservation['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Actions Rapides</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="vehicles.php" class="bg-blue-50 p-4 rounded-lg hover:bg-blue-100 transition">
                        <h3 class="font-semibold text-blue-700">Gérer les Véhicules</h3>
                        <p class="text-sm text-blue-600">Ajouter, modifier ou supprimer des véhicules</p>
                    </a>
                    <a href="categories.php" class="bg-purple-50 p-4 rounded-lg hover:bg-purple-100 transition">
                        <h3 class="font-semibold text-purple-700">Gérer les Catégories</h3>
                        <p class="text-sm text-purple-600">Organiser les catégories de véhicules</p>
                    </a>
                    <a href="reservations.php" class="bg-green-50 p-4 rounded-lg hover:bg-green-100 transition">
                        <h3 class="font-semibold text-green-700">Gérer les Réservations</h3>
                        <p class="text-sm text-green-600">Voir et gérer les réservations en cours</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
