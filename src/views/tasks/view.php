<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;

// Format time function
function formatTime($minutes) {
    if (!$minutes) return 'None';
    
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($hours > 0) {
        return "{$hours}h {$mins}m";
    }
    return "{$mins}m";
}

// Format date function
function formatDate($date) {
    if (!$date) return 'Not set';
    return date('M j, Y', strtotime($date));
}

// Get priority class
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

// Get status class
function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'completed':
        case 'done':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        case 'on_hold':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
    }
}

// Calculate progress
$progress = 0;
$totalSubtasks = !empty($subtasks) ? count($subtasks) : 0;
$completedSubtasks = 0;

if ($totalSubtasks > 0) {
    foreach ($subtasks as $subtask) {
        if (strtolower($subtask->status) === 'completed' || strtolower($subtask->status) === 'done') {
            $completedSubtasks++;
        }
    }
    $progress = ($completedSubtasks / $totalSubtasks) * 100;
}

// Determine if time tracking is currently active
$isTimerActive = isset($activeTimer) && $activeTimer['task_id'] == $task->id;
$timerDuration = $isTimerActive ? time() - $activeTimer['start_time'] : 0;
$formattedDuration = gmdate('H:i:s', $timerDuration);

// Set page parameters
$pageTitle = htmlspecialchars($task->title) . ' - Task Details';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-4 sm:p-6 overflow-y-auto">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <div class="w-full mx-auto">

            <!-- Task Header + Action Bar -->
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold"><?php echo htmlspecialchars($task->title); ?></h1>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo getStatusClass($task->status_name ?? $task->status); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($task->status_name ?? $task->status))); ?>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo getPriorityClass($task->priority); ?>">
                                <?php echo ucfirst(htmlspecialchars($task->priority)); ?> Priority
                            </span>
                            <?php if ($task->is_hourly): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Billable
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($isTimerActive): ?>
                            <form method="POST" action="/tasks/stop-timer/<?php echo $activeTimer['id']; ?>" class="inline-flex">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 flex items-center space-x-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                                    </svg>
                                    <span id="timer"><?php echo $formattedDuration; ?></span>
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="/tasks/start-timer/<?php echo $task->id; ?>" class="inline-flex">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                    </svg>
                                    Start Timer
                                </button>
                            </form>
                        <?php endif; ?>
                        <div class="inline-flex rounded-md shadow-sm" role="group">
                            <a href="/tasks/edit/<?php echo $task->id; ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-l-md hover:bg-indigo-700 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                                Edit
                            </a>
                            <form method="POST" action="/tasks/delete/<?php echo $task->id; ?>" class="inline-flex" onsubmit="return confirm('Are you sure you want to delete this task? This cannot be undone.')">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-r-md hover:bg-red-700 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Quick Info Row -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                    <!-- Project & Assignee Info -->
                    <div class="col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Project</h3>
                                <?php if ($project): ?>
                                    <div class="flex items-center mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                        </svg>
                                        <a href="/projects/view/<?php echo $project->id; ?>" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium truncate max-w-xs">
                                            <?php echo htmlspecialchars($project->name); ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <p class="mt-1 text-gray-500 dark:text-gray-400 italic text-sm">No project assigned</p>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned To</h3>
                                <div class="flex items-center mt-1">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-indigo-500">
                                            <span class="text-xs font-medium leading-none text-white">
                                                <?php echo !empty($task->first_name) ? substr($task->first_name, 0, 1) . substr($task->last_name, 0, 1) : 'NA'; ?>
                                            </span>
                                        </span>
                                    </div>
                                    <div class="ml-2">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate max-w-xs">
                                            <?php echo !empty($task->first_name) ? htmlspecialchars($task->first_name . ' ' . $task->last_name) : 'Unassigned'; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dates Info -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Dates</h3>
                        <div class="mt-1 space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Start:</span>
                                <span class="font-medium"><?php echo formatDate($task->start_date ?? null); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Due:</span>
                                <span class="font-medium <?php echo ($task->due_date && strtotime($task->due_date) < strtotime('today')) ? 'text-red-600 dark:text-red-400' : ''; ?>">
                                    <?php echo formatDate($task->due_date ?? null); ?>
                                    <?php if ($task->due_date && strtotime($task->due_date) < strtotime('today')): ?>
                                        <span class="ml-1 text-xs text-red-600 dark:text-red-400">⚠️</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Time Tracking Info -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Time</h3>
                        <div class="mt-1 space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Estimated:</span>
                                <span class="font-medium"><?php echo formatTime($task->estimated_time ?? 0); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Spent:</span>
                                <span class="font-medium"><?php echo formatTime($task->time_spent ?? 0); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Left Area: Task Description -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold">Description</h2>
                        </div>
                        <div class="p-4">
                            <div class="prose dark:prose-invert max-w-none">
                                <?php if (!empty($task->description)): ?>
                                    <p class="text-gray-700 dark:text-gray-300"><?php echo nl2br(htmlspecialchars($task->description)); ?></p>
                                <?php else: ?>
                                    <p class="text-gray-500 dark:text-gray-400 italic">No description provided</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($task->estimated_time) && !empty($task->time_spent)): ?>
                        <div class="px-4 pb-4">
                            <?php 
                            $timeProgress = min(100, ($task->time_spent / $task->estimated_time) * 100);
                            $progressColor = $timeProgress > 100 ? 'bg-red-500' : 'bg-green-500';
                            ?>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-700 dark:text-gray-300">Time Progress</span>
                                <span class="text-gray-700 dark:text-gray-300"><?php echo round($timeProgress); ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="<?php echo $progressColor; ?> h-2 rounded-full" style="width: <?php echo $timeProgress; ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($task->is_hourly): ?>
                        <div class="p-4 bg-green-50 dark:bg-green-900 border-t border-green-100 dark:border-green-800">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-green-800 dark:text-green-200">Billable Amount</span>
                                <span class="text-lg font-bold text-green-800 dark:text-green-200">
                                    <?php 
                                    $billableHours = ($task->billable_time ?? 0) / 60;
                                    $billableAmount = $billableHours * ($task->hourly_rate ?? 0);
                                    echo '$' . number_format($billableAmount, 2);
                                    ?>
                                </span>
                            </div>
                            <div class="mt-1 text-xs text-green-700 dark:text-green-300">
                                Rate: $<?php echo number_format($task->hourly_rate ?? 0, 2); ?> per hour
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Task Details Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mt-6">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold">Details</h2>
                        </div>
                        <div class="p-4">
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-4">
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <?php echo date('M j, Y', strtotime($task->created_at)); ?>
                                    </dd>
                                </div>
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <?php echo date('M j, Y', strtotime($task->updated_at)); ?>
                                    </dd>
                                </div>
                                <?php if (!empty($task->complete_date)): ?>
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <?php echo formatDate($task->complete_date); ?>
                                    </dd>
                                </div>
                                <?php endif; ?>
                                <?php if ($task->is_subtask && $task->parent_task_id): ?>
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parent Task</dt>
                                    <dd class="mt-1 text-sm">
                                        <a href="/tasks/view/<?php echo $task->parent_task_id; ?>" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            View Parent Task
                                        </a>
                                    </dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Quick Actions</h3>
                            <div class="flex flex-wrap gap-2">
                                <?php if (strtolower($task->status_name ?? $task->status) !== 'completed' && strtolower($task->status_name ?? $task->status) !== 'done'): ?>
                                <a href="/tasks/edit/<?php echo $task->id; ?>?mark_complete=1" class="inline-flex items-center text-sm px-3 py-1 bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 rounded-md hover:bg-green-200 dark:hover:bg-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Mark Complete
                                </a>
                                <?php endif; ?>
                                <a href="/tasks/create?duplicate=<?php echo $task->id; ?>" class="inline-flex items-center text-sm px-3 py-1 bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
                                        <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
                                    </svg>
                                    Duplicate Task
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Area: Subtasks List -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden h-full">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <h2 class="text-lg font-semibold">Subtasks</h2>
                            <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo $completedSubtasks; ?>/<span class="font-medium"><?php echo $totalSubtasks; ?></span></span>
                        </div>

                        <?php if ($totalSubtasks > 0): ?>
                        <!-- Progress Bar -->
                        <div class="px-4 pt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Subtasks List -->
                        <div class="p-4 flex-1 overflow-y-auto" style="max-height: 460px;">
                            <?php if ($totalSubtasks > 0): ?>
                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($subtasks as $subtask): ?>
                                <li class="py-3">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <?php if (strtolower($subtask->status) === 'completed' || strtolower($subtask->status) === 'done'): ?>
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
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    <?php echo htmlspecialchars($subtask->title); ?>
                                                </p>
                                                <span class="ml-2 flex-shrink-0 text-xs px-2 py-0.5 rounded-full <?php echo getStatusClass($subtask->status_name ?? $subtask->status); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($subtask->status_name ?? $subtask->status))); ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($subtask->estimated_time)): ?>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                <?php echo formatTime($subtask->estimated_time); ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($subtask->description)): ?>
                                    <div class="mt-2 ml-8 text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                        <?php echo nl2br(htmlspecialchars($subtask->description)); ?>
                                    </div>
                                    <?php endif; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No subtasks yet</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">Add a subtask to break down this task</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Area: Add Subtask Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold">Add Subtask</h2>
                        </div>
                        <div class="p-4">
                            <form method="POST" action="/tasks/create" class="space-y-4">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="parent_task_id" value="<?php echo $task->id; ?>">
                                <input type="hidden" name="project_id" value="<?php echo $task->project_id; ?>">
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
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        // Timer functionality for active timers
        <?php if ($isTimerActive): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const timerEl = document.getElementById('timer');
            let seconds = <?php echo $timerDuration; ?>;
            
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