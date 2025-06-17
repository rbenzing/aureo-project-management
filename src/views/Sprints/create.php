<?php
// file: Views/Sprints/create.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
use App\Services\SettingsService;
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Sprint - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="w-full px-4 sm:px-6 md:px-8 lg:px-10 xl:px-12 py-6 flex-grow">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <!-- Create Sprint Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <div class="mb-6 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Sprint</h1>
                <a href="/sprints/project/<?= htmlspecialchars($project->id ?? '') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Sprints
                </a>
            </div>

            <form action="/sprints/create" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="project_id" value="<?= htmlspecialchars($project->id ?? '') ?>">

                <!-- Sprint Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sprint Name <span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($_SESSION['form_data']['name'] ?? '') ?>"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                        placeholder="e.g., Sprint 1"
                        required
                    >
                </div>

                <!-- Templates Row -->
                <?php
                $settingsService = SettingsService::getInstance();
                $templateSettings = $settingsService->getTemplateSettings();
                $sprintTemplateSettings = $templateSettings['sprint'] ?? [];
                $showQuickTemplates = $sprintTemplateSettings['show_quick_templates'] ?? true;
                $showCustomTemplates = $sprintTemplateSettings['show_custom_templates'] ?? true;

                // Filter templates to only show sprint templates
                $sprintTemplates = [];
                if (!empty($templates)) {
                    foreach ($templates as $template) {
                        if ($template->template_type === 'sprint') {
                            $sprintTemplates[] = $template;
                        }
                    }
                }
                ?>
                <?php if ($showQuickTemplates || $showCustomTemplates): ?>
                <div class="grid grid-cols-1 <?= ($showQuickTemplates && $showCustomTemplates) ? 'md:grid-cols-2' : '' ?> gap-4">
                    <?php if ($showQuickTemplates): ?>
                    <!-- Quick Templates -->
                    <div>
                        <label for="quick_template" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quick Templates</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <select id="quick_template" name="quick_template" class="pl-10 pr-3 py-2 w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                                <option value="">Select a quick template...</option>
                                <?php
                                $quickTemplates = Config::get('QUICK_SPRINT_TEMPLATES', []);
                                foreach ($quickTemplates as $name => $content):
                                ?>
                                    <option value="<?= htmlspecialchars(strtolower(str_replace(' ', '_', $name))) ?>"
                                            data-title="<?= htmlspecialchars($name) ?>"
                                            data-description="<?= htmlspecialchars($content) ?>">
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Quick pre-built templates</p>
                    </div>
                    <?php endif; ?>

                    <?php if ($showCustomTemplates): ?>
                    <!-- Custom Templates -->
                    <div>
                        <label for="custom_template" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Templates</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <select id="custom_template" name="custom_template" class="pl-10 pr-3 py-2 w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white sm:text-sm">
                                <option value="">Select a custom template...</option>
                                <?php if (!empty($sprintTemplates)): ?>
                                    <?php foreach ($sprintTemplates as $template): ?>
                                        <option value="<?= htmlspecialchars($template->id) ?>"
                                                data-title="<?= htmlspecialchars($template->name) ?>"
                                                data-description="<?= htmlspecialchars($template->description) ?>">
                                            <?= htmlspecialchars($template->name) ?>
                                            <?php if ($template->is_default): ?>
                                                <span class="text-xs text-gray-500">(Default)</span>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Your saved templates</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white"
                        placeholder="Provide a description for this sprint"
                    ><?= htmlspecialchars($_SESSION['form_data']['description'] ?? '') ?></textarea>
                </div>

                <!-- Sprint Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date <span class="text-red-600">*</span></label>
                        <input 
                            type="date" 
                            id="start_date" 
                            name="start_date" 
                            value="<?= htmlspecialchars($_SESSION['form_data']['start_date'] ?? date('Y-m-d')) ?>"
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
                            value="<?= htmlspecialchars($_SESSION['form_data']['end_date'] ?? date('Y-m-d', strtotime('+2 weeks'))) ?>"
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
                        <?php foreach ($sprint_statuses ?? [] as $status): ?>
                            <option 
                                value="<?= $status->id ?>" 
                                <?= isset($_SESSION['form_data']['status_id']) && $_SESSION['form_data']['status_id'] == $status->id ? 'selected' : '' ?>
                                <?= (!isset($_SESSION['form_data']['status_id']) && $status->id == 1) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($status->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Add Tasks Section (if there are tasks in the project) -->
                <?php if (!empty($project_tasks)): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Add Tasks (Optional)</label>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md shadow-inner">
                        <div class="mb-3 flex items-center">
                            <input type="text" id="task-search" placeholder="Search tasks..." class="w-full md:w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-600 dark:text-white">
                            <button type="button" id="select-all-tasks" class="ml-2 px-3 py-2 text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">Select All</button>
                            <button type="button" id="deselect-all-tasks" class="ml-2 px-3 py-2 text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Deselect All</button>
                        </div>

                        <div class="max-h-64 overflow-y-auto space-y-2 pr-2">
                            <?php foreach ($project_tasks as $task): ?>
                                <div class="task-item flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md">
                                    <input 
                                        type="checkbox" 
                                        id="task-<?= $task->id ?>" 
                                        name="tasks[]" 
                                        value="<?= $task->id ?>"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        <?= isset($_SESSION['form_data']['tasks']) && in_array($task->id, $_SESSION['form_data']['tasks']) ? 'checked' : '' ?>
                                    >
                                    <label for="task-<?= $task->id ?>" class="ml-2 flex-1 cursor-pointer">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($task->title) ?></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                            <span class="px-1.5 py-0.5 text-xs rounded-full mr-2 <?= getSprintTaskStatusClass($task->status_name ?? 'Open') ?>">
                                                <?= htmlspecialchars($task->status_name ?? 'Open') ?>
                                            </span>
                                            <?php if (!empty($task->due_date)): ?>
                                                <span>Due: <?= date('M j, Y', strtotime($task->due_date)) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a 
                        href="/sprints/project/<?= htmlspecialchars($project->id ?? '') ?>" 
                        class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-400 dark:hover:bg-gray-600"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Create Sprint
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <?php
    // Include sprint helpers for status functions
    include_once __DIR__ . '/inc/helpers.php';
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Templates Dropdowns
            const quickTemplateSelect = document.getElementById('quick_template');
            const customTemplateSelect = document.getElementById('custom_template');
            const nameInput = document.getElementById('name');
            const descriptionTextarea = document.getElementById('description');

            function applyTemplate(templateSelect) {
                if (!templateSelect.value) return;

                const selectedOption = templateSelect.options[templateSelect.selectedIndex];
                const title = selectedOption.getAttribute('data-title');
                const description = selectedOption.getAttribute('data-description');

                if (descriptionTextarea.value.trim() !== '' &&
                    !confirm('This will replace your current description. Continue?')) {
                    templateSelect.value = ''; // Reset dropdown
                    return;
                }

                if (description) {
                    // Convert literal \n characters to actual line breaks
                    descriptionTextarea.value = description.replace(/\\n/g, '\n');
                }

                // Reset both dropdowns after applying template
                if (quickTemplateSelect) quickTemplateSelect.value = '';
                if (customTemplateSelect) customTemplateSelect.value = '';
            }

            if (quickTemplateSelect) {
                quickTemplateSelect.addEventListener('change', function() {
                    applyTemplate(this);
                });
            }

            if (customTemplateSelect) {
                customTemplateSelect.addEventListener('change', function() {
                    applyTemplate(this);
                });
            }

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