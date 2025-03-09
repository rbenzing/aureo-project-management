<?php
// file: Views/Sprints/view.php
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
    <title>Sprint Details - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
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

        <!-- Sprint Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center mb-4 lg:mb-0">
                    <div class="h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-4">
                        <svg class="h-7 w-7 text-indigo-500 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                <?= htmlspecialchars($sprint->name ?? 'Sprint') ?>
                            </h1>
                            <span class="ml-3 px-2.5 py-0.5 rounded-full text-xs font-medium <?= getSprintStatusClass($sprint->status_id ?? 1) ?>">
                                <?= getSprintStatusLabel($sprint->status_id ?? 1) ?>
                            </span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Project: <a href="/projects/view/<?= $project->id ?? 0 ?>" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                <?= htmlspecialchars($project->name ?? 'Project') ?>
                            </a>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php if (isset($sprint->status_id) && $sprint->status_id == 1): // Planning ?>
                        <form action="/sprints/start/<?= $sprint->id ?? 0 ?>" method="POST" class="inline" onsubmit="return confirm('Start this sprint? This will set it as the active sprint for the project.');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                Start Sprint
                            </button>
                        </form>
                    <?php elseif (isset($sprint->status_id) && $sprint->status_id == 2): // Active ?>
                        <form action="/sprints/complete/<?= $sprint->id ?? 0 ?>" method="POST" class="inline" onsubmit="return confirm('Complete this sprint? This will update the sprint status to completed.');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Complete Sprint
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="/sprints/edit/<?= $sprint->id ?? 0 ?>" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Edit Sprint
                    </a>
                    <a href="/sprints/project/<?= $project->id ?? 0 ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                        All Sprints
                    </a>
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                            More
                        </button>
                        <div 
                            x-show="open" 
                            @click.away="open = false"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                        >
                            <div class="py-1">
                                <?php if (isset($sprint->status_id) && $sprint->status_id !== 3): // Not delayed ?>
                                    <form action="/sprints/delay/<?= $sprint->id ?? 0 ?>" method="POST" class="block">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button 
                                            type="submit"
                                            class="w-full text-left px-4 py-2 text-sm text-yellow-600 dark:text-yellow-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            onclick="return confirm('Mark this sprint as delayed?');"
                                        >
                                            Mark as Delayed
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (isset($sprint->status_id) && $sprint->status_id !== 5): // Not cancelled ?>
                                    <form action="/sprints/cancel/<?= $sprint->id ?? 0 ?>" method="POST" class="block">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button 
                                            type="submit"
                                            class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            onclick="return confirm('Cancel this sprint? This cannot be undone.');"
                                        >
                                            Cancel Sprint
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form action="/sprints/delete/<?= $sprint->id ?? 0 ?>" method="POST" class="block">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button 
                                        type="submit" 
                                        class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        onclick="return confirm('Delete this sprint? This cannot be undone.');"
                                    >
                                        Delete Sprint
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sprint Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Sprint Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Sprint Details</h2>
                
                <div class="space-y-4">
                    <!-- Description -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h3>
                        <p class="mt-1 text-gray-900 dark:text-gray-100">
                            <?= !empty($sprint->description) ? nl2br(htmlspecialchars($sprint->description)) : 'No description provided' ?>
                        </p>
                    </div>
                    
                    <!-- Date Range -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Sprint Duration</h3>
                        <div class="mt-1 flex items-center">
                            <svg class="h-5 w-5 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <?php if (isset($sprint->start_date) && isset($sprint->end_date)): ?>
                                <span class="text-gray-900 dark:text-gray-100">
                                    <?= date('M j, Y', strtotime($sprint->start_date)) ?> - <?= date('M j, Y', strtotime($sprint->end_date)) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-500 dark:text-gray-400">Not specified</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php 
                        // Calculate days remaining if sprint is active
                        if (isset($sprint->status_id) && $sprint->status_id == 2 && isset($sprint->end_date)): 
                            $endDate = new DateTime($sprint->end_date);
                            $today = new DateTime();
                            $daysRemaining = $today <= $endDate ? $today->diff($endDate)->days : 0;
                            $isOverdue = $today > $endDate;
                        ?>
                            <div class="mt-1 text-sm <?= $isOverdue ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400' ?>">
                                <?= $isOverdue ? 'Overdue by ' . abs($daysRemaining) . ' days' : $daysRemaining . ' days remaining' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sprint Progress -->
                    <?php 
                    // Calculate sprint progress percentages
                    $daysElapsed = 0;
                    $totalDays = 0;
                    $timeProgress = 0;
                    
                    if (isset($sprint->start_date) && isset($sprint->end_date)) {
                        $startDate = new DateTime($sprint->start_date);
                        $endDate = new DateTime($sprint->end_date);
                        $today = new DateTime();
                        
                        $totalDays = $startDate->diff($endDate)->days + 1; // +1 to include start day
                        
                        if ($today < $startDate) {
                            // Sprint hasn't started yet
                            $daysElapsed = 0;
                        } elseif ($today > $endDate) {
                            // Sprint has ended
                            $daysElapsed = $totalDays;
                        } else {
                            // Sprint is in progress
                            $daysElapsed = $startDate->diff($today)->days + 1; // +1 to include today
                        }
                        
                        $timeProgress = $totalDays > 0 ? min(100, round(($daysElapsed / $totalDays) * 100)) : 0;
                    }
                    
                    // Calculate task completion percentages
                    $taskCompletion = 0;
                    $totalTasks = count($tasks ?? []);
                    $completedTasks = 0;
                    
                    if ($totalTasks > 0) {
                        foreach ($tasks ?? [] as $task) {
                            if (isset($task->status_id) && $task->status_id == 6) { // 6 = Completed status
                                $completedTasks++;
                            }
                        }
                        $taskCompletion = round(($completedTasks / $totalTasks) * 100);
                    }
                    ?>
                    
                    <!-- Time Progress -->
                    <div>
                        <div class="flex justify-between items-center">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Time Progress</h3>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= $timeProgress ?>%</span>
                        </div>
                        <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $timeProgress ?>%"></div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <?= $daysElapsed ?> of <?= $totalDays ?> days
                        </div>
                    </div>
                    
                    <!-- Task Progress -->
                    <div>
                        <div class="flex justify-between items-center">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Task Completion</h3>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= $taskCompletion ?>%</span>
                        </div>
                        <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-green-600 h-2.5 rounded-full" style="width: <?= $taskCompletion ?>%"></div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <?= $completedTasks ?> of <?= $totalTasks ?> tasks completed
                        </div>
                    </div>
                    
                    <!-- Status Indicator -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
                        <?php 
                        // Calculate sprint health
                        $statusIndicator = '';
                        $statusMessage = '';
                        
                        if (isset($sprint->status_id)) {
                            switch ($sprint->status_id) {
                                case 1: // Planning
                                    $statusIndicator = '<span class="text-blue-600 dark:text-blue-400">Planning</span>';
                                    $statusMessage = 'Sprint is in planning phase.';
                                    break;
                                case 2: // Active
                                    if ($timeProgress > $taskCompletion + 20) {
                                        $statusIndicator = '<span class="text-red-600 dark:text-red-400">Behind Schedule</span>';
                                        $statusMessage = 'Sprint tasks are falling behind schedule.';
                                    } elseif ($taskCompletion >= $timeProgress) {
                                        $statusIndicator = '<span class="text-green-600 dark:text-green-400">On Track</span>';
                                        $statusMessage = 'Sprint is on track or ahead of schedule.';
                                    } else {
                                        $statusIndicator = '<span class="text-yellow-600 dark:text-yellow-400">Slightly Behind</span>';
                                        $statusMessage = 'Sprint slightly behind, but recoverable.';
                                    }
                                    break;
                                case 3: // Delayed
                                    $statusIndicator = '<span class="text-yellow-600 dark:text-yellow-400">Delayed</span>';
                                    $statusMessage = 'Sprint has been marked as delayed.';
                                    break;
                                case 4: // Completed
                                    $statusIndicator = '<span class="text-green-600 dark:text-green-400">Completed</span>';
                                    $statusMessage = 'Sprint has been completed.';
                                    break;
                                case 5: // Cancelled
                                    $statusIndicator = '<span class="text-red-600 dark:text-red-400">Cancelled</span>';
                                    $statusMessage = 'Sprint has been cancelled.';
                                    break;
                                default:
                                    $statusIndicator = '<span class="text-gray-600 dark:text-gray-400">Unknown</span>';
                                    $statusMessage = 'Unknown sprint status.';
                            }
                        }
                        
                        echo '<div class="mt-1 text-gray-900 dark:text-gray-100">' . $statusIndicator . '</div>';
                        echo '<div class="mt-1 text-sm text-gray-500 dark:text-gray-400">' . $statusMessage . '</div>';
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Sprint Burndown Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:col-span-2">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Burndown Chart</h2>
                
                <?php if (!empty($tasks)): ?>
                    <!-- Chart Container -->
                    <div class="h-64" id="burndown-chart"></div>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center h-64 text-gray-400 dark:text-gray-500 text-center">
                        <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p>No tasks available for burndown chart.</p>
                        <p class="text-sm mt-2">Add tasks to this sprint to see the burndown chart.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sprint Tasks -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Sprint Tasks</h2>
                
                <?php if (isset($sprint->status_id) && in_array($sprint->status_id, [1, 2])): // Planning or Active ?>
                    <a href="/tasks/create?sprint_id=<?= $sprint->id ?? 0 ?>&project_id=<?= $project->id ?? 0 ?>" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Add Task
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($tasks)): ?>
                <!-- Tasks Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Task</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assignee</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Priority</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($tasks as $task): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="/tasks/view/<?= $task->id ?? 0 ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                            <?= htmlspecialchars($task->title ?? 'Task') ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getTaskStatusClass($task->status_name ?? 'Open') ?>">
                                            <?= htmlspecialchars($task->status_name ?? 'Open') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($task->first_name) && !empty($task->last_name)): ?>
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($task->due_date)): ?>
                                            <?php 
                                            $dueDate = new DateTime($task->due_date);
                                            $today = new DateTime();
                                            $isPastDue = $dueDate < $today && $task->status_id != 6; // 6 = Completed
                                            ?>
                                            <span class="<?= $isPastDue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-900 dark:text-gray-200' ?>">
                                                <?= date('M j, Y', strtotime($task->due_date)) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500 dark:text-gray-400">No due date</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="<?= getPriorityClass($task->priority ?? 'none') ?>">
                                            <?= ucfirst($task->priority ?? 'None') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="/tasks/view/<?= $task->id ?? 0 ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                            View
                                        </a>
                                        <a href="/tasks/edit/<?= $task->id ?? 0 ?>" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                    No tasks have been added to this sprint yet.
                    <?php if (isset($sprint->status_id) && in_array($sprint->status_id, [1, 2])): // Planning or Active ?>
                        <a href="/tasks/create?sprint_id=<?= $sprint->id ?? 0 ?>&project_id=<?= $project->id ?? 0 ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                            Add your first task
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <?php
// Helper functions for displaying status and priority classes
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
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    };
}

