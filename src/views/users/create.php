<?php
//file: Views/Users/create.php
declare(strict_types=1);

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
    <title>Create User</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>
    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>
    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <h1 class="text-2xl font-bold mb-6">Create User</h1>

        <!-- Create User Form -->
        <form method="POST" action="/create_user" class="space-y-4 max-w-md">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
                        <option value="<?php echo htmlspecialchars($role->id); ?>"><?php echo htmlspecialchars($role->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Company -->
            <div>
                <label for="company_id" class="block text-sm font-medium">Company</label>
                <select id="company_id" name="company_id"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select a company</option>
                    <?php
                    $companies = (new \App\Models\Company())->getAll();
                    foreach ($companies as $company): ?>
                        <option value="<?php echo htmlspecialchars($company->id); ?>"><?php echo htmlspecialchars($company->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Submit Button -->
            <button type="submit" name="register_submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Create User
            </button>
        </form>
    </main>
    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>
</body>
</html>