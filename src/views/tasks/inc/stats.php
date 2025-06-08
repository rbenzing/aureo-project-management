<?php
//file: Views/Tasks/inc/stats.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Calculate stats
$totalTaskCount = 0;
$inProgressCount = 0;
$overdueCount = 0;
$completedCount = 0;

if (!empty($tasks)) {
    // Check if tasks are organized by status (project context) or flat array (other contexts)
    if (is_array($tasks) && isset($tasks['open']) || isset($tasks['in_progress']) || isset($tasks['completed'])) {
        // Tasks are organized by status - flatten them for counting
        $flatTasks = [];
        foreach ($tasks as $statusGroup) {
            if (is_array($statusGroup)) {
                $flatTasks = array_merge($flatTasks, $statusGroup);
            }
        }

        $totalTaskCount = count($flatTasks);

        foreach ($flatTasks as $task) {
            if (is_object($task)) {
                if ($task->status_name === 'In Progress') {
                    $inProgressCount++;
                }

                if (!empty($task->due_date) && strtotime($task->due_date) < time() &&
                    $task->status_name !== 'Completed' && $task->status_name !== 'Closed') {
                    $overdueCount++;
                }

                if ($task->status_name === 'Completed') {
                    $completedCount++;
                }
            }
        }
    } else {
        // Tasks are a flat array - process normally
        $totalTaskCount = count($tasks);

        foreach ($tasks as $task) {
            if (is_object($task)) {
                if ($task->status_name === 'In Progress') {
                    $inProgressCount++;
                }

                if (!empty($task->due_date) && strtotime($task->due_date) < time() &&
                    $task->status_name !== 'Completed' && $task->status_name !== 'Closed') {
                    $overdueCount++;
                }

                if ($task->status_name === 'Completed') {
                    $completedCount++;
                }
            }
        }
    }
}
?>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
        <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3 mr-4">
            <svg class="w-6 h-6 text-blue-500 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tasks</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100"><?= $totalTaskCount ?></div>
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
            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100"><?= $inProgressCount ?></div>
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
            <div class="text-xl font-semibold text-red-600 dark:text-red-400"><?= $overdueCount ?></div>
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
            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100"><?= $completedCount ?></div>
        </div>
    </div>
</div>