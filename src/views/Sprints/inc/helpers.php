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
 * Get CSS class for task status
 * 
 * @param string $statusName
 * @return string CSS class
 */
function getTaskStatusClass($statusName) {
    return match($statusName) {
        'Open' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'On Hold' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'In Review' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        'Closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'Completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'Cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    };
}