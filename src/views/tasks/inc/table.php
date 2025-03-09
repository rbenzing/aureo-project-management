<?php
//file: Views/Tasks/inc/table.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<tbody class="divide-y divide-gray-200 dark:divide-gray-700">
    <?php if (!empty($tasks)): ?>
        <?php foreach ($tasks as $task): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <?php if ($task->is_subtask): ?>
                            <div class="ml-2 mr-3">↳</div>
                        <?php endif; ?>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                <a href="/tasks/view/<?= $task->id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    <?= htmlspecialchars($task->title) ?>
                                </a>
                            </div>
                            <?php if (!empty($task->description)): ?>
                                <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                    <?= htmlspecialchars(substr($task->description, 0, 60)) . (strlen($task->description) > 60 ? '...' : '') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 dark:text-gray-200">
                        <?= htmlspecialchars($task->project_name ?? 'N/A') ?>
                    </div>
                </td>
                <?php if (!$isMyTasksView): // Only show assignee column in backlog view ?>
                <td class="px-6 py-4">
                    <?php if (!empty($task->first_name)): ?>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                <span class="text-xs font-medium text-white">
                                    <?= htmlspecialchars(substr($task->first_name, 0, 1) . substr($task->last_name, 0, 1)) ?>
                                </span>
                            </div>
                            <div class="ml-2">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                    <?= htmlspecialchars($task->first_name . ' ' . $task->last_name) ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            Unassigned
                        </span>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getPriorityClasses($task->priority) ?>">
                        <?= ucfirst(htmlspecialchars($task->priority)) ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getStatusClasses($task->status_name) ?>">
                        <?= htmlspecialchars($task->status_name ?? 'Unknown') ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <?php if (!empty($task->due_date)): ?>
                        <div class="text-sm <?= isDueDateOverdue($task->due_date, $task->status_name) ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-900 dark:text-gray-200' ?>">
                            <?= date('M j, Y', strtotime($task->due_date)) ?>
                            <?php if (isDueDateOverdue($task->due_date, $task->status_name)): ?>
                                <span class="ml-2 text-xs bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 px-2 py-0.5 rounded">Overdue</span>
                            <?php elseif (isDueToday($task->due_date)): ?>
                                <span class="ml-2 text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 px-2 py-0.5 rounded">Today</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="text-sm text-gray-500 dark:text-gray-400">No Due Date</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 dark:text-gray-200">
                        <?php if ($task->time_spent > 0): ?>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <?= formatTimeSpent($task->time_spent) ?>
                            </div>
                        <?php else: ?>
                            <span class="text-gray-500 dark:text-gray-400">0h 0m</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($task->estimated_time)): ?>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Est: <?= formatTimeSpent($task->estimated_time) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex justify-end space-x-3">
                        <?php 
                        // Only show timer button if viewing own tasks or have permission to manage tasks
                        $canTimeTrack = $viewingOwnTasks || 
                                       ($task->assigned_to == $currentUserId) || 
                                       (isset($_SESSION['user']['permissions']) && in_array('manage_tasks', $_SESSION['user']['permissions']));
                        
                        if (!isset($activeTimer) && $canTimeTrack && $task->status_name !== 'Completed' && $task->status_name !== 'Closed'): 
                        ?>
                            <form action="/tasks/start-timer/<?= $task->id ?>" method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button 
                                    type="submit" 
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                    title="Start Timer"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <a 
                            href="/tasks/view/<?= $task->id ?>" 
                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                            View
                        </a>
                        <a 
                            href="/tasks/edit/<?= $task->id ?>" 
                            class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                        >
                            Edit
                        </a>
                        <form 
                            action="/tasks/delete/<?= $task->id ?>" 
                            method="POST" 
                            onsubmit="return confirm('Are you sure you want to delete this task?');"
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
            <td colspan="<?= $isMyTasksView ? '7' : '8' ?>" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                No tasks found. <a href="/tasks/create<?= $isMyTasksView && !$viewingOwnTasks ? '?assign_to=' . htmlspecialchars($userId) : '' ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Create your first task</a>.
            </td>
        </tr>
    <?php endif; ?>
</tbody>