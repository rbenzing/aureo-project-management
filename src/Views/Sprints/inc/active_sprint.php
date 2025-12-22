<?php
// file: Views/Sprints/inc/active_sprint.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

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
$totalTasks = isset($activeSprint->task_count) ? $activeSprint->task_count : 0;
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
            for ($i = 0; $i < $limit; $i++):
                $task = $activeSprint->tasks[$i];
                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="/tasks/view/<?= $task->id ?? 0 ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            <?= htmlspecialchars($task->title ?? 'Task') ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                            // Use consistent status styling
                            $statusMap = [
                                1 => ['label' => 'OPEN', 'color' => 'bg-blue-600'],
                                2 => ['label' => 'IN PROGRESS', 'color' => 'bg-yellow-500'],
                                3 => ['label' => 'ON HOLD', 'color' => 'bg-purple-500'],
                                4 => ['label' => 'IN REVIEW', 'color' => 'bg-indigo-500'],
                                5 => ['label' => 'CLOSED', 'color' => 'bg-gray-500'],
                                6 => ['label' => 'COMPLETED', 'color' => 'bg-green-500'],
                                7 => ['label' => 'CANCELLED', 'color' => 'bg-red-500'],
                            ];
                $statusId = $task->status_id ?? 1;
                $status = $statusMap[$statusId] ?? ['label' => 'UNKNOWN', 'color' => 'bg-gray-500'];
                ?>
                                        <span class="px-3 py-1 text-xs rounded-full bg-opacity-20 text-white font-medium whitespace-nowrap <?= $status['color'] ?>">
                                            <?= $status['label'] ?>
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