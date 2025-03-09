<?php
//file: Views/ProjectTemplates/edit.php
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
    <title>Edit Project Template - <?= htmlspecialchars(Config::get('company_name', 'Slimbooks')) ?></title>
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
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Edit Project Template</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update this template for project descriptions.</p>
            </div>
            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3">
                <a href="/project-templates" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" form="editTemplateForm"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Changes
                </button>
            </div>
        </div>
        
        <div class="flex flex-row gap-4">
            <!-- Edit Template Form -->
            <div class="w-3/4 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <form id="editTemplateForm" method="POST" action="/project-templates/update" class="space-y-6">
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="id" value="<?php echo $template->id; ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Name <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" required
                                    value="<?php echo htmlspecialchars($formData['name'] ?? $template->name); ?>"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>

                            <!-- Company Specific -->
                            <div>
                                <label for="company_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company (optional)</label>
                                <select id="company_id" name="company_id"
                                    class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Global (available to all companies)</option>
                                    <?php foreach ($companies as $company): ?>
                                        <option value="<?php echo $company->id; ?>" 
                                            <?php echo (isset($formData['company_id']) ? $formData['company_id'] : $template->company_id) == $company->id ? 'selected' : ''; ?>>
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
                                           <?php echo (isset($formData['is_default']) ? $formData['is_default'] : $template->is_default) ? 'checked' : ''; ?>
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_default" class="font-medium text-gray-700 dark:text-gray-300">Set as Default</label>
                                    <p class="text-gray-500 dark:text-gray-400">Make this the default template for new projects</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Content <span class="text-red-500">*</span></label>
                            <textarea id="description" name="description" rows="15" required
                                class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono"><?php echo htmlspecialchars($formData['description'] ?? $template->description); ?></textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Markdown is supported. Add structure and formatting to your template.</p>
                        </div>
                    </div>
                </form>
            </div>
        
            <!-- Preview Section -->
            <div class="w-1/4 flex flex-col gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Template Preview</h3>
                    <div id="preview" class="prose dark:prose-invert prose-sm max-w-none overflow-auto">
                        <!-- Preview content will be populated by JavaScript -->
                        <div class="text-gray-500 dark:text-gray-400 italic">
                            Type in the template content to see a live preview here...
                        </div>
                    </div>
                </div>
                
                <!-- Danger Zone -->
                <?php if (isset($_SESSION['user']['permissions']) && in_array('delete_project_templates', $_SESSION['user']['permissions'])): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium text-red-600 dark:text-red-400 mb-4">Danger Zone</h3>
                    <form method="POST" action="/project-templates/delete/<?php echo $template->id; ?>" 
                          onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.');">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Delete Template
                        </button>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            This will permanently delete this template from the system.
                        </p>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>
    
    <!-- Include Marked.js for Markdown preview -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const descriptionTextarea = document.getElementById('description');
            const previewDiv = document.getElementById('preview');
            
            // Function to update preview
            function updatePreview() {
                if (descriptionTextarea.value.trim() === '') {
                    previewDiv.innerHTML = '<div class="text-gray-500 dark:text-gray-400 italic">Type in the template content to see a live preview here...</div>';
                } else {
                    previewDiv.innerHTML = marked.parse(descriptionTextarea.value);
                }
            }
            
            // Initial preview
            updatePreview();
            
            // Update preview on input
            descriptionTextarea.addEventListener('input', updatePreview);
        });
    </script>
</body>
</html>