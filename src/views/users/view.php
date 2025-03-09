<?php
//file: Views/Users/view.php
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
    <title>View User</title>
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

        <h1 class="text-2xl font-bold mb-6"><?= htmlspecialchars($user->first_name) . ' ' . htmlspecialchars($user->last_name)?></h1>

        <!-- User Details -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">User Details</h2>
            <ul class="space-y-2">
                <li><strong>Email:</strong> <?= htmlspecialchars($user->email) ?></li>
                <li><strong>Roles:</strong>
                    <?php if (!empty($user->roles)): ?>
                        <ul class="list-disc pl-5">
                            <?php foreach ($user->roles as $role): ?>
                                <li><?= htmlspecialchars($role->name) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        No roles assigned.
                    <?php endif; ?>
                </li>
            </ul>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex gap-4">
            <a href="/edit_user?id=<?= htmlspecialchars($user->id) ?>" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Edit</a>
            <a href="/users" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Back to Users</a>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

</body>
</html>