<?php
//file: Views/Tasks/inc/helper_functions.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

/**
 * Get CSS classes for task status
 * 
 * @param string $status Status name
 * @return string CSS classes
 */
function getStatusClasses($status) {
    $classes = [
        'Open' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'In Progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'On Hold' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'In Review' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        'Completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'Closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'Cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

/**
 * Get CSS classes for task priority
 * 
 * @param string $priority Priority level
 * @return string CSS classes
 */
function getPriorityClasses($priority) {
    $classes = [
        'none' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'high' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    ];
    return $classes[$priority] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

/**
 * Check if due date is overdue
 * 
 * @param string $dueDate Due date string
 * @param string $status Task status
 * @return bool True if overdue
 */
function isDueDateOverdue($dueDate, $status) {
    return !empty($dueDate) && 
           strtotime($dueDate) < time() && 
           $status !== 'Completed' && 
           $status !== 'Closed' && 
           $status !== 'Cancelled';
}

/**
 * Check if due date is today
 * 
 * @param string $dueDate Due date string
 * @return bool True if due today
 */
function isDueToday($dueDate) {
    return !empty($dueDate) && 
           date('Y-m-d', strtotime($dueDate)) === date('Y-m-d');
}

/**
 * Format time spent in seconds to human-readable format
 * 
 * @param int $seconds Time in seconds
 * @return string Formatted time (e.g., "2h 30m")
 */
function formatTimeSpent($seconds) {
    if ($seconds <= 0) return '0h 0m';
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    return $hours . 'h ' . $minutes . 'm';
}