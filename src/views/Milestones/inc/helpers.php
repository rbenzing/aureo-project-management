<?php
// file: Views/Milestones/inc/helpers.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Helper Functions for milestone views
function getStatusClasses($statusId) {
    $classes = [
        1 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Not Started
        2 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300', // In Progress
        3 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', // Completed
        4 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', // On Hold
        5 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300', // Delayed
    ];
    return $classes[$statusId] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

function getProgressBarColor($completion, $dueDate, $statusId) {
    // If completed, always show green
    if ($statusId == 3) {
        return 'bg-green-500 dark:bg-green-600';
    }
    
    // If delayed status, show red
    if ($statusId == 5) {
        return 'bg-red-500 dark:bg-red-600';
    }
    
    // If overdue and not close to completion, show red
    if (!empty($dueDate) && strtotime($dueDate) < time() && ($completion ?? 0) < 80) {
        return 'bg-red-500 dark:bg-red-600';
    }
    
    // If progress < 25%, show gray
    if (($completion ?? 0) < 25) {
        return 'bg-gray-500 dark:bg-gray-600';
    }
    
    // If progress 25-50%, show orange
    if (($completion ?? 0) < 50) {
        return 'bg-orange-500 dark:bg-orange-600';
    }
    
    // If progress 50-80%, show blue
    if (($completion ?? 0) < 80) {
        return 'bg-blue-500 dark:bg-blue-600';
    }
    
    // If progress > 80%, show green
    return 'bg-green-500 dark:bg-green-600';
}

function isDueDateOverdue($dueDate, $statusId) {
    return !empty($dueDate) && 
           strtotime($dueDate) < time() && 
           $statusId != 3; // Not completed
}

function calculateDaysLeft($dueDate) {
    if (empty($dueDate)) {
        return null;
    }
    
    $dueTimestamp = strtotime($dueDate);
    $now = time();
    $diff = $dueTimestamp - $now;
    
    return floor($diff / (60 * 60 * 24));
}