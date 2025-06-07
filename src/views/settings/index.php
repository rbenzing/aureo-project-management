<?php
//file: Views/Settings/index.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;

// Include form components
require_once BASE_PATH . '/../src/views/layouts/form_components.php';

// Load form data from session if available (for validation errors)
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Load errors from session if available
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

// Page title
$pageTitle = 'Settings';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
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
                <!-- Page Header -->
                <div class="pb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Configure application settings for projects, tasks, milestones, and sprints.
                    </p>
                </div>

                <!-- Settings Form -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
                    <form method="POST" action="/settings/update" class="space-y-0">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                                <button type="button" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm active" data-tab="time-intervals">
                                    Time Intervals
                                </button>
                                <button type="button" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="projects">
                                    Projects
                                </button>
                                <button type="button" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="tasks">
                                    Tasks
                                </button>
                                <button type="button" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="milestones">
                                    Milestones
                                </button>
                                <button type="button" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="sprints">
                                    Sprints
                                </button>
                                <button type="button" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="templates">
                                    Templates
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div class="p-6">
                            <!-- Time Intervals Tab -->
                            <div id="time-intervals" class="tab-content">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200 mb-4">Time Tracking Settings</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Time Unit -->
                                    <div>
                                        <?= renderSelect([
                                            'name' => 'time_intervals[time_unit]',
                                            'label' => 'Time Unit',
                                            'value' => $settings['time_intervals']['time_unit'] ?? 'minutes',
                                            'options' => [
                                                'seconds' => 'Seconds',
                                                'minutes' => 'Minutes',
                                                'hours' => 'Hours',
                                                'days' => 'Days'
                                            ],
                                            'help_text' => 'Default unit for time tracking and estimation',
                                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
                                            'error' => $errors['time_intervals.time_unit'] ?? ''
                                        ]) ?>
                                    </div>

                                    <!-- Time Precision -->
                                    <div>
                                        <?= renderTextInput([
                                            'name' => 'time_intervals[time_precision]',
                                            'type' => 'number',
                                            'label' => 'Time Precision',
                                            'value' => $settings['time_intervals']['time_precision'] ?? '15',
                                            'min' => '1',
                                            'max' => '60',
                                            'help_text' => 'Increment/precision for time inputs (e.g., 15 for 15-minute intervals)',
                                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />',
                                            'error' => $errors['time_intervals.time_precision'] ?? ''
                                        ]) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Projects Tab -->
                            <div id="projects" class="tab-content hidden">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200 mb-4">Project Settings</h3>
                                <div class="space-y-6">
                                    <!-- Default Task Type -->
                                    <div>
                                        <?= renderSelect([
                                            'name' => 'projects[default_task_type]',
                                            'label' => 'Default Task Type',
                                            'value' => $settings['projects']['default_task_type'] ?? 'task',
                                            'options' => [
                                                'task' => 'Task',
                                                'story' => 'User Story',
                                                'bug' => 'Bug',
                                                'epic' => 'Epic'
                                            ],
                                            'help_text' => 'Default task type when creating new tasks',
                                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />',
                                            'error' => $errors['projects.default_task_type'] ?? ''
                                        ]) ?>
                                    </div>

                                    <!-- Auto Assign Creator -->
                                    <div>
                                        <?= renderCheckbox([
                                            'name' => 'projects[auto_assign_creator]',
                                            'label' => 'Auto-assign task creator',
                                            'checked' => ($settings['projects']['auto_assign_creator'] ?? '1') === '1',
                                            'help_text' => 'Automatically assign the task creator as the assignee',
                                            'error' => $errors['projects.auto_assign_creator'] ?? ''
                                        ]) ?>
                                    </div>

                                    <!-- Require Project for Tasks -->
                                    <div>
                                        <?= renderCheckbox([
                                            'name' => 'projects[require_project_for_tasks]',
                                            'label' => 'Require project for tasks',
                                            'checked' => ($settings['projects']['require_project_for_tasks'] ?? '0') === '1',
                                            'help_text' => 'Make project selection mandatory when creating tasks',
                                            'error' => $errors['projects.require_project_for_tasks'] ?? ''
                                        ]) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Tasks Tab -->
                            <div id="tasks" class="tab-content hidden">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200 mb-4">Task Settings</h3>
                                <div class="space-y-6">
                                    <!-- Default Priority -->
                                    <div>
                                        <?= renderSelect([
                                            'name' => 'tasks[default_priority]',
                                            'label' => 'Default Priority',
                                            'value' => $settings['tasks']['default_priority'] ?? 'medium',
                                            'options' => [
                                                'none' => 'None',
                                                'low' => 'Low',
                                                'medium' => 'Medium',
                                                'high' => 'High'
                                            ],
                                            'help_text' => 'Default priority level for new tasks',
                                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
                                            'error' => $errors['tasks.default_priority'] ?? ''
                                        ]) ?>
                                    </div>

                                    <!-- Auto Estimate Enabled -->
                                    <div>
                                        <?= renderCheckbox([
                                            'name' => 'tasks[auto_estimate_enabled]',
                                            'label' => 'Enable automatic time estimation',
                                            'checked' => ($settings['tasks']['auto_estimate_enabled'] ?? '0') === '1',
                                            'help_text' => 'Automatically suggest time estimates based on similar completed tasks',
                                            'error' => $errors['tasks.auto_estimate_enabled'] ?? ''
                                        ]) ?>
                                    </div>

                                    <!-- Story Points Enabled -->
                                    <div>
                                        <?= renderCheckbox([
                                            'name' => 'tasks[story_points_enabled]',
                                            'label' => 'Enable story points',
                                            'checked' => ($settings['tasks']['story_points_enabled'] ?? '1') === '1',
                                            'help_text' => 'Enable Fibonacci-based story points for agile estimation',
                                            'error' => $errors['tasks.story_points_enabled'] ?? ''
                                        ]) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Milestones Tab -->
                            <div id="milestones" class="tab-content hidden">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200 mb-4">Milestone Settings</h3>
                                <div class="space-y-6">
                                    <!-- Auto Create from Sprints -->
                                    <div>
                                        <?= renderCheckbox([
                                            'name' => 'milestones[auto_create_from_sprints]',
                                            'label' => 'Auto-create milestones from sprints',
                                            'checked' => ($settings['milestones']['auto_create_from_sprints'] ?? '0') === '1',
                                            'help_text' => 'Automatically create milestones when sprints are completed',
                                            'error' => $errors['milestones.auto_create_from_sprints'] ?? ''
                                        ]) ?>
                                    </div>

                                    <!-- Milestone Notification Days -->
                                    <div>
                                        <?= renderTextInput([
                                            'name' => 'milestones[milestone_notification_days]',
                                            'type' => 'number',
                                            'label' => 'Notification Days',
                                            'value' => $settings['milestones']['milestone_notification_days'] ?? '7',
                                            'min' => '1',
                                            'max' => '30',
                                            'help_text' => 'Days before milestone due date to send notifications',
                                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM16 3H4v2h12V3zM4 7h12v2H4V7zM4 11h12v2H4v-2z" />',
                                            'error' => $errors['milestones.milestone_notification_days'] ?? ''
                                        ]) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Sprints Tab -->
                            <div id="sprints" class="tab-content hidden">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200 mb-4">Sprint Settings</h3>
                                <div class="space-y-6">
                                    <!-- Default Sprint Length -->
                                    <div>
                                        <?= renderTextInput([
                                            'name' => 'sprints[default_sprint_length]',
                                            'type' => 'number',
                                            'label' => 'Default Sprint Length (days)',
                                            'value' => $settings['sprints']['default_sprint_length'] ?? '14',
                                            'min' => '1',
                                            'max' => '30',
                                            'help_text' => 'Default length for new sprints in days',
                                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
                                            'error' => $errors['sprints.default_sprint_length'] ?? ''
                                        ]) ?>
                                    </div>

                                    <!-- Auto Start Next Sprint -->
                                    <div>
                                        <?= renderCheckbox([
                                            'name' => 'sprints[auto_start_next_sprint]',
                                            'label' => 'Auto-start next sprint',
                                            'checked' => ($settings['sprints']['auto_start_next_sprint'] ?? '0') === '1',
                                            'help_text' => 'Automatically start the next sprint when current sprint ends',
                                            'error' => $errors['sprints.auto_start_next_sprint'] ?? ''
                                        ]) ?>
                                    </div>

                                    <!-- Sprint Planning Enabled -->
                                    <div>
                                        <?= renderCheckbox([
                                            'name' => 'sprints[sprint_planning_enabled]',
                                            'label' => 'Enable sprint planning',
                                            'checked' => ($settings['sprints']['sprint_planning_enabled'] ?? '1') === '1',
                                            'help_text' => 'Enable sprint planning features and workflows',
                                            'error' => $errors['sprints.sprint_planning_enabled'] ?? ''
                                        ]) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Templates Tab -->
                            <div id="templates" class="tab-content hidden">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200 mb-4">Template Settings</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                    Control which template options are available when creating projects, tasks, milestones, and sprints.
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <!-- Project Templates -->
                                    <div class="space-y-4">
                                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-200">Project Templates</h4>
                                        <div class="space-y-3">
                                            <div>
                                                <?= renderCheckbox([
                                                    'name' => 'templates[project_show_quick_templates]',
                                                    'label' => 'Show quick templates',
                                                    'checked' => ($settings['templates']['project_show_quick_templates'] ?? '1') === '1',
                                                    'help_text' => 'Show pre-built quick templates for projects',
                                                    'error' => $errors['templates.project_show_quick_templates'] ?? ''
                                                ]) ?>
                                            </div>
                                            <div>
                                                <?= renderCheckbox([
                                                    'name' => 'templates[project_show_custom_templates]',
                                                    'label' => 'Show custom templates',
                                                    'checked' => ($settings['templates']['project_show_custom_templates'] ?? '1') === '1',
                                                    'help_text' => 'Show user-created custom templates for projects',
                                                    'error' => $errors['templates.project_show_custom_templates'] ?? ''
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Task Templates -->
                                    <div class="space-y-4">
                                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-200">Task Templates</h4>
                                        <div class="space-y-3">
                                            <div>
                                                <?= renderCheckbox([
                                                    'name' => 'templates[task_show_quick_templates]',
                                                    'label' => 'Show quick templates',
                                                    'checked' => ($settings['templates']['task_show_quick_templates'] ?? '1') === '1',
                                                    'help_text' => 'Show pre-built quick templates for tasks',
                                                    'error' => $errors['templates.task_show_quick_templates'] ?? ''
                                                ]) ?>
                                            </div>
                                            <div>
                                                <?= renderCheckbox([
                                                    'name' => 'templates[task_show_custom_templates]',
                                                    'label' => 'Show custom templates',
                                                    'checked' => ($settings['templates']['task_show_custom_templates'] ?? '1') === '1',
                                                    'help_text' => 'Show user-created custom templates for tasks',
                                                    'error' => $errors['templates.task_show_custom_templates'] ?? ''
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Milestone Templates -->
                                    <div class="space-y-4">
                                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-200">Milestone Templates</h4>
                                        <div class="space-y-3">
                                            <div>
                                                <?= renderCheckbox([
                                                    'name' => 'templates[milestone_show_quick_templates]',
                                                    'label' => 'Show quick templates',
                                                    'checked' => ($settings['templates']['milestone_show_quick_templates'] ?? '1') === '1',
                                                    'help_text' => 'Show pre-built quick templates for milestones',
                                                    'error' => $errors['templates.milestone_show_quick_templates'] ?? ''
                                                ]) ?>
                                            </div>
                                            <div>
                                                <?= renderCheckbox([
                                                    'name' => 'templates[milestone_show_custom_templates]',
                                                    'label' => 'Show custom templates',
                                                    'checked' => ($settings['templates']['milestone_show_custom_templates'] ?? '1') === '1',
                                                    'help_text' => 'Show user-created custom templates for milestones',
                                                    'error' => $errors['templates.milestone_show_custom_templates'] ?? ''
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sprint Templates -->
                                    <div class="space-y-4">
                                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-200">Sprint Templates</h4>
                                        <div class="space-y-3">
                                            <div>
                                                <?= renderCheckbox([
                                                    'name' => 'templates[sprint_show_quick_templates]',
                                                    'label' => 'Show quick templates',
                                                    'checked' => ($settings['templates']['sprint_show_quick_templates'] ?? '1') === '1',
                                                    'help_text' => 'Show pre-built quick templates for sprints',
                                                    'error' => $errors['templates.sprint_show_quick_templates'] ?? ''
                                                ]) ?>
                                            </div>
                                            <div>
                                                <?= renderCheckbox([
                                                    'name' => 'templates[sprint_show_custom_templates]',
                                                    'label' => 'Show custom templates',
                                                    'checked' => ($settings['templates']['sprint_show_custom_templates'] ?? '1') === '1',
                                                    'help_text' => 'Show user-created custom templates for sprints',
                                                    'error' => $errors['templates.sprint_show_custom_templates'] ?? ''
                                                ]) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-end space-x-3">
                            <a href="/dashboard" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <!-- Settings Tab JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');

                    // Remove active class from all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                        btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
                    });

                    // Add active class to clicked button
                    this.classList.add('active', 'border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                    this.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');

                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });

                    // Show target tab content
                    const targetContent = document.getElementById(targetTab);
                    if (targetContent) {
                        targetContent.classList.remove('hidden');
                    }
                });
            });

            // Set initial active tab
            const firstTab = tabButtons[0];
            if (firstTab) {
                firstTab.click();
            }
        });
    </script>
</body>
</html>
