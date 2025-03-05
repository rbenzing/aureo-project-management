<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Define status labels and colors
$statusMap = [
    'ready' => [
        'label' => 'READY',
        'color' => 'bg-blue-600'
    ],
    'in_progress' => [
        'label' => 'IN PROGRESS',
        'color' => 'bg-yellow-300'
    ],
    'completed' => [
        'label' => 'COMPLETED',
        'color' => 'bg-green-300'
    ],
    'on_hold' => [
        'label' => 'ON HOLD',
        'color' => 'bg-purple-300'
    ],
    'delayed' => [
        'label' => 'DELAYED',
        'color' => 'bg-red-300'
    ],
    'cancelled' => [
        'label' => 'CANCELLED',
        'color' => 'bg-gray-300'
    ]
];

// Apply filters for search and sorting
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$companyFilter = isset($_GET['company_id']) ? $_GET['company_id'] : '';
$viewByFilter = isset($_GET['by']) ? $_GET['by'] : 'tasks';
$sortField = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sortDirection = isset($_GET['dir']) && $_GET['dir'] === 'asc' ? 'asc' : 'desc';
?>

<!-- Project Table List -->
<div class="space-y-8">
    <?php if (!empty($projects)): ?>
        <?php foreach ($projects as $project): ?>
            <?php
            // Skip projects that don't match filters
            if (!empty($searchQuery) && 
                stripos($project->name ?? '', $searchQuery) === false && 
                stripos($project->company_name ?? '', $searchQuery) === false) {
                continue;
            }
            
            if (!empty($statusFilter) && ($project->status ?? '') !== $statusFilter) {
                continue;
            }
            
            if (!empty($companyFilter) && ($project->company_id ?? '') != $companyFilter) {
                continue;
            }
            
            // Get status info
            $status = $project->status ?? 'ready';
            $statusInfo = $statusMap[$status] ?? ['label' => 'UNKNOWN', 'color' => 'bg-gray-600'];
            
            // Calculate project metrics
            $totalTasks = 0;
            $completedTasks = 0;
            $totalTime = 0;
            $totalBillable = 0;
            $fileCount = 0;
            
            if (isset($project->tasks) && !empty($project->tasks)) {
                $totalTasks = count($project->tasks);
                foreach ($project->tasks as $task) {
                    if (isset($task->status_id) && $task->status_id == 6) { // Completed status
                        $completedTasks++;
                    }
                    $totalTime += isset($task->time_spent) ? (int)$task->time_spent : 0;
                    
                    // Calculate billable amount
                    if (isset($task->is_hourly) && $task->is_hourly && 
                        isset($task->hourly_rate) && isset($task->billable_time)) {
                        $billableAmount = ($task->hourly_rate * $task->billable_time) / 3600; // Convert seconds to hours
                        $totalBillable += $billableAmount;
                    }
                    
                    // Count files if the property exists
                    if (isset($task->files) && is_array($task->files)) {
                        $fileCount += count($task->files);
                    }
                }
            }
            ?>
            <div class="text-white">
                <div class="py-2">
                    <div class="flex justify-between align-center w-full">
                        <div class="flex items-center">
                            <div class="w-1 h-12 <?= $statusInfo['color'] ?> mr-4"></div>
                            <h2 class="inline-block w-text-lg font-medium">
                                <a href="/projects/view/<?= $project->id ?>" class="hover:text-blue-500 text-nowrap"><?= htmlspecialchars($project->name ?? '') ?></a>
                            </h2>
                            <span class="ml-4 px-4 py-2 text-xs rounded-full <?= $statusInfo['color'] ?> bg-opacity-20">
                                <?= htmlspecialchars($statusInfo['label']) ?>
                            </span>
                            <?php if (isset($project->company_name) && !empty($project->company_name)): ?>
                                <span class="ml-4 text-gray-400 text-nowrap"><?= htmlspecialchars($project->company_name) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($_SESSION['user']['permissions']) && in_array('create_tasks', $_SESSION['user']['permissions'])): ?>
                            <a href="/tasks/create/project/<?= $project->id ?>" class="px-3 py-1 text-sm text-white text-sm rounded-md hover:bg-indigo-700 flex items-center">
                                + New Task
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                    <?php if ($viewByFilter === 'tasks' && isset($project->tasks) && !empty($project->tasks)): ?>
                        <!-- Tasks View -->
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr class="text-left text-sm text-gray-500 border-b border-gray-500">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <a href="/projects?view=table&sort=title&dir=<?= ($sortField === 'title' && $sortDirection === 'asc') ? 'desc' : 'asc' ?><?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?><?= !empty($companyFilter) ? '&company_id=' . urlencode($companyFilter) : '' ?><?= !empty($viewByFilter) ? '&by=' . urlencode($viewByFilter) : '' ?>" class="flex items-center">
                                            Task
                                            <?php if ($sortField === 'title'): ?>
                                                <?= $sortDirection === 'asc' ? '↑' : '↓' ?>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Owner</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <a href="/projects?view=table&sort=due_date&dir=<?= ($sortField === 'due_date' && $sortDirection === 'asc') ? 'desc' : 'asc' ?><?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?><?= !empty($companyFilter) ? '&company_id=' . urlencode($companyFilter) : '' ?><?= !empty($viewByFilter) ? '&by=' . urlencode($viewByFilter) : '' ?>" class="flex items-center">
                                            Due Date
                                            <?php if ($sortField === 'due_date'): ?>
                                                <?= $sortDirection === 'asc' ? '↑' : '↓' ?>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actual Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Hourly rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Billable amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Files</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-600">
                                <?php foreach ($project->tasks as $task): ?>
                                    <?php
                                    // Priority level mapping
                                    $priorityLevel = 0;
                                    if (isset($task->priority)) {
                                        switch ($task->priority) {
                                            case 'high': $priorityLevel = 4; break;
                                            case 'medium': $priorityLevel = 3; break;
                                            case 'low': $priorityLevel = 2; break;
                                            case 'none': $priorityLevel = 1; break;
                                        }
                                    }
                                    
                                    // Status color
                                    $taskStatusColor = 'bg-blue-100 text-blue-800';
                                    if (isset($task->status_id)) {
                                        switch ($task->status_id) {
                                            case 6: $taskStatusColor = 'bg-green-100 text-green-800'; break; // Completed
                                            case 3: $taskStatusColor = 'bg-purple-100 text-purple-800'; break; // On Hold
                                            case 5: $taskStatusColor = 'bg-gray-100 text-gray-800'; break; // Closed
                                            case 7: $taskStatusColor = 'bg-red-100 text-red-800'; break; // Cancelled
                                        }
                                    }

                                    // Get task status text
                                    $taskStatus = 'UNKNOWN';
                                    if (isset($task->status)) {
                                        switch(strtolower($task->status)) {
                                            case 'open': $taskStatus = "OPEN"; break;
                                            case 'in_progress': $taskStatus = "IN PROGRESS"; break;
                                            case 'on_hold': $taskStatus = "ON HOLD"; break;
                                            case 'in_review': $taskStatus = "IN REVIEW"; break;
                                            case 'closed': $taskStatus = "CLOSED"; break;
                                            case 'completed': $taskStatus = "COMPLETED"; break;
                                        }
                                    }
                                    
                                    // Calculate billable amount
                                    $billableAmount = 0;
                                    if (isset($task->is_hourly) && $task->is_hourly && 
                                        isset($task->hourly_rate) && isset($task->billable_time)) {
                                        $billableAmount = ($task->hourly_rate * $task->billable_time) / 3600;
                                    }
                                    ?>
                                    <tr>
                                        <td class="pr-6 px-0 py-0">
                                            <div class="w-1 h-12 bg-purple-500"></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="/tasks/view/<?= isset($task->id) ? $task->id : '#' ?>" class="text-white hover:text-blue-400"><?= htmlspecialchars($task->title ?? 'Untitled Task') ?></a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex -space-x-2">
                                                <?php if (isset($task->assigned_to) && !empty($task->assigned_to)): ?>
                                                    <span class="text-white"><?= htmlspecialchars($task->first_name ?? '') ?> <?= htmlspecialchars($task->last_name ?? '') ?></span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">Unassigned</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex text-yellow-400">
                                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                                    <svg class="w-5 h-5" fill="<?= $i <= $priorityLevel ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                    </svg>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 text-sm <?= $taskStatusColor ?> rounded-full">
                                                <?= $taskStatus ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if (isset($task->due_date) && !empty($task->due_date)): ?>
                                                <?php
                                                $dueDate = strtotime($task->due_date);
                                                $today = strtotime('today');
                                                $timeLeftClass = 'bg-green-100 text-green-800';
                                                
                                                if ($dueDate < $today && (isset($task->status_id) && $task->status_id != 6)) {
                                                    $timeLeftClass = 'bg-red-100 text-red-800';
                                                } elseif ($dueDate <= strtotime('+3 days') && (isset($task->status_id) && $task->status_id != 6)) {
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
                                            <?php if (isset($task->time_spent) && !empty($task->time_spent)): ?>
                                                <?= gmdate('H:i:s', $task->time_spent) ?>
                                            <?php else: ?>
                                                0:00:00
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-white">
                                            <?php if (isset($task->is_hourly) && $task->is_hourly && isset($task->hourly_rate)): ?>
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
                                <?php endforeach; ?>
                                
                                <!-- Project Summary Row -->
                                <tr class="bg-gray-700">
                                    <td colspan="6" class="px-6 py-1 text-white">
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
                    
                    <?php elseif ($viewByFilter === 'epics' && isset($project->epics) && !empty($project->epics)): ?>
                        <!-- Epic View -->
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Epics</h3>
                            <div class="space-y-3">
                                <?php foreach ($project->epics as $epic): ?>
                                    <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-md">
                                        <div class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($epic->title ?? 'Untitled Epic') ?></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?= htmlspecialchars($epic->description ?? '') ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    
                    <?php elseif ($viewByFilter === 'milestones' && isset($project->milestones) && !empty($project->milestones)): ?>
                        <!-- Milestone View -->
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Milestones</h3>
                            <div class="space-y-3">
                                <?php foreach ($project->milestones as $milestone): ?>
                                    <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-md">
                                        <div class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($milestone->title ?? 'Untitled Milestone') ?></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <?php if (isset($milestone->due_date)): ?>
                                                <span class="inline-block bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2 py-1 text-xs rounded-full">
                                                    Due: <?= date('M j, Y', strtotime($milestone->due_date)) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    
                    <?php elseif ($viewByFilter === 'sprint' && isset($project->sprints) && !empty($project->sprints)): ?>
                        <!-- Sprint View -->
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Sprints</h3>
                            <div class="space-y-3">
                                <?php foreach ($project->sprints as $sprint): ?>
                                    <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-md">
                                        <div class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($sprint->name ?? 'Untitled Sprint') ?></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <?php 
                                            $startDate = isset($sprint->start_date) ? date('M j', strtotime($sprint->start_date)) : '';
                                            $endDate = isset($sprint->end_date) ? date('M j, Y', strtotime($sprint->end_date)) : '';
                                            $dateRange = '';
                                            if ($startDate && $endDate) {
                                                $dateRange = "$startDate - $endDate";
                                            }
                                            
                                            if ($dateRange):
                                            ?>
                                                <span class="inline-block bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2 py-1 text-xs rounded-full">
                                                    <?= $dateRange ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    
                    <?php else: ?>
                        <!-- Project Overview (default view) -->
                        <div class="p-4">
                            <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-6">
                                <div class="flex-1">
                                    <?php if (isset($project->description)): ?>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Description</h3>
                                        <p class="text-gray-700 dark:text-gray-300"><?= nl2br(htmlspecialchars($project->description)) ?></p>
                                    <?php else: ?>
                                        <p class="text-gray-500 dark:text-gray-400">No description available.</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="w-full md:w-80">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Project Details</h3>
                                    <div class="bg-gray-100 dark:bg-gray-700 rounded-md p-4">
                                        <div class="space-y-3">
                                            <?php if (isset($project->start_date)): ?>
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400 text-sm">Start Date:</span>
                                                    <span class="text-gray-900 dark:text-white block mt-1"><?= date('M j, Y', strtotime($project->start_date)) ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($project->end_date)): ?>
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400 text-sm">End Date:</span>
                                                    <span class="text-gray-900 dark:text-white block mt-1"><?= date('M j, Y', strtotime($project->end_date)) ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400 text-sm">Tasks:</span>
                                                <span class="text-gray-900 dark:text-white block mt-1"><?= $totalTasks ?> (<?= $completedTasks ?> completed)</span>
                                            </div>
                                            
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400 text-sm">Total Time:</span>
                                                <span class="text-gray-900 dark:text-white block mt-1"><?= gmdate('H:i:s', $totalTime) ?></span>
                                            </div>
                                            
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400 text-sm">Billable Amount:</span>
                                                <span class="text-gray-900 dark:text-white block mt-1">$<?= number_format($totalBillable, 2) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-md p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No projects found</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">
                Get started by creating your first project or adjust your search filters.
            </p>
            <?php if (isset($_SESSION['user']['permissions']) && in_array('create_projects', $_SESSION['user']['permissions'])): ?>
                <div class="mt-6">
                    <a href="/projects/create" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        + Create Project
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
    <div class="mt-6">
        <nav class="flex justify-center items-center space-x-4">
            <?php if (isset($page) && $page > 1): ?>
                <a href="/projects/page/<?= $page - 1 ?>?view=table<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?><?= !empty($companyFilter) ? '&company_id=' . urlencode($companyFilter) : '' ?><?= !empty($viewByFilter) ? '&by=' . urlencode($viewByFilter) : '' ?>" class="px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                    &laquo; Previous
                </a>
            <?php endif; ?>
            
            <span class="text-gray-700 dark:text-gray-300">
                Page <?= $page ?? 1 ?> of <?= $totalPages ?>
            </span>
            
            <?php if (isset($page) && $page < $totalPages): ?>
                <a href="/projects/page/<?= $page + 1 ?>?view=table<?= !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '' ?><?= !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : '' ?><?= !empty($companyFilter) ? '&company_id=' . urlencode($companyFilter) : '' ?><?= !empty($viewByFilter) ? '&by=' . urlencode($viewByFilter) : '' ?>" class="px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Next &raquo;
                </a>
            <?php endif; ?>
        </nav>
    </div>
<?php endif; ?>

<!-- JavaScript for interactive elements -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete confirmation function
    window.confirmDelete = function(projectId, projectName) {
        if (confirm(`Are you sure you want to delete the project "${projectName}"? This action cannot be undone.`)) {
            window.location.href = `/projects/delete/${projectId}`;
        }
    };
});
</script>