<?php
// file: Views/Dashboard/index.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use \App\Core\Config;
use \App\Utils\Breadcrumb;
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

        <!-- Breadcrumb -->
        <?= Breadcrumb::render('dashboard', []) ?>

        <!-- Welcome & Stats Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
            <!-- Welcome Card -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Welcome back, <?= htmlspecialchars($user->first_name) ?>!
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Here's an overview of your work and progress.
                        </p>
                    </div>
                    
                    <!-- Active Timer Widget (if any) -->
                    <?php if (isset($_SESSION['active_timer'])): 
                        $activeTask = $dashboardData['active_timer']['task'] ?? null;
                    ?>
                        <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3 timer-pulse">
                            <div class="flex items-center justify-between">
                                <div class="mr-4">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-300">Currently working on:</div>
                                    <a href="/tasks/view/<?= $activeTask->id ?>" class="text-blue-600 dark:text-blue-400 font-medium">
                                        <?= htmlspecialchars($activeTask->title) ?>
                                    </a>
                                </div>
                                <div class="text-right">
                                    <div id="active-timer" class="text-lg font-mono font-bold text-blue-600 dark:text-blue-400">00:00:00</div>
                                    <form action="/timer/stop" method="POST" class="mt-1">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                            Stop Timer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-3 text-center">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?= $dashboardData['task_summary']['in_progress'] ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Tasks In Progress</div>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/30 rounded-lg p-3 text-center">
                        <div class="text-3xl font-bold text-red-600 dark:text-red-400"><?= $dashboardData['task_summary']['overdue'] ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Overdue Tasks</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-3 text-center">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400"><?= $dashboardData['project_summary']['in_progress'] ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Active Projects</div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/30 rounded-lg p-3 text-center">
                        <div class="text-3xl font-bold text-purple-600 dark:text-purple-400"><?= number_format($dashboardData['time_tracking_summary']['this_week'] / 3600, 1) ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Hours This Week</div>
                    </div>
                </div>
            </div>

            <!-- Time Tracking Summary -->
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Time Tracking</h2>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-400">This Week</span>
                            <span class="font-medium"><?= formatTime($dashboardData['time_tracking_summary']['this_week']) ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <?php 
                            // Assume 40-hour work week
                            $weeklyPercentage = min(100, ($dashboardData['time_tracking_summary']['this_week'] / (40 * 3600)) * 100);
                            ?>
                            <div class="bg-blue-600 rounded-full h-2" style="width: <?= $weeklyPercentage ?>%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-400">This Month</span>
                            <span class="font-medium"><?= formatTime($dashboardData['time_tracking_summary']['this_month']) ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <?php 
                            // Assume 160-hour work month
                            $monthlyPercentage = min(100, ($dashboardData['time_tracking_summary']['this_month'] / (160 * 3600)) * 100);
                            ?>
                            <div class="bg-purple-600 rounded-full h-2" style="width: <?= $monthlyPercentage ?>%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-400">Billable Hours</span>
                            <span class="font-medium"><?= formatTime($dashboardData['time_tracking_summary']['billable_hours']) ?></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <?php 
                            $billablePercentage = $dashboardData['time_tracking_summary']['total_hours'] > 0 
                                ? min(100, ($dashboardData['time_tracking_summary']['billable_hours'] / $dashboardData['time_tracking_summary']['total_hours']) * 100)
                                : 0;
                            ?>
                            <div class="bg-green-600 rounded-full h-2" style="width: <?= $billablePercentage ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                        <a href="/tasks/create" class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                            </svg>
                            New Task
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h2>
                
                <div class="space-y-3">
                    <a href="/projects/create" class="flex items-center p-2 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-gray-800 dark:text-gray-200">New Project</span>
                    </a>
                    
                    <a href="/tasks/create" class="flex items-center p-2 bg-green-50 dark:bg-green-900/30 hover:bg-green-100 dark:hover:bg-green-900/50 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="text-gray-800 dark:text-gray-200">New Task</span>
                    </a>
                    
                    <a href="/milestones/create" class="flex items-center p-2 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                        </svg>
                        <span class="text-gray-800 dark:text-gray-200">New Milestone</span>
                    </a>
                    
                    <a href="/sprints/create" class="flex items-center p-2 bg-yellow-50 dark:bg-yellow-900/30 hover:bg-yellow-100 dark:hover:bg-yellow-900/50 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="text-gray-800 dark:text-gray-200">New Sprint</span>
                    </a>
                    
                    <a href="/companies/create" class="flex items-center p-2 bg-purple-50 dark:bg-purple-900/30 hover:bg-purple-100 dark:hover:bg-purple-900/50 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="text-gray-800 dark:text-gray-200">New Company</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Widgets -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tasks Section -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">My Tasks</h2>
                    <a href="/tasks" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        View All →
                    </a>
                </div>
                
                <!-- Task Tabs Navigation -->
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
                
                <!-- Upcoming Tasks Tab -->
                <div class="p-4 task-content" id="upcoming-tasks">
                    <?php 
                    $upcomingTasks = array_filter($dashboardData['recent_tasks'], function($task) {
                        return !empty($task->due_date) &&
                              strtotime($task->due_date) >= time() &&
                              $task->status_id != 5 && $task->status_id != 6; // Not closed or completed
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
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getTaskStatusClass($task->status_id) ?>">
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
                
                <!-- In Progress Tasks Tab -->
                <div class="p-4 task-content hidden" id="in-progress-tasks">
                    <?php 
                    $inProgressTasks = array_filter($dashboardData['recent_tasks'], function($task) {
                        return $task->status_id == 2; // In Progress status
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
                                                <form action="/timer/start" method="POST" class="inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                    <input type="hidden" name="task_id" value="<?= $task->id ?>">
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
                
                <!-- Overdue Tasks Tab -->
                <div class="p-4 task-content hidden" id="overdue-tasks">
                    <?php 
                    $overdueTasks = array_filter($dashboardData['recent_tasks'], function($task) {
                        return !empty($task->due_date) &&
                              strtotime($task->due_date) < time() &&
                              $task->status_id != 5 && $task->status_id != 6; // Not closed or completed
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
                                            <div class="flex space-x-2 mt-1 justify-end">
                                                <form action="/tasks/start-timer" method="POST" class="inline">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                    <input type="hidden" name="task_id" value="<?= $task->id ?>">
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 text-xs">
                                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                <a href="/tasks/edit/<?= $task->id ?>" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 text-xs">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </a>
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

            <!-- Projects & Milestones Section -->
        <div class="lg:col-span-1">
            <!-- Recent Projects -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
                <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Projects</h2>
                    <a href="/projects" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        View All →
                    </a>
                </div>
                
                <div class="p-4">
                    <?php if (!empty($dashboardData['recent_projects'])): ?>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach (array_slice($dashboardData['recent_projects'], 0, 4) as $project): ?>
                                <li class="py-3">
                                    <a href="/projects/view/<?= $project->id ?>" class="flex items-center">
                                        <div class="flex-1">
                                            <div class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                                <?= htmlspecialchars($project->project_name) ?>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                <?= htmlspecialchars($project->company_name ?? '') ?>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getProjectStatusClass($project->status_id) ?>">
                                <?= htmlspecialchars($project->status_name) ?>
                            </span>
                        </a>
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
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                <?= htmlspecialchars($milestone->project_name ?? 'No Project') ?>
                                            </div>
                                        </div>
                                        <div class="text-right text-sm">
                                            <div class="<?= isDueSoon($milestone->due_date) ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400' ?>">
                                                <?= formatDueDate($milestone->due_date) ?>
                                            </div>
                                            
                                            <?php if (isset($milestone->completion_rate)): ?>
                                            <div class="mt-1 w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 ml-auto">
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
        </div>
    </div>

    <!-- Project & Sprint Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Project Status Chart -->
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Project Status</h2>
            
            <div class="flex justify-center">
                <div class="relative inline-block w-40 h-40">
                    <!-- Donut chart using CSS conic gradient -->
                    <div class="absolute inset-0 rounded-full" style="background: conic-gradient(
                        #4ade80 0% <?= $projectCompletedPercent = ($dashboardData['project_summary']['completed'] / max(1, $dashboardData['project_summary']['total'])) * 100 ?>%, 
                        #60a5fa <?= $projectCompletedPercent ?>% <?= $projectCompletedPercent + ($dashboardData['project_summary']['in_progress'] / max(1, $dashboardData['project_summary']['total'])) * 100 ?>%, 
                        #f97316 <?= $projectCompletedPercent + ($dashboardData['project_summary']['in_progress'] / max(1, $dashboardData['project_summary']['total'])) * 100 ?>% <?= $projectCompletedPercent + ($dashboardData['project_summary']['in_progress'] / max(1, $dashboardData['project_summary']['total'])) * 100 + ($dashboardData['project_summary']['delayed'] / max(1, $dashboardData['project_summary']['total'])) * 100 ?>%, 
                        #a8a29e <?= $projectCompletedPercent + ($dashboardData['project_summary']['in_progress'] / max(1, $dashboardData['project_summary']['total'])) * 100 + ($dashboardData['project_summary']['delayed'] / max(1, $dashboardData['project_summary']['total'])) * 100 ?>% 100%);"></div>
                    <!-- Inner white circle to create donut -->
                    <div class="absolute inset-4 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center">
                        <span class="text-lg font-semibold"><?= $dashboardData['project_summary']['total'] ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Legend -->
            <div class="grid grid-cols-2 gap-2 mt-4">
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-green-400 rounded-full mr-2"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Completed (<?= $dashboardData['project_summary']['completed'] ?>)</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-blue-400 rounded-full mr-2"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Active (<?= $dashboardData['project_summary']['in_progress'] ?>)</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-orange-400 rounded-full mr-2"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Delayed (<?= $dashboardData['project_summary']['delayed'] ?>)</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-gray-400 rounded-full mr-2"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">On Hold (<?= $dashboardData['project_summary']['on_hold'] ?? 0 ?>)</span>
                </div>
            </div>
        </div>

        <!-- Task Completion Status -->
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Task Status</h2>
            
            <?php
            // Calculate task completion percentage
            $taskTotal = $dashboardData['task_summary']['total'] ?: 1;
            $completedPercentage = round(($dashboardData['task_summary']['completed'] / $taskTotal) * 100);
            $inProgressPercentage = round(($dashboardData['task_summary']['in_progress'] / $taskTotal) * 100);
            $overduePercentage = round(($dashboardData['task_summary']['overdue'] / $taskTotal) * 100);
            $remainingPercentage = 100 - $completedPercentage - $inProgressPercentage - $overduePercentage;
            ?>
            
            <!-- Task Progress Bars -->
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Completed</span>
                        <span class="font-medium"><?= $dashboardData['task_summary']['completed'] ?> (<?= $completedPercentage ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-green-500 h-2.5 rounded-full" style="width: <?= $completedPercentage ?>%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">In Progress</span>
                        <span class="font-medium"><?= $dashboardData['task_summary']['in_progress'] ?> (<?= $inProgressPercentage ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?= $inProgressPercentage ?>%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Overdue</span>
                        <span class="font-medium"><?= $dashboardData['task_summary']['overdue'] ?> (<?= $overduePercentage ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-red-500 h-2.5 rounded-full" style="width: <?= $overduePercentage ?>%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Open/Other</span>
                        <span class="font-medium"><?= $dashboardData['task_summary']['total'] - $dashboardData['task_summary']['completed'] - $dashboardData['task_summary']['in_progress'] - $dashboardData['task_summary']['overdue'] ?> (<?= $remainingPercentage ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-gray-500 h-2.5 rounded-full" style="width: <?= $remainingPercentage ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Sprints -->
        <div class="lg:col-span-1 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Active Sprints</h2>
                <a href="/sprints" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                    View All →
                </a>
            </div>
            
            <?php if (isset($dashboardData['active_sprints']) && !empty($dashboardData['active_sprints'])): ?>
                <ul class="space-y-4">
                    <?php foreach (array_slice($dashboardData['active_sprints'], 0, 3) as $sprint): 
                        // Calculate sprint progress
                        $sprintStartDate = strtotime($sprint->start_date);
                        $sprintEndDate = strtotime($sprint->end_date);
                        $today = time();
                        $sprintDuration = $sprintEndDate - $sprintStartDate;
                        $elapsed = $today - $sprintStartDate;
                        $progressPercentage = min(100, max(0, ($elapsed / $sprintDuration) * 100));
                        
                        // Calculate days remaining
                        $daysRemaining = ceil(($sprintEndDate - $today) / (60 * 60 * 24));
                    ?>
                        <li class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <a href="/sprints/view/<?= $sprint->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                        <?= htmlspecialchars($sprint->name) ?>
                                    </a>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <?= date('M d', strtotime($sprint->start_date)) ?> - <?= date('M d, Y', strtotime($sprint->end_date)) ?>
                                    </div>
                                </div>
                                <div class="text-sm font-medium <?= $daysRemaining <= 2 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' ?>">
                                    <?= $daysRemaining > 0 ? $daysRemaining . ' days left' : 'Ends today' ?>
                                </div>
                            </div>
                            
                            <!-- Sprint progress bar -->
                            <div class="mt-2">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-500 dark:text-gray-400">Progress</span>
                                    <span><?= round($progressPercentage) ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?= $progressPercentage ?>%"></div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                    No active sprints.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Footer -->
<?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

<!-- Helper Functions -->
<?php
function getTaskStatusClass($statusId) {
    return match((int)$statusId) {
        1 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Open
        2 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300', // In Progress
        3 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', // On Hold
        4 => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300', // In Review
        5 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Closed
        6 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', // Completed
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    };
}

function getProjectStatusClass($statusId) {
    return match((int)$statusId) {
        1 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300', // Ready
        2 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', // In Progress
        3 => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300', // Completed
        4 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', // On Hold
        6 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300', // Delayed
        7 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Cancelled
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
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

function formatTime($seconds) {
    if (!$seconds) return '0h 0m';
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    return "{$hours}h {$minutes}m";
}
?>

<!-- JavaScript for Active Timer and Tab Switching -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Active timer counter
        const activeTimerElement = document.getElementById('active-timer');
        if (activeTimerElement) {
            let startTime = <?= isset($dashboardData['active_timer']) ? $dashboardData['active_timer']['start_time'] : 0 ?>;
            
            function updateTimer() {
                if (!startTime) return;
                
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
                    t.classList.remove('active', 'border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                    t.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
                });
                
                // Activate clicked tab
                tab.classList.add('active', 'border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-300');
                
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