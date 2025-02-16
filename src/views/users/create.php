<?php
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to access this page.';
    header('Location: /login');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../Controllers/UserController.php';
    $controller = new \App\Controllers\UserController();
    $controller->create($_POST);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow md:ml-64 p-6">
        <h1 class="text-2xl font-bold mb-6">Create User</h1>

        <!-- Create User Form -->
        <form method="POST" action="/users/create.php" class="space-y-4 max-w-md">
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

            <!-- Role -->
            <div>
                <label for="role_id" class="block text-sm font-medium">Role</label>
                <select id="role_id" name="role_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select a role</option>
                    <?php
                    $roles = (new \App\Models\Role())->getAll();
                    foreach ($roles as $role): ?>
                        <option value="<?php echo $role->id; ?>"><?php echo htmlspecialchars($role->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Create User
            </button>
        </form>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>