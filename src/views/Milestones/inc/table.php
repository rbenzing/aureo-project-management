<?php
// file: Views/Milestones/inc/table.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Include helper functions
include_once BASE_PATH . '/../src/Views/Milestones/inc/helpers.php';

// Permission checks
$canEditMilestones = isset($_SESSION['user']['permissions']) && in_array('edit_milestones', $_SESSION['user']['permissions']);
$canDeleteMilestones = isset($_SESSION['user']['permissions']) && in_array('delete_milestones', $_SESSION['user']['permissions']);
?>

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
                                    <?php if (isset($milestone->task_count)): ?>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <?= $milestone->task_count ?> tasks
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
                                <?php if ($canEditMilestones): ?>
                                <a 
                                    href="/milestones/edit/<?= $milestone->id ?>" 
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                >
                                    Edit
                                </a>
                                <?php endif; ?>
                                <?php if ($canDeleteMilestones): ?>
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
                                <?php endif; ?>
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