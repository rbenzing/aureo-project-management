<?php
//file: Views/Activity/inc/pagination.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Only show pagination if there are multiple pages
if ($pagination['total_pages'] <= 1) {
    return;
}

// Build query string from current filters
$queryParams = [];
foreach ($filters as $key => $value) {
    if (!empty($value)) {
        $queryParams[$key] = $value;
    }
}

function buildPaginationUrl($page, array $queryParams): string {
    $queryParams['page'] = (int)$page;
    return '/activity?' . http_build_query($queryParams);
}

// Calculate pagination range
$currentPage = $pagination['current_page'];
$totalPages = $pagination['total_pages'];
$range = 2; // Show 2 pages before and after current page

$startPage = max(1, $currentPage - $range);
$endPage = min($totalPages, $currentPage + $range);

// Adjust range if we're near the beginning or end
if ($currentPage <= $range + 1) {
    $endPage = min($totalPages, 2 * $range + 1);
}
if ($currentPage >= $totalPages - $range) {
    $startPage = max(1, $totalPages - 2 * $range);
}
?>

<div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6 rounded-lg shadow mt-6">
    <div class="flex-1 flex justify-between sm:hidden">
        <!-- Mobile pagination -->
        <?php if ($pagination['has_prev']): ?>
            <a href="<?= buildPaginationUrl($pagination['prev_page'], $queryParams) ?>" 
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                Previous
            </a>
        <?php else: ?>
            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-700 cursor-not-allowed">
                Previous
            </span>
        <?php endif; ?>

        <?php if ($pagination['has_next']): ?>
            <a href="<?= buildPaginationUrl($pagination['next_page'], $queryParams) ?>" 
               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                Next
            </a>
        <?php else: ?>
            <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-700 cursor-not-allowed">
                Next
            </span>
        <?php endif; ?>
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Showing
                <span class="font-medium"><?= number_format(($currentPage - 1) * $pagination['items_per_page'] + 1) ?></span>
                to
                <span class="font-medium"><?= number_format(min($currentPage * $pagination['items_per_page'], $pagination['total_items'])) ?></span>
                of
                <span class="font-medium"><?= number_format($pagination['total_items']) ?></span>
                activities
            </p>
        </div>
        <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <!-- Previous Page -->
                <?php if ($pagination['has_prev']): ?>
                    <a href="<?= buildPaginationUrl($pagination['prev_page'], $queryParams) ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <span class="sr-only">Previous</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-sm font-medium text-gray-300 dark:text-gray-600 cursor-not-allowed">
                        <span class="sr-only">Previous</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                <?php endif; ?>

                <!-- First page if not in range -->
                <?php if ($startPage > 1): ?>
                    <a href="<?= buildPaginationUrl(1, $queryParams) ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                        1
                    </a>
                    <?php if ($startPage > 2): ?>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300">
                            ...
                        </span>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Page numbers -->
                <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                    <?php if ($page == $currentPage): ?>
                        <span class="relative inline-flex items-center px-4 py-2 border border-indigo-500 bg-indigo-50 dark:bg-indigo-900 text-sm font-medium text-indigo-600 dark:text-indigo-300">
                            <?= $page ?>
                        </span>
                    <?php else: ?>
                        <a href="<?= buildPaginationUrl($page, $queryParams) ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <?= $page ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Last page if not in range -->
                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300">
                            ...
                        </span>
                    <?php endif; ?>
                    <a href="<?= buildPaginationUrl($totalPages, $queryParams) ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <?= $totalPages ?>
                    </a>
                <?php endif; ?>

                <!-- Next Page -->
                <?php if ($pagination['has_next']): ?>
                    <a href="<?= buildPaginationUrl($pagination['next_page'], $queryParams) ?>" 
                       class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <span class="sr-only">Next</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                <?php else: ?>
                    <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-sm font-medium text-gray-300 dark:text-gray-600 cursor-not-allowed">
                        <span class="sr-only">Next</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</div>