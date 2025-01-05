<?php
require_once '../config/Session.php';
require_once '../config/database.php';
require_once '../models/Vehicle.php';
require_once '../models/Category.php';

$session = Session::getInstance();

// Check if user is admin
if (!$session->isLoggedIn() || !$session->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Initialize models
$vehicle = new Vehicle($db);
$category = new Category($db);

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Handle image upload
                $image_url = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/vehicles/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid() . '.' . $file_extension;
                    $target_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        $image_url = 'uploads/vehicles/' . $file_name;
                    }
                }

                $vehicleData = [
                    'brand' => $_POST['brand'],
                    'model' => $_POST['model'],
                    'year' => $_POST['year'],
                    'category_id' => $_POST['category_id'],
                    'price_per_day' => $_POST['price_per_day'],
                    'status' => 'available',
                    'image_url' => $image_url,
                    'description' => $_POST['description']
                ];

                if ($vehicle->create($vehicleData)) {
                    $message = 'Véhicule ajouté avec succès';
                } else {
                    $message = 'Erreur lors de l\'ajout du véhicule';
                }
                break;

            case 'delete':
                if (isset($_POST['vehicle_id'])) {
                    $vehicle->getById($_POST['vehicle_id']);
                    if ($vehicle->delete()) {
                        $message = 'Véhicule supprimé avec succès';
                    } else {
                        $message = 'Erreur: Impossible de supprimer un véhicule avec des réservations actives';
                    }
                }
                break;
        }
    }
}

// Get all categories for the form
$categories = Category::getAll($db);

// Get all vehicles
$vehicles = Vehicle::getAll($db);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Véhicules - Drive & Loc</title>
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
                            <a href="vehicles.php" class="px-3 py-2 rounded-md text-sm font-medium bg-blue-700">Véhicules</a>
                            <a href="categories.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Catégories</a>
                            <a href="reservations.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Réservations</a>
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

            <!-- Add Vehicle Form -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Ajouter un véhicule</h2>
                <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="add">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Marque</label>
                        <input type="text" name="brand" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Modèle</label>
                        <input type="text" name="model" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Année</label>
                        <input type="number" name="year" required min="1900" max="<?= date('Y') + 1 ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                        <select name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Prix par jour (€)</label>
                        <input type="number" name="price_per_day" required min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Image</label>
                        <input type="file" name="image" accept="image/*" class="mt-1 block w-full">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" required rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Ajouter le véhicule
                        </button>
                    </div>
                </form>
            </div>

            <!-- Vehicles List -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4">Liste des véhicules</h2>
                <div class="overflow-x-auto">
                    <table id="vehiclesTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Marque</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modèle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Année</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix/Jour</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($vehicles as $v): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($v['image_url']): ?>
                                            <img src="../<?= htmlspecialchars($v['image_url']) ?>" alt="<?= htmlspecialchars($v['model']) ?>" class="h-10 w-10 object-cover rounded">
                                        <?php else: ?>
                                            <div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center">
                                                <span class="text-gray-500">N/A</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($v['brand']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($v['model']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($v['year']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($v['category_name']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= number_format($v['price_per_day'], 2) ?>€</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $v['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                            <?= htmlspecialchars($v['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
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
            $('#vehiclesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
                }
            });
        });
    </script>
</body>
</html>
