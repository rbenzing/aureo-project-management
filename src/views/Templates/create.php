<?php
//file: Views/Templates/create.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
use App\Models\Template;

// Load form data from session if available (for validation errors)
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Template - <?= htmlspecialchars(Config::get('company_name', 'Slimbooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container p-6 overflow-y-auto mx-auto">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <!-- Page Header -->
        <div class="pb-6 flex justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Create Template</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create a reusable template for projects, tasks, milestones, or sprints.</p>
            </div>
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3">
                <a href="/templates" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" form="createTemplateForm"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Template
                </button>
            </div>
        </div>
        
        <div class="flex flex-row gap-4">
            <!-- Create Template Form -->
            <div class="w-3/4 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <form id="createTemplateForm" method="POST" action="/templates/create" class="space-y-6">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Name <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" required
                                    value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <!-- Template Type -->
                            <div>
                                <label for="template_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Type <span class="text-red-500">*</span></label>
                                <select id="template_type" name="template_type" required
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select template type...</option>
                                    <?php foreach (Template::TEMPLATE_TYPES as $type => $label): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>" 
                                            <?php echo (isset($formData['template_type']) && $formData['template_type'] === $type) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Choose what type of content this template will be used for</p>
                            </div>

                            <!-- Company Specific -->
                            <div>
                                <label for="company_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company (optional)</label>
                                <select id="company_id" name="company_id"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Global (available to all companies)</option>
                                    <?php foreach ($companies as $company): ?>
                                        <option value="<?php echo $company->id; ?>" 
                                            <?php echo (isset($formData['company_id']) && $formData['company_id'] == $company->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($company->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">If selected, this template will only be available to this company</p>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Is Default -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_default" name="is_default" type="checkbox" 
                                           <?php echo isset($formData['is_default']) ? 'checked' : ''; ?>
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_default" class="font-medium text-gray-700 dark:text-gray-300">Set as Default</label>
                                    <p class="text-gray-500 dark:text-gray-400">Make this the default template for this type</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Content <span class="text-red-500">*</span></label>
                            <textarea id="description" name="description" rows="15" required
                                class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono"><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Markdown is supported. Add structure and formatting to your template.</p>
                        </div>
                    </div>
                </form>
            </div>
        
            <!-- Help Section -->
            <div class="w-1/4 bg-blue-50 dark:bg-blue-900 rounded-lg p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-300 dark:text-blue-200">Template Tips</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <ul class="list-disc pl-5 space-y-1">
                                <li><strong>Project:</strong> Include objectives, scope, timeline</li>
                                <li><strong>Task:</strong> Add acceptance criteria, steps</li>
                                <li><strong>Milestone:</strong> Define deliverables, success criteria</li>
                                <li><strong>Sprint:</strong> Set goals, capacity, focus areas</li>
                                <li>Use markdown for better formatting</li>
                                <li>Include placeholders for specific information</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const templateTypeSelect = document.getElementById('template_type');
            const descriptionTextarea = document.getElementById('description');
            
            // Default templates for each type
            const defaultTemplates = {
                'project': <?= json_encode(Config::get('DEFAULT_PROJECT_TEMPLATE')) ?>,
                'task': <?= json_encode(Config::get('DEFAULT_TASK_TEMPLATE')) ?>,
                'milestone': <?= json_encode(Config::get('DEFAULT_MILESTONE_TEMPLATE')) ?>,
                'sprint': <?= json_encode(Config::get('DEFAULT_SPRINT_TEMPLATE')) ?>
            };
            
            if (templateTypeSelect && descriptionTextarea) {
                templateTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;
                    
                    if (selectedType && defaultTemplates[selectedType]) {
                        // Only update if textarea is empty or user confirms
                        if (!descriptionTextarea.value.trim() || 
                            confirm('This will replace your current content. Continue?')) {
                            descriptionTextarea.value = defaultTemplates[selectedType];
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
