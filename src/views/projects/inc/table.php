<?php
//file: Views/Projects/inc/table.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

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

// Apply filters for search and sorting
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$companyFilter = isset($_GET['company_id']) ? $_GET['company_id'] : '';
$viewByFilter = isset($_GET['by']) ? $_GET['by'] : 'tasks';
$sortField = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sortDirection = isset($_GET['dir']) && $_GET['dir'] === 'asc' ? 'asc' : 'desc';

// Function to format time in seconds to a more readable format
function formatTimeInSeconds($seconds) {
    if (!$seconds) return '0h 0m';
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return "{$hours}h {$minutes}m";
}
?>

<!-- Project Table List -->
<div class="space-y-8">
    <?php if (!empty($projects)): ?>
        <?php 
        $filteredProjects = $projects;
        
        // Apply search filtering if needed
        if (!empty($searchQuery)) {
            $filteredProjects = array_filter($projects, function($project) use ($searchQuery) {
                return (
                    stripos($project->name ?? '', $searchQuery) !== false || 
                    stripos($project->company_name ?? '', $searchQuery) !== false ||
                    stripos($project->description ?? '', $searchQuery) !== false
                );
            });
        }
        
        // Apply status filtering if needed
        if (!empty($statusFilter)) {
            $filteredProjects = array_filter($filteredProjects, function($project) use ($statusFilter) {
                return $project->status_id == $statusFilter;
            });
        }
        
        // Apply company filtering if needed
        if (!empty($companyFilter)) {
            $filteredProjects = array_filter($filteredProjects, function($project) use ($companyFilter) {
                return $project->company_id == $companyFilter;
            });
        }
        
        foreach ($filteredProjects as $project): 
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
                    if (isset($task->is_hourly) && $task->is_hourly && 
                        isset($task->hourly_rate) && isset($task->billable_time)) {
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
                            <?php if (isset($_SESSION['user']['permissions']) && in_array('create_tasks', $_SESSION['user']['permissions'])): ?>
                                <a href="/tasks/create?project_id=<?= $project->id ?>" class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    New Task
                                </a>
                            <?php endif; ?>
                            
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
                
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
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
                                                            <?= htmlspecialchars($task->first_name ?? '') ?> <?= htmlspecialchars($task->last_name ?? '') ?>
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
                                                    <?= formatTimeInSeconds($task->time_spent ?? 0) ?>
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
                                            Total: <?= formatTimeInSeconds($totalTime) ?>
                                        </td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    <?php elseif ($viewByFilter === 'milestones' && isset($project->milestones) && !empty($project->milestones)): ?>
                        <!-- Milestone View -->
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Milestones</h3>
                            <div class="space-y-4">
                                <?php foreach ($project->milestones as $milestone): ?>
                                    <?php
                                    $statusClass = '';
                                    switch ($milestone->status_id) {
                                        case 1: $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'; break;
                                        case 2: $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; break;
                                        case 3: $statusClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                        default: $statusClass = 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                    }
                                    ?>
                                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="text-base font-medium text-gray-900 dark:text-white">
                                                    <a href="/milestones/view/<?= $milestone->id ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                                        <?= htmlspecialchars($milestone->title) ?>
                                                    </a>
                                                </h4>
                                                <?php if (!empty($milestone->description)): ?>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                                    <?= htmlspecialchars($milestone->description) ?>
                                                </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                                    <?= isset($milestone->status_name) ? htmlspecialchars($milestone->status_name) : 'Unknown' ?>
                                                </span>
                                                <?php if (isset($milestone->due_date) && !empty($milestone->due_date)): ?>
                                                <span class="ml-3 text-xs text-gray-500 dark:text-gray-400">
                                                    Due: <?= date('M j, Y', strtotime($milestone->due_date)) ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
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
                                                    <?= formatTimeInSeconds($totalTime) ?>
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
</div>

<!-- Pagination -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
    <div class="mt-6 flex justify-center">
        <nav class="inline-flex rounded-md shadow">
            <?php if ($page > 1): ?>
                <a href="/projects/page/<?= $page - 1 ?>?view=<?= $activeTab ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-l-md hover:bg-gray-50 dark:hover:bg-gray-700">
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
                    <a href="/projects/page/<?= $i ?>?view=<?= $activeTab ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="/projects/page/<?= $page + 1 ?>?view=<?= $activeTab ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-r-md hover:bg-gray-50 dark:hover:bg-gray-700">
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
// Delete confirmation function
function confirmDelete(projectId, projectName) {
    if (confirm(`Are you sure you want to delete the project "${projectName}"? This action cannot be undone.`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/projects/delete/${projectId}`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = 'csrf_token';
        csrfToken.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdownButtons = document.querySelectorAll('.dropdown button');
    dropdownButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.parentElement.querySelector('.dropdown-menu');
            
            // Close all other menus first
            document.querySelectorAll('.dropdown-menu').forEach(otherMenu => {
                if (otherMenu !== menu && !otherMenu.classList.contains('hidden')) {
                    otherMenu.classList.add('hidden');
                }
            });
            
            // Toggle this menu
            if (menu) {
                menu.classList.toggle('hidden');
            }
        });
    });
    
    // Close when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    });
});
</script>