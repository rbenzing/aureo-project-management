<?php
//file: Views/Layouts/view_helpers.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Utils\Time;

/**
 * Common view helper functions for consistent UI across all views
 */

/**
 * Format time duration for display
 */
function formatTime(?int $seconds): string
{
    return Time::formatSeconds($seconds);
}

/**
 * Format due date with relative time
 */
function formatDueDate(?string $dueDate): string
{
    if (empty($dueDate)) {
        return 'No due date';
    }
    
    $due = new DateTime($dueDate);
    $now = new DateTime();
    $diff = $now->diff($due);
    
    if ($due < $now) {
        return 'Overdue by ' . $diff->format('%a days');
    } elseif ($diff->days == 0) {
        return 'Due today';
    } elseif ($diff->days == 1) {
        return 'Due tomorrow';
    } else {
        return 'Due in ' . $diff->days . ' days';
    }
}

/**
 * Format overdue date
 */
function formatOverdueDate(?string $dueDate): string
{
    if (empty($dueDate)) {
        return '';
    }
    
    $due = new DateTime($dueDate);
    $now = new DateTime();
    $diff = $now->diff($due);
    
    if ($due < $now) {
        return 'Overdue by ' . $diff->format('%a days');
    }
    
    return '';
}

/**
 * Check if a date is due soon (within 3 days)
 */
function isDueSoon(?string $dueDate): bool
{
    if (empty($dueDate)) {
        return false;
    }
    
    $due = new DateTime($dueDate);
    $now = new DateTime();
    $diff = $now->diff($due);
    
    return $due > $now && $diff->days <= 3;
}

/**
 * Get CSS classes for task status
 */
