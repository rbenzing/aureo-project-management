<?php
// file: Views/Sprints/inc/helpers.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

/**
 * Get CSS class for sprint status
 * 
 * @param int $statusId
 * @return string CSS class
 */
function getSprintStatusClass($statusId) {
    return match((int)$statusId) {
        1 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', // Planning
        2 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', // Active
        3 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300', // Delayed
        4 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300', // Completed
        5 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Cancelled
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    };
}

/**
 * Get label for sprint status
 * 
 * @param int $statusId
 * @return string Status label
 */
function getSprintStatusLabel($statusId) {
    return match((int)$statusId) {
        1 => 'Planning',
        2 => 'Active',
        3 => 'Delayed',
        4 => 'Completed',
        5 => 'Cancelled',
        default => 'Unknown'
    };
}

/**
 * Get CSS class for task status - Updated for consistency
 *
 * @param string $statusName
 * @return string CSS class
 */
function getTaskStatusClass($statusName) {
    // Map status names to IDs for consistent styling
    $statusNameToId = [
        'Open' => 1,
        'In Progress' => 2,
        'On Hold' => 3,
        'In Review' => 4,
        'Closed' => 5,
        'Completed' => 6,
        'Cancelled' => 7
    ];

    $statusMap = [
        1 => ['label' => 'OPEN', 'color' => 'bg-blue-600'],
        2 => ['label' => 'IN PROGRESS', 'color' => 'bg-yellow-500'],
        3 => ['label' => 'ON HOLD', 'color' => 'bg-purple-500'],
        4 => ['label' => 'IN REVIEW', 'color' => 'bg-indigo-500'],
        5 => ['label' => 'CLOSED', 'color' => 'bg-gray-500'],
        6 => ['label' => 'COMPLETED', 'color' => 'bg-green-500'],
        7 => ['label' => 'CANCELLED', 'color' => 'bg-red-500']
    ];

    $statusId = $statusNameToId[$statusName] ?? 1;
    $status = $statusMap[$statusId] ?? ['label' => 'UNKNOWN', 'color' => 'bg-gray-500'];
    return $status['color'] . ' bg-opacity-20 text-white';
}

/**
 * Gets the priority classes
 * 
 * @param string $priority
 * @return string CSS class
 */
function getPriorityClass($priority) {
    return match($priority) {
        'high' => 'text-red-600 dark:text-red-400 font-medium',
        'medium' => 'text-yellow-600 dark:text-yellow-400',
        'low' => 'text-blue-600 dark:text-blue-400',
        default => 'text-gray-500 dark:text-gray-400'
    };
}