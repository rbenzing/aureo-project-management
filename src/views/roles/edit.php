<?php
//file: Views/Roles/edit.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Fetch grouped permissions
$permissionModel = new \App\Models\Permission();
$groupedPermissions = $permissionModel->getGroupedPermissions();

// Get current role's permissions
$currentPermissionIds = array_column($role->permissions, 'id');

// Prepare form data from session if exists
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Role: <?= htmlspecialchars($role->name); ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <!-- Notifications -->
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <!-- Breadcrumb -->
        <?php echo \App\Utils\Breadcrumb::render('roles/edit', ['id' => $role->id]); ?>

        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Edit Role: <?= htmlspecialchars($role->name); ?>
                </h1>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    Modify role details and permissions
                </p>
            </div>

            <form method="POST" action="/roles/update" class="space-y-6 p-6">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$role->id); ?>">

                <!-- Role Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Role Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required 
                        value="<?= htmlspecialchars($formData['name'] ?? $role->name); ?>"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Enter role name"
                    >
                </div>

                <!-- Role Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Provide a brief description of the role"
                    ><?= htmlspecialchars($formData['description'] ?? ($role->description ?? '')); ?></textarea>
                </div>

                <!-- Permissions -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Manage Permissions
                    </h3>
                    
                    <?php foreach ($groupedPermissions as $group => $permissions): ?>
                        <div class="mb-4">
                            <h4 class="text-md font-semibold text-gray-700 dark:text-gray-300 mb-2 capitalize">
                                <?= htmlspecialchars(str_replace('_', ' ', $group)); ?> Permissions
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <?php foreach ($permissions as $permission): ?>
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            id="permission_<?= htmlspecialchars((string)$permission->id); ?>" 
                                            name="permissions[]" 
                                            value="<?= htmlspecialchars((string)$permission->id); ?>"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            <?= 
                                                (isset($formData['permissions']) && in_array($permission->id, $formData['permissions'])) || 
                                                in_array($permission->id, $currentPermissionIds) 
                                                ? 'checked' : ''; 
                                            ?>
                                        >
                                        <label 
                                            for="permission_<?= htmlspecialchars((string)$permission->id); ?>" 
                                            class="ml-2 block text-sm text-gray-700 dark:text-gray-300"
                                        >
                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $permission->name))); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Action Buttons -->
                <div class="pt-5">
                    <div class="flex justify-between items-center">
                        <!-- Role Details Section -->
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <p>Created: <?= date('M j, Y', strtotime($role->created_at)); ?></p>
                            <p>Updated: <?= date('M j, Y', strtotime($role->updated_at)); ?></p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex space-x-3">
                            <a 
                                href="/roles" 
                                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Update Role
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>
</body>
</html>