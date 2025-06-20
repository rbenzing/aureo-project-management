<?php
//file: Views/Sprints/inc/table_tasks_hierarchical.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Utils\Time;
use App\Utils\Sort;

// Include helper functions for consistent styling
include_once BASE_PATH . '/../src/Views/Tasks/inc/helper_functions.php';
include_once BASE_PATH . '/../src/Views/Sprints/inc/helpers.php';

// Get current sort parameters
$taskSortField = isset($_GET['task_sort']) ? htmlspecialchars($_GET['task_sort']) : 'priority';
$taskSortDir = isset($_GET['task_dir']) && $_GET['task_dir'] === 'asc' ? 'asc' : 'desc';

// Status mapping to match tasks page style
$taskStatusMap = [
    1 => ['label' => 'OPEN', 'color' => 'bg-blue-600'],
    2 => ['label' => 'IN PROGRESS', 'color' => 'bg-yellow-500'],
    3 => ['label' => 'ON HOLD', 'color' => 'bg-purple-500'],
    4 => ['label' => 'IN REVIEW', 'color' => 'bg-indigo-500'],
    5 => ['label' => 'CLOSED', 'color' => 'bg-gray-500'],
    6 => ['label' => 'COMPLETED', 'color' => 'bg-green-500'],
    7 => ['label' => 'CANCELLED', 'color' => 'bg-red-500']
];

