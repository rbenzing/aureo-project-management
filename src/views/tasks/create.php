<?php
//file: Views/Tasks/create.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;

// Load form data from session if available (for validation errors)
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Check if we are duplicating a task
$duplicateTaskId = $_GET['duplicate'] ?? null;
$duplicateTask = null;
if ($duplicateTaskId) {
    $duplicateTask = (new \App\Models\Task())->find($duplicateTaskId);
}

// Check if we're assigning to a specific user
$assignToUserId = $_GET['assign_to'] ?? null;
$preSelectedProject = $_GET['project_id'] ?? ($duplicateTask->project_id ?? null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
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

        <!-- Page Header -->
        <div class="pb-6 flex justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <?= $duplicateTask ? 'Duplicate Task' : 'Create New Task' ?>
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    <?= $duplicateTask ? 'Create a new task based on an existing one.' : 'Add a new task to track your work and progress.' ?>
                </p>
            </div>
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3">
                <a href="<?= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/tasks' ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" form="createTaskForm"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <?= $duplicateTask ? 'Duplicate Task' : 'Create Task' ?>
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Main Form -->
            <div class="w-full lg:w-2/3">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <form id="createTaskForm" method="POST" action="/tasks/create" class="space-y-6">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <?php if ($duplicateTask): ?>
                            <input type="hidden" name="duplicate_from" value="<?= $duplicateTask->id ?>">
                        <?php endif; ?>
                        <?php if ($assignToUserId): ?>
                            <input type="hidden" name="assigned_to" value="<?= $assignToUserId ?>">
                        <?php endif; ?>

                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Task Title <span class="text-red-500">*</span></label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h7a2 2 0 012 2v12a2 2 0 01-2 2H9a2 2 0 01-2-2V7a2 2 0 012-2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <input type="text" id="title" name="title" required
                                    value="<?php echo htmlspecialchars($formData['title'] ?? ($duplicateTask ? $duplicateTask->title . ' (Copy)' : '')); ?>"
                                    class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Project & Parent Task Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Project Selection -->
                            <div>
                                <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                        </svg>
                                    </div>
                                    <select id="project_id" name="project_id" required
                                        class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Select a project</option>
                                        <?php foreach ($projects['records'] as $project): ?>
                                            <option value="<?php echo $project->id; ?>" 
                                                <?php echo ($preSelectedProject == $project->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($project->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Parent Task (for subtasks) -->
                            <div>
                                <label for="parent_task_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent Task <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                    <select id="parent_task_id" name="parent_task_id" 
                                        class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">None (Create as main task)</option>
                                        <!-- Project tasks will be populated via JavaScript -->
                                    </select>
                                    <input type="hidden" name="is_subtask" id="is_subtask" value="0">
                                </div>
                            </div>
                        </div>

                        <!-- Status & Priority Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Status -->
                            <div>
                                <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <select id="status_id" name="status_id" required
                                        class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status->id; ?>" 
                                                <?php echo (isset($formData['status_id']) && $formData['status_id'] == $status->id) || 
                                                            ($duplicateTask && $duplicateTask->status_id == $status->id && $status->id == 1) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($status->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Priority -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <select id="priority" name="priority"
                                        class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="none" <?php echo (isset($formData['priority']) && $formData['priority'] == 'none') || ($duplicateTask && $duplicateTask->priority == 'none') ? 'selected' : ''; ?>>None</option>
                                        <option value="low" <?php echo (isset($formData['priority']) && $formData['priority'] == 'low') || ($duplicateTask && $duplicateTask->priority == 'low') ? 'selected' : ''; ?>>Low</option>
                                        <option value="medium" <?php echo (isset($formData['priority']) && $formData['priority'] == 'medium') || ($duplicateTask && $duplicateTask->priority == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                        <option value="high" <?php echo (isset($formData['priority']) && $formData['priority'] == 'high') || ($duplicateTask && $duplicateTask->priority == 'high') ? 'selected' : ''; ?>>High</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Assignee & Dates Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Assignee (if not pre-set) -->
                            <?php if (!$assignToUserId): ?>
                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assign To</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <select id="assigned_to" name="assigned_to"
                                        class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Unassigned</option>
                                        <option value="<?= $_SESSION['user']['profile']['id'] ?? '' ?>" selected>
                                            Assign to me (<?= htmlspecialchars($_SESSION['user']['profile']['first_name'] ?? 'Me') ?>)
                                        </option>
                                        <?php foreach ($users as $user): ?>
                                            <?php if (($user->id ?? 0) != ($_SESSION['user']['profile']['id'] ?? -1)): ?>
                                            <option value="<?php echo $user->id; ?>" 
                                                <?php echo (isset($formData['assigned_to']) && $formData['assigned_to'] == $user->id) || 
                                                        ($duplicateTask && $duplicateTask->assigned_to == $user->id) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user->first_name . ' ' . $user->last_name); ?>
                                            </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php else: ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assigning To</label>
                                <div class="mt-1 flex items-center">
                                    <?php 
                                    $assignedUser = null;
                                    foreach ($users as $user) {
                                        if ($user->id == $assignToUserId) {
                                            $assignedUser = $user;
                                            break;
                                        }
                                    }
                                    ?>
                                    <?php if ($assignedUser): ?>
                                    <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-md px-3 py-2">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center">
                                            <span class="text-xs font-medium text-white">
                                                <?= htmlspecialchars(substr($assignedUser->first_name, 0, 1) . substr($assignedUser->last_name, 0, 1)) ?>
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <?= htmlspecialchars($assignedUser->first_name . ' ' . $assignedUser->last_name) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        User ID: <?= $assignToUserId ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Due Date -->
                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <input type="date" id="due_date" name="due_date"
                                        value="<?php echo htmlspecialchars($formData['due_date'] ?? ($duplicateTask ? $duplicateTask->due_date : '')); ?>"
                                        class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Time Tracking Row -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Estimated Time -->
                            <div>
                                <label for="estimated_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Time (minutes)</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <input type="number" id="estimated_time" name="estimated_time" min="0" step="15"
                                        value="<?php echo htmlspecialchars($formData['estimated_time'] ?? ($duplicateTask ? $duplicateTask->estimated_time : '')); ?>"
                                        class="pl-10 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <!-- Billable Toggle -->
                            <div>
                                <label for="is_hourly" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billable Task</label>
                                <div class="mt-3 relative flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="is_hourly" name="is_hourly" type="checkbox" value="1"
                                            <?php echo (isset($formData['is_hourly']) && $formData['is_hourly']) || 
                                                    ($duplicateTask && $duplicateTask->is_hourly) ? 'checked' : ''; ?>
                                            class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 dark:bg-gray-700">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_hourly" class="font-medium text-gray-700 dark:text-gray-300">Mark as billable</label>
                                        <p class="text-gray-500 dark:text-gray-400">Track billable hours for client invoicing</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Hourly Rate (conditional on billable) -->
                            <div id="hourly-rate-container" class="hidden">
                                <label for="hourly_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hourly Rate ($)</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" id="hourly_rate" name="hourly_rate" min="0" step="0.01"
                                        value="<?php echo htmlspecialchars($formData['hourly_rate'] ?? ($duplicateTask ? $duplicateTask->hourly_rate : '')); ?>"
                                        class="pl-7 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <div class="mt-1">
                                <textarea id="description" name="description" rows="5"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?php echo htmlspecialchars($formData['description'] ?? ($duplicateTask ? $duplicateTask->description : '')); ?></textarea>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Add any additional details or context for this task.
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="w-full lg:w-1/3">
                <!-- Help Box -->
                <div class="bg-indigo-50 dark:bg-indigo-900 rounded-lg shadow-md p-6 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-indigo-800 dark:text-indigo-300">Tips for effective tasks</h3>
                            <div class="mt-2 text-sm text-indigo-700 dark:text-indigo-200">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Use clear, descriptive titles</li>
                                    <li>Set realistic due dates</li>
                                    <li>Break down complex tasks into subtasks</li>
                                    <li>Assign tasks to specific team members</li>
                                    <li>Include necessary context in the description</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Templates -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200 mb-4">Quick Templates</h3>
                    <div class="space-y-3">
                        <button type="button" class="task-template w-full flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-left" 
                                data-title="Bug Fix" 
                                data-priority="high" 
                                data-description="## Bug Description
- What is happening?
- What should be happening?

## Steps to Reproduce
1. 
2. 
3. 

## Additional Information
- Browser/Device:
- Version:">
                            <svg class="h-5 w-5 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Bug Fix</span>
                        </button>

                        <button type="button" class="task-template w-full flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-left" 
                                data-title="Feature Implementation" 
                                data-priority="medium" 
                                data-description="## Feature Overview
Brief description of the feature to be implemented.

## Requirements
- Requirement 1
- Requirement 2
- Requirement 3

## Acceptance Criteria
- [ ] Criteria 1
- [ ] Criteria 2
- [ ] Criteria 3

## Additional Notes
Any other relevant information.">
                            <svg class="h-5 w-5 text-blue-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Feature Implementation</span>
                        </button>

                        <button type="button" class="task-template w-full flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-left" 
                                data-title="Research Task" 
                                data-priority="low" 
                                data-description="## Research Objective
Define what needs to be researched.

## Key Questions
1. 
2. 
3. 

## Resources
- 
- 
- 

## Expected Outcome
What deliverable is expected from this research?">
                            <svg class="h-5 w-5 text-purple-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Research Task</span>
                        </button>

                        <button type="button" class="task-template w-full flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 text-left" 
                                data-title="Documentation Update" 
                                data-priority="medium" 
                                data-description="## Documentation to Update
Specify which documentation needs updating.

## Changes Required
- 
- 
- 

## Reason for Update
Why are these changes necessary?

## Additional Context
Any other relevant information.">
                            <svg class="h-5 w-5 text-yellow-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Documentation Update</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Billable Toggle
            const billableCheckbox = document.getElementById('is_hourly');
            const hourlyRateContainer = document.getElementById('hourly-rate-container');
            
            function updateBillableVisibility() {
                if (billableCheckbox.checked) {
                    hourlyRateContainer.classList.remove('hidden');
                } else {
                    hourlyRateContainer.classList.add('hidden');
                }
            }
            
            // Set initial state
            updateBillableVisibility();
            
            // Listen for changes
            billableCheckbox.addEventListener('change', updateBillableVisibility);
            
            // Handle Project Change for Parent Task dropdown
            const projectSelect = document.getElementById('project_id');
            const parentTaskSelect = document.getElementById('parent_task_id');
            const isSubtaskInput = document.getElementById('is_subtask');
            
            async function fetchProjectTasks(projectId) {
                try {
                    const response = await fetch(`/api/projects/${projectId}/tasks`);
                    if (!response.ok) {
                        throw new Error('Failed to fetch tasks');
                    }
                    return await response.json();
                } catch (error) {
                    console.error('Error fetching tasks:', error);
                    return [];
                }
            }
            
            async function updateParentTaskOptions() {
                const projectId = projectSelect.value;
                parentTaskSelect.innerHTML = '<option value="">None (Create as main task)</option>';
                
                if (!projectId) return;
                
                try {
                    const tasks = await fetchProjectTasks(projectId);
                    if (tasks && tasks.length > 0) {
                        tasks.forEach(task => {
                            if (!task.is_subtask) { // Only main tasks can be parents
                                const option = document.createElement('option');
                                option.value = task.id;
                                option.textContent = task.title;
                                parentTaskSelect.appendChild(option);
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error updating parent task options:', error);
                }
            }
            
            // Update parent task select on project change
            projectSelect.addEventListener('change', updateParentTaskOptions);
            
            // Update is_subtask value when parent task changes
            parentTaskSelect.addEventListener('change', function() {
                isSubtaskInput.value = this.value ? '1' : '0';
            });
            
            // Handle task templates
            const templateButtons = document.querySelectorAll('.task-template');
            const titleInput = document.getElementById('title');
            const prioritySelect = document.getElementById('priority');
            const descriptionTextarea = document.getElementById('description');
            
            templateButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const title = this.getAttribute('data-title');
                    const priority = this.getAttribute('data-priority');
                    const description = this.getAttribute('data-description');
                    
                    if (titleInput.value && !confirm('This will replace your current task information. Continue?')) {
                        return;
                    }
                    
                    titleInput.value = title;
                    prioritySelect.value = priority;
                    descriptionTextarea.value = description;
                });
            });
            
            // Initialize parent task options if project is already selected
            if (projectSelect.value) {
                updateParentTaskOptions();
            }
        });
    </script>
</body>
</html>