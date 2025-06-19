<?php
// file: Views/Sprints/inc/helpers.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

/**
 * Status functions moved to centralized view_helpers.php
 * Use getSprintStatusInfo() and getTaskStatusInfo() instead
 */

/**
 * Gets the priority classes for sprint views
 *
 * @param string $priority
 * @return string CSS class
 */
function getSprintPriorityClass($priority) {
    return match($priority) {
        'high' => 'text-red-600 dark:text-red-400 font-medium',
        'medium' => 'text-yellow-600 dark:text-yellow-400',
        'low' => 'text-blue-600 dark:text-blue-400',
        default => 'text-gray-500 dark:text-gray-400'
    };
}