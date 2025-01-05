<?php
session_start();
require_once './config/database.php';
require_once './config/session.php';
require_once './models/Category.php';
require_once './models/Vehicle.php';
require_once './models/Reservation.php';      

$database = new Database();
$db = $database->getConnection();
$session = Session::getInstance();

// Initialize variables
$message = '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_id = isset($_GET['category']) ? $_GET['category'] : '';

// Get vehicles based on filters
$filters = [];
if (!empty($category_id)) $filters['category_id'] = $category_id;
if (!empty($search)) $filters['search'] = $search;

// Get vehicles and categories
$vehicles = Vehicle::getAll($db, $filters);
$categories = Category::getAll($db);

// Get user reservations if logged in
$userReservations = [];
if ($session->isLoggedIn()) {
    $reservation = new Reservation($db);
    $userReservations = $reservation->getUserReservations($db, $session->get('user_id'));
}

// Handle reservation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reserve') {
    if (!$session->isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    $reservation = new Reservation($db);
    $reservationData = [
        'user_id' => $session->get('user_id'),
        'vehicle_id' => $_POST['vehicle_id'],
        'pickup_date' => $_POST['pickup_date'],
        'return_date' => $_POST['return_date']
    ];

    if ($reservation->create($reservationData)) {
        $message = 'Réservation effectuée avec succès! En attente de confirmation.';
    } else {
        $message = 'Erreur: Le véhicule n\'est pas disponible pour ces dates.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $reservation = new Reservation($db);
    $reservation->cancel($db, $_POST['reservation_id']);
    $message = 'Réservation annulée avec succès!';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drive Location - Location de Véhicules</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Racing+Sans+One&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="flex items-center">
                        <img src="assets/images/Car Renting.svg" alt="Drive & Loc" class="logo">
                        <span class="brand-name ml-2">Drive Location</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($session->isLoggedIn()): ?>
                        <?php if ($session->isAdmin()): ?>
                            <a href="admin/dashboard.php" class="nav-link px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                                <i class="fas fa-dashboard mr-2"></i>Admin Panel
                            </a>
                        <?php endif; ?>
                        <a href="profile.php" class="nav-link px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                            <i class="fas fa-user mr-2"></i>Mon Profil
                        </a>
                        <a href="logout.php" class="nav-link px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700">
                            <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="nav-link px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                            <i class="fas fa-sign-in-alt mr-2"></i>Connexion
                        </a>
                        <a href="register.php" class="nav-link px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                            <i class="fas fa-user-plus mr-2"></i>Inscription
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section mb-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold mb-4">Location de Véhicules de Qualité</h1>
            <p class="text-xl">Trouvez le véhicule parfait pour tous vos besoins</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <?php if ($message): ?>
            <div class="mb-4 p-4 rounded <?= strpos($message, 'Erreur') === false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($session->isLoggedIn()): ?>
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4">Mes Réservations</h2>
                <?php if (empty($userReservations)): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-600">Vous n'avez pas encore de réservations.</p>
                    </div>
                <?php else: ?>
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($userReservations as $res): ?>
                            <div class="bg-white rounded-lg shadow p-6">
                                <div class="flex items-center mb-4">
                                    <?php if ($res['image_url']): ?>
                                        <img src="<?= htmlspecialchars($res['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($res['brand'] . ' ' . $res['model']) ?>"
                                             class="w-20 h-20 object-cover rounded-lg">
                                    <?php endif; ?>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold">
                                            <?= htmlspecialchars($res['brand'] . ' ' . $res['model']) ?>
                                        </h3>
                                        <p class="text-gray-600"><?= htmlspecialchars($res['category_name']) ?></p>
                                    </div>
                                </div>
                                
                                <div class="space-y-2 border-t pt-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Du:</span>
                                        <span><?= date('d/m/Y', strtotime($res['pickup_date'])) ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Au:</span>
                                        <span><?= date('d/m/Y', strtotime($res['return_date'])) ?></span>
                                    </div>
                                    <div class="flex justify-between font-semibold">
                                        <span>Total:</span>
                                        <span><?= number_format($res['total_price'], 2) ?> €</span>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                                        <?php
                                        switch ($res['status']) {
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
                                        <?= ucfirst(htmlspecialchars($res['status'])) ?>
                                    </span>
                                    
                                    <?php if ($res['status'] === 'pending' || $res['status'] === 'confirmed'): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="reservation_id" value="<?= $res['id'] ?>">
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                Annuler
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                    <select name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category_id == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Recherche</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Marque, modèle..." 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Filtrer
                    </button>
                </div>
            </form>
        </div>

        <!-- Vehicles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($vehicles as $vehicle): ?>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <?php if ($vehicle['image_url']): ?>
                        <img src="<?= htmlspecialchars($vehicle['image_url']) ?>" 
                             alt="<?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>"
                             class="w-full h-48 object-cover">
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <h3 class="text-lg font-bold"><?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?></h3>
                        <p class="text-gray-600"><?= htmlspecialchars($vehicle['category_name']) ?></p>
                        <p class="text-sm text-gray-500 mt-2"><?= htmlspecialchars($vehicle['description']) ?></p>
                        <p class="text-lg font-bold text-blue-600 mt-2"><?= number_format($vehicle['price_per_day'], 2) ?>€ / jour</p>
                        
                        <?php if ($vehicle['status'] === 'available'): ?>
                            <button onclick="openReservationModal(<?= htmlspecialchars(json_encode($vehicle)) ?>)" 
                                    class="mt-4 w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Réserver
                            </button>
                        <?php else: ?>
                            <button disabled class="mt-4 w-full bg-gray-300 text-gray-500 px-4 py-2 rounded-md cursor-not-allowed">
                                Non disponible
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($vehicles)): ?>
            <div class="text-center py-12">
                <p class="text-gray-500">Aucun véhicule disponible pour les critères sélectionnés.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Reservation Modal -->
    <div id="reservationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-bold" id="modalTitle"></h3>
                <form id="reservationForm" method="POST" class="mt-4">
                    <input type="hidden" name="action" value="reserve">
                    <input type="hidden" name="vehicle_id" id="vehicleId">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Date de début</label>
                        <input type="date" name="pickup_date" required min="<?= date('Y-m-d') ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                        <input type="date" name="return_date" required min="<?= date('Y-m-d') ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button" onclick="closeReservationModal()"
                                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            Annuler
                        </button>
                        <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">À propos de Drive & Loc</h3>
                    <p class="text-gray-300">
                        Votre partenaire de confiance pour la location de véhicules depuis 2024.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Liens Rapides</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="footer-link">Accueil</a></li>
                        <li><a href="#" class="footer-link">Nos Véhicules</a></li>
                        <li><a href="#" class="footer-link">Comment ça marche</a></li>
                        <li><a href="#" class="footer-link">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Contact</h3>
                    <ul class="space-y-2">
                        <li><i class="fas fa-phone mr-2"></i>+33 1 23 45 67 89</li>
                        <li><i class="fas fa-envelope mr-2"></i>contact@driveloc.fr</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i>123 Rue de Paris, 75000 Paris</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Suivez-nous</h3>
                    <div class="flex">
                        <a href="#" class="footer-link"><i class="fab fa-facebook social-icon"></i></a>
                        <a href="#" class="footer-link"><i class="fab fa-twitter social-icon"></i></a>
                        <a href="#" class="footer-link"><i class="fab fa-instagram social-icon"></i></a>
                        <a href="#" class="footer-link"><i class="fab fa-linkedin social-icon"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-blue-800 mt-8 pt-8 text-center">
                <p>&copy; <?php echo date('Y'); ?> Drive & Loc. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        function openReservationModal(vehicle) {
            <?php if (!$session->isLoggedIn()): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            document.getElementById('modalTitle').textContent = `Réserver ${vehicle.brand} ${vehicle.model}`;
            document.getElementById('vehicleId').value = vehicle.id;
            document.getElementById('reservationModal').classList.remove('hidden');
        }

        function closeReservationModal() {
            document.getElementById('reservationModal').classList.add('hidden');
        }

        // Date validation
        document.addEventListener('DOMContentLoaded', function() {
            const pickupDate = document.querySelector('input[name="pickup_date"]');
            const returnDate = document.querySelector('input[name="return_date"]');

            pickupDate.addEventListener('change', function() {
                returnDate.min = this.value;
                if (returnDate.value && returnDate.value < this.value) {
                    returnDate.value = this.value;
                }
            });
        });
    </script>
</body>
</html>
