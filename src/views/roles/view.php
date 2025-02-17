<?php
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to access this page.';
    header('Location: /login');
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
        <h1 class="text-2xl font-bold mb-6"><?= htmlspecialchars($role->name) ?></h1>

        <!-- Role Details -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Role Details</h2>
            <ul class="space-y-2">
                <li><strong>Description:</strong> <?= htmlspecialchars($role->description ?? 'No description available') ?></li>
                <li><strong>Permissions:</strong>
                    <?php if (!empty($role->permissions)): ?>
                        <ul class="list-disc pl-5">
                            <?php foreach ($role->permissions as $permission): ?>
                                <li><?= htmlspecialchars($permission->name) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        No permissions assigned.
                    <?php endif; ?>
                </li>
            </ul>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex gap-4">
            <a href="/roles" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Back to Roles</a>
            <a href="/edit_role?id=<?= htmlspecialchars($role->id) ?>" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Edit</a>
            <a href="/delete_role?id=<?= htmlspecialchars($role->id) ?>" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
               onclick="return confirm('Are you sure you want to delete this role?')">Delete Role</a>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>