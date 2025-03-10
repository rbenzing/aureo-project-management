<?php
//file: Views/Projects/inc/table_tasks.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Utils\Time;
?>
<!-- Tasks View -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Task</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assignee</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Priority</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time Spent</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (isset($project->tasks) && !empty($project->tasks)): ?>
                <?php foreach ($project->tasks as $task): ?>
                    <?php
                    // Priority level mapping
                    $priorityLevel = isset($task->priority) ? $task->priority : 'none';
                    $priorityClasses = [
                        'high' => 'text-red-600 dark:text-red-400',
                        'medium' => 'text-yellow-600 dark:text-yellow-400',
                        'low' => 'text-blue-600 dark:text-blue-400',
                        'none' => 'text-gray-600 dark:text-gray-400'
                    ];
                    $priorityClass = $priorityClasses[$priorityLevel] ?? 'text-gray-600 dark:text-gray-400';

                    // Status mapping
                    $taskStatusMap = [
                        1 => ['label' => 'Open', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'],
                        2 => ['label' => 'In Progress', 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'],
                        3 => ['label' => 'On Hold', 'class' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'],
                        4 => ['label' => 'In Review', 'class' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200'],
                        5 => ['label' => 'Closed', 'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'],
                        6 => ['label' => 'Completed', 'class' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200']
                    ];
                    $taskStatus = $taskStatusMap[$task->status_id] ?? ['label' => 'Unknown', 'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'];
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="/tasks/view/<?= $task->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                <?= htmlspecialchars($task->title) ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if (isset($task->assigned_to) && !empty($task->assigned_to)): ?>
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    <?php if (isset($task->first_name) && isset($task->last_name)): ?>
                                        <?= htmlspecialchars($task->first_name) ?> <?= htmlspecialchars($task->last_name) ?>
                                    <?php else: ?>
                                        <?php
                                        // Fetch user details if not provided with task
                                        $user = (new \App\Models\User())->find($task->assigned_to);
                                        if ($user): ?>
                                            <?= htmlspecialchars($user->first_name) ?> <?= htmlspecialchars($user->last_name) ?>
                                        <?php else: ?>
                                            ID: <?= $task->assigned_to ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium <?= $priorityClass ?>">
                                <?= ucfirst($priorityLevel) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $taskStatus['class'] ?>">
                                <?= $taskStatus['label'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                            <?php if (isset($task->due_date) && !empty($task->due_date)): ?>
                                <?php
                                $dueDate = strtotime($task->due_date);
                                $today = strtotime('today');
                                $isDue = $dueDate < $today && ($task->status_id != 6 && $task->status_id != 5);
                                ?>
                                <span class="<?= $isDue ? 'text-red-600 dark:text-red-400 font-medium' : '' ?>">
                                    <?= date('M j, Y', $dueDate) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-gray-500 dark:text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                            <?= Time::formatSeconds($task->time_spent ?? 0) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                        No tasks found for this project
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>

        <!-- Project Summary Row -->
        <?php if (isset($project->tasks) && !empty($project->tasks)): ?>
            <tfoot class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <td colspan="5" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Summary: <?= $completedTasks ?> / <?= $totalTasks ?> Tasks Completed
                    </td>
                    <td class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Total: <?= Time::formatSeconds($totalTime) ?>
                    </td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>
</div>