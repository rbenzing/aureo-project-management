<?php
//file: Views/Tasks/view.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
use App\Utils\Time;

// Helper functions for formatting and styling
function formatTimeTracking($seconds) {
    if ($seconds === null || $seconds == 0) {
        return '0h 0m';
    }

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    return "{$hours}h {$minutes}m";
}

function formatDate($date) {
    if (!$date) return 'Not set';
    return date('M j, Y', strtotime($date));
}

function getPriorityClass($priority) {
    switch (strtolower($priority)) {
        case 'high':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        case 'medium':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'low':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
    }
}

function getStatusClass($statusId) {
    $statusMap = [
        1 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', // Open
        2 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', // In Progress
        3 => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200', // On Hold
        4 => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200', // In Review
        5 => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200', // Closed
        6 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', // Completed
        7 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', // Cancelled
    ];
    
    return $statusMap[$statusId] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
}

// Calculate progress
$totalSubtasks = !empty($subtasks) ? count($subtasks) : 0;
$completedSubtasks = 0;

if ($totalSubtasks > 0) {
    foreach ($subtasks as $subtask) {
        if ($subtask->status_id == 6) { // Completed status is 6
            $completedSubtasks++;
        }
    }
}
$progress = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;

// Determine if time tracking is currently active
$isTimerActive = isset($activeTimer) && $activeTimer['task_id'] == $task->id;
$timerDuration = $isTimerActive ? time() - strtotime($activeTimer['start_time']) : 0;
$formattedDuration = gmdate('H:i:s', $timerDuration);

