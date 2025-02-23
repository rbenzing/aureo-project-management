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
    <title>Roles</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Roles</h1>
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
        <!-- Table of Roles -->
        <?php if (!empty($roles)): ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 mt-2">
                    <thead class="bg-gray-200 dark:bg-gray-700">
                        <tr class="bg-gray-100 dark:bg-gray-700 border-b border-gray-300 dark:border-gray-600">
                            <th class="py-3 px-6 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Name</th>
                            <th class="py-3 px-6 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Description</th>
                            <th class="py-3 px-6 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Created</th>
                            <th class="py-3 px-6 text-center text-sm font-medium text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($roles as $role): ?>
                            <tr class="border-b border-gray-300 dark:border-gray-600">
                                <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300">
                                    <a href="/roles/view/<?= htmlspecialchars($role->id) ?>" class="text-indigo-600 hover:text-indigo-900">
                                        <?= htmlspecialchars($role->name) ?>
                                    </a>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($role->description ?? '') ?></td>
                                <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($role->created_at ?? '') ?></td>
                                <td class="py-4 px-6 text-center text-sm">
                                    <a href="/roles/edit/<?= htmlspecialchars($role->id) ?>" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                    <a href="/roles/delete/<?= htmlspecialchars($role->id) ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this role?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No roles found.</p>
        <?php endif; ?>
        <!-- Pagination -->
        <div class="mt-4">
            <?php if (isset($pagination)): ?>
                <nav class="flex justify-between items-center">
                    <?php if ($pagination['prev_page']): ?>
                        <a href="/roles/page/<?= htmlspecialchars($pagination['prev_page']) ?>" class="text-indigo-600 hover:text-indigo-900">&laquo; Previous</a>
                    <?php endif; ?>
                    <?php if ($pagination['next_page']): ?>
                        <a href="/roles/page/<?= htmlspecialchars($pagination['next_page']) ?>" class="text-indigo-600 hover:text-indigo-900">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
        <!-- Create Button -->
        <a href="/roles/create" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Create New Role</a>
    </main>
    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>