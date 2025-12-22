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
            Showing page <?= $page ?> of <?= $totalPages ?>
        </div>
        <div class="flex space-x-2">
            <?php if ($page > 1): ?>
                <a 
                    href="<?= $isMyTasksView ? "/tasks/assigned/{$userId}/page/" : "/tasks/page/" ?><?= $page - 1 ?>" 
                    class="flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </a>
            <?php else: ?>
                <span class="flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-400 dark:text-gray-500 cursor-not-allowed">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                </span>
            <?php endif; ?>
            
            <!-- Page Numbers (shown only for a reasonable number of pages) -->
            <?php if ($totalPages <= 7): ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a href="<?= $isMyTasksView ? "/tasks/assigned/{$userId}/page/" : "/tasks/page/" ?><?= $i ?>" 
                           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php else: ?>
                <!-- For a large number of pages, show limited page numbers with ellipsis -->
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);

                // Ensure we always show 5 page links when possible
                if ($startPage === 1) {
                    $endPage = min($totalPages, 5);
                } elseif ($endPage === $totalPages) {
                    $startPage = max(1, $totalPages - 4);
                }

                // First Page
                if ($startPage > 1): ?>
                    <a href="<?= $isMyTasksView ? "/tasks/assigned/{$userId}/page/1" : "/tasks/page/1" ?>" 
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        1
                    </a>
                    
                    <!-- Ellipsis if needed -->
                    <?php if ($startPage > 2): ?>
                        <span class="inline-flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                            ...
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Page Numbers -->
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a href="<?= $isMyTasksView ? "/tasks/assigned/{$userId}/page/" : "/tasks/page/" ?><?= $i ?>" 
                           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <!-- Last Page -->
                <?php if ($endPage < $totalPages): ?>
                    <!-- Ellipsis if needed -->
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="inline-flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                            ...
                        </span>
                    <?php endif; ?>
                    
                    <a href="<?= $isMyTasksView ? "/tasks/assigned/{$userId}/page/" : "/tasks/page/" ?><?= $totalPages ?>" 
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <?= $totalPages ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($page < $totalPages): ?>
                <a 
                    href="<?= $isMyTasksView ? "/tasks/assigned/{$userId}/page/" : "/tasks/page/" ?><?= $page + 1 ?>" 
                    class="flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    Next
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            <?php else: ?>
                <span class="flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-400 dark:text-gray-500 cursor-not-allowed">
                    Next
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>