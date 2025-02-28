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
    <title>Milestones - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
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

        <!-- Milestone Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
                <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3 mr-4">
                    <svg class="w-6 h-6 text-blue-500 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</div>
                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        <?php
                        $totalCount = count($milestones);
                        echo $totalCount;
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
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">In Progress</div>
                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        <?php 
                        $inProgressCount = 0;
                        if (!empty($milestones)) {
                            foreach ($milestones as $milestone) {
                                if ($milestone->status_id == 2) { // In Progress status
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
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">At Risk/Delayed</div>
                    <div class="text-xl font-semibold text-red-600 dark:text-red-400">
                        <?php 
                        $atRiskCount = 0;
                        if (!empty($milestones)) {
                            foreach ($milestones as $milestone) {
                                if ($milestone->status_id == 5 || // Delayed status
                                   ($milestone->status_id != 3 && !empty($milestone->due_date) && strtotime($milestone->due_date) < time())) {
                                    $atRiskCount++;
                                }
                            }
                        }
                        echo $atRiskCount;
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
                        if (!empty($milestones)) {
                            foreach ($milestones as $milestone) {
                                if ($milestone->status_id == 3) { // Completed status
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Milestones</h1>
            
            <div class="flex space-x-4">
                <!-- Filter Dropdown -->
                <div class="relative">
                    <select id="milestone-filter" class="appearance-none w-40 px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all">All Milestones</option>
                        <option value="epic">Epics Only</option>
                        <option value="milestone">Regular Milestones</option>
                        <option value="overdue">Overdue</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="on-hold">On Hold</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>

                <!-- Project Filter (If applicable) -->
                <?php if (!empty($projects)): ?>
                <div class="relative">
                    <select id="project-filter" class="appearance-none w-48 px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Projects</option>
                        <?php foreach ($projects as $project): ?>
                        <option value="<?= $project->id ?>"><?= htmlspecialchars($project->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Search -->
                <div class="relative">
                    <input 
                        type="search" 
                        id="milestone-search"
                        placeholder="Search milestones..." 
                        class="w-64 px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- New Milestone Button -->
                <a 
                    href="/milestones/create" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    + New Milestone
                </a>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px">
                <li class="mr-2">
                    <button id="table-view-btn" class="inline-block py-2 px-4 text-sm font-medium text-center text-indigo-600 border-b-2 border-indigo-600 active">
                        <svg class="w-5 h-5 mr-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Table View
                    </button>
                </li>
                <li class="mr-2">
                    <button id="timeline-view-btn" class="inline-block py-2 px-4 text-sm font-medium text-center text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                        <svg class="w-5 h-5 mr-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Timeline View
                    </button>
                </li>
                <li class="mr-2">
                    <button id="card-view-btn" class="inline-block py-2 px-4 text-sm font-medium text-center text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                        <svg class="w-5 h-5 mr-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        Card View
                    </button>
                </li>
            </ul>
        </div>

        <!-- Table View -->
        <div id="table-view" class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700" id="milestone-table-body">
                    <?php if (!empty($milestones)): ?>
                        <?php foreach ($milestones as $milestone): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition milestone-row" 
                                data-status="<?= $milestone->status_id ?>" 
                                data-project="<?= $milestone->project_id ?>"
                                data-type="<?= $milestone->milestone_type ?? 'milestone' ?>"
                                data-title="<?= htmlspecialchars($milestone->title) ?>"
                                data-overdue="<?= (!empty($milestone->due_date) && strtotime($milestone->due_date) < time() && $milestone->status_id != 3) ? '1' : '0' ?>">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if (isset($milestone->milestone_type) && $milestone->milestone_type === 'epic'): ?>
                                            <div class="flex-shrink-0 h-8 w-8 bg-purple-100 dark:bg-purple-900 rounded-md flex items-center justify-center mr-3">
                                                <svg class="h-5 w-5 text-purple-500 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                            </div>
                                        <?php elseif (isset($milestone->epic_id) && $milestone->epic_id): ?>
                                            <div class="ml-2 mr-3">↳</div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                                <?= htmlspecialchars($milestone->title) ?>
                                            </div>
                                            <?php if (!empty($milestone->description)): ?>
                                                <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                                    <?= htmlspecialchars(substr($milestone->description, 0, 60)) . (strlen($milestone->description) > 60 ? '...' : '') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= isset($milestone->milestone_type) && $milestone->milestone_type === 'epic' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' ?>">
                                        <?= isset($milestone->milestone_type) && $milestone->milestone_type === 'epic' ? 'Epic' : 'Milestone' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-200">
                                        <?= htmlspecialchars($milestone->project_name ?? 'Unassigned') ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getStatusClasses($milestone->status_id) ?>">
                                        <?= htmlspecialchars($milestone->status_name ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                                        <div 
                                            class="<?= getProgressBarColor($milestone->completion_rate, $milestone->due_date, $milestone->status_id) ?> h-2.5 rounded-full" 
                                            style="width: <?= $milestone->completion_rate ?? 0 ?>%"
                                        ></div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        <?= isset($milestone->completion_rate) ? number_format((float)$milestone->completion_rate, 1) : '0' ?>%
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($milestone->due_date)): ?>
                                        <div class="text-sm <?= isDueDateOverdue($milestone->due_date, $milestone->status_id) ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-900 dark:text-gray-200' ?>">
                                            <?= date('M j, Y', strtotime($milestone->due_date)) ?>
                                            <?php $daysLeft = calculateDaysLeft($milestone->due_date); ?>
                                            <?php if ($daysLeft !== null): ?>
                                                <?php if ($daysLeft < 0 && $milestone->status_id != 3): ?>
                                                    <span class="ml-2 text-xs bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 px-2 py-0.5 rounded">
                                                        <?= abs($daysLeft) ?> days overdue
                                                    </span>
                                                <?php elseif ($daysLeft === 0): ?>
                                                    <span class="ml-2 text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 px-2 py-0.5 rounded">Today</span>
                                                <?php elseif ($daysLeft <= 3): ?>
                                                    <span class="ml-2 text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 px-2 py-0.5 rounded">
                                                        <?= $daysLeft ?> days left
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">No Due Date</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end space-x-3">
                                        <a 
                                            href="/milestones/view/<?= $milestone->id ?>" 
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            View
                                        </a>
                                        <a 
                                            href="/milestones/edit/<?= $milestone->id ?>" 
                                            class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                        >
                                            Edit
                                        </a>
                                        <form 
                                            action="/milestones/delete/<?= $milestone->id ?>" 
                                            method="POST" 
                                            onsubmit="return confirm('Are you sure you want to delete this milestone?');"
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
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No milestones found. <a href="/milestones/create" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Create your first milestone</a>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Timeline View (Hidden by default) -->
        <div id="timeline-view" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 hidden">
            <div class="relative">
                <!-- Timeline Header -->
                <div class="flex mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                    <div class="w-1/4 font-medium text-gray-500 dark:text-gray-400">Milestone</div>
                    <div class="w-3/4 flex">
                        <?php 
                        // Generate months for timeline (3 months in past, 6 months in future)
                        $startDate = strtotime('-3 months');
                        $monthsToShow = 9;
                        
                        for ($i = 0; $i < $monthsToShow; $i++) {
                            $monthTime = strtotime("+{$i} months", $startDate);
                            echo '<div class="flex-1 text-center text-xs font-medium text-gray-500 dark:text-gray-400">';
                            echo date('M Y', $monthTime);
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <?php if (!empty($milestones)): ?>
                    <?php foreach ($milestones as $milestone): ?>
                        <?php
                        // Skip if no dates
                        if (empty($milestone->start_date) && empty($milestone->due_date)) continue;
                        
                        // For positioning in timeline
                        $startTimestamp = !empty($milestone->start_date) ? strtotime($milestone->start_date) : null;
                        $endTimestamp = !empty($milestone->due_date) ? strtotime($milestone->due_date) : null;
                        
                        // Default to current date if no start date
                        if (!$startTimestamp && $endTimestamp) {
                            $startTimestamp = $endTimestamp;
                        }
                        
                        // Default to start date if no end date
                        if ($startTimestamp && !$endTimestamp) {
                            $endTimestamp = $startTimestamp;
                        }
                        
                        // Skip if still no dates
                        if (!$startTimestamp || !$endTimestamp) continue;
                        
                        // Calculate position and width
                        $timelineStart = strtotime('-3 months');
                        $timelineEnd = strtotime('+6 months');
                        $timelineWidth = $timelineEnd - $timelineStart;
                        
                        $startPos = max(0, ($startTimestamp - $timelineStart) / $timelineWidth * 100);
                        $endPos = min(100, ($endTimestamp - $timelineStart) / $timelineWidth * 100);
                        $width = max(5, $endPos - $startPos); // Minimum 5% width for visibility
                        
                        // Determine color based on status
                        $bgColor = match($milestone->status_id) {
                            1 => 'bg-gray-400 dark:bg-gray-600', // Not Started
                            2 => 'bg-blue-400 dark:bg-blue-600', // In Progress
                            3 => 'bg-green-400 dark:bg-green-600', // Completed
                            4 => 'bg-yellow-400 dark:bg-yellow-600', // On Hold
                            5 => 'bg-red-400 dark:bg-red-600', // Delayed
                            default => 'bg-gray-400 dark:bg-gray-600'
                        };
                        ?>
                        <div class="flex items-center py-3 border-b border-gray-200 dark:border-gray-700 milestone-timeline-row"
                             data-status="<?= $milestone->status_id ?>" 
                             data-project="<?= $milestone->project_id ?>"
                             data-type="<?= $milestone->milestone_type ?? 'milestone' ?>"
                             data-title="<?= htmlspecialchars($milestone->title) ?>"
                             data-overdue="<?= (!empty($milestone->due_date) && strtotime($milestone->due_date) < time() && $milestone->status_id != 3) ? '1' : '0' ?>">
                            <div class="w-1/4 pr-4">
                                <div class="flex items-center">
                                    <?php if (isset($milestone->milestone_type) && $milestone->milestone_type === 'epic'): ?>
                                        <div class="h-6 w-6 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-2">
                                            <svg class="h-4 w-4 text-purple-500 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                            <?= htmlspecialchars($milestone->title) ?>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <?= htmlspecialchars($milestone->project_name ?? 'Unassigned') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-3/4 relative">
                                <div class="absolute h-6 rounded-md <?= $bgColor ?>" 
                                     style="left: <?= $startPos ?>%; width: <?= $width ?>%;">
                                    <div class="h-full flex items-center justify-center text-xs text-white font-medium overflow-hidden px-2">
                                        <?php if ($width > 10): ?>
                                            <?= ($milestone->completion_rate ?? 0) ?>%
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="py-4 text-center text-gray-500 dark:text-gray-400">
                        No milestones found with date information.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card View (Hidden by default) -->
        <div id="card-view" class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (!empty($milestones)): ?>
                    <?php foreach ($milestones as $milestone): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden milestone-card"
                             data-status="<?= $milestone->status_id ?>" 
                             data-project="<?= $milestone->project_id ?>"
                             data-type="<?= $milestone->milestone_type ?? 'milestone' ?>"
                             data-title="<?= htmlspecialchars($milestone->title) ?>"
                             data-overdue="<?= (!empty($milestone->due_date) && strtotime($milestone->due_date) < time() && $milestone->status_id != 3) ? '1' : '0' ?>">
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center">
                                        <?php if (isset($milestone->milestone_type) && $milestone->milestone_type === 'epic'): ?>
                                            <div class="h-8 w-8 bg-purple-100 dark:bg-purple-900 rounded-md flex items-center justify-center mr-3">
                                                <svg class="h-5 w-5 text-purple-500 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            <?= htmlspecialchars($milestone->title) ?>
                                        </h3>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getStatusClasses($milestone->status_id) ?>">
                                        <?= htmlspecialchars($milestone->status_name ?? 'Unknown') ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($milestone->description)): ?>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        <?= htmlspecialchars(substr($milestone->description, 0, 100)) . (strlen($milestone->description) > 100 ? '...' : '') ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="mb-4">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Progress</div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                                        <div 
                                            class="<?= getProgressBarColor($milestone->completion_rate, $milestone->due_date, $milestone->status_id) ?> h-2.5 rounded-full" 
                                            style="width: <?= $milestone->completion_rate ?? 0 ?>%"
                                        ></div>
                                    </div>
                                    <div class="text-xs text-right text-gray-500 dark:text-gray-400 mt-1">
                                        <?= isset($milestone->completion_rate) ? number_format((float)$milestone->completion_rate, 1) : '0' ?>%
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Project</div>
                                        <div class="text-sm text-gray-900 dark:text-gray-200">
                                            <?= htmlspecialchars($milestone->project_name ?? 'Unassigned') ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Type</div>
                                        <div class="text-sm text-gray-900 dark:text-gray-200">
                                            <?= isset($milestone->milestone_type) && $milestone->milestone_type === 'epic' ? 'Epic' : 'Milestone' ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <?php if (!empty($milestone->start_date)): ?>
                                    <div>
                                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Start Date</div>
                                        <div class="text-sm text-gray-900 dark:text-gray-200">
                                            <?= date('M j, Y', strtotime($milestone->start_date)) ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($milestone->due_date)): ?>
                                    <div>
                                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Due Date</div>
                                        <div class="text-sm <?= isDueDateOverdue($milestone->due_date, $milestone->status_id) ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-900 dark:text-gray-200' ?>">
                                            <?= date('M j, Y', strtotime($milestone->due_date)) ?>
                                            <?php $daysLeft = calculateDaysLeft($milestone->due_date); ?>
                                            <?php if ($daysLeft !== null && $daysLeft < 0 && $milestone->status_id != 3): ?>
                                                <span class="text-xs bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 px-2 py-0.5 rounded ml-1">
                                                    <?= abs($daysLeft) ?> days overdue
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-end space-x-3">
                                <a 
                                    href="/milestones/view/<?= $milestone->id ?>" 
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                >
                                    View
                                </a>
                                <a 
                                    href="/milestones/edit/<?= $milestone->id ?>" 
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                >
                                    Edit
                                </a>
                                <form 
                                    action="/milestones/delete/<?= $milestone->id ?>" 
                                    method="POST" 
                                    onsubmit="return confirm('Are you sure you want to delete this milestone?');"
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
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center text-gray-500 dark:text-gray-400 py-8">
                        No milestones found. <a href="/milestones/create" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Create your first milestone</a>.
                    </div>
                <?php endif; ?>
            </div>
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
                            href="/milestones/page/<?= $page - 1 ?>" 
                            class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a 
                            href="/milestones/page/<?= $page + 1 ?>" 
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
    function getStatusClasses($statusId) {
        $classes = [
            1 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Not Started
            2 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300', // In Progress
            3 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', // Completed
            4 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', // On Hold
            5 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300', // Delayed
        ];
        return $classes[$statusId] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }

    function getProgressBarColor($completion, $dueDate, $statusId) {
        // If completed, always show green
        if ($statusId == 3) {
            return 'bg-green-500 dark:bg-green-600';
        }
        
        // If delayed status, show red
        if ($statusId == 5) {
            return 'bg-red-500 dark:bg-red-600';
        }
        
        // If overdue and not close to completion, show red
        if (!empty($dueDate) && strtotime($dueDate) < time() && ($completion ?? 0) < 80) {
            return 'bg-red-500 dark:bg-red-600';
        }
        
        // If progress < 25%, show gray
        if (($completion ?? 0) < 25) {
            return 'bg-gray-500 dark:bg-gray-600';
        }
        
        // If progress 25-50%, show orange
        if (($completion ?? 0) < 50) {
            return 'bg-orange-500 dark:bg-orange-600';
        }
        
        // If progress 50-80%, show blue
        if (($completion ?? 0) < 80) {
            return 'bg-blue-500 dark:bg-blue-600';
        }
        
        // If progress > 80%, show green
        return 'bg-green-500 dark:bg-green-600';
    }

    function isDueDateOverdue($dueDate, $statusId) {
        return !empty($dueDate) && 
               strtotime($dueDate) < time() && 
               $statusId != 3; // Not completed
    }

    function calculateDaysLeft($dueDate) {
        if (empty($dueDate)) {
            return null;
        }
        
        $dueTimestamp = strtotime($dueDate);
        $now = time();
        $diff = $dueTimestamp - $now;
        
        return floor($diff / (60 * 60 * 24));
    }
    ?>

    <!-- JavaScript for View Toggle and Filtering -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View toggle
            const tableViewBtn = document.getElementById('table-view-btn');
            const timelineViewBtn = document.getElementById('timeline-view-btn');
            const cardViewBtn = document.getElementById('card-view-btn');
            const tableView = document.getElementById('table-view');
            const timelineView = document.getElementById('timeline-view');
            const cardView = document.getElementById('card-view');
            
            tableViewBtn.addEventListener('click', function() {
                tableView.classList.remove('hidden');
                timelineView.classList.add('hidden');
                cardView.classList.add('hidden');
                
                tableViewBtn.classList.add('text-indigo-600', 'border-indigo-600');
                tableViewBtn.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
                
                timelineViewBtn.classList.remove('text-indigo-600', 'border-indigo-600');
                timelineViewBtn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
                
                cardViewBtn.classList.remove('text-indigo-600', 'border-indigo-600');
                cardViewBtn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
            });
            
            timelineViewBtn.addEventListener('click', function() {
                tableView.classList.add('hidden');
                timelineView.classList.remove('hidden');
                cardView.classList.add('hidden');
                
                tableViewBtn.classList.remove('text-indigo-600', 'border-indigo-600');
                tableViewBtn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
                
                timelineViewBtn.classList.add('text-indigo-600', 'border-indigo-600');
                timelineViewBtn.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
                
                cardViewBtn.classList.remove('text-indigo-600', 'border-indigo-600');
                cardViewBtn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
            });
            
            cardViewBtn.addEventListener('click', function() {
                tableView.classList.add('hidden');
                timelineView.classList.add('hidden');
                cardView.classList.remove('hidden');
                
                tableViewBtn.classList.remove('text-indigo-600', 'border-indigo-600');
                tableViewBtn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
                
                timelineViewBtn.classList.remove('text-indigo-600', 'border-indigo-600');
                timelineViewBtn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
                
                cardViewBtn.classList.add('text-indigo-600', 'border-indigo-600');
                cardViewBtn.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
            });
            
            // Filtering
            const milestoneFilter = document.getElementById('milestone-filter');
            const projectFilter = document.getElementById('project-filter');
            const searchInput = document.getElementById('milestone-search');
            
            // Helper function to update visibility
            function updateVisibility() {
                const filter = milestoneFilter ? milestoneFilter.value : 'all';
                const projectId = projectFilter ? projectFilter.value : '';
                const searchText = searchInput ? searchInput.value.toLowerCase() : '';
                
                // Table rows
                const tableRows = document.querySelectorAll('.milestone-row');
                tableRows.forEach(row => {
                    let shouldShow = true;
                    
                    // Status filter
                    if (filter === 'epic' && row.dataset.type !== 'epic') {
                        shouldShow = false;
                    } else if (filter === 'milestone' && row.dataset.type === 'epic') {
                        shouldShow = false;
                    } else if (filter === 'overdue' && row.dataset.overdue !== '1') {
                        shouldShow = false;
                    } else if (filter === 'in-progress' && row.dataset.status !== '2') {
                        shouldShow = false;
                    } else if (filter === 'completed' && row.dataset.status !== '3') {
                        shouldShow = false;
                    } else if (filter === 'on-hold' && row.dataset.status !== '4') {
                        shouldShow = false;
                    }
                    
                    // Project filter
                    if (projectId && row.dataset.project !== projectId) {
                        shouldShow = false;
                    }
                    
                    // Search filter
                    if (searchText && !row.dataset.title.toLowerCase().includes(searchText)) {
                        shouldShow = false;
                    }
                    
                    row.style.display = shouldShow ? '' : 'none';
                });
                
                // Timeline rows
                const timelineRows = document.querySelectorAll('.milestone-timeline-row');
                timelineRows.forEach(row => {
                    let shouldShow = true;
                    
                    // Status filter
                    if (filter === 'epic' && row.dataset.type !== 'epic') {
                        shouldShow = false;
                    } else if (filter === 'milestone' && row.dataset.type === 'epic') {
                        shouldShow = false;
                    } else if (filter === 'overdue' && row.dataset.overdue !== '1') {
                        shouldShow = false;
                    } else if (filter === 'in-progress' && row.dataset.status !== '2') {
                        shouldShow = false;
                    } else if (filter === 'completed' && row.dataset.status !== '3') {
                        shouldShow = false;
                    } else if (filter === 'on-hold' && row.dataset.status !== '4') {
                        shouldShow = false;
                    }
                    
                    // Project filter
                    if (projectId && row.dataset.project !== projectId) {
                        shouldShow = false;
                    }
                    
                    // Search filter
                    if (searchText && !row.dataset.title.toLowerCase().includes(searchText)) {
                        shouldShow = false;
                    }
                    
                    row.style.display = shouldShow ? '' : 'none';
                });
                
                // Card view
                const cards = document.querySelectorAll('.milestone-card');
                cards.forEach(card => {
                    let shouldShow = true;
                    
                    // Status filter
                    if (filter === 'epic' && card.dataset.type !== 'epic') {
                        shouldShow = false;
                    } else if (filter === 'milestone' && card.dataset.type === 'epic') {
                        shouldShow = false;
                    } else if (filter === 'overdue' && card.dataset.overdue !== '1') {
                        shouldShow = false;
                    } else if (filter === 'in-progress' && card.dataset.status !== '2') {
                        shouldShow = false;
                    } else if (filter === 'completed' && card.dataset.status !== '3') {
                        shouldShow = false;
                    } else if (filter === 'on-hold' && card.dataset.status !== '4') {
                        shouldShow = false;
                    }
                    
                    // Project filter
                    if (projectId && card.dataset.project !== projectId) {
                        shouldShow = false;
                    }
                    
                    // Search filter
                    if (searchText && !card.dataset.title.toLowerCase().includes(searchText)) {
                        shouldShow = false;
                    }
                    
                    card.style.display = shouldShow ? '' : 'none';
                });
            }
            
            // Add event listeners for filters
            if (milestoneFilter) {
                milestoneFilter.addEventListener('change', updateVisibility);
            }
            
            if (projectFilter) {
                projectFilter.addEventListener('change', updateVisibility);
            }
            
            if (searchInput) {
                searchInput.addEventListener('input', updateVisibility);
            }
        });
    </script>
</body>
</html>