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
    <title>Users</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Users</h1>

        <!-- Table of Users -->
        <?php if (!empty($users)): ?>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 mt-2">
                    <thead class="bg-gray-200 dark:bg-gray-700">
                        <tr class="bg-gray-100 dark:bg-gray-700 border-b border-gray-300 dark:border-gray-600 pl-6">
                            <th class="py-3 px-6 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Name</th>
                            <th class="py-3 px-6 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Company</th>
                            <th class="py-3 px-6 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Email</th>
                            <th class="py-3 px-6 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Phone</th>
                            <th class="py-3 px-6 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Created</th>
                            <th class="py-3 px-6 text-center text-sm font-medium text-gray-700 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b border-gray-300 dark:border-gray-600">
                                <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300">
                                    <a href="/view_user?id=<?= htmlspecialchars($user->id) ?>" class="text-indigo-600 hover:text-indigo-900">
                                        <?= htmlspecialchars($user->first_name) ?> <?= htmlspecialchars($user->last_name) ?>
                                    </a>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($user->company_name ?? '') ?></td>
                                <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($user->email ?? '') ?></td>
                                <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($user->phone ?? '') ?></td>
                                <td class="py-4 px-6 text-sm text-gray-700 dark:text-gray-300"><?= htmlspecialchars($user->created_at ?? '') ?></td>
                                <td class="py-4 px-6 text-center text-sm">
                                    <a href="/edit_user?id=<?= htmlspecialchars($user->id) ?>" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                    <a href="/delete_user?id=<?= htmlspecialchars($user->id) ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No users found.</p>
        <?php endif; ?>

        <!-- Pagination -->
        <div class="mt-4">
            <?php if (isset($pagination)): ?>
                <nav class="flex justify-between items-center">
                    <?php if ($pagination['prev_page']): ?>
                        <a href="?page=<?php echo $pagination['prev_page']; ?>" class="text-indigo-600 hover:text-indigo-900">&laquo; Previous</a>
                    <?php endif; ?>
                    <?php if ($pagination['next_page']): ?>
                        <a href="?page=<?php echo $pagination['next_page']; ?>" class="text-indigo-600 hover:text-indigo-900">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>

        <!-- Create Button -->
        <a href="/create_user" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Create New User</a>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>