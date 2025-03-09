<?php
//file: Views/Roles/view.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Get users with this role
$usersWithRole = $this->roleModel->getUsers($role->id);

// Ensure permissions exist and are an array
$role->permissions = $role->permissions ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Details: <?= htmlspecialchars($role->name); ?></title>
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
        <?php echo \App\Utils\Breadcrumb::render('roles/view', ['id' => $role->id]); ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Role Details Card -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        <?= htmlspecialchars($role->name); ?> Role Details
                    </h1>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        Detailed view of role configuration and permissions
                    </p>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                                Role Information
                            </h3>
                            <div class="space-y-2">
                                <p>
                                    <span class="font-medium text-gray-500 dark:text-gray-400">Name:</span>
                                    <?= htmlspecialchars($role->name); ?>
                                </p>
                                <p>
                                    <span class="font-medium text-gray-500 dark:text-gray-400">Description:</span>
                                    <?= htmlspecialchars($role->description ?? 'No description'); ?>
                                </p>
                                <p>
                                    <span class="font-medium text-gray-500 dark:text-gray-400">Created:</span>
                                    <?= date('M j, Y h:i A', strtotime($role->created_at)); ?>
                                </p>
                                <p>
                                    <span class="font-medium text-gray-500 dark:text-gray-400">Last Updated:</span>
                                    <?= date('M j, Y h:i A', strtotime($role->updated_at)); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Permissions Section -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">
                                Assigned Permissions
                            </h3>
                            <?php if (!empty($role->permissions)): ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <?php 
                                    $groupedPermissions = [];
                                    foreach ($role->permissions as $permission) {
                                        $parts = explode('_', $permission->name);
                                        $group = $parts[0];
                                        if (!isset($groupedPermissions[$group])) {
                                            $groupedPermissions[$group] = [];
                                        }
                                        $groupedPermissions[$group][] = $permission;
                                    }

                                    foreach ($groupedPermissions as $group => $permissions): ?>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1 capitalize">
                                                <?= htmlspecialchars(str_replace('_', ' ', $group)); ?>
                                            </h4>
                                            <ul class="space-y-1">
                                                <?php foreach ($permissions as $permission): ?>
                                                    <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $permission->name))); ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    No permissions assigned to this role.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users with this Role -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Users with this Role
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        <?= count($usersWithRole); ?> user(s) currently assigned
                    </p>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <?php if (!empty($usersWithRole)): ?>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($usersWithRole as $user): ?>
                                <li class="py-4 flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                        <span class="text-gray-600 dark:text-gray-300">
                                            <?= strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)); ?>
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <?= htmlspecialchars($user->first_name . ' ' . $user->last_name); ?>
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <?= htmlspecialchars($user->email); ?>
                                        </p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No users are currently assigned to this role.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex space-x-3">
            <?php if (isset($_SESSION['user']['permissions']) && in_array('edit_roles', $_SESSION['user']['permissions'])): ?>
                <a 
                    href="/roles/edit/<?= htmlspecialchars((string)$role->id); ?>" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Edit Role
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user']['permissions']) && in_array('delete_roles', $_SESSION['user']['permissions'])): ?>
                <form 
                    action="/roles/delete/<?= htmlspecialchars((string)$role->id); ?>" 
                    method="POST" 
                    onsubmit="return confirm('Are you sure you want to delete this role? This cannot be undone.');"
                >
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        Delete Role
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>
</body>
</html>