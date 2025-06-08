<?php
//file: Views/Tasks/backlog.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;

// Include helper functions
include BASE_PATH . '/../src/Views/Layouts/view_helpers.php';

// Include task helper functions for consistent styling
include_once __DIR__ . '/inc/helper_functions.php';

// Set up filter options for backlog
$filterOptions = [
    'all' => 'All Items',
    'ready' => 'Ready for Sprint',
    'story' => 'User Stories',
    'bug' => 'Bugs',
    'task' => 'Tasks',
    'epic' => 'Epics',
    'high-priority' => 'High Priority',
    'unestimated' => 'Unestimated'
];

// Define task status labels and colors to match projects style
$taskStatusMap = [
    1 => [
        'label' => 'OPEN',
        'color' => 'bg-blue-600'
    ],
    2 => [
        'label' => 'IN PROGRESS',
        'color' => 'bg-yellow-500'
    ],
    3 => [
        'label' => 'ON HOLD',
        'color' => 'bg-purple-500'
    ],
    4 => [
        'label' => 'IN REVIEW',
        'color' => 'bg-indigo-500'
    ],
    5 => [
        'label' => 'CLOSED',
        'color' => 'bg-gray-500'
    ],
    6 => [
        'label' => 'COMPLETED',
        'color' => 'bg-green-500'
    ],
    7 => [
        'label' => 'CANCELLED',
        'color' => 'bg-red-500'
    ]
];

