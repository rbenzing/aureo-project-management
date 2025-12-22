<?php
//file: Views/Tasks/inc/table_header.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Get current sort parameters from controller or fallback to $_GET
$taskSortField = isset($currentSortField) ? $currentSortField : (isset($_GET['task_sort']) ? htmlspecialchars($_GET['task_sort']) : 'due_date');
$taskSortDir = isset($currentSortDirection) ? $currentSortDirection : (isset($_GET['task_dir']) && $_GET['task_dir'] === 'desc' ? 'desc' : 'asc');

/**
 * Generate sort indicator icon based on field and current sort state
 *
 * @param string $field Field name
 * @param string $currentField Current sorted field
 * @param string $currentDir Current sort direction
 * @return string HTML
 */
function getSortIndicator($field, $currentField, $currentDir)
{
    if ($field === $currentField) {
        return $currentDir === 'asc'
            ? '<svg class="w-4 h-4 ml-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>'
            : '<svg class="w-4 h-4 ml-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>';
    }

    return '<svg class="w-4 h-4 ml-1 text-gray-400 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>';
}

/**
 * Generate URL for column sorting
 *
 * @param string $field Field to sort by
 * @param string $currentField Current sorted field
 * @param string $currentDir Current sort direction
 * @return string URL
 */
function getSortUrl($field, $currentField, $currentDir)
{
    $newDir = ($field === $currentField && $currentDir === 'asc') ? 'desc' : 'asc';

    // Determine base URL based on current context
    $currentPath = $_SERVER['REQUEST_URI'] ?? '';
    $baseUrl = '/tasks';

    if (strpos($currentPath, '/tasks/assigned/') !== false && isset($GLOBALS['userId'])) {
        $baseUrl = "/tasks/assigned/{$GLOBALS['userId']}";
    } elseif (strpos($currentPath, '/tasks/unassigned') !== false) {
        $baseUrl = '/tasks/unassigned';
    } elseif (strpos($currentPath, '/tasks/project/') !== false && isset($GLOBALS['projectId'])) {
        $baseUrl = "/tasks/project/{$GLOBALS['projectId']}";
    }

    $queryParams = $_GET;
    $queryParams['task_sort'] = $field;
    $queryParams['task_dir'] = $newDir;

    return $baseUrl . '?' . http_build_query($queryParams);
}
?>

<thead class="bg-gray-50 dark:bg-gray-700">
    <tr>
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <a href="<?= getSortUrl('title', $taskSortField, $taskSortDir) ?>" class="group inline-flex items-center">
                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Task
                <?= getSortIndicator('title', $taskSortField, $taskSortDir) ?>
            </a>
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <a href="<?= getSortUrl('project_name', $taskSortField, $taskSortDir) ?>" class="group inline-flex items-center">
                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                Project
                <?= getSortIndicator('project_name', $taskSortField, $taskSortDir) ?>
            </a>
        </th>
        <?php if (!$isMyTasksView): // Only show assignee column in backlog view?>
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <a href="<?= getSortUrl('assigned_to', $taskSortField, $taskSortDir) ?>" class="group inline-flex items-center">
                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Assignee
                <?= getSortIndicator('assigned_to', $taskSortField, $taskSortDir) ?>
            </a>
        </th>
        <?php endif; ?>
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <a href="<?= getSortUrl('priority', $taskSortField, $taskSortDir) ?>" class="group inline-flex items-center">
                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7m-7-7v18"></path>
                </svg>
                Priority
                <?= getSortIndicator('priority', $taskSortField, $taskSortDir) ?>
            </a>
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">
            <a href="<?= getSortUrl('status_id', $taskSortField, $taskSortDir) ?>" class="group inline-flex items-center whitespace-nowrap">
                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Status
                <?= getSortIndicator('status_id', $taskSortField, $taskSortDir) ?>
            </a>
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-36">
            <a href="<?= getSortUrl('due_date', $taskSortField, $taskSortDir) ?>" class="group inline-flex items-center whitespace-nowrap">
                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Due Date
                <?= getSortIndicator('due_date', $taskSortField, $taskSortDir) ?>
            </a>
        </th>
        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <a href="<?= getSortUrl('time_spent', $taskSortField, $taskSortDir) ?>" class="group inline-flex items-center">
                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Time
                <?= getSortIndicator('time_spent', $taskSortField, $taskSortDir) ?>
            </a>
        </th>
        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <span class="inline-flex items-center">
                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Actions
            </span>
        </th>
    </tr>
</thead>