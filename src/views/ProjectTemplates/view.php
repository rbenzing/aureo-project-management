<?php
//file: Views/ProjectTemplates/view.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($template->name) ?> - Template - <?= htmlspecialchars(Config::get('company_name', 'Slimbooks')) ?></title>
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
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($template->name) ?></h1>
                <div class="flex items-center mt-2">
                    <?php if ($template->is_default): ?>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            Default Template
                        </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($template->company_id)): ?>
                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            Company Specific
                        </span>
                    <?php else: ?>
                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            Global
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user']['permissions']) && in_array('edit_project_templates', $_SESSION['user']['permissions'])): ?>
                    <a href="/project-templates/edit/<?= $template->id ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-700">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Edit Template
                    </a>
                <?php endif; ?>
                
                <a href="/project-templates" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-700">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to Templates
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Template Content</h2>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Created: <?= date('M j, Y', strtotime($template->created_at)) ?>
                    </span>
                    <?php if ($template->created_at != $template->updated_at): ?>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            | Updated: <?= date('M j, Y', strtotime($template->updated_at)) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Raw Template -->
                    <div class="w-full md:w-1/2 overflow-auto">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden">
                            <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Raw Template</h3>
                            </div>
                            <pre class="p-4 bg-gray-50 dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-300 whitespace-pre-wrap font-mono overflow-x-auto"><?= htmlspecialchars($template->description) ?></pre>
                        </div>
                    </div>
                    
                    <!-- Rendered Preview -->
                    <div class="w-full md:w-1/2 overflow-auto">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden">
                            <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Rendered Preview</h3>
                            </div>
                            <div id="rendered-preview" class="p-4 bg-white dark:bg-gray-800 prose dark:prose-invert prose-sm max-w-none">
                                <!-- Content will be rendered by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-6 flex justify-end gap-4">
                    <?php if (isset($_SESSION['user']['permissions']) && in_array('create_projects', $_SESSION['user']['permissions'])): ?>
                        <a href="/projects/create?template_id=<?= $template->id ?>" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Create Project Using This Template
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['user']['permissions']) && in_array('edit_project_templates', $_SESSION['user']['permissions']) && !$template->is_default): ?>
                        <form method="POST" action="/project-templates/update" class="inline">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="id" value="<?php echo $template->id; ?>">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($template->name); ?>">
                            <input type="hidden" name="description" value="<?php echo htmlspecialchars($template->description); ?>">
                            <input type="hidden" name="company_id" value="<?php echo $template->company_id; ?>">
                            <input type="hidden" name="is_default" value="1">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-700">
                                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Set as Default
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>
    
    <!-- Include Marked.js for Markdown preview -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const previewDiv = document.getElementById('rendered-preview');
            const templateContent = <?= json_encode($template->description) ?>;
            
            // Render the template content
            previewDiv.innerHTML = marked.parse(templateContent);
        });
    </script>
</body>
</html>