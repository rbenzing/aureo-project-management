<?php
//file: Views/Roles/edit.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;

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
    <title>Edit Role: <?= htmlspecialchars($role->name) ?> - <?= htmlspecialchars(Config::get('company_name', 'Slimbooks')) ?></title>
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
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Edit Role: <?= htmlspecialchars($role->name) ?></h1>
            
            <div class="flex space-x-2">
                <a href="/roles/view/<?= htmlspecialchars((string)$role->id) ?>" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Role
                </a>
                <a href="/roles" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Role Information</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update role details and assigned permissions</p>
            </div>
            
            <!-- Edit Role Form -->
            <form method="POST" action="/roles/update" class="p-6 space-y-6">
                <!-- CSRF Token and ID -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$role->id); ?>">
                
                <!-- Form Grid Layout -->
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <!-- Role Name -->
                    <div class="sm:col-span-3">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1">
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($formData['name'] ?? $role->name); ?>" required
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full px-3 py-2 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="sm:col-span-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <div class="mt-1">
                            <textarea id="description" name="description" rows="3"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full px-3 py-2 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                placeholder="Provide a brief description of this role"><?= htmlspecialchars($formData['description'] ?? ($role->description ?? '')); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Permissions Section -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Manage Permissions
                    </h3>
                    
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
                        <div class="flex items-center mb-4">
                            <input id="select-all-permissions" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="select-all-permissions" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Select All Permissions
                            </label>
                        </div>
                        
                        <?php foreach ($groupedPermissions as $group => $permissions): ?>
                            <div class="mb-6">
                                <div class="flex items-center mb-3">
                                    <input id="select-group-<?= htmlspecialchars($group) ?>" data-group="<?= htmlspecialchars($group) ?>" type="checkbox" class="group-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                                    <label for="select-group-<?= htmlspecialchars($group) ?>" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">
                                        Select All <?= htmlspecialchars(str_replace('_', ' ', $group)); ?> Permissions
                                    </label>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pl-6">
                                    <?php foreach ($permissions as $permission): ?>
                                        <div class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                id="permission_<?= htmlspecialchars((string)$permission->id); ?>" 
                                                name="permissions[]" 
                                                value="<?= htmlspecialchars((string)$permission->id); ?>"
                                                data-group="<?= htmlspecialchars($group); ?>"
                                                class="permission-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded"
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
                                                <span class="text-xs text-gray-500 dark:text-gray-400 block"><?= htmlspecialchars($permission->description ?? ''); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="pt-5 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-end">
                        <a href="/roles/view/<?= htmlspecialchars((string)$role->id) ?>" 
                            class="bg-white dark:bg-gray-700 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Update Role
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>
    
    <!-- JavaScript for permission selection -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all-permissions');
            const groupCheckboxes = document.querySelectorAll('.group-checkbox');
            const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
            
            // Select all permissions
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                permissionCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                groupCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });
            
            // Select all permissions in a group
            groupCheckboxes.forEach(groupCheckbox => {
                groupCheckbox.addEventListener('change', function() {
                    const group = this.dataset.group;
                    const isChecked = this.checked;
                    
                    document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`).forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                    
                    updateSelectAllStatus();
                });
            });
            
            // Update group and select all checkboxes based on individual permissions
            permissionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateGroupStatus(this.dataset.group);
                    updateSelectAllStatus();
                });
            });
            
            // Initialize checkbox states
            initializeCheckboxStates();
            
            function initializeCheckboxStates() {
                groupCheckboxes.forEach(groupCheckbox => {
                    updateGroupStatus(groupCheckbox.dataset.group);
                });
                
                updateSelectAllStatus();
            }
            
            function updateGroupStatus(group) {
                const groupPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
                const groupCheckbox = document.querySelector(`#select-group-${group}`);
                
                if (groupCheckbox) {
                    const allChecked = Array.from(groupPermissions).every(checkbox => checkbox.checked);
                    groupCheckbox.checked = allChecked;
                }
            }
            
            function updateSelectAllStatus() {
                const allChecked = Array.from(permissionCheckboxes).every(checkbox => checkbox.checked);
                selectAllCheckbox.checked = allChecked;
            }
        });
    </script>
</body>
</html>