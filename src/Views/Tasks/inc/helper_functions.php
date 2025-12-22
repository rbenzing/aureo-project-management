<?php

//file: Views/Tasks/inc/helper_functions.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

/**
 * Status functions moved to centralized view_helpers.php
 * Use getTaskStatusInfo() instead
 */

/**
 * Get CSS classes for task priority
 *
 * @param string $priority Priority level
 * @return string CSS classes
 */
function getPriorityClasses($priority)
{
    $classes = [
        'none' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'low' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'high' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    ];

    return $classes[$priority] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

/**
 * Get icon HTML for task status
 *
 * @param string $status Status name
 * @return string HTML for icon
 */
function getStatusIcon($status)
{
    $icons = [
        'Open' => '<svg class="w-4 h-4 text-blue-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'In Progress' => '<svg class="w-4 h-4 text-yellow-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'On Hold' => '<svg class="w-4 h-4 text-purple-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'In Review' => '<svg class="w-4 h-4 text-indigo-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
        'Completed' => '<svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'Closed' => '<svg class="w-4 h-4 text-gray-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        'Cancelled' => '<svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    ];

    return $icons[$status] ?? '<svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle></svg>';
}

/**
 * Get icon HTML for task priority
 *
 * @param string $priority Priority level
 * @return string HTML for icon
 */
function getPriorityIcon($priority)
{
    $icons = [
        'high' => '<svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>',
        'medium' => '<svg class="w-4 h-4 text-yellow-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h16l-9-11v18"></path></svg>',
        'low' => '<svg class="w-4 h-4 text-blue-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>',
        'none' => '<svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"></path></svg>',
    ];

    return $icons[$priority] ?? $icons['none'];
}

/**
 * Check if due date is overdue
 *
 * @param string $dueDate Due date string
 * @param string $status Task status
 * @return bool True if overdue
 */
function isDueDateOverdue($dueDate, $status)
{
    return !empty($dueDate) &&
           strtotime($dueDate) < time() &&
           $status !== 'Completed' &&
           $status !== 'Closed' &&
           $status !== 'Cancelled';
}

/**
 * Check if due date is today using configured timezone
 *
 * @param string $dueDate Due date string
 * @return bool True if due today
 */
function isDueToday($dueDate)
{
    if (empty($dueDate)) {
        return false;
    }

    try {
        $settingsService = \App\Services\SettingsService::getInstance();
        $timezone = $settingsService->getDefaultTimezone();

        $due = new DateTime($dueDate, new DateTimeZone($timezone));
        $now = new DateTime('now', new DateTimeZone($timezone));

        return $due->format('Y-m-d') === $now->format('Y-m-d');
    } catch (Exception $e) {
        // Fallback to original method
        return date('Y-m-d', strtotime($dueDate)) === date('Y-m-d');
    }
}

/**
 * Get due date display with icon
 *
 * @param string $dueDate Due date string
 * @param string $status Task status
 * @return array Contains class and icon HTML
 */
function getDueDateDisplay($dueDate, $status)
{
    if (empty($dueDate)) {
        return [
            'class' => 'text-gray-500 dark:text-gray-400 flex items-center',
            'icon' => '<svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-dasharray="2"></path></svg>',
            'badge' => '',
        ];
    }

    $dueDateObj = strtotime($dueDate);
    $today = strtotime('today');
    $tomorrow = strtotime('tomorrow');

    if (isDueDateOverdue($dueDate, $status)) {
        return [
            'class' => 'text-red-600 dark:text-red-400 font-semibold flex items-center',
            'icon' => '<svg class="w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'badge' => '<span class="ml-2 text-xs bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 px-2 py-0.5 rounded">Overdue</span>',
        ];
    } elseif (isDueToday($dueDate)) {
        return [
            'class' => 'text-yellow-600 dark:text-yellow-400 font-semibold flex items-center',
            'icon' => '<svg class="w-4 h-4 mr-1 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'badge' => '<span class="ml-2 text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 px-2 py-0.5 rounded">Today</span>',
        ];
    } else {
        return [
            'class' => 'text-gray-900 dark:text-gray-200 flex items-center',
            'icon' => '<svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
            'badge' => '',
        ];
    }
}
