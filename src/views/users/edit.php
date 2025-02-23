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
    <title>Edit User</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Edit User</h1>
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
        <!-- Edit User Form -->
        <form method="POST" action="/update_user" class="space-y-4 max-w-md">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($user->id); ?>">
            <!-- First Name -->
            <div>
                <label for="first_name" class="block text-sm font-medium">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user->first_name); ?>" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <!-- Last Name -->
            <div>
                <label for="last_name" class="block text-sm font-medium">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user->last_name); ?>" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user->email); ?>" required
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
                        <option value="<?= htmlspecialchars($role->id); ?>" <?= $role->id == $user->role_id ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($role->name); ?>
                        </option>
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
                        <option value="<?= htmlspecialchars($company->id); ?>" <?= $company->id == $user->company_id ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($company->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Submit Button -->
            <button type="submit" name="update_user_submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update User
            </button>
        </form>
    </main>
    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>