function getPriorityClass($priority) {
    return match($priority) {
        'high' => 'text-red-600 dark:text-red-400 font-medium',
        'medium' => 'text-yellow-600 dark:text-yellow-400',
        'low' => 'text-blue-600 dark:text-blue-400',
        default => 'text-gray-500 dark:text-gray-400'
    };
}
?>

<!-- Include Alpine.js for dropdown functionality -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<!-- Include Chart.js for burndown chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Create burndown chart if the container and tasks exist
        const burndownContainer = document.getElementById('burndown-chart');
        if (burndownContainer) {
            <?php if (isset($sprint->start_date) && isset($sprint->end_date) && !empty($tasks)): ?>
                // Prepare chart data
                const startDate = new Date('<?= $sprint->start_date ?>');
                const endDate = new Date('<?= $sprint->end_date ?>');
                const today = new Date();
                const totalTasks = <?= $totalTasks ?>;
                
                // Get all dates between start and end
                const dateLabels = [];
                const idealBurndown = [];
                
                // Create the date range and ideal burndown line
                let currentDate = new Date(startDate);
                const totalDays = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                const decrementPerDay = totalTasks / totalDays;
                
                let tasksRemaining = totalTasks;
                while (currentDate <= endDate) {
                    dateLabels.push(currentDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                    idealBurndown.push(tasksRemaining);
                    tasksRemaining = Math.max(0, tasksRemaining - decrementPerDay);
                    
                    // Move to next day
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                
                // Create actual burndown data based on task completion dates
                const actualBurndown = Array(dateLabels.length).fill(totalTasks);
                
                // If sprint has started, update actual burndown based on completed tasks
                if (today >= startDate) {
                    <?php
                    // Create JavaScript array of completed tasks with completion dates
                    echo "const completedTasks = [";
                    foreach ($tasks as $task) {
                        if (isset($task->status_id) && $task->status_id == 6 && isset($task->complete_date)) {
                            echo "{id: {$task->id}, date: new Date('{$task->complete_date}')},";
                        }
                    }
                    echo "];";
                    ?>
                    
                    // Sort completed tasks by completion date
                    completedTasks.sort((a, b) => a.date - b.date);
                    
                    // Update actual burndown line based on task completion
                    let remainingTasks = totalTasks;
                    let taskIndex = 0;
                    
                    for (let i = 0; i < dateLabels.length; i++) {
                        const currentDate = new Date(startDate);
                        currentDate.setDate(startDate.getDate() + i);
                        
                        // Count completed tasks up to this date
                        while (taskIndex < completedTasks.length && completedTasks[taskIndex].date <= currentDate) {
                            remainingTasks--;
                            taskIndex++;
                        }
                        
                        actualBurndown[i] = remainingTasks;
                        
                        // Don't predict beyond today
                        if (currentDate > today) {
                            break;
                        }
                    }
                }
                
                // Create the chart
                const ctx = burndownContainer.getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dateLabels,
                        datasets: [
                            {
                                label: 'Ideal Burndown',
                                data: idealBurndown,
                                borderColor: 'rgba(156, 163, 175, 0.7)',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                fill: false,
                                pointRadius: 0
                            },
                            {
                                label: 'Actual Burndown',
                                data: actualBurndown,
                                borderColor: 'rgba(79, 70, 229, 1)',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Tasks Remaining'
                                },
                                ticks: {
                                    stepSize: 1
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            },
                            legend: {
                                position: 'top'
                            }
                        }
                    }
                });
            <?php else: ?>
                // If no data available, display a message
                burndownContainer.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-64 text-gray-400 dark:text-gray-500 text-center">
                        <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p>Insufficient data for burndown chart.</p>
                        <p class="text-sm mt-2">Make sure start date, end date, and tasks are set.</p>
                    </div>
                `;
            <?php endif; ?>
        }
    });
</script>
</body>
</html>