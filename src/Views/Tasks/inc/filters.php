<?php
//file: Views/Tasks/inc/filters.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
    <div class="flex items-center">
        <div class="flex items-center space-x-2 mr-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= $viewTitle ?></h1>
            <button class="favorite-star text-gray-400 hover:text-yellow-400 transition-colors"
                    data-type="page"
                    data-title="<?= htmlspecialchars($viewTitle) ?>"
                    data-url="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>"
                    data-icon="✅"
                    title="Add to favorites">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
            </button>
        </div>
        
        <?php if ($isMyTasksView && !$viewingOwnTasks && isset($userDetails)): ?>
        <div class="flex items-center bg-gray-200 dark:bg-gray-700 rounded-full px-3 py-1">
            <div class="w-7 h-7 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-xs mr-2">
                <?= htmlspecialchars(substr($userDetails->first_name, 0, 1) . substr($userDetails->last_name, 0, 1)) ?>
            </div>
            <span class="text-sm font-medium"><?= htmlspecialchars($userDetails->first_name . ' ' . $userDetails->last_name) ?></span>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="flex flex-wrap md:flex-nowrap gap-2 mt-2 md:mt-0">
        <!-- Context Switcher -->
        <?php if (!$isMyTasksView || $viewingOwnTasks): ?>
        <div class="relative">
            <select id="context-switcher" onchange="window.location.href=this.value" class="h-10 appearance-none w-full md:w-48 px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-10 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="/tasks/backlog" <?= $isBacklogView ? 'selected' : '' ?>>Backlog</option>
                <option value="/tasks" <?= (!$isMyTasksView && !$isUnassignedView && !$isBacklogView && !isset($_GET['no_subtasks'])) ? 'selected' : '' ?>>All Tasks</option>
                <option value="/tasks?no_subtasks=1" <?= (!$isMyTasksView && !$isUnassignedView && !$isBacklogView && isset($_GET['no_subtasks'])) ? 'selected' : '' ?>>No Subtasks</option>
                <option value="/tasks/assigned/<?= $currentUserId ?>" <?= $viewingOwnTasks ? 'selected' : '' ?>>My Tasks</option>
                <?php if (isset($_SESSION['user']) && isset($_SESSION['user']['permissions']) && in_array('manage_tasks', $_SESSION['user']['permissions'])): ?>
                <option value="/tasks/unassigned" <?= $isUnassignedView ? 'selected' : '' ?>>Unassigned Tasks</option>
                <?php endif; ?>
            </select>
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search Box -->
        <div class="relative flex-grow min-w-[200px]">
            <input type="search" id="search-tasks" placeholder="Search tasks..." class="h-10 w-full appearance-none py-2 pr-10 pl-10 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Filter Dropdown -->
        <div class="relative min-w-[160px]">
            <select id="task-filter" class="h-10 appearance-none w-full px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-10 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <?php foreach ($filterOptions as $value => $label): ?>
                <option value="<?= $value ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
            </div>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>

        <!-- Active Timer Button (if there's an active timer) -->
        <?php if (isset($activeTimer)): ?>
        <form action="/tasks/stop-timer/<?= $activeTimer['id'] ?>" method="POST" class="inline">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="h-10 inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                </svg>
                <span id="timer-display"><?= gmdate("H:i:s", $activeTimer['duration'] ?? 0) ?></span>
            </button>
        </form>
        <?php endif; ?>


    </div>
</div>