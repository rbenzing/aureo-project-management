<?php
//file: Views/TimeTracking/view.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
use App\Utils\Time;

// Include helper functions
include BASE_PATH . '/../src/Views/Layouts/view_helpers.php';
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Entry Details - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header and Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6 flex-grow">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>
        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-medium dark:text-white">Time Entry Details</h1>
            <div class="flex items-center space-x-3">
                <a href="/time-tracking/edit/<?= $timeEntry->id ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm">
                    Edit Entry
                </a>
                <a href="/time-tracking" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm">
                    Back to Time Tracking
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Details -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Entry Information</h2>
                    </div>

                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Task -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Task</dt>
                                <dd class="mt-1">
                                    <a href="/tasks/view/<?= $timeEntry->task_id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                        <?= htmlspecialchars($timeEntry->task_title ?? $timeEntry->title ?? 'Unknown Task') ?>
                                    </a>
                                </dd>
                            </div>

                            <!-- Project -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Project</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?php if (!empty($timeEntry->project_name)): ?>
                                        <a href="/projects/view/<?= $timeEntry->project_id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            <?= htmlspecialchars($timeEntry->project_name) ?>
                                        </a>
                                    <?php else: ?>
                                        No Project
                                    <?php endif; ?>
                                </dd>
                            </div>

                            <!-- User -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">User</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?= htmlspecialchars(($timeEntry->first_name ?? '') . ' ' . ($timeEntry->last_name ?? '')) ?>
                                </dd>
                            </div>

                            <!-- Duration -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                                <dd class="mt-1">
                                    <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        <?= Time::formatSeconds($timeEntry->duration ?? 0) ?>
                                    </span>
                                </dd>
                            </div>

                            <!-- Start Time -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Time</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?= date('l, F j, Y \a\t g:i A', strtotime($timeEntry->start_time)) ?>
                                </dd>
                            </div>

                            <!-- End Time -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Time</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?php if (!empty($timeEntry->end_time)): ?>
                                        <?= date('l, F j, Y \a\t g:i A', strtotime($timeEntry->end_time)) ?>
                                    <?php else: ?>
                                        <span class="text-yellow-600 dark:text-yellow-400">In Progress</span>
                                    <?php endif; ?>
                                </dd>
                            </div>

                            <!-- Billable Status -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Billable</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $timeEntry->is_billable ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' ?>">
                                        <?= $timeEntry->is_billable ? 'Billable' : 'Non-billable' ?>
                                    </span>
                                </dd>
                            </div>

                            <!-- Created -->
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?= date('M j, Y \a\t g:i A', strtotime($timeEntry->created_at)) ?>
                                </dd>
                            </div>

                            <!-- Notes -->
                            <?php if (!empty($timeEntry->notes)): ?>
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    <?= nl2br(htmlspecialchars($timeEntry->notes)) ?>
                                </dd>
                            </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="/time-tracking/edit/<?= $timeEntry->id ?>" class="flex items-center p-2 bg-yellow-50 dark:bg-yellow-900/30 hover:bg-yellow-100 dark:hover:bg-yellow-900/50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            <span class="text-gray-800 dark:text-gray-200">Edit Entry</span>
                        </a>

                        <a href="/time-tracking/create?task_id=<?= $timeEntry->task_id ?>" class="flex items-center p-2 bg-green-50 dark:bg-green-900/30 hover:bg-green-100 dark:hover:bg-green-900/50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="text-gray-800 dark:text-gray-200">Add Another Entry</span>
                        </a>

                        <button onclick="deleteTimeEntry(<?= $timeEntry->id ?>)" class="flex items-center p-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-lg transition-colors w-full text-left">
                            <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span class="text-gray-800 dark:text-gray-200">Delete Entry</span>
                        </button>
                    </div>
                </div>

                <!-- Time Summary -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Time Summary</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">This Entry</span>
                                <span class="font-medium"><?= Time::formatSeconds($timeEntry->duration ?? 0) ?></span>
                            </div>

                            <?php if (isset($taskTimeTotal)): ?>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Total for Task</span>
                                <span class="font-medium"><?= Time::formatSeconds($taskTimeTotal) ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (isset($projectTimeTotal)): ?>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Total for Project</span>
                                <span class="font-medium"><?= Time::formatSeconds($projectTimeTotal) ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if ($timeEntry->is_billable && isset($billableRate)): ?>
                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Billable Amount</span>
                                    <span class="font-medium text-green-600 dark:text-green-400">
                                        $<?= number_format(($timeEntry->duration / 3600) * $billableRate, 2) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <script>
        function deleteTimeEntry(id) {
            if (confirm('Are you sure you want to delete this time entry? This action cannot be undone.')) {
                fetch('/time-tracking/delete/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '/time-tracking';
                    } else {
                        alert('Error deleting time entry: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting time entry');
                });
            }
        }
    </script>
</body>
</html>
