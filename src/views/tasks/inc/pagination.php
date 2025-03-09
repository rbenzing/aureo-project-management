<?php
//file: Views/Tasks/inc/pagination.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<?php if (isset($totalPages) && $totalPages > 1): ?>
    <div class="mt-6 flex justify-between items-center">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Page <?= $page ?> of <?= $totalPages ?>
        </div>
        <div class="flex space-x-2">
            <?php if ($page > 1): ?>
                <a 
                    href="<?= $isMyTasksView ? "/tasks/assigned/{$userId}/page/" : "/tasks/page/" ?><?= $page - 1 ?>" 
                    class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    Previous
                </a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a 
                    href="<?= $isMyTasksView ? "/tasks/assigned/{$userId}/page/" : "/tasks/page/" ?><?= $page + 1 ?>" 
                    class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    Next
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>