// Function to render a task row
function renderSprintTaskRow($task, $taskStatusMap, $indentLevel = 0) {
    $priorityLevel = isset($task->priority) ? $task->priority : 'none';
    $taskStatus = $taskStatusMap[$task->status_id] ?? ['label' => 'UNKNOWN', 'color' => 'bg-gray-500'];
    $indentClass = $indentLevel > 0 ? 'pl-' . ($indentLevel * 6) : '';
    
    echo '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700">';
    echo '<td class="px-6 py-4 whitespace-nowrap ' . $indentClass . '">';
    echo '<a href="/tasks/view/' . $task->id . '" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">';
    echo htmlspecialchars($task->title);
    echo '</a>';
    echo '</td>';
    
    echo '<td class="px-6 py-4 whitespace-nowrap">';
    $taskStatusInfo = getTaskStatusInfo($task->status_id ?? 1);
    echo renderStatusPill($taskStatusInfo['label'], $taskStatusInfo['color'], 'sm');
    echo '</td>';
    
    echo '<td class="px-6 py-4 whitespace-nowrap">';
    if (!empty($task->first_name) && !empty($task->last_name)) {
        echo '<div class="flex items-center">';
        echo '<div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">';
        echo '<span class="text-xs font-medium text-white">';
        echo htmlspecialchars(substr($task->first_name, 0, 1) . substr($task->last_name, 0, 1));
        echo '</span>';
        echo '</div>';
        echo '<div class="ml-2">';
        echo '<div class="text-sm font-medium text-gray-900 dark:text-gray-200">';
        echo htmlspecialchars("{$task->first_name} {$task->last_name}");
        echo '</div>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<span class="text-gray-500 dark:text-gray-400">Unassigned</span>';
    }
    echo '</td>';
    
    echo '<td class="px-6 py-4 whitespace-nowrap">';
    if (!empty($task->due_date)) {
        $dueDate = new DateTime($task->due_date);
        $today = new DateTime();
        $isPastDue = $dueDate < $today && $task->status_id != 6; // 6 = Completed
        echo '<span class="' . ($isPastDue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-900 dark:text-gray-200') . ' whitespace-nowrap">';
        echo date('M j, Y', strtotime($task->due_date));
        echo '</span>';
    } else {
        echo '<span class="text-gray-500 dark:text-gray-400 whitespace-nowrap">No due date</span>';
    }
    echo '</td>';
    
    echo '<td class="px-6 py-4 whitespace-nowrap">';
    echo '<span class="' . getSprintPriorityClass($task->priority ?? 'none') . '">';
    echo ucfirst($task->priority ?? 'None');
    echo '</span>';
    echo '</td>';
    
    echo '<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">';
    echo '<a href="/tasks/view/' . $task->id . '" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">';
    echo 'View';
    echo '</a>';
    echo '<a href="/tasks/edit/' . $task->id . '" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">';
    echo 'Edit';
    echo '</a>';
    echo '</td>';
    
    echo '</tr>';
}

// Function to render milestone/epic header
function renderSprintMilestoneHeader($item, $type) {
    $bgColor = $type === 'epic' ? 'bg-purple-50 dark:bg-purple-900' : 'bg-blue-50 dark:bg-blue-900';
    $textColor = $type === 'epic' ? 'text-purple-800 dark:text-purple-200' : 'text-blue-800 dark:text-blue-200';
    $icon = $type === 'epic' ? '🎯' : '🏁';
    
    echo '<tr class="' . $bgColor . '">';
    echo '<td colspan="6" class="px-6 py-3 ' . $textColor . ' font-semibold">';
    echo '<div class="flex items-center">';
    echo '<span class="mr-2">' . $icon . '</span>';
    echo '<a href="/milestones/view/' . $item->id . '" class="hover:underline">';
    echo htmlspecialchars($item->title);
    echo '</a>';
    if (isset($item->status_name)) {
        echo '<span class="ml-3 px-2 py-1 text-xs rounded-full bg-white bg-opacity-50">';
        echo htmlspecialchars($item->status_name);
        echo '</span>';
    }
    echo '</div>';
    echo '</td>';
    echo '</tr>';
}
?>

<!-- Hierarchical Sprint Tasks View -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    <a href="<?= Sort::getUrl('title', $taskSortField, $taskSortDir, 'task_sort', 'task_dir') ?>" class="group inline-flex items-center">
                        Task / Epic / Milestone
                        <?= Sort::getIndicator('title', $taskSortField, $taskSortDir) ?>
                    </a>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    <a href="<?= Sort::getUrl('status_id', $taskSortField, $taskSortDir, 'task_sort', 'task_dir') ?>" class="group inline-flex items-center">
                        Status
                        <?= Sort::getIndicator('status_id', $taskSortField, $taskSortDir) ?>
                    </a>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    <a href="<?= Sort::getUrl('assigned_to', $taskSortField, $taskSortDir, 'task_sort', 'task_dir') ?>" class="group inline-flex items-center">
                        Assignee
                        <?= Sort::getIndicator('assigned_to', $taskSortField, $taskSortDir) ?>
                    </a>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    <a href="<?= Sort::getUrl('due_date', $taskSortField, $taskSortDir, 'task_sort', 'task_dir') ?>" class="group inline-flex items-center">
                        Due Date
                        <?= Sort::getIndicator('due_date', $taskSortField, $taskSortDir) ?>
                    </a>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    <a href="<?= Sort::getUrl('priority', $taskSortField, $taskSortDir, 'task_sort', 'task_dir') ?>" class="group inline-flex items-center">
                        Priority
                        <?= Sort::getIndicator('priority', $taskSortField, $taskSortDir) ?>
                    </a>
                </th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (isset($sprint->hierarchy) && !empty($sprint->hierarchy)): ?>
                <?php foreach ($sprint->hierarchy as $item): ?>
                    <?php if ($item['type'] === 'epic'): ?>
                        <?php renderSprintMilestoneHeader($item['data'], 'epic'); ?>
                        
                        <!-- Epic's direct tasks -->
                        <?php foreach ($item['tasks'] as $task): ?>
                            <?php renderSprintTaskRow($task, $taskStatusMap, 1); ?>
                        <?php endforeach; ?>
                        
                        <!-- Epic's milestones -->
                        <?php foreach ($item['milestones'] as $milestone): ?>
                            <?php renderSprintMilestoneHeader($milestone['data'], 'milestone'); ?>
                            
                            <!-- Milestone's tasks -->
                            <?php foreach ($milestone['tasks'] as $task): ?>
                                <?php renderSprintTaskRow($task, $taskStatusMap, 2); ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        
                    <?php elseif ($item['type'] === 'milestone'): ?>
                        <?php renderSprintMilestoneHeader($item['data'], 'milestone'); ?>
                        
                        <!-- Milestone's tasks -->
                        <?php foreach ($item['tasks'] as $task): ?>
                            <?php renderSprintTaskRow($task, $taskStatusMap, 1); ?>
                        <?php endforeach; ?>
                        
                    <?php elseif ($item['type'] === 'unassigned_tasks'): ?>
                        <?php if (!empty($item['tasks'])): ?>
                            <tr class="bg-gray-50 dark:bg-gray-700">
                                <td colspan="6" class="px-6 py-3 text-gray-700 dark:text-gray-300 font-semibold">
                                    <div class="flex items-center">
                                        <span class="mr-2">📋</span>
                                        <?= htmlspecialchars($item['data']->title) ?>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Unassigned tasks -->
                            <?php foreach ($item['tasks'] as $task): ?>
                                <?php renderSprintTaskRow($task, $taskStatusMap, 1); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                        No tasks have been added to this sprint yet.
                        <?php if (isset($sprint->status_id) && in_array($sprint->status_id, [1, 2])): // Planning or Active ?>
                            <a href="/tasks/create?sprint_id=<?= $sprint->id ?? 0 ?>&project_id=<?= $project->id ?? 0 ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                Add your first task
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
