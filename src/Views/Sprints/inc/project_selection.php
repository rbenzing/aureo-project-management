<?php
// file: Views/Sprints/inc/project_selection.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
    <h2 class="text-xl font-semibold mb-4">Select a Project</h2>
    <?php if (!empty($projects)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($projects as $proj): ?>
                <a href="/sprints/project/<?= $proj->id ?>" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center">
                    <div class="h-10 w-10 rounded-md bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-3">
                        <svg class="h-6 w-6 text-indigo-500 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($proj->name) ?></div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <?php
                            // Get sprint counts for this project
                            $activeSprintCount = 0;
                            $completedSprintCount = 0;
                            
                            if (!empty($projectSprintCounts) && isset($projectSprintCounts[$proj->id])) {
                                $activeSprintCount = $projectSprintCounts[$proj->id]['active'] ?? 0;
                                $completedSprintCount = $projectSprintCounts[$proj->id]['completed'] ?? 0;
                            }
                            
                            echo $activeSprintCount > 0 ? 
                                "<span class='text-indigo-600 dark:text-indigo-400'>{$activeSprintCount} active</span>" : 
                                "No active sprints";
                            
                            if ($completedSprintCount > 0) {
                                echo ", {$completedSprintCount} completed";
                            }
                            ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400 mb-4">No projects found.</p>
            <a href="/projects/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                + Create Project
            </a>
        </div>
    <?php endif; ?>
</div>