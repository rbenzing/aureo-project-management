<?php
// Ensure the user is not already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_register'])) {
    require_once __DIR__ . '/../../Controllers/AuthController.php';
    $controller = new \App\Controllers\AuthController();
    $controller->register($_POST);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center mt-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md p-6">
            <h1 class="text-2xl font-bold mb-6 text-center">Register</h1>

            <!-- Registration Form -->
            <form method="POST" action="/register" class="space-y-4" autocomplete="off">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium">First Name</label>
                    <input type="text" id="first_name" name="first_name" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="block text-sm font-medium">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium">Email</label>
                    <input type="email" id="email" name="email" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium">Password</label>
                    <input type="password" id="password" name="password" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit_register"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Register
                </button>
            </form>

            <!-- Links -->
            <div class="mt-4 text-center">
                <a href="/login" class="text-indigo-600 hover:text-indigo-900">Already have an account? Login</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>