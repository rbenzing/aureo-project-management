<?php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;

// Determine the context based on URL
$isMyTasksView = isset($userId); // This would be set in the controller when /:user_id is present
$currentUserId = $_SESSION['user']['id'] ?? null;
$viewingOwnTasks = $isMyTasksView && $userId == $currentUserId;
$viewTitle = $isMyTasksView ? ($viewingOwnTasks ? 'My Tasks' : 'User Tasks') : 'Task Backlog';

// Set up filter options based on context
$filterOptions = [
    'all' => 'All Statuses',
    'overdue' => 'Overdue',
    'today' => 'Due Today',
    'week' => 'Due This Week',
    'in-progress' => 'In Progress',
    'completed' => 'Completed'
];

// If viewing another user's tasks, we may want to add more filters
if ($isMyTasksView && !$viewingOwnTasks) {
    $filterOptions['assigned'] = 'Assigned';
    $filterOptions['unassigned'] = 'Unassigned';
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $viewTitle ?> - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6 flex-grow">
        <!-- Notification Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-50 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Task Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
                <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3 mr-4">
                    <svg class="w-6 h-6 text-blue-500 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tasks</div>
                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100"><?= $totalTasks ?? 0 ?></div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
                <div class="rounded-full bg-yellow-100 dark:bg-yellow-900 p-3 mr-4">
                    <svg class="w-6 h-6 text-yellow-500 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">In Progress</div>
                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        <?php 
                        $inProgressCount = 0;
                        if (!empty($tasks)) {
                            foreach ($tasks as $task) {
                                if ($task->status_name === 'In Progress') {
                                    $inProgressCount++;
                                }
                            }
                        }
                        echo $inProgressCount;
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
                <div class="rounded-full bg-red-100 dark:bg-red-900 p-3 mr-4">
                    <svg class="w-6 h-6 text-red-500 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Overdue</div>
                    <div class="text-xl font-semibold text-red-600 dark:text-red-400">
                        <?php 
                        $overdueCount = 0;
                        if (!empty($tasks)) {
                            foreach ($tasks as $task) {
                                if (!empty($task->due_date) && strtotime($task->due_date) < time() && $task->status_name !== 'Completed' && $task->status_name !== 'Closed') {
                                    $overdueCount++;
                                }
                            }
                        }
                        echo $overdueCount;
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
                <div class="rounded-full bg-green-100 dark:bg-green-900 p-3 mr-4">
                    <svg class="w-6 h-6 text-green-500 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</div>
                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        <?php 
                        $completedCount = 0;
                        if (!empty($tasks)) {
                            foreach ($tasks as $task) {
                                if ($task->status_name === 'Completed') {
                                    $completedCount++;
                                }
                            }
                        }
                        echo $completedCount;
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?= $viewTitle ?></h1>
                
                <?php if ($isMyTasksView && !$viewingOwnTasks && isset($userDetails)): ?>
                <div class="ml-4 flex items-center bg-gray-200 dark:bg-gray-700 rounded-full px-3 py-1">
                    <div class="w-7 h-7 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-xs mr-2">
                        <?= htmlspecialchars(substr($userDetails->first_name, 0, 1) . substr($userDetails->last_name, 0, 1)) ?>
                    </div>
                    <span class="text-sm font-medium"><?= htmlspecialchars($userDetails->first_name . ' ' . $userDetails->last_name) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="flex space-x-4">
                <!-- Context Switcher (only show in appropriate contexts) -->
                <?php if (!$isMyTasksView || $viewingOwnTasks): ?>
                <div class="relative">
                    <select id="context-switcher" onchange="window.location.href=this.value" class="appearance-none w-48 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="/tasks" <?= !$isMyTasksView ? 'selected' : '' ?>>All Tasks (Backlog)</option>
                        <option value="/tasks/assigned/<?= $currentUserId ?>" <?= $viewingOwnTasks ? 'selected' : '' ?>>My Tasks</option>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['permissions'] && in_array('manage_tasks', $_SESSION['user']['permissions'])): ?>
                        <option value="/tasks/unassigned">Unassigned Tasks</option>
                        <?php endif; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Filter Dropdown -->
                <div class="relative">
                    <select id="task-filter" class="appearance-none w-40 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($filterOptions as $value => $label): ?>
                        <option value="<?= $value ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative">
                    <input 
                        type="search" 
                        placeholder="Search tasks..." 
                        class="w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Time Tracking Button - if there's an active timer -->
                <?php if (isset($activeTimer)): ?>
                <form action="/tasks/stop-timer/<?= $activeTimer['task_id'] ?>" method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                        </svg>
                        Stop Timer (<?= gmdate("H:i:s", $activeTimer['duration'] ?? 0) ?>)
                    </button>
                </form>
                <?php endif; ?>

                <!-- New Task Button -->
                <a 
                    href="/tasks/create<?= $isMyTasksView && !$viewingOwnTasks ? '?assign_to=' . htmlspecialchars($userId) : '' ?>" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    + New Task
                </a>
            </div>
        </div>

        <!-- Tasks Table -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Task</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Project</th>
                        <?php if (!$isMyTasksView): // Only show assignee column in backlog view ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assignee</th>
                        <?php endif; ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (!empty($tasks)): ?>
                        <?php foreach ($tasks as $task): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if ($task->is_subtask): ?>
                                            <div class="ml-2 mr-3">↳</div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                <a href="/tasks/view/<?= $task->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    <?= htmlspecialchars($task->title) ?>
                                                </a>
                                            </div>
                                            <?php if (!empty($task->description)): ?>
                                                <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                                    <?= htmlspecialchars(substr($task->description, 0, 60)) . (strlen($task->description) > 60 ? '...' : '') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-200">
                                        <?= htmlspecialchars($task->project_name ?? 'N/A') ?>
                                    </div>
                                </td>
                                <?php if (!$isMyTasksView): // Only show assignee column in backlog view ?>
                                <td class="px-6 py-4">
                                    <?php if (!empty($task->first_name)): ?>
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                                <span class="text-xs font-medium text-white">
                                                    <?= htmlspecialchars(substr($task->first_name, 0, 1) . substr($task->last_name, 0, 1)) ?>
                                                </span>
                                            </div>
                                            <div class="ml-2">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                    <?= htmlspecialchars($task->first_name . ' ' . $task->last_name) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Unassigned
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getPriorityClasses($task->priority) ?>">
                                        <?= ucfirst(htmlspecialchars($task->priority)) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getStatusClasses($task->status_name) ?>">
                                        <?= htmlspecialchars($task->status_name ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($task->due_date)): ?>
                                        <div class="text-sm <?= isDueDateOverdue($task->due_date, $task->status_name) ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-900 dark:text-gray-200' ?>">
                                            <?= date('M j, Y', strtotime($task->due_date)) ?>
                                            <?php if (isDueDateOverdue($task->due_date, $task->status_name)): ?>
                                                <span class="ml-2 text-xs bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 px-2 py-0.5 rounded">Overdue</span>
                                            <?php elseif (isDueToday($task->due_date)): ?>
                                                <span class="ml-2 text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 px-2 py-0.5 rounded">Today</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">No Due Date</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-200">
                                        <?php if ($task->time_spent > 0): ?>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <?= formatTimeSpent($task->time_spent) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-500 dark:text-gray-400">0h 0m</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($task->estimated_time)): ?>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Est: <?= formatTimeSpent($task->estimated_time) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <?php 
                                        // Only show timer button if viewing own tasks or have permission to manage tasks
                                        $canTimeTrack = $viewingOwnTasks || 
                                                       ($task->assigned_to == $currentUserId) || 
                                                       (isset($_SESSION['user']['permissions']) && in_array('manage_tasks', $_SESSION['user']['permissions']));
                                        
                                        if (!isset($activeTimer) && $canTimeTrack && $task->status_name !== 'Completed' && $task->status_name !== 'Closed'): 
                                        ?>
                                            <form action="/tasks/start-timer/<?= $task->id ?>" method="POST" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <button 
                                                    type="submit" 
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                    title="Start Timer"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <a 
                                            href="/tasks/view/<?= $task->id ?>" 
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            View
                                        </a>
                                        <a 
                                            href="/tasks/edit/<?= $task->id ?>" 
                                            class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                        >
                                            Edit
                                        </a>
                                        <form 
                                            action="/tasks/delete/<?= $task->id ?>" 
                                            method="POST" 
                                            onsubmit="return confirm('Are you sure you want to delete this task?');"
                                            class="inline"
                                        >
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button 
                                                type="submit" 
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $isMyTasksView ? '7' : '8' ?>" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No tasks found. <a href="/tasks/create<?= $isMyTasksView && !$viewingOwnTasks ? '?assign_to=' . htmlspecialchars($userId) : '' ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Create your first task</a>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Page <?= $page ?> of <?= $totalPages ?>
                </div>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a 
                            href="<?= $isMyTasksView ? "/tasks/assigned/{$userId}/page/" : "/tasks/page/" ?><?= $page - 1 ?>" 
                            class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a 
                            href="<?= $isMyTasksView ? "/tasks/assigned/{$userId}/page/" : "/tasks/page/" ?><?= $page + 1 ?>" 
                            class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <!-- Helper Functions -->
    <?php
    function getStatusClasses($status) {
        $classes = [
            'Open' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'On Hold' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'In Review' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            'Completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'Closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'Cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        ];
        return $classes[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }

    function getPriorityClasses($priority) {
        $classes = [
            'none' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'high' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        ];
        return $classes[$priority] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }

    function isDueDateOverdue($dueDate, $status) {
        return !empty($dueDate) && 
               strtotime($dueDate) < time() && 
               $status !== 'Completed' && 
               $status !== 'Closed' && 
               $status !== 'Cancelled';
    }

    function isDueToday($dueDate) {
        return !empty($dueDate) && 
               date('Y-m-d', strtotime($dueDate)) === date('Y-m-d');
    }

    function formatTimeSpent($seconds) {
        if ($seconds <= 0) return '0h 0m';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return $hours . 'h ' . $minutes . 'm';
    }
    ?>

    <!-- JavaScript for Task Filtering -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelect = document.getElementById('task-filter');
            if (filterSelect) {
                filterSelect.addEventListener('change', function() {
                    const filter = this.value;
                    const rows = document.querySelectorAll('tbody tr');
                    const today = new Date().toISOString().split('T')[0];
                    const oneWeekLater = new Date();
                    oneWeekLater.setDate(oneWeekLater.getDate() + 7);
                    const weekEnd = oneWeekLater.toISOString().split('T')[0];
                    
                    rows.forEach(row => {
                        // Default to showing the row
                        let shouldShow = true;
                        
                        if (filter === 'all') {
                            // Show all rows
                            shouldShow = true;
                        } else if (filter === 'overdue') {
                            // Check if task is overdue
                            const dueDateEl = row.querySelector('td:nth-child(5)'); // Adjust index based on columns
                            const statusEl = row.querySelector('td:nth-child(4)'); // Adjust index based on columns
                            
                            if (dueDateEl && statusEl) {
                                const hasOverdueBadge = dueDateEl.textContent.includes('Overdue');
                                const completedStatus = statusEl.textContent.includes('Completed') || 
                                                       statusEl.textContent.includes('Closed');
                                shouldShow = hasOverdueBadge && !completedStatus;
                            }
                        } else if (filter === 'today') {
                            // Check if task is due today
                            const dueDateEl = row.querySelector('td:nth-child(5)'); // Adjust index based on columns
                            if (dueDateEl) {
                                shouldShow = dueDateEl.textContent.includes('Today');
                            }
                        } else if (filter === 'week') {
                            // Check if task is due this week (harder to do client-side)
                            // This would need server-side filtering for accuracy
                            // For now, we'll just show tasks marked as "Today" or with near dates
                            const dueDateEl = row.querySelector('td:nth-child(5)'); // Adjust index based on columns
                            if (dueDateEl) {
                                const dueDateText = dueDateEl.textContent;
                                // This is a simplified approach - server-side would be better
                                shouldShow = (dueDateText.includes('Today') || 
                                            !dueDateText.includes('Overdue'));
                            }
                        } else if (filter === 'in-progress') {
                            // Check if task is in progress
                            const statusEl = row.querySelector('td:nth-child(4)'); // Adjust index based on columns
                            if (statusEl) {
                                shouldShow = statusEl.textContent.includes('In Progress');
                            }
                        } else if (filter === 'completed') {
                            // Check if task is completed
                            const statusEl = row.querySelector('td:nth-child(4)'); // Adjust index based on columns
                            if (statusEl) {
                                shouldShow = statusEl.textContent.includes('Completed');
                            }
                        }
                        
                        row.style.display = shouldShow ? '' : 'none';
                    });
                });
            }
        });
    </script>
</body>
</html>