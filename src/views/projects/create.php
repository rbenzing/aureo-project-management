<?php
//file: Views/Projects/create.php
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project - <?= htmlspecialchars(Config::get('company_name', 'Slimbooks')) ?></title>
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
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Create New Project</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create a new project to organize your tasks and track progress.</p>
            </div>
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3">
                <a href="/projects" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" form="createProjectForm"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Project
                </button>
            </div>
        </div>

        <div class="flex flex-row gap-4">
            <!-- Create Project Form -->
            <div class="w-3/4 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <form id="createProjectForm" method="POST" action="/projects/create" class="space-y-6">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Project Name <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" required
                                    value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <!-- Owner -->
                            <div>
                                <label for="owner_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Owner <span class="text-red-500">*</span></label>
                                <select id="owner_id" name="owner_id" required
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select a owner</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo htmlspecialchars((string)$user->id); ?>"
                                            <?php echo (isset($formData['owner_id']) && $formData['owner_id'] == $user->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user->first_name) . ' ' . htmlspecialchars($user->last_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Company -->
                            <div>
                                <label for="company_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company <span class="text-red-500">*</span></label>
                                <select id="company_id" name="company_id" required
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select a company</option>
                                    <?php foreach ($companies as $company): ?>
                                        <option value="<?php echo $company->id; ?>"
                                            <?php echo (isset($formData['company_id']) && $formData['company_id'] == $company->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($company->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Status -->
                            <div>
                                <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status <span class="text-red-500">*</span></label>
                                <select id="status_id" name="status_id" required
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select a status</option>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?php echo $status->id; ?>"
                                            <?php echo (isset($formData['status_id']) && $formData['status_id'] == $status->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $status->name))); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                                <input type="date" id="start_date" name="start_date"
                                    value="<?php echo htmlspecialchars($formData['start_date'] ?? ''); ?>"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expected End Date</label>
                                <input type="date" id="end_date" name="end_date"
                                    value="<?php echo htmlspecialchars($formData['end_date'] ?? ''); ?>"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <!-- Template Selection -->
                        <div>
                            <label for="template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description Template</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <select id="template_id" name="template_id"
                                    class="block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select a template or write your own</option>
                                    <?php
                                    // Load templates (we'll get these from the controller)
                                    if (isset($templates) && !empty($templates)) {
                                        foreach ($templates as $template) {
                                            echo '<option value="' . $template->id . '"';
                                            if (isset($formData['template_id']) && $formData['template_id'] == $template->id) {
                                                echo ' selected';
                                            }
                                            echo '>' . htmlspecialchars($template->name) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select a template or create your own description</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea id="description" name="description" rows="10"
                                class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Briefly describe the project scope and objectives.</p>
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
                        <h3 class="text-sm font-medium text-blue-300 dark:text-blue-200">Tips for creating effective projects</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Use clear, descriptive project names</li>
                                <li>Set realistic start and end dates</li>
                                <li>Provide detailed project descriptions</li>
                                <li>After creating your project, you can add tasks and milestones</li>
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
        // Simple date validation
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            endDateInput.addEventListener('change', function() {
                if (startDateInput.value && endDateInput.value) {
                    if (new Date(endDateInput.value) < new Date(startDateInput.value)) {
                        alert('End date cannot be earlier than start date');
                        endDateInput.value = '';
                    }
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const templateSelect = document.getElementById('template_id');
            const descriptionTextarea = document.getElementById('description');

            if (templateSelect && descriptionTextarea) {
                templateSelect.addEventListener('change', function() {
                    const templateId = this.value;

                    // If no template selected or description already has content, don't overwrite
                    if (!templateId || (descriptionTextarea.value.trim() !== '' &&
                            !confirm('This will replace your current description. Continue?'))) {
                        return;
                    }

                    // Fetch template content
                    fetch(`/project-templates/get/${templateId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.template) {
                                descriptionTextarea.value = data.template.description;
                            } else {
                                console.error('Error loading template:', data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching template:', error);
                        });
                });
            }
        });
    </script>
</body>
</html>