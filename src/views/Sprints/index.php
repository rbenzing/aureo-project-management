<?php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprints - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
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

        <!-- Project Selection (if not already selected) -->
        <?php if (empty($project)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Select a Project</h2>
                <?php if (!empty($projects)): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($projects as $proj): ?>
                            <a href="/sprints/project/<?= $proj->id ?>" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center">
                                <div class="h-10 w-10 rounded-md bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-3">
                                    <svg class="h-6 w-6 text-indigo-500 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($proj->name) ?></div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php
                                        // Get sprint counts for this project
                                        $activeSprintCount = 0;
                                        $completedSprintCount = 0;
                                        
                                        if (!empty($projectSprintCounts) && isset($projectSprintCounts[$proj->id])) {
                                            $activeSprintCount = $projectSprintCounts[$proj->id]['active'] ?? 0;
                                            $completedSprintCount = $projectSprintCounts[$proj->id]['completed'] ?? 0;
                                        }
                                        
                                        echo $activeSprintCount > 0 ? 
                                            "<span class='text-indigo-600 dark:text-indigo-400'>{$activeSprintCount} active</span>" : 
                                            "No active sprints";
                                        
                                        if ($completedSprintCount > 0) {
                                            echo ", {$completedSprintCount} completed";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400 mb-4">No projects found.</p>
                        <a href="/projects/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            + Create Project
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Project is selected - display sprints -->
            
            <!-- Sprint Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
                    <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3 mr-4">
                        <svg class="w-6 h-6 text-blue-500 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sprints</div>
                        <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            <?= !empty($sprints) ? count($sprints) : 0 ?>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
                    <div class="rounded-full bg-green-100 dark:bg-green-900 p-3 mr-4">
                        <svg class="w-6 h-6 text-green-500 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Sprint</div>
                        <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            <?php 
                            $activeSprintCount = 0;
                            if (!empty($sprints)) {
                                foreach ($sprints as $sprint) {
                                    if (isset($sprint->status_id) && $sprint->status_id == 2) { // Active status
                                        $activeSprintCount++;
                                    }
                                }
                            }
                            echo $activeSprintCount;
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
                    <div class="rounded-full bg-yellow-100 dark:bg-yellow-900 p-3 mr-4">
                        <svg class="w-6 h-6 text-yellow-500 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Planning</div>
                        <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            <?php 
                            $planningCount = 0;
                            if (!empty($sprints)) {
                                foreach ($sprints as $sprint) {
                                    if (isset($sprint->status_id) && $sprint->status_id == 1) { // Planning status
                                        $planningCount++;
                                    }
                                }
                            }
                            echo $planningCount;
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
                    <div class="rounded-full bg-purple-100 dark:bg-purple-900 p-3 mr-4">
                        <svg class="w-6 h-6 text-purple-500 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Velocity</div>
                        <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            <?php
                            $completedSprints = 0;
                            $totalVelocity = 0;
                            
                            if (!empty($sprints)) {
                                foreach ($sprints as $sprint) {
                                    if (isset($sprint->status_id) && $sprint->status_id == 4 && isset($sprint->velocity_percentage)) { // Completed
                                        $completedSprints++;
                                        $totalVelocity += $sprint->velocity_percentage;
                                    }
                                }
                            }
                            
                            echo $completedSprints > 0 ? 
                                round($totalVelocity / $completedSprints) . '%' : 
                                'N/A';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Header with Navigation -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 overflow-hidden">
                <div class="p-4 md:p-6 flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center mb-4 md:mb-0">
                        <div class="h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-4">
                            <svg class="h-7 w-7 text-indigo-500 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars($project->name ?? 'Project') ?>
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                <?= !empty($project->description) ? htmlspecialchars(substr($project->description, 0, 100)) . (strlen($project->description) > 100 ? '...' : '') : 'No description' ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="/projects/view/<?= $project->id ?? 0 ?>" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Project Details
                        </a>
                        <a href="/sprints/create?project_id=<?= $project->id ?? 0 ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            + New Sprint
                        </a>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 dark:border-gray-700 px-4">
                    <div class="flex overflow-x-auto">
                        <a href="/projects/view/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap">
                            Overview
                        </a>
                        <a href="/tasks/project/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap">
                            Tasks
                        </a>
                        <a href="/sprints/project/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                            Sprints
                        </a>
                        <a href="/milestones/project/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap">
                            Milestones
                        </a>
                    </div>
                </div>
            </div>

            <!-- Page Header with Filters -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                <div class="flex items-center">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Sprints</h2>
                    <?php if (!empty($sprints) && $activeSprintCount > 0): ?>
                        <span class="ml-3 px-2.5 py-0.5 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 text-xs font-medium rounded-full">
                            Active Sprint
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="flex flex-col md:flex-row gap-3">
                    <!-- Status Filter -->
                    <div class="relative">
                        <select id="status-filter" class="appearance-none px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md pl-4 pr-10 w-full md:w-40 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="all">All Statuses</option>
                            <option value="1">Planning</option>
                            <option value="2">Active</option>
                            <option value="3">Delayed</option>
                            <option value="4">Completed</option>
                            <option value="5">Cancelled</option>
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
                            id="sprint-search"
                            type="search" 
                            placeholder="Search sprints..." 
                            class="w-full md:w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Sprint Panel (if exists) -->
            <?php 
            $activeSprint = null;
            if (!empty($sprints)) {
                foreach ($sprints as $sprint) {
                    if (isset($sprint->status_id) && $sprint->status_id == 2) { // Active status
                        $activeSprint = $sprint;
                        break;
                    }
                }
            }
            
            if ($activeSprint): 
                // Calculate days remaining in sprint
                $sprintEndDate = strtotime($activeSprint->end_date ?? date('Y-m-d'));
                $today = time();
                $daysRemaining = ceil(($sprintEndDate - $today) / (60 * 60 * 24));
                
                // Calculate sprint progress
                $sprintStartDate = strtotime($activeSprint->start_date ?? date('Y-m-d', strtotime('-7 days')));
                $sprintDuration = $sprintEndDate - $sprintStartDate;
                $elapsed = $today - $sprintStartDate;
                $progressPercentage = min(100, max(0, ($elapsed / $sprintDuration) * 100));
                
                // Calculate task completion stats
                $totalTasks = isset($activeSprint->total_tasks) ? $activeSprint->total_tasks : 0;
                $completedTasks = isset($activeSprint->completed_tasks) ? $activeSprint->completed_tasks : 0;
                $taskPercentage = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
            ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8 overflow-hidden border-l-4 border-green-500 dark:border-green-600">
                <div class="p-5">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4">
                        <div class="flex items-center mb-3 md:mb-0">
                            <div class="bg-green-100 dark:bg-green-900 p-2 rounded-md mr-3">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        <?= htmlspecialchars($activeSprint->name ?? 'Active Sprint') ?>
                                    </h3>
                                    <span class="ml-2 px-2.5 py-0.5 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 text-xs font-medium rounded-full">
                                        Active
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <?= isset($activeSprint->start_date) ? date('M j', strtotime($activeSprint->start_date)) : '?' ?> - 
                                    <?= isset($activeSprint->end_date) ? date('M j, Y', strtotime($activeSprint->end_date)) : '?' ?>
                                    <span class="ml-2 font-medium <?= $daysRemaining < 3 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' ?>">
                                        <?= $daysRemaining ?> days remaining
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="/sprints/view/<?= $activeSprint->id ?? 0 ?>" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                View Details
                            </a>
                            <form action="/sprints/complete/<?= $activeSprint->id ?? 0 ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to complete this sprint?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    Complete Sprint
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- Sprint Progress -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Sprint Progress</h4>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?= round($progressPercentage) ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5 mb-2">
                                <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?= $progressPercentage ?>%"></div>
                            </div>
                        </div>
                        
                        <!-- Task Completion -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tasks Completed</h4>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?= $completedTasks ?> of <?= $totalTasks ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5 mb-2">
                                <div class="bg-green-500 h-2.5 rounded-full" style="width: <?= $taskPercentage ?>%"></div>
                            </div>
                        </div>
                        
                        <!-- Burndown Status -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h4>
                                <?php
                                $statusIndicator = '';
                                if ($progressPercentage > $taskPercentage + 20) {
                                    $statusIndicator = '<span class="text-red-500 dark:text-red-400">Behind Schedule</span>';
                                } elseif ($taskPercentage >= $progressPercentage) {
                                    $statusIndicator = '<span class="text-green-500 dark:text-green-400">On Track</span>';
                                } else {
                                    $statusIndicator = '<span class="text-yellow-500 dark:text-yellow-400">Slightly Behind</span>';
                                }
                                echo $statusIndicator;
                                ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php
                                if ($progressPercentage > $taskPercentage + 20) {
                                    echo 'Sprint tasks are falling behind schedule.';
                                } elseif ($taskPercentage >= $progressPercentage) {
                                    echo 'Sprint is on track or ahead of schedule.';
                                } else {
                                    echo 'Sprint slightly behind, but recoverable.';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Task Summary -->
                    <?php if (!empty($activeSprint->tasks)): ?>
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Recent Tasks</h4>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Task</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assignee</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php 
                                        $limit = min(5, count($activeSprint->tasks));
                                        for($i = 0; $i < $limit; $i++): 
                                            $task = $activeSprint->tasks[$i];
                                        ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <a href="/tasks/view/<?= $task->id ?? 0 ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                        <?= htmlspecialchars($task->title ?? 'Task') ?>
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getTaskStatusClass($task->status_name ?? 'Open') ?>">
                                                        <?= htmlspecialchars($task->status_name ?? 'Open') ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if (!empty($task->first_name)): ?>
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                                                <span class="text-xs font-medium text-white">
                                                                    <?= htmlspecialchars(substr($task->first_name, 0, 1) . substr($task->last_name, 0, 1)) ?>
                                                                </span>
                                                            </div>
                                                            <div class="ml-2">
                                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                                    <?= htmlspecialchars("{$task->first_name} {$task->last_name}") ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-gray-500 dark:text-gray-400">Unassigned</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($activeSprint->tasks) > 5): ?>
                                <div class="mt-2 text-right">
                                    <a href="/sprints/view/<?= $activeSprint->id ?? 0 ?>" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        View all <?= count($activeSprint->tasks) ?> tasks →
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- All Sprints List -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sprint</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Duration</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tasks</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Velocity</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700" id="sprints-list">
                        <?php if (!empty($sprints)): ?>
                            <?php foreach ($sprints as $sprint): 
                                // Safe access to properties with fallbacks
                                $totalTasks = isset($sprint->total_tasks) ? $sprint->total_tasks : 0;
                                $completedTasks = isset($sprint->completed_tasks) ? $sprint->completed_tasks : 0;
                                $taskPercentage = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                                
                                // Calculate days from start to end
                                $sprintDays = 0;
                                $daysLeft = 0;
                                
                                if (isset($sprint->start_date) && isset($sprint->end_date)) {
                                    $startDate = new DateTime($sprint->start_date);
                                    $endDate = new DateTime($sprint->end_date);
                                    $interval = $startDate->diff($endDate);
                                    $sprintDays = $interval->days + 1; // +1 to include both start and end dates
                                    
                                    // Check if sprint is ongoing
                                    $now = new DateTime();
                                    if (isset($sprint->status_id) && $sprint->status_id == 2 && $now >= $startDate && $now <= $endDate) {
                                        $daysLeft = $now->diff($endDate)->days;
                                    }
                                }
                            ?>
                                <tr class="sprint-row hover:bg-gray-50 dark:hover:bg-gray-700" 
                                    data-status="<?= $sprint->status_id ?? 0 ?>"
                                    data-name="<?= htmlspecialchars($sprint->name ?? '') ?>">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                <a href="/sprints/view/<?= $sprint->id ?? 0 ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    <?= htmlspecialchars($sprint->name ?? 'Sprint') ?>
                                                </a>
                                            </div>
                                            <?php if (!empty($sprint->description)): ?>
                                                <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                                    <?= htmlspecialchars(substr($sprint->description, 0, 60)) . (strlen($sprint->description) > 60 ? '...' : '') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if (isset($sprint->start_date) && isset($sprint->end_date)): ?>
                                            <div class="text-sm text-gray-900 dark:text-gray-200">
                                                <?= date('M j', strtotime($sprint->start_date)) ?> - <?= date('M j, Y', strtotime($sprint->end_date)) ?>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                <?= $sprintDays ?> days
                                                <?php if ($daysLeft > 0): ?>
                                                    <span class='ml-2 font-medium text-blue-600 dark:text-blue-400'><?= $daysLeft ?> days left</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Date not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getSprintStatusClass($sprint->status_id ?? 0) ?>">
                                            <?= getSprintStatusLabel($sprint->status_id ?? 0) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-full max-w-[120px]">
                                                <div class="text-xs mb-1 flex justify-between">
                                                    <span class="font-medium text-gray-900 dark:text-gray-200"><?= $completedTasks ?>/<?= $totalTasks ?></span>
                                                    <span class="text-gray-500 dark:text-gray-400"><?= round($taskPercentage) ?>%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                                                    <div class="bg-green-500 h-1.5 rounded-full" style="width: <?= $taskPercentage ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if (isset($sprint->status_id) && $sprint->status_id == 4): // Completed ?>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                <?= isset($sprint->velocity_percentage) ? round($sprint->velocity_percentage) . '%' : 'N/A' ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end space-x-3">
                                            <a 
                                                href="/sprints/view/<?= $sprint->id ?? 0 ?>" 
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                                View
                                            </a>
                                            <a 
                                                href="/sprints/edit/<?= $sprint->id ?? 0 ?>" 
                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                            >
                                                Edit
                                            </a>
                                            <form 
                                                action="/sprints/delete/<?= $sprint->id ?? 0 ?>" 
                                                method="POST" 
                                                onsubmit="return confirm('Are you sure you want to delete this sprint?');"
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
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No sprints found for this project. <a href="/sprints/create/project/<?= $sprint->id ?? 0 ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Create your first sprint</a>.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (if needed) -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="mt-6 flex justify-between items-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Page <?= $page ?? 1 ?> of <?= $totalPages ?>
                    </div>
                    <div class="flex space-x-2">
                        <?php if (($page ?? 1) > 1): ?>
                            <a 
                                href="/sprints/project/<?= $project->id ?? 0 ?>&page=<?= ($page ?? 1) - 1 ?>" 
                                class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if (($page ?? 1) < $totalPages): ?>
                            <a 
                                href="/sprints/project/<?= $project->id ?? 0 ?>&page=<?= ($page ?? 1) + 1 ?>" 
                                class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <!-- Helper Functions -->
    <?php
    function getSprintStatusClass($statusId) {
        return match((int)$statusId) {
            1 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', // Planning
            2 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', // Active
            3 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300', // Delayed
            4 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300', // Completed
            5 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Cancelled
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        };
    }

    function getSprintStatusLabel($statusId) {
        return match((int)$statusId) {
            1 => 'Planning',
            2 => 'Active',
            3 => 'Delayed',
            4 => 'Completed',
            5 => 'Cancelled',
            default => 'Unknown'
        };
    }

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
    ?>

    <!-- JavaScript for Filtering -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('status-filter');
            const searchInput = document.getElementById('sprint-search');
            const sprintRows = document.querySelectorAll('.sprint-row');
            
            function filterSprints() {
                const statusValue = statusFilter ? statusFilter.value : 'all';
                const searchText = searchInput ? searchInput.value.toLowerCase() : '';
                
                sprintRows.forEach(row => {
                    let show = true;
                    
                    // Status filter
                    if (statusValue !== 'all' && row.dataset.status !== statusValue) {
                        show = false;
                    }
                    
                    // Search filter
                    if (searchText && !row.dataset.name.toLowerCase().includes(searchText)) {
                        show = false;
                    }
                    
                    row.style.display = show ? '' : 'none';
                });
                
                // Check if any visible rows
                const visibleRows = Array.from(sprintRows).filter(row => row.style.display !== 'none');
                const noResultsRow = document.getElementById('no-results-row');
                
                if (visibleRows.length === 0) {
                    if (!noResultsRow) {
                        const tbody = document.getElementById('sprints-list');
                        if (tbody) {
                            const newRow = document.createElement('tr');
                            newRow.id = 'no-results-row';
                            newRow.innerHTML = `
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No sprints match your filters. <a href="#" onclick="resetFilters(); return false;" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Reset filters</a>
                                </td>
                            `;
                            tbody.appendChild(newRow);
                        }
                    }
                } else if (noResultsRow) {
                    noResultsRow.remove();
                }
            }
            
            // Add event listeners
            if (statusFilter) {
                statusFilter.addEventListener('change', filterSprints);
            }
            
            if (searchInput) {
                searchInput.addEventListener('input', filterSprints);
            }
            
            // Function to reset filters (for the "Reset filters" link)
            window.resetFilters = function() {
                if (statusFilter) statusFilter.value = 'all';
                if (searchInput) searchInput.value = '';
                filterSprints();
            };
        });
    </script>
</body>
</html>