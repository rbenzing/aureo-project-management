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
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <h1 class="text-2xl font-bold mb-6">Edit Role</h1>
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
        <!-- Edit Role Form -->
        <form method="POST" action="/roles/update" class="space-y-4 max-w-md">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($role->id); ?>">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium">Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($role->name); ?>" required
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium">Description</label>
                <textarea id="description" name="description"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?= htmlspecialchars($role->description ?? ''); ?></textarea>
            </div>
            <!-- Permissions -->
            <div>
                <label class="block text-sm font-medium">Permissions</label>
                <?php
                $allPermissions = (new \App\Models\Permission())->getAll();
                $rolePermissions = (new \App\Models\Role())->getPermissions($role->id);
                $rolePermissionIds = array_column($rolePermissions, 'id');
                foreach ($allPermissions as $permission): ?>
                    <div class="flex items-center">
                        <input type="checkbox" id="permission_<?= htmlspecialchars($permission->id); ?>" name="permissions[]" value="<?= htmlspecialchars($permission->id); ?>"
                               <?= in_array($permission->id, $rolePermissionIds) ? 'checked' : ''; ?>
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="permission_<?= htmlspecialchars($permission->id); ?>" class="ml-2 text-sm text-gray-700">
                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $permission->name))); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Submit Button -->
            <button type="submit" name="update_role_submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update Role
            </button>
        </form>
    </main>
    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>