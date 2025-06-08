<?php
// file: Views/tasks/inc/project_header.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 overflow-hidden">
    <div class="p-4 md:p-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <div class="flex items-center mb-4 md:mb-0">
            <div class="h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-4">
                <svg class="h-7 w-7 text-indigo-500 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-gray-100">
                    <?= htmlspecialchars($project->name ?? 'Project') ?>
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    <?= !empty($project->description) ? htmlspecialchars(substr($project->description, 0, 100)) . (strlen($project->description) > 100 ? '...' : '') : 'No description' ?>
                </p>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="/projects/view/<?= $project->id ?? 0 ?>" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                Project Details
            </a>
            <a href="/tasks/create?project_id=<?= $project->id ?? 0 ?>" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                + New Task
            </a>
        </div>
    </div>
    
    <div class="border-t border-gray-200 dark:border-gray-700 px-4">
        <div class="flex overflow-x-auto">
            <a href="/projects/view/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap">
                Overview
            </a>
            <a href="/tasks/project/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                Tasks
            </a>
            <a href="/sprints/project/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap">
                Sprints
            </a>
            <a href="/milestones/project/<?= $project->id ?? 0 ?>" class="px-4 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap">
                Milestones
            </a>
        </div>
    </div>
</div>
