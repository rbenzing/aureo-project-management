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

$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$breadcrumbs = [
    ['name' => 'Dashboard', 'url' => '/dashboard'],
    ['name' => 'Product Backlog', 'url' => '/tasks/backlog']
];
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Backlog - Slimbooks</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"}
                    }
                }
            }
        }
    </script>
</head>

<body class="h-full bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="flex h-full">
        <!-- Sidebar -->
        <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

            <!-- Main Content -->
            <main class="container mx-auto p-6 flex-grow">
                <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>
                <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

                <!-- Backlog Header -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Product Backlog</h1>
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
                                <input type="text" id="search-input" placeholder="Search by title or description..." 
                                       onkeyup="searchTasks(this.value)"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backlog Items Table -->
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
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
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php if (!empty($tasks)): ?>
                                    <?php foreach ($tasks as $task): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors task-row" 
                                            data-task-id="<?= $task->id ?>"
                                            data-priority="<?= $task->priority ?>"
                                            data-task-type="<?= $task->task_type ?? 'task' ?>"
                                            data-ready="<?= $task->is_ready_for_sprint ? '1' : '0' ?>"
                                            data-story-points="<?= $task->story_points ?? '' ?>">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                            <a href="/tasks/view/<?= $task->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                                <?= htmlspecialchars($task->title) ?>
                                                            </a>
                                                        </div>
                                                        <?php if (!empty($task->description)): ?>
                                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                                <?= htmlspecialchars(substr($task->description, 0, 100)) ?><?= strlen($task->description) > 100 ? '...' : '' ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-200">
                                                    <?= htmlspecialchars($task->project_name ?? 'Unknown') ?>
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
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full 
                                                    <?php 
                                                    switch($task->priority) {
                                                        case 'high': echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; break;
                                                        case 'medium': echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; break;
                                                        case 'low': echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                                        default: echo 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; break;
                                                    }
                                                    ?>">
                                                    <?= ucfirst($task->priority) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                <?= $task->story_points ?? '-' ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    <?= htmlspecialchars($task->status_name ?? 'Open') ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="/tasks/view/<?= $task->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        View
                                                    </a>
                                                    <a href="/tasks/edit/<?= $task->id ?>" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                        Edit
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
            <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>
        </div>
    </div>

    <script>
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
            const rows = document.querySelectorAll('.task-row');
            
            rows.forEach(row => {
                let shouldShow = true;
                
                if (filter === 'ready') {
                    shouldShow = row.getAttribute('data-ready') === '1';
                } else if (filter === 'story' || filter === 'bug' || filter === 'task' || filter === 'epic') {
                    shouldShow = row.getAttribute('data-task-type') === filter;
                } else if (filter === 'high-priority') {
                    shouldShow = row.getAttribute('data-priority') === 'high';
                } else if (filter === 'unestimated') {
                    shouldShow = !row.getAttribute('data-story-points');
                }
                
                row.style.display = shouldShow ? '' : 'none';
            });
        }

        function searchTasks(searchTerm) {
            const rows = document.querySelectorAll('.task-row');
            const term = searchTerm.toLowerCase();
            
            rows.forEach(row => {
                const title = row.querySelector('a').textContent.toLowerCase();
                const description = row.querySelector('.text-gray-500')?.textContent?.toLowerCase() || '';
                
                const shouldShow = title.includes(term) || description.includes(term);
                row.style.display = shouldShow ? '' : 'none';
            });
        }
    </script>
</body>
</html>
