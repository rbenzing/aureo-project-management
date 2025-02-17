<?php
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to access this page.';
    header('Location: /login');
    exit;
}

// Fetch the role details
require_once __DIR__ . '/../../Controllers/RoleController.php';
$controller = new \App\Controllers\RoleController();
$role = (new \App\Models\Role())->find($_GET['id']);
if (!$role) {
    $_SESSION['error'] = 'Role not found.';
    header('Location: /roles');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->update($_POST, $_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Role</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Edit Role</h1>

        <!-- Edit Role Form -->
        <form method="POST" action="/roles/edit.php?id=<?php echo $role->id; ?>" class="space-y-4 max-w-md">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($role->name); ?>" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium">Description</label>
                <textarea id="description" name="description"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($role->description ?? ''); ?></textarea>
            </div>

            <!-- Permissions -->
            <div>
                <label class="block text-sm font-medium">Permissions</label>
                <?php
                $permissions = [
                    'view_dashboard',
                    'manage_users',
                    'manage_roles',
                    'manage_projects',
                    'manage_tasks',
                    'manage_companies',
                ];
                $rolePermissions = (new \App\Models\Role())->getPermissions($role->id);
                $rolePermissionNames = array_column($rolePermissions, 'name');
                foreach ($permissions as $permission): ?>
                    <div class="flex items-center">
                        <input type="checkbox" id="permission_<?php echo htmlspecialchars($permission); ?>" name="permissions[]" value="<?php echo htmlspecialchars($permission); ?>"
                            <?php echo in_array($permission, $rolePermissionNames) ? 'checked' : ''; ?>
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="permission_<?php echo htmlspecialchars($permission); ?>" class="ml-2 text-sm text-gray-700">
                            <?php echo ucfirst(str_replace('_', ' ', $permission)); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update Role
            </button>
        </form>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>