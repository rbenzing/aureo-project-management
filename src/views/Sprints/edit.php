<?php
// file: Views/Sprints/edit.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sprint - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6 flex-grow">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <!-- Edit Sprint Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <div class="mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Sprint</h1>
                <div class="flex space-x-2">
                    <a href="/sprints/view/<?= htmlspecialchars($sprint->id ?? '') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Sprint
                    </a>
                    <a href="/sprints/project/<?= htmlspecialchars($project->id ?? '') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Sprints
                    </a>
                </div>
            </div>

            <!-- Sprint Info Summary -->
            <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="flex items-center mb-2">
                    <div class="h-8 w-8 rounded-md bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-3">
                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <?= htmlspecialchars($sprint->name ?? 'Sprint') ?>
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Project: <a href="/projects/view/<?= $project->id ?? 0 ?>" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                <?= htmlspecialchars($project->name ?? 'Project') ?>
                            </a>
                            <span class="mx-2">•</span>
                            <span class="px-2 py-0.5 text-xs rounded-full <?= getSprintStatusClass($sprint->status_id ?? 1) ?>">
                                <?= getSprintStatusLabel($sprint->status_id ?? 1) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <form action="/sprints/update/<?= htmlspecialchars($sprint->id ?? '') ?>" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars($sprint->id ?? '') ?>">

                <!-- Sprint Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sprint Name <span class="text-red-600">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?= htmlspecialchars($_SESSION['form_data']['name'] ?? $sprint->name ?? '') ?>"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white" 
                        required
                    >
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                    ><?= htmlspecialchars($_SESSION['form_data']['description'] ?? $sprint->description ?? '') ?></textarea>
                </div>

                <!-- Sprint Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date <span class="text-red-600">*</span></label>
                        <input 
                            type="date" 
                            id="start_date" 
                            name="start_date" 
                            value="<?= htmlspecialchars($_SESSION['form_data']['start_date'] ?? $sprint->start_date ?? '') ?>"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white" 
                            required
                        >
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date <span class="text-red-600">*</span></label>
                        <input 
                            type="date" 
                            id="end_date" 
                            name="end_date" 
                            value="<?= htmlspecialchars($_SESSION['form_data']['end_date'] ?? $sprint->end_date ?? '') ?>"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white" 
                            required
                        >
                    </div>
                </div>

                <!-- Sprint Status -->
                <div>
                    <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-600">*</span></label>
                    <select 
                        id="status_id" 
                        name="status_id" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                        required
                    >
                        <?php foreach ($statuses ?? [] as $status): ?>
                            <option 
                                value="<?= $status->id ?>" 
                                <?= isset($_SESSION['form_data']['status_id']) && $_SESSION['form_data']['status_id'] == $status->id ? 'selected' : '' ?>
                                <?= (!isset($_SESSION['form_data']['status_id']) && isset($sprint->status_id) && $sprint->status_id == $status->id) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($status->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <?php if (isset($sprint->status_id) && $sprint->status_id == 2): // If sprint is active ?>
                    <p class="mt-1 text-sm text-yellow-600 dark:text-yellow-400">
                        <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Changing status from Active may affect task scheduling.
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Sprint Tasks Section -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sprint Tasks</label>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                        <div class="mb-3 flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0">
                            <input type="text" id="task-search" placeholder="Search tasks..." class="w-full md:w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-600 dark:text-white">
                            <div class="flex md:ml-auto">
                                <button type="button" id="select-all-tasks" class="mr-2 px-3 py-2 text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">Select All</button>
                                <button type="button" id="deselect-all-tasks" class="px-3 py-2 text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Deselect All</button>
                            </div>
                        </div>

                        <div class="max-h-64 overflow-y-auto space-y-2 pr-2">
                            <!-- Sprint Tasks -->
                            <?php if (!empty($sprintTasks)): ?>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Currently in Sprint:</h3>
                                <?php foreach ($sprintTasks as $task): ?>
                                    <div class="task-item flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md">
                                        <input 
                                            type="checkbox" 
                                            id="task-<?= $task->id ?>" 
                                            name="tasks[]" 
                                            value="<?= $task->id ?>"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            checked
                                        >
                                        <label for="task-<?= $task->id ?>" class="ml-2 flex-1 cursor-pointer">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($task->title) ?></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                <span class="px-1.5 py-0.5 text-xs rounded-full mr-2 <?= getTaskStatusClass($task->status_name ?? 'Open') ?>">
                                                    <?= htmlspecialchars($task->status_name ?? 'Open') ?>
                                                </span>
                                                <?php if (!empty($task->due_date)): ?>
                                                    <span>Due: <?= date('M j, Y', strtotime($task->due_date)) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Available Project Tasks (not in sprint) -->
                            <?php if (!empty($projectTasks)): ?>
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-4 mb-2">Available Project Tasks:</h3>
                                <?php 
                                // Skip tasks already in sprint
                                $sprintTaskIds = array_map(function($task) { 
                                    return $task->id; 
                                }, $sprintTasks ?? []);
                                
                                foreach ($projectTasks as $task): 
                                    // Skip if task is already in sprint
                                    if (in_array($task->id, $sprintTaskIds)) continue;
                                ?>
                                    <div class="task-item flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md">
                                        <input 
                                            type="checkbox" 
                                            id="task-<?= $task->id ?>" 
                                            name="tasks[]" 
                                            value="<?= $task->id ?>"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        >
                                        <label for="task-<?= $task->id ?>" class="ml-2 flex-1 cursor-pointer">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($task->title) ?></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                <span class="px-1.5 py-0.5 text-xs rounded-full mr-2 <?= getTaskStatusClass($task->status_name ?? 'Open') ?>">
                                                    <?= htmlspecialchars($task->status_name ?? 'Open') ?>
                                                </span>
                                                <?php if (!empty($task->due_date)): ?>
                                                    <span>Due: <?= date('M j, Y', strtotime($task->due_date)) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if (empty($sprintTasks) && empty($projectTasks)): ?>
                                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    No tasks available for this sprint.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a 
                        href="/sprints/view/<?= htmlspecialchars($sprint->id ?? '') ?>" 
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-400 dark:hover:bg-gray-600"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Update Sprint
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <?php
    // Helper function for sprint status display
    function getSprintStatusClass($statusId) {
        return match((int)$statusId) {
            1 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', // Planning
            2 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', // Active
            3 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300', // Delayed
            4 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300', // Completed
            5 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Cancelled
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        };
    }

    function getSprintStatusLabel($statusId) {
        return match((int)$statusId) {
            1 => 'Planning',
            2 => 'Active',
            3 => 'Delayed',
            4 => 'Completed',
            5 => 'Cancelled',
            default => 'Unknown'
        };
    }

    // Helper function for task status display
    function getTaskStatusClass($statusName) {
        return match($statusName) {
            'Open' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'On Hold' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'In Review' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            'Closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'Completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        };
    }
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Task search functionality
            const searchInput = document.getElementById('task-search');
            const taskItems = document.querySelectorAll('.task-item');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchText = this.value.toLowerCase();
                    
                    taskItems.forEach(item => {
                        const taskTitle = item.querySelector('label div:first-child').textContent.toLowerCase();
                        if (taskTitle.includes(searchText)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
            
            // Select all tasks
            const selectAllBtn = document.getElementById('select-all-tasks');
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    const visibleTaskCheckboxes = Array.from(taskItems)
                        .filter(item => item.style.display !== 'none')
                        .map(item => item.querySelector('input[type="checkbox"]'));
                    
                    visibleTaskCheckboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                });
            }
            
            // Deselect all tasks
            const deselectAllBtn = document.getElementById('deselect-all-tasks');
            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', function() {
                    const visibleTaskCheckboxes = Array.from(taskItems)
                        .filter(item => item.style.display !== 'none')
                        .map(item => item.querySelector('input[type="checkbox"]'));
                    
                    visibleTaskCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                });
            }
            
            // Date validation
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            if (startDateInput && endDateInput) {
                // Ensure end date is not before start date
                startDateInput.addEventListener('change', function() {
                    if (endDateInput.value && this.value > endDateInput.value) {
                        endDateInput.value = this.value;
                    }
                });
                
                endDateInput.addEventListener('change', function() {
                    if (startDateInput.value && this.value < startDateInput.value) {
                        alert('End date cannot be earlier than start date.');
                        this.value = startDateInput.value;
                    }
                });
            }
        });
    </script>
</body>
</html>