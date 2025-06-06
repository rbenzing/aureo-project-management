<?php
//file: Views/TimeTracking/index.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
use App\Utils\Time;
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Tracking - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
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
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl font-medium dark:text-white">Time Tracking</h1>
                <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Export Button -->
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export
                </button>
            </div>
        </div>

        <!-- Time Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            <?= Time::formatSeconds($timeData['today'] ?? 0) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Week</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            <?= Time::formatSeconds($timeData['this_week'] ?? 0) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Month</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            <?= Time::formatSeconds($timeData['this_month'] ?? 0) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Billable</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            <?= Time::formatSeconds($timeData['billable'] ?? 0) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
            <form method="GET" class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-64">
                    <label for="date_range" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
                    <select name="date_range" id="date_range" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                        <option value="today" <?= ($filters['date_range'] ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="yesterday" <?= ($filters['date_range'] ?? '') === 'yesterday' ? 'selected' : '' ?>>Yesterday</option>
                        <option value="this_week" <?= ($filters['date_range'] ?? '') === 'this_week' ? 'selected' : '' ?>>This Week</option>
                        <option value="last_week" <?= ($filters['date_range'] ?? '') === 'last_week' ? 'selected' : '' ?>>Last Week</option>
                        <option value="this_month" <?= ($filters['date_range'] ?? '') === 'this_month' ? 'selected' : '' ?>>This Month</option>
                        <option value="last_month" <?= ($filters['date_range'] ?? '') === 'last_month' ? 'selected' : '' ?>>Last Month</option>
                        <option value="custom" <?= ($filters['date_range'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                    </select>
                </div>

                <div class="flex-1 min-w-48">
                    <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project</label>
                    <select name="project_id" id="project_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">All Projects</option>
                        <?php foreach ($projects ?? [] as $project): ?>
                            <option value="<?= $project->id ?>" <?= ($filters['project_id'] ?? '') == $project->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($project->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex-1 min-w-48">
                    <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User</label>
                    <select name="user_id" id="user_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                        <option value="">All Users</option>
                        <?php foreach ($users ?? [] as $user): ?>
                            <option value="<?= $user->id ?>" <?= ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Time Entries Table -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Task
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Project
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Start Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Duration
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Billable
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (!empty($timeEntries)): ?>
                            <?php foreach ($timeEntries as $entry): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <a href="/tasks/view/<?= $entry->task_id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                <?= htmlspecialchars($entry->task_title ?? 'Unknown Task') ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            <?= htmlspecialchars($entry->project_name ?? 'No Project') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            <?= htmlspecialchars(($entry->first_name ?? '') . ' ' . ($entry->last_name ?? '')) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            <?= date('M j, Y g:i A', strtotime($entry->start_time)) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <?= Time::formatSeconds($entry->duration ?? 0) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $entry->is_billable ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' ?>">
                                            <?= $entry->is_billable ? 'Billable' : 'Non-billable' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="/time-tracking/edit/<?= $entry->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                            Edit
                                        </a>
                                        <button onclick="deleteTimeEntry(<?= $entry->id ?>)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No time entries found for the selected criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
            <div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6 mt-6 rounded-lg shadow">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?><?= http_build_query(array_filter($filters ?? [])) ? '&' . http_build_query(array_filter($filters ?? [])) : '' ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?><?= http_build_query(array_filter($filters ?? [])) ? '&' . http_build_query(array_filter($filters ?? [])) : '' ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing <span class="font-medium"><?= $pagination['start'] ?></span> to <span class="font-medium"><?= $pagination['end'] ?></span> of <span class="font-medium"><?= $pagination['total'] ?></span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <a href="?page=<?= $i ?><?= http_build_query(array_filter($filters ?? [])) ? '&' . http_build_query(array_filter($filters ?? [])) : '' ?>" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?= $i === $pagination['current_page'] ? 'z-10 bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <script>
        function deleteTimeEntry(id) {
            if (confirm('Are you sure you want to delete this time entry?')) {
                fetch(`/time-tracking/delete/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
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

        // Auto-submit form when filters change
        document.getElementById('date_range').addEventListener('change', function() {
            this.form.submit();
        });
        document.getElementById('project_id').addEventListener('change', function() {
            this.form.submit();
        });
        document.getElementById('user_id').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>
