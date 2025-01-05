<?php
require_once '../config/Session.php';
require_once '../config/database.php';
require_once '../models/Reservation.php';
require_once '../models/Vehicle.php';
require_once '../models/User.php';

$session = Session::getInstance();

// Check if user is admin
if (!$session->isLoggedIn() || !$session->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Initialize models
$reservation = new Reservation($db);
$vehicle = new Vehicle($db);

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $reservationData = [
                    'user_id' => $_POST['user_id'],
                    'vehicle_id' => $_POST['vehicle_id'],
                    'pickup_date' => $_POST['pickup_date'],
                    'return_date' => $_POST['return_date']
                ];

                if ($reservation->create($reservationData)) {
                    $message = 'Réservation ajoutée avec succès';
                } else {
                    $message = 'Erreur: Le véhicule n\'est pas disponible pour ces dates';
                }
                break;

            case 'update_status':
                if (isset($_POST['reservation_id']) && isset($_POST['status'])) {
                    $reservation->getById($_POST['reservation_id']);
                    if ($reservation->updateStatus($_POST['status'])) {
                        $message = 'Statut de la réservation mis à jour avec succès';
                    } else {
                        $message = 'Erreur lors de la mise à jour du statut';
                    }
                }
                break;
        }
    }
}

// Get all reservations
$reservations = Reservation::getAll($db);

// Get users and vehicles for the form
$stmt = $db->query("SELECT id, username FROM users ORDER BY username");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$vehicles = Vehicle::getAll($db);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations - Drive & Loc</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="dashboard.php" class="font-bold text-xl">Admin Panel</a>
                        <div class="ml-10 flex space-x-4">
                            <a href="vehicles.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Véhicules</a>
                            <a href="categories.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Catégories</a>
                            <a href="reservations.php" class="px-3 py-2 rounded-md text-sm font-medium bg-blue-700">Réservations</a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <a href="../index.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Retour au site</a>
                        <a href="../logout.php" class="ml-4 px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700">Déconnexion</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <?php if ($message): ?>
                <div class="mb-4 p-4 rounded <?= strpos($message, 'Erreur') === false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Add Reservation Form -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Ajouter une réservation</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="add">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Client</label>
                        <select name="user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Véhicule</label>
                        <select name="vehicle_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['brand'] . ' ' . $v['model']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de début</label>
                        <input type="date" name="pickup_date" required min="<?= date('Y-m-d') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                        <input type="date" name="return_date" required min="<?= date('Y-m-d') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Ajouter la réservation
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reservations List -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4">Liste des réservations</h2>
                <div class="overflow-x-auto">
                    <table id="reservationsTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Véhicule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date début</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date fin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($reservations as $r): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($r['user_name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($r['vehicle_name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($r['pickup_date']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($r['return_date']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= number_format($r['total_price'], 2) ?>€</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php
                                            switch($r['status']) {
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
                                            <?= htmlspecialchars($r['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" class="inline-flex space-x-2">
                                            <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                                            <input type="hidden" name="action" value="update_status">
                                            <select name="status" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="pending" <?= $r['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                                                <option value="confirmed" <?= $r['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmée</option>
                                                <option value="completed" <?= $r['status'] === 'completed' ? 'selected' : '' ?>>Terminée</option>
                                                <option value="cancelled" <?= $r['status'] === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
                                            </select>
                                            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                Mettre à jour
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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

            // Add date validation
            const pickupDate = document.querySelector('input[name="pickup_date"]');
            const returnDate = document.querySelector('input[name="return_date"]');

            pickupDate.addEventListener('change', function() {
                returnDate.min = this.value;
            });

            returnDate.addEventListener('change', function() {
                if (this.value < pickupDate.value) {
                    this.value = pickupDate.value;
                }
            });
        });
    </script>
</body>
</html>
