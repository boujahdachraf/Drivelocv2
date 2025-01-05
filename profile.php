<?php
require_once 'config/Session.php';
require_once 'config/database.php';
require_once 'models/Reservation.php';
require_once 'models/Vehicle.php';

$session = Session::getInstance();

if (!$session->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get user's reservations
$reservations = Reservation::getUserReservations($db, $session->getUserId());
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Drive & Loc</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="font-bold text-xl">Drive & Loc</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($session->isAdmin()): ?>
                        <a href="admin/dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Admin Panel</a>
                    <?php endif; ?>
                    <a href="profile.php" class="px-3 py-2 rounded-md text-sm font-medium bg-blue-700">Mon Profil</a>
                    <a href="logout.php" class="px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700">Déconnexion</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- User Info -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4">Bienvenue, <?= htmlspecialchars($session->get('username')) ?></h2>
            <p class="text-gray-600">Gérez vos réservations et consultez votre historique.</p>
        </div>

        <!-- Reservations -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-xl font-bold mb-4">Mes Réservations</h3>
            
            <?php if (empty($reservations)): ?>
                <p class="text-gray-500 text-center py-4">Vous n'avez pas encore de réservations.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table id="reservationsTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Véhicule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date début</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date fin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php if ($reservation['image_url']): ?>
                                                <img src="<?= htmlspecialchars($reservation['image_url']) ?>" 
                                                     alt="<?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?>"
                                                     class="h-10 w-10 rounded-full mr-3 object-cover">
                                            <?php endif; ?>
                                            <div>
                                                <?= htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($reservation['category_name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($reservation['pickup_date']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($reservation['return_date']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= number_format($reservation['total_price'], 2) ?>€</td>
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
                                                case 'completed':
                                                    echo 'bg-blue-100 text-blue-800';
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
            <?php endif; ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#reservationsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
                },
                "order": [[2, "desc"]] // Sort by pickup date by default
            });
        });
    </script>
</body>
</html>
