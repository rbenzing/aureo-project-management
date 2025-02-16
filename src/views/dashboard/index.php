<?php
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to access this page.';
    header('Location: /login');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../Controllers/ProjectController.php';
    $controller = new \App\Controllers\ProjectController();
    $controller->create($_POST);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="flex-grow p-6">
        <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

        <!-- Welcome Message -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6 p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Welcome back, <?= htmlspecialchars($user->first_name) ?>!</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Here's a quick overview of your recent activity.</p>
        </div>

        <!-- Recent Projects -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6 p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Projects</h2>
            <?php if (!empty($projects)): ?>
                <ul class="mt-2 space-y-2">
                    <?php foreach ($projects as $project): ?>
                        <li class="text-gray-700 dark:text-gray-300"><?= htmlspecialchars($project->name) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500 dark:text-gray-400">No recent projects found.</p>
            <?php endif; ?>
        </div>

        <!-- Recent Tasks -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Tasks</h2>
            <?php if (!empty($tasks)): ?>
                <ul class="mt-2 space-y-2">
                    <?php foreach ($tasks as $task): ?>
                        <li class="text-gray-700 dark:text-gray-300"><?= htmlspecialchars($task->title) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500 dark:text-gray-400">No recent tasks found.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>