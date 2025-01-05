<?php
require_once '../config/Session.php';
require_once '../config/database.php';
require_once '../models/Category.php';

$session = Session::getInstance();

// Check if user is admin
if (!$session->isLoggedIn() || !$session->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Initialize model
$category = new Category($db);

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if ($category->create($_POST['name'], $_POST['description'])) {
                    $message = 'Catégorie ajoutée avec succès';
                } else {
                    $message = 'Erreur lors de l\'ajout de la catégorie';
                }
                break;

            case 'delete':
                if (isset($_POST['category_id'])) {
                    $category->getById($_POST['category_id']);
                    if ($category->delete()) {
                        $message = 'Catégorie supprimée avec succès';
                    } else {
                        $message = 'Erreur: Impossible de supprimer une catégorie qui contient des véhicules';
                    }
                }
                break;
        }
    }
}

// Get all categories
$categories = Category::getAll($db);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories - Drive & Loc</title>
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
                            <a href="categories.php" class="px-3 py-2 rounded-md text-sm font-medium bg-blue-700">Catégories</a>
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

            <!-- Add Category Form -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Ajouter une catégorie</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" required rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Ajouter la catégorie
                        </button>
                    </div>
                </form>
            </div>

            <!-- Categories List -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4">Liste des catégories</h2>
                <div class="overflow-x-auto">
                    <table id="categoriesTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($cat['name']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($cat['description']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
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
            $('#categoriesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
                }
            });
        });
    </script>
</body>
</html>
