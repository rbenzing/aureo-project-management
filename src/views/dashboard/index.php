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
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .timer-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6 flex-grow">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <!-- Welcome & Quick Actions Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Welcome Card -->
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Welcome back, <?= htmlspecialchars($user->first_name) ?>!
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Here's an overview of your work and progress.
                        </p>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="h-full bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3 text-center">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?= $dashboardData['task_summary']['in_progress'] ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Tasks In Progress</div>
                    </div>
                    <div class="h-full bg-red-50 dark:bg-red-900/30 rounded-lg p-3 text-center">
                        <div class="text-3xl font-bold text-red-600 dark:text-red-400"><?= $dashboardData['task_summary']['overdue'] ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Overdue Tasks</div>
                    </div>
                    <div class="h-full bg-green-50 dark:bg-green-900/30 rounded-lg p-3 text-center">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400"><?= $dashboardData['project_summary']['in_progress'] ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Active Projects</div>
                    </div>
                    <div class="h-full bg-purple-50 dark:bg-purple-900/30 rounded-lg p-3 text-center">
                        <div class="text-3xl font-bold text-purple-600 dark:text-purple-400"><?= number_format($dashboardData['time_tracking_summary']['this_week'] / 3600, 1) ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Hours This Week</div>
                    </div>
                </div>
            </div>

            <!-- Active Timer / Quick Actions -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">Quick Actions</h2>
                <div class="space-y-2">
                    <a href="/projects/create" class="flex items-center p-2 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-gray-800 dark:text-gray-200">+ New Project</span>
                    </a>
                    <a href="/milestones/create?type=epic" class="flex items-center p-2 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                        <span class="text-gray-800 dark:text-gray-200">+ New Epic</span>
                    </a>
                    <a href="/milestones/create" class="flex items-center p-2 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                        <span class="text-gray-800 dark:text-gray-200">+ New Milestone</span>
                    </a>
                    <a href="/sprints/create" class="flex items-center p-2 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-gray-800 dark:text-gray-200">+ New Sprint</span>
                    </a>
                    <a href="/tasks/create" class="flex items-center p-2 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="text-gray-800 dark:text-gray-200">+ New Task</span>
                    </a>
                </div>
            </div>

            <!-- Favorites -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">Favorite Links</h2>
                <div class="flex flex-col justify-center items-center">
                    No favorite links found.
                </div>
            </div>
        </div>

        <!-- Dashboard Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Tasks Section -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">My Tasks</h2>
                    <a href="/tasks/assigned" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        View All →
                    </a>
                </div>
                
                <!-- Task Categories -->
                <div class="grid grid-cols-3 border-b border-gray-200 dark:border-gray-700">
                    <button class="task-tab active p-3 text-center font-medium border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400" data-target="upcoming-tasks">
                        Upcoming
                    </button>
                    <button class="task-tab p-3 text-center font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 border-b-2 border-transparent" data-target="in-progress-tasks">
                        In Progress
                    </button>
                    <button class="task-tab p-3 text-center font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 border-b-2 border-transparent" data-target="overdue-tasks">
                        Overdue
                    </button>
                </div>
                
                <!-- Task Lists -->
                <div class="p-4 task-content" id="upcoming-tasks">
                    <?php 
                    $upcomingTasks = array_filter($dashboardData['recent_tasks'], function($task) {
                        return !empty($task->due_date) && 
                               strtotime($task->due_date) > time() && 
                               $task->status_name !== 'Completed' && 
                               $task->status_name !== 'Closed';
                    });
                    if (!empty($upcomingTasks)): 
                    ?>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach (array_slice($upcomingTasks, 0, 5) as $task): ?>
                                <li class="py-3">
                                    <div class="flex justify-between">
                                        <div>
                                            <a href="/tasks/view/<?= $task->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                                <?= htmlspecialchars($task->title) ?>
                                            </a>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <?= htmlspecialchars($task->project_name ?? 'No Project') ?>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium <?= isDueSoon($task->due_date) ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400' ?>">
                                                <?= formatDueDate($task->due_date) ?>
                                            </div>
                                            <div class="text-xs mt-1">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getTaskStatusClass($task->status_name) ?>">
                                                    <?= htmlspecialchars($task->status_name) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="py-6 text-center text-gray-500 dark:text-gray-400">
                            No upcoming tasks.
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-4 task-content hidden" id="in-progress-tasks">
                    <?php 
                    $inProgressTasks = array_filter($dashboardData['recent_tasks'], function($task) {
                        return $task->status_name === 'In Progress';
                    });
                    if (!empty($inProgressTasks)): 
                    ?>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach (array_slice($inProgressTasks, 0, 5) as $task): ?>
                                <li class="py-3">
                                    <div class="flex justify-between">
                                        <div>
                                            <a href="/tasks/view/<?= $task->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                                <?= htmlspecialchars($task->title) ?>
                                            </a>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <?= htmlspecialchars($task->project_name ?? 'No Project') ?>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <?php if (!empty($task->due_date)): ?>
                                                <div class="text-sm font-medium <?= isDueSoon($task->due_date) ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400' ?>">
                                                    <?= formatDueDate($task->due_date) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="mt-1">
                                                <form action="/tasks/start-timer/<?= $task->id ?>" method="POST" class="inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 text-xs">
                                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Start Timer
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="py-6 text-center text-gray-500 dark:text-gray-400">
                            No tasks in progress.
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-4 task-content hidden" id="overdue-tasks">
                    <?php 
                    $overdueTasks = array_filter($dashboardData['recent_tasks'], function($task) {
                        return !empty($task->due_date) && 
                               strtotime($task->due_date) < time() && 
                               $task->status_name !== 'Completed' && 
                               $task->status_name !== 'Closed';
                    });
                    if (!empty($overdueTasks)): 
                    ?>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach (array_slice($overdueTasks, 0, 5) as $task): ?>
                                <li class="py-3">
                                    <div class="flex justify-between">
                                        <div>
                                            <a href="/tasks/view/<?= $task->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                                <?= htmlspecialchars($task->title) ?>
                                            </a>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <?= htmlspecialchars($task->project_name ?? 'No Project') ?>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-red-600 dark:text-red-400">
                                                <?= formatOverdueDate($task->due_date) ?>
                                            </div>
                                            <div class="text-xs mt-1">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getTaskStatusClass($task->status_name) ?>">
                                                    <?= htmlspecialchars($task->status_name) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="py-6 text-center text-gray-500 dark:text-gray-400">
                            No overdue tasks.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Weekly Activity Report -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow rounded-lg p-5">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Weekly Activity</h2>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <?= date('M j', strtotime('this week monday')) ?> - <?= date('M j', strtotime('this week sunday')) ?>
                    </div>
                </div>
                
                <!-- Time Tracking -->
                <div class="mb-6">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span>Time Tracking</span>
                        <span><?= number_format($dashboardData['time_tracking_summary']['this_week'] / 3600, 1) ?> hrs</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <?php 
                        // Assume 40-hour work week for percentage
                        $weeklyTimePercentage = min(100, ($dashboardData['time_tracking_summary']['this_week'] / (40 * 3600)) * 100);
                        ?>
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $weeklyTimePercentage ?>%"></div>
                    </div>
                </div>
                
                <!-- Task Completion -->
                <div class="mb-6">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span>Task Completion</span>
                        <span>
                            <?php 
                            // Calculate based on task summary
                            $completed = $dashboardData['task_summary']['completed'];
                            $total = $dashboardData['task_summary']['total'];
                            $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;
                            echo round($completionRate, 1) . '%';
                            ?>
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: <?= $completionRate ?>%"></div>
                    </div>
                </div>
                
                <!-- Project Distribution Circle Chart -->
                <div class="flex flex-col gap-2">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3">Activity Distribution</h3>
                    <div class="flex flex-row justify-start">
                        <div class="w-32 h-32 rounded-full flex items-center justify-center" style="background: conic-gradient(#60a5fa <?= $weeklyTimePercentage * 3.6 ?>deg, #34d399 <?= $weeklyTimePercentage * 3.6 ?>deg <?= $weeklyTimePercentage * 3.6 + $completionRate * 3.6 ?>deg, #f87171 <?= $weeklyTimePercentage * 3.6 + $completionRate * 3.6 ?>deg <?= $weeklyTimePercentage * 3.6 + $completionRate * 3.6 + 40 * 3.6 ?>deg, #e4e4e7 <?= $weeklyTimePercentage * 3.6 + $completionRate * 3.6 + 40 * 3.6 ?>deg);">
                            <div class="w-24 h-24 bg-white dark:bg-gray-800 rounded-full"></div>
                        </div>
                        <div class="grid grid-cols-1 gap-2 ml-4">
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Time Tracked</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Completed Tasks</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">In Progress</span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-3 h-3 bg-gray-300 dark:bg-gray-600 rounded-full mr-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Remaining</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Projects Overview -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Projects Overview</h2>
                    <a href="/projects" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        View All →
                    </a>
                </div>
                
                <!-- Project Summary -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100"><?= $dashboardData['project_summary']['total'] ?></div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?= $dashboardData['project_summary']['in_progress'] ?></div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Active</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600 dark:text-red-400"><?= $dashboardData['project_summary']['delayed'] ?></div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Delayed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600 dark:text-green-400"><?= $dashboardData['project_summary']['completed'] ?></div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Completed</div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Projects -->
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3">Recent Projects</h3>
                    <?php if (!empty($dashboardData['recent_projects'])): ?>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($dashboardData['recent_projects'] as $project): ?>
                                <li class="py-3">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <a href="/projects/view/<?= $project->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                                <?= htmlspecialchars($project->name) ?>
                                            </a>
                                            <?php if (!empty($project->description)): ?>
                                                <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                                    <?= htmlspecialchars(substr($project->description, 0, 60)) . (strlen($project->description) > 60 ? '...' : '') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-right">
                                            <?php 
                                            // Show different data based on status
                                            if (isset($project->status_id)): 
                                                $statusClass = getProjectStatusClass($project->status_id);
                                                $statusName = getProjectStatusName($project->status_id);
                                            ?>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                                    <?= $statusName ?>
                                                </span>
                                            <?php endif; ?>
                                            <div class="text-gray-500 dark:text-gray-400 mt-1">
                                                <?= date('M j, Y', strtotime($project->created_at)) ?>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="py-6 text-center text-gray-500 dark:text-gray-400">
                            No recent projects.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Milestones & Sprints -->
            <div class="lg:col-span-2 grid grid-cols-1 gap-6">
                <!-- Upcoming Milestones -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upcoming Milestones</h2>
                        <a href="/milestones" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                            View All →
                        </a>
                    </div>
                    
                    <div class="p-4">
                        <?php if (!empty($dashboardData['upcoming_milestones'])): ?>
                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach (array_slice($dashboardData['upcoming_milestones'], 0, 4) as $milestone): ?>
                                    <li class="py-3">
                                        <div class="flex justify-between">
                                            <div>
                                                <a href="/milestones/view/<?= $milestone->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                                    <?= htmlspecialchars($milestone->title) ?>
                                                </a>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    <?= htmlspecialchars($milestone->project_name ?? 'No Project') ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm <?= isDueSoon($milestone->due_date) ? 'text-yellow-600 dark:text-yellow-400 font-medium' : 'text-gray-600 dark:text-gray-400' ?>">
                                                    <?= $milestone->due_date ? date('M j, Y', strtotime($milestone->due_date)) : 'No Due Date' ?>
                                                </div>
                                                <?php if (isset($milestone->completion_rate)): ?>
                                                    <div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 mt-1 ml-auto">
                                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?= $milestone->completion_rate ?>%"></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="py-6 text-center text-gray-500 dark:text-gray-400">
                                No upcoming milestones.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Active Sprints -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Active Sprints
                            <?php if (isset($dashboardData['active_sprints']) && count($dashboardData['active_sprints']) > 0): ?>
                                <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-xs rounded-full">
                                    <?= count($dashboardData['active_sprints']) ?>
                                </span>
                            <?php endif; ?>
                        </h2>
                        <a href="/sprints" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                            View All →
                        </a>
                    </div>
                    
                    <div class="p-4">
                        <?php if (isset($dashboardData['active_sprints']) && !empty($dashboardData['active_sprints'])): ?>
                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($dashboardData['active_sprints'] as $sprint): 
                                    // Calculate sprint progress
                                    $sprintStartDate = strtotime($sprint->start_date);
                                    $sprintEndDate = strtotime($sprint->end_date);
                                    $today = time();
                                    $sprintDuration = $sprintEndDate - $sprintStartDate;
                                    $elapsed = $today - $sprintStartDate;
                                    $progressPercentage = min(100, max(0, ($elapsed / $sprintDuration) * 100));
                                ?>
                                    <li class="py-3">
                                        <div class="flex justify-between">
                                            <div>
                                                <a href="/sprints/view/<?= $sprint->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                                    <?= htmlspecialchars($sprint->name) ?>
                                                </a>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    <?= htmlspecialchars($sprint->project_name ?? 'No Project') ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                                    <?php
                                                    $daysRemaining = ceil(($sprintEndDate - $today) / (60 * 60 * 24));
                                                    echo $daysRemaining > 0 ? $daysRemaining . ' days left' : 'Ends today';
                                                    ?>
                                                </div>
                                                <div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 mt-1 ml-auto">
                                                    <div class="bg-green-600 h-1.5 rounded-full" style="width: <?= $progressPercentage ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="py-6 text-center text-gray-500 dark:text-gray-400">
                                No active sprints.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <!-- Helper Functions -->
    <?php
    function getTaskStatusClass($statusName) {
        return match($statusName) {
            'Open' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'On Hold' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'In Review' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            'Closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'Completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'Cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        };
    }

    function getProjectStatusClass($statusId) {
        return match((int)$statusId) {
            1 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300', // Ready
            2 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', // In Progress
            3 => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300', // Completed
            4 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', // On Hold
            5 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300', // Delayed
            6 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Cancelled
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        };
    }

    function getProjectStatusName($statusId) {
        return match((int)$statusId) {
            1 => 'Ready',
            2 => 'In Progress',
            3 => 'Completed',
            4 => 'On Hold',
            5 => 'Delayed',
            6 => 'Cancelled',
            default => 'Unknown'
        };
    }

    function isDueSoon($dueDate) {
        if (empty($dueDate)) return false;
        $dueTimestamp = strtotime($dueDate);
        $now = time();
        $diff = $dueTimestamp - $now;
        $daysDiff = floor($diff / (60 * 60 * 24));
        return ($daysDiff >= 0 && $daysDiff <= 3);
    }

    function formatDueDate($dueDate) {
        if (empty($dueDate)) return 'No due date';
        
        $dueTimestamp = strtotime($dueDate);
        $now = time();
        $diff = $dueTimestamp - $now;
        $daysDiff = floor($diff / (60 * 60 * 24));
        
        if ($daysDiff === 0) {
            return 'Due today';
        } else if ($daysDiff === 1) {
            return 'Due tomorrow';
        } else if ($daysDiff > 1 && $daysDiff <= 7) {
            return 'Due in ' . $daysDiff . ' days';
        } else {
            return date('M j, Y', $dueTimestamp);
        }
    }

    function formatOverdueDate($dueDate) {
        if (empty($dueDate)) return 'No due date';
        
        $dueTimestamp = strtotime($dueDate);
        $now = time();
        $diff = $now - $dueTimestamp;
        $daysDiff = floor($diff / (60 * 60 * 24));
        
        if ($daysDiff === 0) {
            return 'Due today';
        } else if ($daysDiff === 1) {
            return '1 day overdue';
        } else {
            return $daysDiff . ' days overdue';
        }
    }
    ?>

    <!-- JavaScript for Active Timer and Tab Switching -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Active timer counter
            const activeTimerElement = document.getElementById('active-timer');
            if (activeTimerElement) {
                let startTime = <?= !empty($dashboardData['active_timer']) ? $dashboardData['active_timer']['start_time'] : 0 ?>;
                
                function updateTimer() {
                    const now = Math.floor(Date.now() / 1000);
                    const duration = now - startTime;
                    
                    const hours = Math.floor(duration / 3600).toString().padStart(2, '0');
                    const minutes = Math.floor((duration % 3600) / 60).toString().padStart(2, '0');
                    const seconds = Math.floor(duration % 60).toString().padStart(2, '0');
                    
                    activeTimerElement.textContent = `${hours}:${minutes}:${seconds}`;
                }
                
                // Update every second
                setInterval(updateTimer, 1000);
                updateTimer(); // Initial update
            }
            
            // Tab switching for tasks
            const taskTabs = document.querySelectorAll('.task-tab');
            const taskContents = document.querySelectorAll('.task-content');
            
            taskTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Deactivate all tabs
                    taskTabs.forEach(t => {
                        t.classList.remove('border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                        t.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
                    });
                    
                    // Activate clicked tab
                    tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
                    tab.classList.add('border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                    
                    // Hide all content
                    taskContents.forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    // Show relevant content
                    const targetId = tab.dataset.target;
                    document.getElementById(targetId).classList.remove('hidden');
                });
            });
        });
    </script>
</body>
</html>