<?php
//file: Views/Tasks/edit.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
use App\Utils\Time;

// Load form data from session if available (for validation errors)
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Determine if we are marking the task as complete
$markComplete = isset($_GET['mark_complete']) && $_GET['mark_complete'] == 1;

// Format time for display
function formatTimeInput($seconds) {
    if (!$seconds) return '';
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    return $hours . ':' . str_pad((string)$minutes, 2, '0', STR_PAD_LEFT);
}

// Get priority and status classes
function getPriorityClass($priority) {
    switch (strtolower($priority)) {
        case 'high':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        case 'medium':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'low':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200';
    }
}

function getStatusClass($statusId) {
    $statusMap = [
        1 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', // Open
        2 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', // In Progress
        3 => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200', // On Hold
        4 => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200', // In Review
        5 => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200', // Closed
        6 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', // Completed
        7 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', // Cancelled
    ];
    
    return $statusMap[$statusId] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
}

$pageTitle = $markComplete ? 'Complete Task' : 'Edit Task';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= htmlspecialchars($task->title) ?> - <?= htmlspecialchars(Config::get('company_name', 'Slimbooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= $pageTitle ?></h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    <a href="/tasks/view/<?= $task->id ?>" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        <?= htmlspecialchars($task->title) ?>
                    </a>
                    <?php if (isset($project->name)): ?>
                        • <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <a href="/projects/view/<?= $project->id ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                <?= htmlspecialchars($project->name) ?>
                            </a>
                        </span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="/tasks/view/<?= $task->id ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <form id="task-form" method="POST" action="/tasks/update" class="divide-y divide-gray-200 dark:divide-gray-700">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="id" value="<?php echo $task->id; ?>">
                
                <?php if ($markComplete): ?>
                    <input type="hidden" name="status_id" value="6"> <!-- Set to Completed -->
                    <input type="hidden" name="complete_date" value="<?= date('Y-m-d') ?>">
                <?php endif; ?>

                <!-- Task Details -->
                <div class="px-4 py-5 sm:p-6">
                    <?php if ($markComplete): ?>
                        <div class="bg-green-50 dark:bg-green-900 border-l-4 border-green-400 dark:border-green-600 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700 dark:text-green-300">
                                        You are marking this task as complete. Add any final notes below before confirming.
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Task Title <span class="text-red-500">*</span></label>
                                <input type="text" id="title" name="title" required
                                    value="<?= htmlspecialchars($formData['title'] ?? $task->title) ?>"
                                    <?= $markComplete ? 'readonly' : '' ?>
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm <?= $markComplete ? 'bg-gray-100 dark:bg-gray-800' : '' ?>">
                            </div>

                            <!-- Project -->
                            <div>
                                <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project <span class="text-red-500">*</span></label>
                                <select id="project_id" name="project_id" required
                                    <?= $markComplete ? 'disabled' : '' ?>
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm <?= $markComplete ? 'bg-gray-100 dark:bg-gray-800' : '' ?>">
                                    <option value="">Select a project</option>
                                    <?php foreach ($projects['records'] as $p): ?>
                                        <option value="<?= $p->id; ?>" <?= ($formData['project_id'] ?? $task->project_id) == $p->id ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($p->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($markComplete): ?>
                                    <input type="hidden" name="project_id" value="<?= $task->project_id ?>">
                                <?php endif; ?>
                            </div>

                            <!-- Assignee -->
                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigned To</label>
                                <select id="assigned_to" name="assigned_to" 
                                    <?= $markComplete ? 'disabled' : '' ?>
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm <?= $markComplete ? 'bg-gray-100 dark:bg-gray-800' : '' ?>">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user->id; ?>" <?= ($formData['assigned_to'] ?? $task->assigned_to) == $user->id ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($user->first_name . ' ' . $user->last_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($markComplete): ?>
                                    <input type="hidden" name="assigned_to" value="<?= $task->assigned_to ?>">
                                <?php endif; ?>
                            </div>

                            <!-- Priority & Status -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Priority -->
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                                    <select id="priority" name="priority" required
                                        <?= $markComplete ? 'disabled' : '' ?>
                                        class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm <?= $markComplete ? 'bg-gray-100 dark:bg-gray-800' : '' ?>">
                                        <option value="none" <?= ($formData['priority'] ?? $task->priority) === 'none' ? 'selected' : ''; ?>>None</option>
                                        <option value="low" <?= ($formData['priority'] ?? $task->priority) === 'low' ? 'selected' : ''; ?>>Low</option>
                                        <option value="medium" <?= ($formData['priority'] ?? $task->priority) === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="high" <?= ($formData['priority'] ?? $task->priority) === 'high' ? 'selected' : ''; ?>>High</option>
                                    </select>
                                    <?php if ($markComplete): ?>
                                        <input type="hidden" name="priority" value="<?= $task->priority ?>">
                                    <?php endif; ?>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <select id="status_id" name="status_id" required
                                        <?= $markComplete ? 'disabled' : '' ?>
                                        class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm <?= $markComplete ? 'bg-gray-100 dark:bg-gray-800' : '' ?>">
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?= $status->id; ?>" <?= ($formData['status_id'] ?? $task->status_id) == $status->id ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($status->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Start Date -->
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                                    <input type="date" id="start_date" name="start_date"
                                        value="<?= htmlspecialchars($formData['start_date'] ?? $task->start_date ?? '') ?>"
                                        <?= $markComplete ? 'readonly' : '' ?>
                                        class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm <?= $markComplete ? 'bg-gray-100 dark:bg-gray-800' : '' ?>">
                                    <?php if ($markComplete): ?>
                                        <input type="hidden" name="start_date" value="<?= $task->start_date ?>">
                                    <?php endif; ?>
                                </div>

                                <!-- Due Date -->
                                <div>
                                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</label>
                                    <input type="date" id="due_date" name="due_date"
                                        value="<?= htmlspecialchars($formData['due_date'] ?? $task->due_date ?? '') ?>"
                                        <?= $markComplete ? 'readonly' : '' ?>
                                        class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm <?= $markComplete ? 'bg-gray-100 dark:bg-gray-800' : '' ?>">
                                    <?php if ($markComplete): ?>
                                        <input type="hidden" name="due_date" value="<?= $task->due_date ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea id="description" name="description" rows="5"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm"><?= htmlspecialchars($formData['description'] ?? $task->description ?? '') ?></textarea>
                            </div>

                            <!-- Time Tracking -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Time Tracking</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Estimated Time -->
                                    <div>
                                        <label for="estimated_time" class="block text-xs text-gray-500 dark:text-gray-400">Estimated Time (H:M)</label>
                                        <input type="text" id="estimated_time" name="estimated_time" placeholder="00:00"
                                            value="<?= formatTimeInput($formData['estimated_time'] ?? $task->estimated_time) ?>"
                                            class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm">
                                    </div>

                                    <!-- Time Spent -->
                                    <div>
                                        <label for="time_spent" class="block text-xs text-gray-500 dark:text-gray-400">Time Spent (H:M)</label>
                                        <input type="text" id="time_spent" name="time_spent" placeholder="00:00" 
                                            value="<?= formatTimeInput($formData['time_spent'] ?? $task->time_spent) ?>"
                                            class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>

                            <!-- Billing Settings -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Billing Settings</h3>
                                <div class="flex items-start mb-3">
                                    <div class="flex items-center h-5">
                                        <input id="is_hourly" name="is_hourly" type="checkbox" value="1" 
                                            <?= ($formData['is_hourly'] ?? $task->is_hourly) ? 'checked' : '' ?>
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_hourly" class="font-medium text-gray-700 dark:text-gray-300">Billable Task</label>
                                        <p class="text-gray-500 dark:text-gray-400">This task will be billed at the hourly rate specified</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Hourly Rate -->
                                    <div>
                                        <label for="hourly_rate" class="block text-xs text-gray-500 dark:text-gray-400">Hourly Rate ($)</label>
                                        <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" min="0"
                                            value="<?= htmlspecialchars($formData['hourly_rate'] ?? $task->hourly_rate ?? '') ?>"
                                            class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm">
                                    </div>

                                    <!-- Billable Time -->
                                    <div>
                                        <label for="billable_time" class="block text-xs text-gray-500 dark:text-gray-400">Billable Time (H:M)</label>
                                        <input type="text" id="billable_time" name="billable_time" placeholder="00:00"
                                            value="<?= formatTimeInput($formData['billable_time'] ?? $task->billable_time) ?>"
                                            class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>

                            <?php if ($markComplete): ?>
                            <!-- Completion Notes -->
                            <div>
                                <label for="completion_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Completion Notes</label>
                                <textarea id="completion_notes" name="completion_notes" rows="3"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 text-white focus:border-indigo-500 sm:text-sm"><?= htmlspecialchars($formData['completion_notes'] ?? '') ?></textarea>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add any final notes about this task completion</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-right sm:px-6">
                    <a href="/tasks/view/<?= $task->id ?>" class="mr-2 inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white <?= $markComplete ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' ?> focus:outline-none focus:ring-2 focus:ring-offset-2">
                        <?= $markComplete ? 'Mark Task Complete' : 'Update Task' ?>
                    </button>
                </div>
            </form>
        </div>

        <?php if (!$markComplete): ?>
        <!-- Danger Zone -->
        <div class="mt-8 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-red-600 dark:text-red-400">Danger Zone</h3>
            <div class="mt-4 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Delete this task</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Once deleted, this task and all its data will be permanently removed.</p>
                </div>
                <form method="POST" action="/tasks/delete/<?= $task->id ?>" onsubmit="return confirm('Are you sure you want to delete this task? This action cannot be undone.');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete Task
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <script>
        // Handle time input formatting
        document.addEventListener('DOMContentLoaded', function() {
            function setupTimeInput(inputId) {
                const input = document.getElementById(inputId);
                if (!input) return;
                
                input.addEventListener('blur', function() {
                    let value = this.value.trim();
                    
                    // Handle empty input
                    if (!value) {
                        return;
                    }
                    
                    // If input contains only numbers, assume it's minutes
                    if (/^\d+$/.test(value)) {
                        const hours = Math.floor(parseInt(value) / 60);
                        const minutes = parseInt(value) % 60;
                        this.value = hours + ':' + minutes.toString().padStart(2, '0');
                        return;
                    }
                    
                    // If input contains colon, parse as hours:minutes
                    if (value.includes(':')) {
                        const [hours, minutes] = value.split(':');
                        if (hours && minutes) {
                            this.value = parseInt(hours) + ':' + parseInt(minutes).toString().padStart(2, '0');
                        }
                        return;
                    }
                    
                    // Default fallback - just clear invalid input
                    if (!/^\d+:\d{2}$/.test(value)) {
                        this.value = '';
                    }
                });
            }
            
            // Setup time formatting for all time inputs
            setupTimeInput('estimated_time');
            setupTimeInput('time_spent');
            setupTimeInput('billable_time');
            
            // Date validation
            const startDateInput = document.getElementById('start_date');
            const dueDateInput = document.getElementById('due_date');
            
            if (startDateInput && dueDateInput) {
                dueDateInput.addEventListener('change', function() {
                    if (startDateInput.value && dueDateInput.value) {
                        if (new Date(dueDateInput.value) < new Date(startDateInput.value)) {
                            alert('Due date cannot be earlier than start date');
                            dueDateInput.value = '';
                        }
                    }
                });
            }
            
            // Billable task toggle
            const isHourlyCheckbox = document.getElementById('is_hourly');
            const hourlyRateInput = document.getElementById('hourly_rate');
            const billableTimeInput = document.getElementById('billable_time');
            
            if (isHourlyCheckbox && hourlyRateInput && billableTimeInput) {
                function updateBillingFields() {
                    const isDisabled = !isHourlyCheckbox.checked;
                    hourlyRateInput.disabled = isDisabled;
                    billableTimeInput.disabled = isDisabled;
                    
                    if (isDisabled) {
                        hourlyRateInput.classList.add('bg-gray-100', 'dark:bg-gray-800');
                        billableTimeInput.classList.add('bg-gray-100', 'dark:bg-gray-800');
                    } else {
                        hourlyRateInput.classList.remove('bg-gray-100', 'dark:bg-gray-800');
                        billableTimeInput.classList.remove('bg-gray-100', 'dark:bg-gray-800');
                    }
                }
                
                // Initial state
                updateBillingFields();
                
                // Handle checkbox change
                isHourlyCheckbox.addEventListener('change', updateBillingFields);
            }
        });
    </script>
</body>
</html>