$pageTitle = htmlspecialchars($task->title) . ' - Task Details';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= htmlspecialchars(Config::get('company_name', 'Slimbooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <!-- Task Header -->
        <div class="flex justify-between items-start mb-6">
            <div>
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mr-3"><?= htmlspecialchars($task->title) ?></h1>
                    <span class="px-3 py-1 text-sm rounded-full <?= getStatusClass($task->status_id) ?>">
                        <?= htmlspecialchars($task->status_name) ?>
                    </span>
                    <span class="ml-2 px-3 py-1 text-sm rounded-full <?= getPriorityClass($task->priority) ?>">
                        <?= ucfirst(htmlspecialchars($task->priority)) ?> Priority
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    <?php if (isset($project->name)): ?>
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <a href="/projects/view/<?= $project->id ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                <?= htmlspecialchars($project->name) ?>
                            </a>
                        </span>
                        •
                    <?php endif; ?>
                    Created <?= date('M j, Y', strtotime($task->created_at)) ?>
                </p>
            </div>
            <div class="flex space-x-3">
                <?php if ($isTimerActive): ?>
                    <form method="POST" action="/tasks/stop-timer/<?= $activeTimer['id'] ?>" class="inline-flex">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span id="timer"><?= $formattedDuration ?></span>
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="/tasks/start-timer/<?= $task->id ?>" class="inline-flex">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Start Timer
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user']['permissions']) && in_array('edit_tasks', $_SESSION['user']['permissions'])): ?>
                    <a href="/tasks/edit/<?= $task->id ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Edit Task
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Task Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Task Overview & Details Column -->
            <div class="md:col-span-1">
                <!-- Task Details Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Task Details</h3>
                        <button type="button" class="section-toggle" data-target="task-details">
                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div id="task-details" class="p-4 space-y-3 text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="text-gray-500 dark:text-gray-400 font-medium">Assignee:</div>
                            <div class="text-gray-900 dark:text-white">
                                <?= !empty($task->first_name) ? htmlspecialchars($task->first_name . ' ' . $task->last_name) : 'Unassigned' ?>
                            </div>
                            
                            <div class="text-gray-500 dark:text-gray-400 font-medium">Status:</div>
                            <div class="text-gray-900 dark:text-white">
                                <?= htmlspecialchars($task->status_name) ?>
                            </div>
                            
                            <div class="text-gray-500 dark:text-gray-400 font-medium">Priority:</div>
                            <div class="text-gray-900 dark:text-white">
                                <?= ucfirst(htmlspecialchars($task->priority)) ?>
                            </div>
                            
                            <div class="text-gray-500 dark:text-gray-400 font-medium">Start Date:</div>
                            <div class="text-gray-900 dark:text-white">
                                <?= isset($task->start_date) && !empty($task->start_date) ? formatDate($task->start_date) : 'Not set' ?>
                            </div>
                            
                            <div class="text-gray-500 dark:text-gray-400 font-medium">Due Date:</div>
                            <div class="text-gray-900 dark:text-white <?= (!empty($task->due_date) && strtotime($task->due_date) < time() && $task->status_id != 6) ? 'text-red-600 dark:text-red-400' : '' ?>">
                                <?= isset($task->due_date) && !empty($task->due_date) ? formatDate($task->due_date) : 'Not set' ?>
                            </div>
                            <?php if (!empty($task->complete_date)): ?>
                            <div class="text-gray-500 dark:text-gray-400 font-medium">Completed:</div>
                            <div class="text-gray-900 dark:text-white">
                                <?= formatDate($task->complete_date) ?>
                            </div>
                            <?php endif; ?>

                            <div class="text-gray-500 dark:text-gray-400 font-medium">Estimated:</div>
                            <div class="text-gray-900 dark:text-white">
                                <?= formatTimeTracking($task->estimated_time) ?>
                            </div>
                            
                            <div class="text-gray-500 dark:text-gray-400 font-medium">Time spent:</div>
                            <div class="text-gray-900 dark:text-white">
                                <?= formatTimeTracking($task->time_spent) ?>
                            </div>
                            
                            <?php if ($task->is_hourly): ?>
                            <div class="text-gray-500 dark:text-gray-400 font-medium">Billable:</div>
                            <div class="text-gray-900 dark:text-white">
                                <?= formatTimeTracking($task->billable_time) ?>
                            </div>
                            
                            <div class="text-gray-500 dark:text-gray-400 font-medium">Hourly rate:</div>
                            <div class="text-gray-900 dark:text-white">
                                $<?= number_format($task->hourly_rate ?? 0, 2) ?>
                            </div>
                            
                            <div class="text-gray-500 dark:text-gray-400 font-medium">Billable amount:</div>
                            <div class="text-green-600 dark:text-green-400 font-medium">
                                <?php
                                $billableSeconds = $task->billable_time ?? 0;
                                $billableHours = $billableSeconds / 3600;
                                $billableAmount = $billableHours * ($task->hourly_rate ?? 0);
                                echo '$' . number_format($billableAmount, 2);
                                ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($task->estimated_time) && !empty($task->time_spent)): ?>
                        <!-- Progress Bar -->
                        <div class="mt-2">
                            <?php 
                            $timeProgress = min(100, ($task->time_spent / $task->estimated_time) * 100);
                            $progressColor = $timeProgress > 100 ? 'bg-red-600' : 'bg-blue-600';
                            ?>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500 dark:text-gray-400">Progress</span>
                                <span class="text-gray-500 dark:text-gray-400"><?= round($timeProgress) ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="<?= $progressColor ?> h-2 rounded-full" style="width: <?= $timeProgress ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Parent Task Card (if this is a subtask) -->
                <?php if ($task->is_subtask && $task->parent_task_id): ?>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Parent Task</h3>
                    </div>
                    <div class="p-4">
                        <a href="/tasks/view/<?= $task->parent_task_id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            View Parent Task
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Milestones Card (if task is associated with milestones) -->
                <?php if (!empty($task->milestones)): ?>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Milestones</h3>
                        <button type="button" class="section-toggle" data-target="task-milestones">
                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div id="task-milestones" class="p-4">
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($task->milestones as $milestone): ?>
                                <?php
                                $milestoneStatusClass = '';
                                switch ($milestone->status_id) {
                                    case 1: $milestoneStatusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'; break;
                                    case 2: $milestoneStatusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; break;
                                    case 3: $milestoneStatusClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                    default: $milestoneStatusClass = 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                }
                                ?>
                                <li class="py-3">
                                    <a href="/milestones/view/<?= $milestone->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
                                        <?= htmlspecialchars($milestone->title) ?>
                                    </a>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            <?= !empty($milestone->due_date) ? formatDate($milestone->due_date) : 'No due date' ?>
                                        </span>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $milestoneStatusClass ?>">
                                            <?= htmlspecialchars($milestone->status_name) ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Task Description Column -->
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Description</h3>
                        <button type="button" class="section-toggle" data-target="task-description">
                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div id="task-description" class="p-4">
                        <?php if (!empty($task->description)): ?>
                            <div class="prose dark:prose-invert max-w-none">
                                <?= nl2br(htmlspecialchars($task->description)) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 dark:text-gray-400 italic">No description provided</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Task History -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Activity History</h3>
                        <button type="button" class="section-toggle" data-target="task-history">
                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div id="task-history" class="p-4 max-h-96 overflow-y-auto">
                        <?php if (!empty($task->history)): ?>
                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($task->history as $historyItem): ?>
                                    <li class="py-3">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                                <span class="text-xs font-medium text-white">
                                                    <?= htmlspecialchars(substr($historyItem->first_name ?? 'U', 0, 1) . substr($historyItem->last_name ?? 'U', 0, 1)) ?>
                                                </span>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    <span class="font-medium">
                                                        <?= htmlspecialchars(($historyItem->first_name ?? '') . ' ' . ($historyItem->last_name ?? 'Unknown User')) ?>
                                                    </span>
                                                    <span class="ml-1">
                                                        <?= htmlspecialchars($historyItem->action) ?>
                                                    </span>
                                                    <?php if ($historyItem->field_changed): ?>
                                                        <span class="ml-1">
                                                            changed <strong><?= htmlspecialchars($historyItem->field_changed) ?></strong>
                                                            from "<?= htmlspecialchars($historyItem->old_value ?? '(empty)') ?>"
                                                            to "<?= htmlspecialchars($historyItem->new_value ?? '(empty)') ?>"
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    <?= date('M j, Y g:i A', strtotime($historyItem->created_at)) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-gray-500 dark:text-gray-400 italic">No activity history available</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Task Comments -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Comments</h3>
                    </div>
                    <div class="p-4 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (!empty($task->comments)): ?>
                            <div class="mb-4 max-h-96 overflow-y-auto">
                                <?php foreach ($task->comments as $comment): ?>
                                    <div class="mb-4">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-semibold text-xs">
                                                <?= substr($comment->first_name ?? 'U', 0, 1) . substr($comment->last_name ?? 'U', 0, 1) ?>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    <?= htmlspecialchars(($comment->first_name ?? '') . ' ' . ($comment->last_name ?? '')) ?>
                                                </div>
                                                <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                                    <?= nl2br(htmlspecialchars($comment->content)) ?>
                                                </div>
                                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                    <?= date('M j, Y g:i A', strtotime($comment->created_at)) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 dark:text-gray-400 italic mb-4">No comments yet</p>
                        <?php endif; ?>
                        
                        <!-- Comment Form -->
                        <div class="pt-4">
                            <form action="/tasks/add-comment/<?= $task->id ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <div>
                                    <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Add a comment</label>
                                    <textarea id="content" name="content" rows="3" class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </textarea>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Post Comment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Subtasks Column -->
            <div class="md:col-span-1">
                <!-- Subtasks List Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Subtasks</h3>
                        <div class="flex items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400 mr-2"><?= $completedSubtasks ?>/<span class="font-medium"><?= $totalSubtasks ?></span></span>
                            <button type="button" class="section-toggle" data-target="task-subtasks">
                                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <?php if ($totalSubtasks > 0): ?>
                    <!-- Progress Bar -->
                    <div class="px-4 pt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $progress ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div id="task-subtasks" class="p-4 max-h-96 overflow-y-auto">
                        <?php if ($totalSubtasks > 0): ?>
                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($subtasks as $subtask): ?>
                                    <li class="py-3">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <?php if ($subtask->status_id == 6): // Completed ?>
                                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                <?php else: ?>
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ml-3 flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                        <a href="/tasks/view/<?= $subtask->id ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                                            <?= htmlspecialchars($subtask->title) ?>
                                                        </a>
                                                    </p>
                                                    <span class="ml-2 flex-shrink-0 text-xs px-2 py-0.5 rounded-full <?= getStatusClass($subtask->status_id) ?>">
                                                        <?= htmlspecialchars($subtask->status_name) ?>
                                                    </span>
                                                </div>
                                                <?php if (!empty($subtask->estimated_time)): ?>
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <?= formatTimeTracking($subtask->estimated_time) ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($subtask->description)): ?>
                                        <div class="mt-2 ml-8 text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                            <?= nl2br(htmlspecialchars($subtask->description)) ?>
                                        </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-6">
                                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No subtasks yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Add Subtask Form Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">Add Subtask</h3>
                    </div>
                    <div class="p-4">
                        <form method="POST" action="/tasks/create" class="space-y-4">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="parent_task_id" value="<?= $task->id ?>">
                            <input type="hidden" name="project_id" value="<?= $task->project_id ?>">
                            <input type="hidden" name="is_subtask" value="1">

                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title <span class="text-red-500">*</span></label>
                                <input type="text" id="title" name="title" required
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <!-- Row with Priority and Status -->
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Priority -->
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                                    <select id="priority" name="priority"
                                        class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="none">None</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <select id="status_id" name="status_id" required
                                        class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="1">Open</option>
                                        <option value="2">In Progress</option>
                                        <option value="3">On Hold</option>
                                        <option value="6">Completed</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea id="description" name="description" rows="3"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            </div>

                            <!-- Estimated Time -->
                            <div>
                                <label for="estimated_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Time (minutes)</label>
                                <input type="number" id="estimated_time" name="estimated_time" min="0"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <!-- Submit Button -->
                            <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Subtask
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
                
        <!-- Task Actions (like Mark Complete) -->
        <?php if ($task->status_id != 6 && $task->status_id != 5): // Not completed or closed ?>
        <div class="mt-6 flex justify-center">
            <div class="inline-flex rounded-md shadow-sm">
                <a href="/tasks/edit/<?= $task->id ?>?mark_complete=1" class="inline-flex items-center px-4 py-2 bg-green-600 text-white border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Mark Complete
                </a>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <script>
        // Section toggles
        document.addEventListener('DOMContentLoaded', function() {
            const toggles = document.querySelectorAll('.section-toggle');
            
            toggles.forEach(toggle => {
                const targetId = toggle.getAttribute('data-target');
                const targetElement = document.getElementById(targetId);
                
                // Store initial state
                if (!sessionStorage.getItem(targetId)) {
                    sessionStorage.setItem(targetId, 'open');
                }
                
                // Apply initial state
                const initialState = sessionStorage.getItem(targetId);
                if (initialState === 'closed') {
                    targetElement.classList.add('hidden');
                    toggle.querySelector('svg').classList.add('rotate-180');
                }
                
                toggle.addEventListener('click', function() {
                    // Toggle visibility
                    targetElement.classList.toggle('hidden');
                    
                    // Toggle rotation of arrow icon
                    const icon = toggle.querySelector('svg');
                    icon.classList.toggle('rotate-180');
                    
                    // Store state
                    const newState = targetElement.classList.contains('hidden') ? 'closed' : 'open';
                    sessionStorage.setItem(targetId, newState);
                });
            });
        });

        // Timer functionality for active timers
        <?php if ($isTimerActive): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const timerEl = document.getElementById('timer');
            let seconds = <?= $timerDuration ?>;
            
            setInterval(function() {
                seconds++;
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const secs = seconds % 60;
                
                timerEl.textContent = 
                    (hours < 10 ? '0' + hours : hours) + ':' +
                    (minutes < 10 ? '0' + minutes : minutes) + ':' +
                    (secs < 10 ? '0' + secs : secs);
            }, 1000);
        });
        <?php endif; ?>
    </script>
</body>
</html>