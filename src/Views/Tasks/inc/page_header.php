<?php
//file: Views/Tasks/inc/page_header.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<!-- Page Header with Breadcrumb and New Task Button -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <!-- Breadcrumb Section -->
    <div class="flex-1">
        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>
    </div>
    
    <!-- New Task Button Section -->
    <div class="flex-shrink-0">
        <a href="/tasks/create<?= $isMyTasksView && !$viewingOwnTasks ? '?assign_to=' . htmlspecialchars($userId) : '' ?>" 
           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            New Task
        </a>
    </div>
</div>
