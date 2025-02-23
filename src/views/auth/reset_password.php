<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <!-- Main Content -->
    <main class="container grow h-max mx-auto flex flex-col items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-1/3 p-6">
            <h1 class="text-2xl font-bold mb-6">Reset Password</h1>
            <!-- Reset Password Form -->
            <form method="POST" action="/reset-password" class="space-y-4 max-w-md" autocomplete="off">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <!-- Token -->
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium">New Password</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <!-- Confirm Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <!-- Submit Button -->
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset Password
                </button>
            </form>
        </div>
        <!-- Links -->
        <div class="mt-4">
            <a href="/login" class="text-indigo-600 hover:text-indigo-900">Back to Login</a>
        </div>
    </main>
    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>