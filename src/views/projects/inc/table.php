<?php
//file: Views/Projects/inc/table.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Utils\Time;

// Define status labels and colors
$statusMap = [
    1 => [
        'label' => 'READY',
        'color' => 'bg-blue-600'
    ],
    2 => [
        'label' => 'IN PROGRESS',
        'color' => 'bg-yellow-500'
    ],
    3 => [
        'label' => 'COMPLETED',
        'color' => 'bg-green-500'
    ],
    4 => [
        'label' => 'ON HOLD',
        'color' => 'bg-purple-500'
    ],
    6 => [
        'label' => 'DELAYED',
        'color' => 'bg-red-500'
    ],
    7 => [
        'label' => 'CANCELLED',
        'color' => 'bg-gray-500'
    ]
];

// Get filter parameters
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status_id']) ? (int)$_GET['status_id'] : '';
$companyFilter = isset($_GET['company_id']) ? (int)$_GET['company_id'] : '';
$viewByFilter = isset($_GET['by']) ? $_GET['by'] : 'tasks';
$sortField = isset($_GET['sort']) ? $_GET['sort'] : 'updated_at';
$sortDirection = isset($_GET['dir']) && $_GET['dir'] === 'asc' ? 'asc' : 'desc';
?>

<!-- Project Table List -->
<?php if (!empty($projects)): ?>
    <?php
    foreach ($projects as $project):
        // Get status info
        $statusId = $project->status_id ?? 1;
        $statusInfo = $statusMap[$statusId] ?? ['label' => 'UNKNOWN', 'color' => 'bg-gray-500'];

        // Calculate project metrics
        $totalTasks = 0;
        $completedTasks = 0;
        $totalTime = 0;
        $totalBillable = 0;
        $fileCount = 0;

        if (isset($project->tasks) && is_array($project->tasks)) {
            $totalTasks = count($project->tasks);
            foreach ($project->tasks as $task) {
                if (isset($task->status_id) && $task->status_id == 6) { // Completed status is 6
                    $completedTasks++;
                }
                $totalTime += isset($task->time_spent) ? (int)$task->time_spent : 0;

                // Calculate billable amount
                if (
                    isset($task->is_hourly) && $task->is_hourly &&
                    isset($task->hourly_rate) && isset($task->billable_time)
                ) {
                    $billableAmount = ($task->hourly_rate * $task->billable_time) / 3600; // Convert seconds to hours
                    $totalBillable += $billableAmount;
                }
            }
        }
    ?>
        <div class="text-gray-900 dark:text-white">
            <div class="py-2">
                <div class="flex justify-between items-center w-full">
                    <div class="flex items-center">
                        <div class="w-1 h-12 <?= $statusInfo['color'] ?> mr-4"></div>
                        <!-- Chevron for toggle -->
                        <button type="button" class="project-toggle mr-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 project-chevron transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <h2 class="inline-block text-lg font-medium">
                            <a href="/projects/view/<?= $project->id ?>" class="hover:text-blue-500 dark:hover:text-blue-400"><?= htmlspecialchars($project->name ?? '') ?></a>
                        </h2>
                        <span class="ml-4 px-3 py-1 text-xs rounded-full bg-opacity-20 text-white <?= $statusInfo['color'] ?>">
                            <?= $statusInfo['label'] ?>
                        </span>
                        <?php if (isset($project->company_name) && !empty($project->company_name)): ?>
                            <span class="ml-4 text-gray-500 dark:text-gray-400"><?= htmlspecialchars($project->company_name) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="dropdown relative">
                            <button class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                </svg>
                            </button>
                            <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-10">
                                <div class="py-1">
                                    <a href="/projects/view/<?= $project->id ?>" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        View Details
                                    </a>
                                    <?php if (isset($_SESSION['user']['permissions']) && in_array('edit_projects', $_SESSION['user']['permissions'])): ?>
                                        <a href="/projects/edit/<?= $project->id ?>" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Edit Project
                                        </a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['user']['permissions']) && in_array('create_tasks', $_SESSION['user']['permissions'])): ?>
                                        <a href="/tasks/create?project_id=<?= $project->id ?>" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Add Task
                                        </a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['user']['permissions']) && in_array('create_milestones', $_SESSION['user']['permissions'])): ?>
                                        <a href="/milestones/create?project_id=<?= $project->id ?>" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Add Milestone
                                        </a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['user']['permissions']) && in_array('delete_projects', $_SESSION['user']['permissions'])): ?>
                                        <a href="#" onclick="confirmDelete(<?= $project->id ?>, '<?= htmlspecialchars(addslashes($project->name)) ?>')" class="block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            Delete Project
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg" data-project-details>
                <?php if ($viewByFilter === 'tasks'): ?>
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
                <?php elseif ($viewByFilter === 'sprints' && isset($project->sprints) && !empty($project->sprints)): ?>
                    <!-- Sprint View -->
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Sprints</h3>
                        <div class="space-y-6">
                            <?php foreach ($project->sprints as $sprint): ?>
                                <?php
                                // Get sprint tasks if not already loaded
                                if (!isset($sprint->tasks) || empty($sprint->tasks)) {
                                    $sprintTasks = (new \App\Models\Sprint())->getSprintTasks($sprint->id);
                                } else {
                                    $sprintTasks = $sprint->tasks;
                                }

                                // Calculate sprint progress
                                $totalTasks = count($sprintTasks);
                                $completedTasks = 0;
                                foreach ($sprintTasks as $task) {
                                    if (isset($task->status_id) && $task->status_id == 6) { // Completed status is 6
                                        $completedTasks++;
                                    }
                                }
                                $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

                                // Set sprint status class
                                $statusClass = '';
                                $statusId = $sprint->status_id ?? 1;
                                switch ($statusId) {
                                    case 1:
                                        $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                        break; // Planning
                                    case 2:
                                        $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                        break; // Active
                                    case 3:
                                        $statusClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                        break; // Completed
                                    case 4:
                                        $statusClass = 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                        break; // Cancelled
                                    case 5:
                                        $statusClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                        break; // Delayed
                                    default:
                                        $statusClass = 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                }
                                ?>
                                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                                    <div class="p-4 flex justify-between items-start border-b border-gray-200 dark:border-gray-700">
                                        <div>
                                            <h4 class="text-base font-medium text-gray-900 dark:text-white flex items-center">
                                                <a href="/sprints/view/<?= $sprint->id ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                                    <?= htmlspecialchars($sprint->name) ?>
                                                </a>
                                                <span class="ml-3 px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                                    <?= isset($sprint->status_name) ? htmlspecialchars($sprint->status_name) : 'Unknown' ?>
                                                </span>
                                            </h4>
                                            <?php if (!empty($sprint->description)): ?>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    <?= htmlspecialchars(substr($sprint->description, 0, 150)) ?>
                                                    <?= strlen($sprint->description) > 150 ? '...' : '' ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex flex-col items-end space-y-2">
                                            <div class="flex space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                                <?php if (isset($sprint->start_date) && !empty($sprint->start_date)): ?>
                                                    <span>Start: <?= date('M j, Y', strtotime($sprint->start_date)) ?></span>
                                                <?php endif; ?>
                                                <?php if (isset($sprint->end_date) && !empty($sprint->end_date)): ?>
                                                    <span>End: <?= date('M j, Y', strtotime($sprint->end_date)) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="w-48">
                                                <div class="flex items-center">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 mr-2"><?= $progressPercentage ?>%</span>
                                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $progressPercentage ?>%"></div>
                                                    </div>
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 text-right mt-1">
                                                    <?= $completedTasks ?>/<?= $totalTasks ?> tasks completed
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Display tasks associated with this sprint -->
                                    <?php if (!empty($sprintTasks)): ?>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                <thead class="bg-gray-50 dark:bg-gray-700">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Task</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assignee</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Priority</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                    <?php foreach ($sprintTasks as $task): ?>
                                                        <?php
                                                        $priorityLevel = isset($task->priority) ? $task->priority : 'none';
                                                        $priorityClasses = [
                                                            'high' => 'text-red-600 dark:text-red-400',
                                                            'medium' => 'text-yellow-600 dark:text-yellow-400',
                                                            'low' => 'text-blue-600 dark:text-blue-400',
                                                            'none' => 'text-gray-600 dark:text-gray-400'
                                                        ];
                                                        $priorityClass = $priorityClasses[$priorityLevel] ?? 'text-gray-600 dark:text-gray-400';

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
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-4 text-sm text-gray-500 dark:text-gray-400 italic">
                                            No tasks associated with this sprint.
                                        </div>
                                    <?php endif; ?>

                                    <!-- Sprint Actions (only shown for active sprints) -->
                                    <?php if ($statusId == 2): // Active sprint 
                                    ?>
                                        <div class="p-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-end space-x-3">
                                            <a href="/sprints/view/<?= $sprint->id ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                View Details
                                            </a>
                                            <?php if (isset($_SESSION['user']['permissions']) && in_array('edit_sprints', $_SESSION['user']['permissions'])): ?>
                                                <a href="/sprints/edit/<?= $sprint->id ?>" class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                    Manage Tasks
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php elseif ($viewByFilter === 'milestones' && isset($project->milestones) && !empty($project->milestones)): ?>
                    <!-- Milestone View -->
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Milestones</h3>
                        <div class="space-y-6">
                            <?php foreach ($project->milestones as $milestone): ?>
                                <?php
                                // Get milestone tasks if not already loaded
                                if (!isset($milestone->tasks) || empty($milestone->tasks)) {
                                    $milestoneTasks = (new \App\Models\Milestone())->getTasks($milestone->id);
                                } else {
                                    $milestoneTasks = $milestone->tasks;
                                }

                                $statusClass = '';
                                switch ($milestone->status_id) {
                                    case 1:
                                        $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                        break;
                                    case 2:
                                        $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                        break;
                                    case 3:
                                        $statusClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                        break;
                                    default:
                                        $statusClass = 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                }
                                ?>
                                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                                    <div class="p-4 flex justify-between items-start border-b border-gray-200 dark:border-gray-700">
                                        <div>
                                            <h4 class="text-base font-medium text-gray-900 dark:text-white">
                                                <a href="/milestones/view/<?= $milestone->id ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                                    <?= htmlspecialchars($milestone->title) ?>
                                                </a>
                                                <span class="ml-3 px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                                    <?= isset($milestone->status_name) ? htmlspecialchars($milestone->status_name) : 'Unknown' ?>
                                                </span>
                                            </h4>
                                            <?php if (!empty($milestone->description)): ?>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    <?= htmlspecialchars(substr($milestone->description, 0, 150)) ?>
                                                    <?= strlen($milestone->description) > 150 ? '...' : '' ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                            <?php if (isset($milestone->start_date) && !empty($milestone->start_date)): ?>
                                                <span>Start: <?= date('M j, Y', strtotime($milestone->start_date)) ?></span>
                                            <?php endif; ?>
                                            <?php if (isset($milestone->due_date) && !empty($milestone->due_date)): ?>
                                                <span>Due: <?= date('M j, Y', strtotime($milestone->due_date)) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Display tasks associated with this milestone -->
                                    <?php if (!empty($milestoneTasks)): ?>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                <thead class="bg-gray-50 dark:bg-gray-700">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Task</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assignee</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Priority</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                    <?php foreach ($milestoneTasks as $task): ?>
                                                        <?php
                                                        $priorityLevel = isset($task->priority) ? $task->priority : 'none';
                                                        $priorityClasses = [
                                                            'high' => 'text-red-600 dark:text-red-400',
                                                            'medium' => 'text-yellow-600 dark:text-yellow-400',
                                                            'low' => 'text-blue-600 dark:text-blue-400',
                                                            'none' => 'text-gray-600 dark:text-gray-400'
                                                        ];
                                                        $priorityClass = $priorityClasses[$priorityLevel] ?? 'text-gray-600 dark:text-gray-400';

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
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-4 text-sm text-gray-500 dark:text-gray-400 italic">
                                            No tasks associated with this milestone.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Project Overview (default view) -->
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-6">
                            <div class="flex-1">
                                <?php if (isset($project->description) && !empty($project->description)): ?>
                                    <h3 class="text-base font-medium text-gray-900 dark:text-white mb-2">Description</h3>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        <?= nl2br(htmlspecialchars($project->description)) ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No description available.</p>
                                <?php endif; ?>
                            </div>

                            <div class="w-full md:w-64">
                                <h3 class="text-base font-medium text-gray-900 dark:text-white mb-2">Project Details</h3>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-4">
                                    <div class="space-y-3 text-sm">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Owner:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">
                                                <?= isset($project->owner_firstname) ? htmlspecialchars($project->owner_firstname . ' ' . $project->owner_lastname) : 'Not assigned' ?>
                                            </span>
                                        </div>

                                        <?php if (isset($project->start_date) && !empty($project->start_date)): ?>
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">Start Date:</span>
                                                <span class="text-gray-900 dark:text-white ml-2">
                                                    <?= date('M j, Y', strtotime($project->start_date)) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isset($project->end_date) && !empty($project->end_date)): ?>
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">End Date:</span>
                                                <span class="text-gray-900 dark:text-white ml-2">
                                                    <?= date('M j, Y', strtotime($project->end_date)) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Tasks:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">
                                                <?= $totalTasks ?> (<?= $completedTasks ?> completed)
                                            </span>
                                        </div>

                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Total Time:</span>
                                            <span class="text-gray-900 dark:text-white ml-2">
                                                <?= Time::formatSeconds($totalTime) ?>
                                            </span>
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
                <a href="/projects/create" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Project
                </a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Pagination -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
    <div class="mt-6 flex justify-center">
        <nav class="inline-flex rounded-md shadow">
            <?php if ($page > 1): ?>
                <a href="<?= '/projects/page/' . ($page - 1) ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-l-md hover:bg-gray-50 dark:hover:bg-gray-700">
                    Previous
                </a>
            <?php else: ?>
                <span class="px-4 py-2 text-sm font-medium text-gray-500 bg-white dark:bg-gray-800 dark:text-gray-500 border border-gray-300 dark:border-gray-600 rounded-l-md cursor-not-allowed">
                    Previous
                </span>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 dark:bg-blue-900 dark:text-blue-200 border border-gray-300 dark:border-gray-600">
                        <?= $i ?>
                    </span>
                <?php else: ?>
                    <a href="<?= '/projects/page/' . $i ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= '/projects/page/' . ($page + 1) ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-r-md hover:bg-gray-50 dark:hover:bg-gray-700">
                    Next
                </a>
            <?php else: ?>
                <span class="px-4 py-2 text-sm font-medium text-gray-500 bg-white dark:bg-gray-800 dark:text-gray-500 border border-gray-300 dark:border-gray-600 rounded-r-md cursor-not-allowed">
                    Next
                </span>
            <?php endif; ?>
        </nav>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle dropdowns
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            const button = dropdown.querySelector('button');
            const menu = dropdown.querySelector('.dropdown-menu');

            if (button && menu) {
                button.addEventListener('click', function() {
                    let useForce = menu.classList.contains('hidden');
                    menu.classList.toggle('hidden', useForce);
                });

                // Close when clicking outside
                document.addEventListener('click', function(event) {
                    if (!dropdown.contains(event.target)) {
                        menu.classList.add('hidden');
                    }
                });
            }
        });

        // Handle project accordions
        const projectToggles = document.querySelectorAll('.project-toggle');
        console.log('Found', projectToggles.length, 'project toggles');

        const toggleAllBtn = document.getElementById('toggle-all-projects');
        const toggleAllText = toggleAllBtn ? toggleAllBtn.querySelector('.toggle-all-text') : null;

        // Check if we found everything we need
        if (!toggleAllBtn || !toggleAllText) {
            console.error('Toggle all button or text not found');
        }

        let allExpanded = true; // Start with all expanded

        // Function to toggle a single project
        function toggleProject(toggle) {
            const projectSection = toggle.closest('.text-gray-900');
            const detailsSection = projectSection.querySelector('[data-project-details]');
            const chevron = toggle.querySelector('.project-chevron');

            if (detailsSection) {
                if (detailsSection.classList.contains('hidden')) {
                    // Expanding
                    detailsSection.classList.remove('hidden');
                    chevron.style.transform = 'rotate(0deg)';
                } else {
                    // Collapsing
                    detailsSection.classList.add('hidden');
                    chevron.style.transform = 'rotate(-90deg)';
                }
            } else {
                console.error('Details section not found for project', projectSection);
            }
        }

        // Add click handler for each project toggle
        projectToggles.forEach(toggle => {
            // Set initial rotation
            const chevron = toggle.querySelector('.project-chevron');
            if (chevron) {
                chevron.style.transition = 'transform 0.2s ease';
            }

            // Initialize expanded state
            const projectSection = toggle.closest('.text-gray-900');
            const detailsSection = projectSection.querySelector('[data-project-details]');

            if (detailsSection) {
                console.log('Found details section for project');

                // Start expanded by default (don't add hidden class initially)
                // But set up the rotation for consistency
                if (chevron) {
                    chevron.style.transform = 'rotate(0deg)';
                }
            } else {
                console.error('Could not find details section for project');
            }

            toggle.addEventListener('click', function() {
                toggleProject(this);

                // Update the state of all expanded based on current visibility
                updateToggleAllState();
            });
        });

        // Function to toggle all projects
        function toggleAllProjects() {
            if (allExpanded) {
                // Collapse all
                projectToggles.forEach(toggle => {
                    const projectSection = toggle.closest('.text-gray-900');
                    const detailsSection = projectSection.querySelector('[data-project-details]');
                    const chevron = toggle.querySelector('.project-chevron');

                    if (detailsSection && !detailsSection.classList.contains('hidden')) {
                        detailsSection.classList.add('hidden');
                        chevron.style.transform = 'rotate(-90deg)';
                    }
                });
                toggleAllText.textContent = 'Expand All';
                allExpanded = false;
            } else {
                // Expand all
                projectToggles.forEach(toggle => {
                    const projectSection = toggle.closest('.text-gray-900');
                    const detailsSection = projectSection.querySelector('[data-project-details]');
                    const chevron = toggle.querySelector('.project-chevron');

                    if (detailsSection && detailsSection.classList.contains('hidden')) {
                        detailsSection.classList.remove('hidden');
                        chevron.style.transform = 'rotate(0deg)';
                    }
                });
                toggleAllText.textContent = 'Collapse All';
                allExpanded = true;
            }
        }

        // Function to check if all projects are expanded or collapsed
        function updateToggleAllState() {
            if (!toggleAllText) return;

            let expandedCount = 0;

            projectToggles.forEach(toggle => {
                const projectSection = toggle.closest('.text-gray-900');
                const detailsSection = projectSection.querySelector('[data-project-details]');

                if (detailsSection && !detailsSection.classList.contains('hidden')) {
                    expandedCount++;
                }
            });

            // Update the toggle all button text
            if (expandedCount === 0) {
                toggleAllText.textContent = 'Expand All';
                allExpanded = false;
            } else if (expandedCount === projectToggles.length) {
                toggleAllText.textContent = 'Collapse All';
                allExpanded = true;
            }
        }

        // Add click handler for toggle all button
        if (toggleAllBtn) {
            toggleAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleAllProjects();
            });
        } else {
            console.error('Toggle all button not found!');
        }
    });
</script>