$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '/dashboard'],
    ['name' => 'Backlog', 'url' => '/tasks/backlog']
];
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backlog - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include BASE_PATH . '/../src/views/layouts/sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <?php include BASE_PATH . '/../src/views/layouts/header.php'; ?>

            <!-- Main Content -->
            <main class="container mx-auto p-6 flex-grow">
                <?php include BASE_PATH . '/../src/views/layouts/notifications.php'; ?>
                <?php include BASE_PATH . '/../src/views/layouts/breadcrumb.php'; ?>

                <!-- Backlog Header -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Backlog</h1>
                                <p class="text-gray-600 dark:text-gray-400 mt-1">
                                    Manage your product backlog - tasks not yet assigned to sprints
                                </p>
                            </div>
                            <div class="flex space-x-3">
                                <a href="/tasks/create" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Task
                                </a>
                                <a href="/tasks/sprint-planning" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Sprint Planning
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Backlog Stats -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"><?= $totalTasks ?? 0 ?></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Total Items</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    <?php 
                                    $readyCount = 0;
                                    if (!empty($tasks)) {
                                        foreach ($tasks as $task) {
                                            if ($task->is_ready_for_sprint ?? false) $readyCount++;
                                        }
                                    }
                                    echo $readyCount;
                                    ?>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Ready for Sprint</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                    <?php 
                                    $unestimatedCount = 0;
                                    if (!empty($tasks)) {
                                        foreach ($tasks as $task) {
                                            if (empty($task->story_points)) $unestimatedCount++;
                                        }
                                    }
                                    echo $unestimatedCount;
                                    ?>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Unestimated</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                    <?php 
                                    $highPriorityCount = 0;
                                    if (!empty($tasks)) {
                                        foreach ($tasks as $task) {
                                            if ($task->priority === 'high') $highPriorityCount++;
                                        }
                                    }
                                    echo $highPriorityCount;
                                    ?>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">High Priority</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Project Selection -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                    <div class="px-6 py-4">
                        <div class="flex flex-wrap gap-4 items-center">
                            <!-- Project Filter -->
                            <div class="flex-1 min-w-64">
                                <label for="project-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Filter by Project
                                </label>
                                <select id="project-filter" onchange="filterByProject(this.value)" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <option value="">All Projects</option>
                                    <?php if (!empty($projects)): ?>
                                        <?php foreach ($projects as $project): ?>
                                            <option value="<?= $project->id ?>" <?= ($selectedProjectId == $project->id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($project->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Status Filters -->
                            <div class="flex-1 min-w-64">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Filter by Type/Status
                                </label>
                                <select id="status-filter" onchange="filterTasks(this.value)" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                    <?php foreach ($filterOptions as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Search -->
                            <div class="flex-1 min-w-64">
                                <label for="search-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Search Tasks
                                </label>
                                <input type="text" id="search-input" placeholder="Search by title, description, or project..."
                                       onkeyup="searchTasks(this.value)"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>

                            <!-- Reset Filters Button -->
                            <div class="flex flex-col justify-end">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 opacity-0">
                                    Reset
                                </label>
                                <button type="button" onclick="resetFilters()"
                                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors text-sm h-[42px] flex items-center justify-center">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backlog Items Table -->
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Backlog</h3>
                            <div class="flex items-center space-x-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                        Drag to reorder priority
                                    </span>
                                </div>
                                <div id="filter-indicator" class="text-sm text-indigo-600 dark:text-indigo-400 hidden">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                        </svg>
                                        Filters active
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            Task
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Project
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Priority
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Story Points
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="backlog-tbody" class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php if (!empty($tasks)): ?>
                                    <?php foreach ($tasks as $task): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors task-row draggable-task"
                                            data-task-id="<?= $task->id ?>"
                                            data-priority="<?= $task->priority ?>"
                                            data-task-type="<?= $task->task_type ?? 'task' ?>"
                                            data-ready="<?= $task->is_ready_for_sprint ? '1' : '0' ?>"
                                            data-story-points="<?= $task->story_points ?? '' ?>"
                                            data-project-id="<?= $task->project_id ?? '' ?>"
                                            data-project-name="<?= htmlspecialchars($task->project_name ?? '') ?>"
                                            data-status-name="<?= htmlspecialchars($task->status_name ?? '') ?>"
                                            draggable="true">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <!-- Drag Handle -->
                                                    <div class="drag-handle mr-3 cursor-move text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                            <a href="/tasks/view/<?= $task->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                                <?= htmlspecialchars($task->title) ?>
                                                            </a>
                                                        </div>
                                                        <?php if (!empty($task->description)): ?>
                                                            <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                                                <?= nl2br(htmlspecialchars(substr($task->description, 0, 60))) . (strlen($task->description) > 60 ? '...' : '') ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 text-indigo-500 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                    </svg>
                                                    <span class="text-sm text-gray-900 dark:text-gray-200">
                                                        <?= htmlspecialchars($task->project_name ?? 'N/A') ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full
                                                    <?php
                                                    switch($task->task_type ?? 'task') {
                                                        case 'story': echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'; break;
                                                        case 'bug': echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; break;
                                                        case 'epic': echo 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'; break;
                                                        default: echo 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; break;
                                                    }
                                                    ?>">
                                                    <?= ucfirst($task->task_type ?? 'task') ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getPriorityClasses($task->priority) ?>">
                                                    <?= getPriorityIcon($task->priority) ?>
                                                    <?= ucfirst(htmlspecialchars($task->priority)) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                <?= $task->story_points ?? '-' ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                // Get status info using the same style as projects
                                                $statusId = $task->status_id ?? 1;
                                                $statusInfo = $taskStatusMap[$statusId] ?? ['label' => 'UNKNOWN', 'color' => 'bg-gray-500'];
                                                ?>
                                                <span class="px-3 py-1 text-xs rounded-full bg-opacity-20 text-white font-medium whitespace-nowrap <?= $statusInfo['color'] ?>">
                                                    <?= $statusInfo['label'] ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex justify-end space-x-3">
                                                    <a
                                                        href="/tasks/view/<?= $task->id ?>"
                                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                        title="View Task"
                                                    >
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </a>
                                                    <a
                                                        href="/tasks/edit/<?= $task->id ?>"
                                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                                        title="Edit Task"
                                                    >
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                                <h3 class="text-lg font-medium mb-2">No backlog items found</h3>
                                                <p class="text-sm mb-4">All tasks are either assigned to sprints or there are no tasks yet.</p>
                                                <a href="/tasks/create" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                                                    Create First Task
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <div class="mt-6 flex justify-between items-center">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Page <?= $page ?? 1 ?> of <?= $totalPages ?>
                        </div>
                        <div class="flex space-x-2">
                            <?php if (($page ?? 1) > 1): ?>
                                <a href="/tasks/backlog?page=<?= ($page ?? 1) - 1 ?><?= $selectedProjectId ? '&project_id=' . $selectedProjectId : '' ?>" 
                                   class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    Previous
                                </a>
                            <?php endif; ?>
                            <?php if (($page ?? 1) < $totalPages): ?>
                                <a href="/tasks/backlog?page=<?= ($page ?? 1) + 1 ?><?= $selectedProjectId ? '&project_id=' . $selectedProjectId : '' ?>" 
                                   class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    Next
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>

            <!-- Footer -->
            <?php include BASE_PATH . '/../src/views/layouts/footer.php'; ?>
        </div>
    </div>

    <script>
        // Global filter state
        let currentFilters = {
            search: '',
            type: 'all',
            project: '' // This will be handled server-side
        };

        function filterByProject(projectId) {
            const url = new URL(window.location);
            if (projectId) {
                url.searchParams.set('project_id', projectId);
            } else {
                url.searchParams.delete('project_id');
            }
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }

        function filterTasks(filter) {
            currentFilters.type = filter;
            applyAllFilters();
        }

        function searchTasks(searchTerm) {
            currentFilters.search = searchTerm.toLowerCase();
            applyAllFilters();
        }

        function applyAllFilters() {
            const rows = document.querySelectorAll('.task-row');

            rows.forEach(row => {
                let shouldShow = true;

                // Apply search filter
                if (currentFilters.search) {
                    const title = row.querySelector('a').textContent.toLowerCase();
                    const description = row.querySelector('.text-gray-500')?.textContent?.toLowerCase() || '';
                    const projectName = row.getAttribute('data-project-name').toLowerCase();

                    const matchesSearch = title.includes(currentFilters.search) ||
                                        description.includes(currentFilters.search) ||
                                        projectName.includes(currentFilters.search);

                    if (!matchesSearch) {
                        shouldShow = false;
                    }
                }

                // Apply type/status filter
                if (currentFilters.type !== 'all' && shouldShow) {
                    if (currentFilters.type === 'ready') {
                        shouldShow = row.getAttribute('data-ready') === '1';
                    } else if (currentFilters.type === 'story' || currentFilters.type === 'bug' ||
                              currentFilters.type === 'task' || currentFilters.type === 'epic') {
                        shouldShow = row.getAttribute('data-task-type') === currentFilters.type;
                    } else if (currentFilters.type === 'high-priority') {
                        shouldShow = row.getAttribute('data-priority') === 'high';
                    } else if (currentFilters.type === 'unestimated') {
                        const storyPoints = row.getAttribute('data-story-points');
                        shouldShow = !storyPoints || storyPoints === '';
                    }
                }

                row.style.display = shouldShow ? '' : 'none';
            });

            // Update visible count and filter indicator
            updateVisibleCount();
            updateFilterIndicator();
        }

        function updateVisibleCount() {
            const allRows = document.querySelectorAll('.task-row');
            const visibleRows = document.querySelectorAll('.task-row[style=""], .task-row:not([style])');
            const totalCount = allRows.length;
            const visibleCount = visibleRows.length;

            // Update stats if they exist
            const totalElement = document.querySelector('.text-2xl.font-bold.text-indigo-600');
            if (totalElement && (currentFilters.search || currentFilters.type !== 'all')) {
                totalElement.textContent = `${visibleCount}/${totalCount}`;
            } else if (totalElement) {
                totalElement.textContent = totalCount;
            }
        }

        function updateFilterIndicator() {
            const indicator = document.getElementById('filter-indicator');
            const hasActiveFilters = currentFilters.search || currentFilters.type !== 'all';

            if (hasActiveFilters) {
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.add('hidden');
            }
        }

        function resetFilters() {
            currentFilters = {
                search: '',
                type: 'all',
                project: ''
            };

            // Reset form elements
            document.getElementById('search-input').value = '';
            document.getElementById('status-filter').value = 'all';

            applyAllFilters();
        }

        // Drag and Drop Functionality for Backlog Priority
        let draggedElement = null;

        function initializeDragAndDrop() {
            const tbody = document.getElementById('backlog-tbody');
            const draggableRows = document.querySelectorAll('.draggable-task');

            draggableRows.forEach(row => {
                row.addEventListener('dragstart', handleDragStart);
                row.addEventListener('dragover', handleDragOver);
                row.addEventListener('drop', handleDrop);
                row.addEventListener('dragend', handleDragEnd);
            });
        }

        function handleDragStart(e) {
            draggedElement = this;
            this.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.outerHTML);
        }

        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            return false;
        }

        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }

            if (draggedElement !== this) {
                const tbody = this.parentNode;
                const draggedIndex = Array.from(tbody.children).indexOf(draggedElement);
                const targetIndex = Array.from(tbody.children).indexOf(this);

                if (draggedIndex < targetIndex) {
                    tbody.insertBefore(draggedElement, this.nextSibling);
                } else {
                    tbody.insertBefore(draggedElement, this);
                }

                // Update backlog priorities
                updateBacklogPriorities();
            }
            return false;
        }

        function handleDragEnd(e) {
            this.style.opacity = '';
            draggedElement = null;
        }

        function updateBacklogPriorities() {
            const rows = document.querySelectorAll('.draggable-task');
            const taskIds = [];

            rows.forEach((row, index) => {
                const taskId = row.getAttribute('data-task-id');
                taskIds.push({
                    id: taskId,
                    priority: index + 1
                });
            });

            // Send AJAX request to update priorities
            fetch('/api/tasks/update-backlog-priorities', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ tasks: taskIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Backlog priorities updated successfully');
                } else {
                    console.error('Failed to update backlog priorities:', data.message);
                    // Optionally reload the page or show an error message
                }
            })
            .catch(error => {
                console.error('Error updating backlog priorities:', error);
            });
        }

        // Initialize drag and drop when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeDragAndDrop();

            // Initialize filter state from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const projectId = urlParams.get('project_id');
            if (projectId) {
                currentFilters.project = projectId;
            }

            // Apply initial filters
            applyAllFilters();
        });
    </script>
</body>
</html>
