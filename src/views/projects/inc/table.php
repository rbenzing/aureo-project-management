<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<!-- Project Table List -->
<div class="space-y-8">
    <?php if (!empty($projects)): ?>
        <?php foreach ($projects as $project): ?>
            <div class="text-white shadow border-b border-gray-200">
                <div class="py-4">
                    <div class="flex items-center w-full">
                        <?php 
                        // Status indicator color
                        $statusColor = 'bg-gray-600';
                        switch ($project->status) {
                            case 'ready':
                                $statusColor = 'bg-blue-600';
                                break;
                            case 'in_progress':
                                $statusColor = 'bg-yellow-600';
                                break;
                            case 'completed':
                                $statusColor = 'bg-green-600';
                                break;
                            case 'on_hold':
                                $statusColor = 'bg-purple-600';
                                break;
                            case 'delayed':
                                $statusColor = 'bg-red-600';
                                break;
                            case 'cancelled':
                                $statusColor = 'bg-gray-600';
                                break;
                        }
                        ?>
                        <div class="w-1 h-6 <?= $statusColor ?> rounded-full mr-4"></div>
                        <h2 class="inline-block w-text-lg font-medium">
                            <a href="/projects/view/<?= $project->id ?>" class="hover:text-blue-500 text-nowrap"><?= htmlspecialchars($project->name) ?></a>
                        </h2>
                        <span class="ml-4 px-2 py-1 text-xs rounded-full <?= $statusColor ?> bg-opacity-20">
                            <?= ucfirst(htmlspecialchars($project->status)) ?>
                        </span>
                        <div class="ml-4 flex justify-between align-center w-full">
                            <?php if ($project->company_name): ?>
                                <span class="text-gray-400 text-nowrap"><?= htmlspecialchars($project->company_name) ?></span>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['user']['permissions']) && in_array('create_tasks', $_SESSION['user']['permissions'])): ?>
                                <a href="/tasks/create/project/<?= $project->id ?>" class="px-4 py-2 bg-indigo-700 text-white text-sm rounded-md hover:bg-blue-600">
                                    + New Task
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-sm text-gray-500 border-b border-gray-500">
                                <th class="px-6 py-3 font-medium"></th>
                                <th class="px-6 py-3 font-medium">Task</th>
                                <th class="px-6 py-3 font-medium">Owner</th>
                                <th class="px-6 py-3 font-medium">Client</th>
                                <th class="px-6 py-3 font-medium">Priority</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 font-medium">Timeline</th>
                                <th class="px-6 py-3 font-medium">Actual Time</th>
                                <th class="px-6 py-3 font-medium">Hourly rate</th>
                                <th class="px-6 py-3 font-medium">Billable amount</th>
                                <th class="px-6 py-3 font-medium">Files</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-600">
                            <?php 
                            $totalTasks = 0;
                            $completedTasks = 0;
                            $totalTime = 0;
                            $totalBillable = 0;
                            $fileCount = 0;
                            
                            if (isset($project->tasks) && !empty($project->tasks)): 
                                foreach ($project->tasks as $task): 
                                    $totalTasks++;
                                    if ($task->status_id == 6) { // Completed status
                                        $completedTasks++;
                                    }
                                    $totalTime += $task->time_spent ?? 0;
                                    
                                    // Calculate billable amount if hourly rate is set
                                    $billableAmount = 0;
                                    if ($task->is_hourly && $task->hourly_rate && $task->billable_time) {
                                        $billableAmount = ($task->hourly_rate * $task->billable_time) / 3600; // Convert seconds to hours
                                        $totalBillable += $billableAmount;
                                    }
                                    
                                    // Priority level mapping
                                    $priorityLevel = 0;
                                    switch ($task->priority) {
                                        case 'high':
                                            $priorityLevel = 3;
                                            break;
                                        case 'medium':
                                            $priorityLevel = 2;
                                            break;
                                        case 'low':
                                            $priorityLevel = 1;
                                            break;
                                    }
                                    
                                    // Status color
                                    $taskStatusColor = 'bg-blue-100 text-blue-800';
                                    switch ($task->status_id) {
                                        case 6: // Completed
                                            $taskStatusColor = 'bg-green-100 text-green-800';
                                            break;
                                        case 3: // On Hold
                                            $taskStatusColor = 'bg-purple-100 text-purple-800';
                                            break;
                                        case 5: // Closed
                                            $taskStatusColor = 'bg-gray-100 text-gray-800';
                                            break;
                                        case 7: // Cancelled
                                            $taskStatusColor = 'bg-red-100 text-red-800';
                                            break;
                                    }
                            ?>
                            <tr>
                                <td class="pr-6 px-0">
                                    <div class="w-1 h-6 bg-purple-500 rounded-full"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="/tasks/view/<?= $task->id ?>" class="text-white hover:text-blue-400"><?= htmlspecialchars($task->title) ?></a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex -space-x-2">
                                        <?php if ($task->assigned_to): ?>
                                            <span class="text-white"><?= htmlspecialchars($task->first_name ?? '') ?> <?= htmlspecialchars($task->last_name ?? '') ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-400">Unassigned</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-white"><?= htmlspecialchars($project->company_name) ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex text-yellow-400">
                                        <?php for ($i = 1; $i <= 3; $i++): ?>
                                            <svg class="w-5 h-5" fill="<?= $i <= $priorityLevel ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-sm <?= $taskStatusColor ?> rounded-full">
                                        <?= htmlspecialchars($task->status_name ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($task->due_date): ?>
                                        <?php
                                        $dueDate = strtotime($task->due_date);
                                        $today = strtotime('today');
                                        $timeLeftClass = 'bg-green-100 text-green-800';
                                        
                                        if ($dueDate < $today && $task->status_id != 6) {
                                            $timeLeftClass = 'bg-red-100 text-red-800';
                                        } elseif ($dueDate <= strtotime('+3 days') && $task->status_id != 6) {
                                            $timeLeftClass = 'bg-yellow-100 text-yellow-800';
                                        }
                                        ?>
                                        <span class="px-3 py-1 text-sm <?= $timeLeftClass ?> rounded-full">
                                            <?= date('M j, Y', $dueDate) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">No deadline</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-white">
                                    <?php if ($task->time_spent): ?>
                                        <?= gmdate('H:i:s', $task->time_spent) ?>
                                    <?php else: ?>
                                        0:00:00
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-white">
                                    <?php if ($task->is_hourly && $task->hourly_rate): ?>
                                        $<?= htmlspecialchars($task->hourly_rate) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-white">
                                    <?php if ($billableAmount > 0): ?>
                                        $<?= number_format($billableAmount, 2) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <svg class="w-5 h-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
                                    </svg>
                                </td>
                            </tr>
                            <?php 
                                endforeach; 
                            endif; 
                            ?>

                            <!-- Project Summary Row -->
                            <tr class="bg-gray-700">
                                <td colspan="7" class="px-6 py-1 text-white">
                                    <?= $completedTasks ?> / <?= $totalTasks ?> Tasks Completed
                                </td>
                                <td class="px-6 py-1 text-white">
                                    <?= gmdate('H:i:s', $totalTime) ?>
                                </td>
                                <td class="px-6 py-1 text-white">-</td>
                                <td class="px-6 py-1 text-white">
                                    $<?= number_format($totalBillable, 2) ?>
                                </td>
                                <td class="px-6 py-1">
                                    <div class="flex items-center">
                                        <span class="bg-gray-200 rounded-full px-2 py-1 text-blue-800 text-xs"><?= $fileCount ?></span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-gray-800 rounded-md p-6 text-center">
            <p class="text-gray-400">No projects found. Create your first project to get started.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<div class="mt-6">
    <?php if ($totalPages > 1): ?>
        <nav class="flex justify-center items-center space-x-4">
            <?php if ($page > 1): ?>
                <a href="/projects/page/<?= $page - 1 ?>?view=table<?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?><?= isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : '' ?><?= isset($_GET['company_id']) ? '&company_id=' . htmlspecialchars($_GET['company_id']) : '' ?>" class="px-3 py-2 bg-gray-800 rounded-md hover:bg-gray-700">&laquo; Previous</a>
            <?php endif; ?>
            
            <span class="text-gray-400">Page <?= $page ?> of <?= $totalPages ?></span>
            
            <?php if ($page < $totalPages): ?>
                <a href="/projects/page/<?= $page + 1 ?>?view=table<?= isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : '' ?><?= isset($_GET['status']) ? '&status=' . htmlspecialchars($_GET['status']) : '' ?><?= isset($_GET['company_id']) ? '&company_id=' . htmlspecialchars($_GET['company_id']) : '' ?>" class="px-3 py-2 bg-gray-800 rounded-md hover:bg-gray-700">Next &raquo;</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</div>