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
    if($user->countUsers() <= 0) {
        $user->register($_POST['username'], $_POST['email'], $_POST['password'], 'admin');
    }
    else {
        $user->register($_POST['username'], $_POST['email'], $_POST['password'], 'client');
    }

    header("Location: login.php");
    exit;

}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Drive Location</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Racing+Sans+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="register-background">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full glass-effect rounded-xl p-8 space-y-8">
            <div>
                <a href="index.php" class="block text-center mb-6">
                    <img src="assets/images/Car Renting.svg" alt="Logo" class="mx-auto h-20 w-auto">
                </a>
                <h2 class="register-title text-3xl text-center">Rejoignez Drive Location</h2>
                <p class="mt-2 text-center text-gray-600">Créez votre compte et commencez à louer</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium">Nom d'utilisateur</label>
                        <input type="text" name="username" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium">Email</label>
                        <input type="email" name="email" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium">Mot de passe</label>
                        <input type="password" name="password" required 
                               class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white
                                   bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
                                   transition-all duration-300 transform hover:scale-105">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        S'inscrire
                    </button>
                </div>
            </form>

            <div class="text-center">
                <p class="text-gray-600">
                    Déjà un compte? 
                    <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-300">
                        Se connecter
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
