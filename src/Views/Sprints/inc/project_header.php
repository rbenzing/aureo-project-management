<?php
// file: Views/Sprints/inc/project_header.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 overflow-hidden">
    <!-- Breadcrumb with Create Sprint Button -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 border-b border-gray-200 dark:border-gray-700 gap-4">
        <!-- Breadcrumb Section -->
        <div class="flex-1">
            <?php
            $breadcrumbParams = [
                'route' => 'sprints/project',
                'params' => ['project_id' => $project->id ?? 0],
            ];
include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php';
?>
        </div>

        <!-- Create New Sprint Button -->
        <?php if (isset($_SESSION['user']['permissions']) && in_array('create_sprints', $_SESSION['user']['permissions'])): ?>
        <div class="flex-shrink-0">
            <a
                href="/sprints/create/<?= $project->id ?? 0 ?>"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Sprint
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Project Action Buttons Row -->
    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex space-x-3">
            <a href="/projects/view/<?= $project->id ?? 0 ?>" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                Project Details
            </a>
        </div>
    </div>
    
    <div class="border-t border-gray-200 dark:border-gray-700 px-4">
        <div class="flex overflow-x-auto">
            <a href="/projects/view/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap">
                Overview
            </a>
            <a href="/tasks/project/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap">
                Tasks
            </a>
            <a href="/sprints/project/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                Sprints
            </a>
            <a href="/milestones/project/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap">
                Milestones
            </a>
        </div>
    </div>
</div>