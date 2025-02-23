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
    <title>Dashboard</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

        <!-- Welcome Message -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6 p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Welcome back, <?= htmlspecialchars($user->first_name) ?>!</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Here's a quick overview of your recent activity.</p>
        </div>

        <div class="flex flex-row gap-4">
            <!-- Recent Projects -->
            <div class="w-1/2 min-h-64 grow bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Projects</h2>

                <?php if (!empty($projects)): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 mt-2">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700 border-b border-gray-300 dark:border-gray-600">
                                    <th class="w-60 text-left p-2">Project Name</th>
                                    <th class="w-20 text-center p-2">Start Date</th>
                                    <th class="w-20 text-center p-2">Create Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                <tr class="border-b border-gray-300 dark:border-gray-600">
                                    <td class="w-60 p-2"><?= htmlspecialchars($project->name ?? 'Unknown') ?></td>
                                    <td class="w-20 text-center p-2"><?= htmlspecialchars($project->start_date ?? 'Not Started') ?></td>
                                    <td class="w-20 text-center p-2"><?= htmlspecialchars(date('m-d-Y', strtotime($project->created_at)) ?? 'Unknown') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <ul class="mt-2 space-y-2">
                        <li class="text-gray-500 dark:text-gray-400">No recent projects found.</li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Recent Tasks -->
            <div class="w-1/2 min-h-64 grow bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Tasks</h2>
                <?php if (!empty($tasks)): ?>
                    <ul class="mt-2 space-y-2">
                        <?php foreach ($tasks as $task): ?>
                            <li class="text-gray-700 dark:text-gray-300"><?= htmlspecialchars($task->title) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <ul class="mt-2 space-y-2">
                        <li class="text-gray-500 dark:text-gray-400">No recent tasks found.</li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>