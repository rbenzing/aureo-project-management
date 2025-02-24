<?php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use \App\Core\Config;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Config::get('company_name', 'Slimbooks') ?> - Login</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <!-- Main Content -->
    <main class="container grow h-max mx-auto flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-1/3 p-6">
            <h1 class="text-2xl font-bold mb-6 text-center">
                Login to <?= htmlspecialchars(Config::get('company_name', 'Slimbooks')) ?>
            </h1>
            <!-- Display Errors or Success Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="/login" class="space-y-4 max-w-md">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <!-- Email -->
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        autocomplete="email"
                        placeholder="Enter your email"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                               dark:bg-gray-700 dark:text-gray-200"
                    >
                </div>
                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm 
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                               dark:bg-gray-700 dark:text-gray-200"
                    >
                </div>
                <!-- Submit Button -->
                <button 
                        type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md 
                               shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500
                               dark:bg-indigo-500 dark:hover:bg-indigo-600"
                    >
                        Sign In
                </button>
            </form>
            <!-- Links -->
            <div class="mt-4">
                <a href="/register" class="text-indigo-600 hover:text-indigo-900">Register</a>
                <span class="mx-2">|</span>
                <a href="/forgot-password" class="text-indigo-600 hover:text-indigo-900">Forgot Password?</a>
            </div>
        </div>
    </main>
    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>