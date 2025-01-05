<?php
require_once 'config/database.php';
require_once 'config/Session.php';
require_once 'models/User.php';

$session = Session::getInstance();
$error = '';

// Redirect if already logged in
if ($session->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    
    if ($user->login($_POST['email'], $_POST['password'])) {
        $session->set('user_id', $user->getId());
        $session->set('username', $user->getUsername());
        $session->set('role', $user->getRole());
        header('Location: index.php');
        exit;
    } else {
        $error = 'Email ou mot de passe incorrect';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Drive & Loc</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-xl p-8">
            <h2 class="text-2xl font-bold text-center mb-8">Connexion</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-700">Email</label>
                    <input type="email" name="email" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700">Mot de passe</label>
                    <input type="password" name="password" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white rounded-md py-2 px-4 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Se connecter
                </button>
            </form>

            <p class="mt-4 text-center text-gray-600">
                Pas encore de compte? 
                <a href="register.php" class="text-blue-600 hover:text-blue-700">S'inscrire</a>
            </p>
        </div>
    </div>
</body>
</html>
