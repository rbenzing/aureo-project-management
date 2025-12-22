<?php

// file: Views/Milestones/inc/helpers.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Helper Functions for milestone views
// Status functions moved to centralized view_helpers.php

function getProgressBarColor($completion, $dueDate, $statusId)
{
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

function isDueDateOverdue($dueDate, $statusId)
{
    return !empty($dueDate) &&
           strtotime($dueDate) < time() &&
           $statusId != 3; // Not completed
}

function calculateDaysLeft($dueDate)
{
    if (empty($dueDate)) {
        return null;
    }

    $dueTimestamp = strtotime($dueDate);
    $now = time();
    $diff = $dueTimestamp - $now;

    return floor($diff / (60 * 60 * 24));
}
