<?php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use \App\Core\Config;
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= Config::get('company_name', 'Slimbooks') ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <main class="container mx-auto p-6 flex-grow">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Welcome Section -->
            <div class="md:col-span-3 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Welcome back, <?= htmlspecialchars($user->first_name) ?>!
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">
                            Here's an overview of your current work and progress.
                        </p>
                    </div>
                    <?php if (!empty($dashboardData['active_timer'])): ?>
                        <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-md">
                            Active Timer: 
                            <?= htmlspecialchars($dashboardData['active_timer']['task']->title) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Project Summary -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Project Overview</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Total Projects</span>
                        <span class="font-bold"><?= $dashboardData['project_summary']['total'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">In Progress</span>
                        <span class="font-bold text-blue-600"><?= $dashboardData['project_summary']['in_progress'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Completed</span>
                        <span class="font-bold text-green-600"><?= $dashboardData['project_summary']['completed'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Delayed</span>
                        <span class="font-bold text-red-600"><?= $dashboardData['project_summary']['delayed'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Task Summary -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Task Status</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Total Tasks</span>
                        <span class="font-bold"><?= $dashboardData['task_summary']['total'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Completed</span>
                        <span class="font-bold text-green-600"><?= $dashboardData['task_summary']['completed'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">In Progress</span>
                        <span class="font-bold text-blue-600"><?= $dashboardData['task_summary']['in_progress'] ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Overdue</span>
                        <span class="font-bold text-red-600"><?= $dashboardData['task_summary']['overdue'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Time Tracking -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Time Tracking</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Total Hours</span>
                        <span class="font-bold"><?= number_format($dashboardData['time_tracking_summary']['total_hours'] / 3600, 2) ?> hrs</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Billable Hours</span>
                        <span class="font-bold text-green-600"><?= number_format($dashboardData['time_tracking_summary']['billable_hours'] / 3600, 2) ?> hrs</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">This Week</span>
                        <span class="font-bold text-blue-600"><?= number_format($dashboardData['time_tracking_summary']['this_week'] / 3600, 2) ?> hrs</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">This Month</span>
                        <span class="font-bold text-purple-600"><?= number_format($dashboardData['time_tracking_summary']['this_month'] / 3600, 2) ?> hrs</span>
                    </div>
                </div>
            </div>

            <!-- Recent Projects -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Recent Projects</h2>
                <?php if (!empty($dashboardData['recent_projects'])): ?>
                    <ul class="space-y-2">
                        <?php foreach ($dashboardData['recent_projects'] as $project): ?>
                            <li class="flex justify-between items-center">
                                <span class="text-gray-700 dark:text-gray-300">
                                    <?= htmlspecialchars($project->name) ?>
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    <?= date('M d', strtotime($project->created_at)) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400">No recent projects.</p>
                <?php endif; ?>
            </div>

            <!-- Recent Tasks -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Recent Tasks</h2>
                <?php if (!empty($dashboardData['recent_tasks'])): ?>
                    <ul class="space-y-2">
                        <?php foreach ($dashboardData['recent_tasks'] as $task): ?>
                            <li class="flex justify-between items-center">
                                <span class="text-gray-700 dark:text-gray-300">
                                    <?= htmlspecialchars($task->title) ?>
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($task->project_name ?? 'No Project') ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400">No recent tasks.</p>
                <?php endif; ?>
            </div>

            <!-- Upcoming Milestones -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Upcoming Milestones</h2>
                <?php if (!empty($dashboardData['upcoming_milestones'])): ?>
                    <ul class="space-y-2">
                        <?php foreach ($dashboardData['upcoming_milestones'] as $milestone): ?>
                            <li class="flex justify-between items-center">
                                <span class="text-gray-700 dark:text-gray-300">
                                    <?= htmlspecialchars($milestone->title) ?>
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    Due: <?= date('M d', strtotime($milestone->due_date)) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400">No upcoming milestones.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>