function getTaskStatusClass(int $statusId): string
{
    $statusClasses = [
        1 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // New
        2 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', // In Progress
        3 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', // On Hold
        4 => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200', // Under Review
        5 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', // Closed
        6 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', // Completed
    ];
    
    return $statusClasses[$statusId] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

/**
 * Get CSS classes for project status
 */
function getProjectStatusClass(int $statusId): string
{
    $statusClasses = [
        1 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Planning
        2 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', // Active
        3 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200', // On Hold
        4 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', // Completed
        5 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', // Cancelled
    ];
    
    return $statusClasses[$statusId] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

/**
 * Get CSS classes for milestone status
 */
function getMilestoneStatusClass(int $statusId): string
{
    $statusClasses = [
        1 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Planning
        2 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', // In Progress
        3 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', // Completed
        4 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', // Cancelled
    ];
    
    return $statusClasses[$statusId] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

/**
 * Get CSS classes for sprint status
 */
function getSprintStatusClass(int $statusId): string
{
    $statusClasses = [
        1 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Planning
        2 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', // Active
        3 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', // Completed
        4 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200', // Cancelled
    ];
    
    return $statusClasses[$statusId] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

/**
 * Get priority badge classes
 */
function getPriorityClass(string $priority): string
{
    $priorityClasses = [
        'none' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'high' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    ];
    
    return $priorityClasses[$priority] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

/**
 * Generate pagination HTML
 */
function renderPagination(array $pagination, array $filters = []): string
{
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $queryString = http_build_query(array_filter($filters));
    $baseUrl = $queryString ? '&' . $queryString : '';
    
    $html = '<div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6 mt-6 rounded-lg shadow">';
    
    // Mobile pagination
    $html .= '<div class="flex-1 flex justify-between sm:hidden">';
    if ($pagination['current_page'] > 1) {
        $html .= '<a href="?page=' . ($pagination['current_page'] - 1) . $baseUrl . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">Previous</a>';
    }
    if ($pagination['current_page'] < $pagination['total_pages']) {
        $html .= '<a href="?page=' . ($pagination['current_page'] + 1) . $baseUrl . '" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">Next</a>';
    }
    $html .= '</div>';
    
    // Desktop pagination
    $html .= '<div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">';
    $html .= '<div><p class="text-sm text-gray-700 dark:text-gray-300">Showing <span class="font-medium">' . $pagination['start'] . '</span> to <span class="font-medium">' . $pagination['end'] . '</span> of <span class="font-medium">' . $pagination['total'] . '</span> results</p></div>';
    
    $html .= '<div><nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">';
    
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        $isActive = $i === $pagination['current_page'];
        $classes = $isActive 
            ? 'z-10 bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-600 dark:text-indigo-400'
            : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600';
        
        $html .= '<a href="?page=' . $i . $baseUrl . '" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium ' . $classes . '">' . $i . '</a>';
    }
    
    $html .= '</nav></div></div></div>';
    
    return $html;
}

/**
 * Generate search and filter form
 */
function renderSearchFilters(array $options = []): string
{
    $defaults = [
        'search_placeholder' => 'Search...',
        'show_status_filter' => true,
        'show_priority_filter' => false,
        'show_project_filter' => false,
        'show_user_filter' => false,
        'additional_filters' => []
    ];
    
    $options = array_merge($defaults, $options);
    
    $html = '<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">';
    $html .= '<form method="GET" class="flex flex-wrap items-center gap-4">';
    
    // Search input
    $html .= '<div class="flex-1 min-w-64">';
    $html .= '<label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>';
    $html .= '<input type="text" name="search" id="search" value="' . htmlspecialchars($_GET['search'] ?? '') . '" placeholder="' . htmlspecialchars($options['search_placeholder']) . '" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">';
    $html .= '</div>';
    
    // Status filter
    if ($options['show_status_filter']) {
        $html .= '<div class="flex-1 min-w-48">';
        $html .= '<label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>';
        $html .= '<select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">';
        $html .= '<option value="">All Statuses</option>';
        // Status options would be populated by the controller
        $html .= '</select>';
        $html .= '</div>';
    }
    
    // Priority filter
    if ($options['show_priority_filter']) {
        $html .= '<div class="flex-1 min-w-48">';
        $html .= '<label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>';
        $html .= '<select name="priority" id="priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100">';
        $html .= '<option value="">All Priorities</option>';
        $html .= '<option value="high"' . (($_GET['priority'] ?? '') === 'high' ? ' selected' : '') . '>High</option>';
        $html .= '<option value="medium"' . (($_GET['priority'] ?? '') === 'medium' ? ' selected' : '') . '>Medium</option>';
        $html .= '<option value="low"' . (($_GET['priority'] ?? '') === 'low' ? ' selected' : '') . '>Low</option>';
        $html .= '<option value="none"' . (($_GET['priority'] ?? '') === 'none' ? ' selected' : '') . '>None</option>';
        $html .= '</select>';
        $html .= '</div>';
    }
    
    // Submit button
    $html .= '<div class="flex items-end">';
    $html .= '<button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">Filter</button>';
    $html .= '</div>';
    
    $html .= '</form>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Generate action buttons for list views
 */
function renderActionButtons(string $entityType, int $id, array $permissions = []): string
{
    $html = '<div class="flex items-center space-x-2">';
    
    // View button
    $html .= '<a href="/' . $entityType . '/view/' . $id . '" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">View</a>';
    
    // Edit button
    if (empty($permissions) || in_array('edit', $permissions)) {
        $html .= '<a href="/' . $entityType . '/edit/' . $id . '" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300 text-sm">Edit</a>';
    }
    
    // Delete button
    if (empty($permissions) || in_array('delete', $permissions)) {
        $html .= '<button onclick="deleteEntity(\'' . $entityType . '\', ' . $id . ')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-sm">Delete</button>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Generate timer controls for tasks
 */
function renderTimerControls(int $taskId, bool $hasActiveTimer = false): string
{
    if ($hasActiveTimer) {
        return '<form action="/timer/stop" method="POST" class="inline">
                    <input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'] ?? '') . '">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-sm flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                        </svg>
                        Stop
                    </button>
                </form>';
    }
    
    return '<form action="/timer/start" method="POST" class="inline">
                <input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'] ?? '') . '">
                <input type="hidden" name="task_id" value="' . $taskId . '">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-md text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Start
                </button>
            </form>';
}

/**
 * Generate breadcrumb navigation
 */
function renderBreadcrumbs(array $breadcrumbs): string
{
    if (empty($breadcrumbs)) {
        return '';
    }
    
    $html = '<nav class="flex mb-6" aria-label="Breadcrumb">';
    $html .= '<ol class="inline-flex items-center space-x-1 md:space-x-3">';
    
    foreach ($breadcrumbs as $index => $crumb) {
        $isLast = $index === count($breadcrumbs) - 1;
        
        $html .= '<li class="inline-flex items-center">';
        
        if ($index > 0) {
            $html .= '<svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>';
        }
        
        if ($isLast) {
            $html .= '<span class="ml-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ml-2">' . htmlspecialchars($crumb['title']) . '</span>';
        } else {
            $html .= '<a href="' . htmlspecialchars($crumb['url']) . '" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">';
            if (isset($crumb['icon'])) {
                $html .= $crumb['icon'];
            }
            $html .= htmlspecialchars($crumb['title']) . '</a>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ol>';
    $html .= '</nav>';
    
    return $html;
}
