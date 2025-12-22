<?php
//file: Views/time-tracking/inc/table.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Utils\Time;

?>

<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
    <?php if (!empty($timeEntries)): ?>
        <?php foreach ($timeEntries as $entry): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                <!-- Task -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                <?php
                                $taskIcon = match($entry->task_type ?? 'task') {
                                    'bug' => '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                                    'story' => '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>',
                                    'epic' => '<svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
                                    default => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>'
                                };
            echo $taskIcon;
            ?>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                <a href="/tasks/view/<?= $entry->task_id ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                                    <?= htmlspecialchars($entry->task_title ?? 'Unknown Task') ?>
                                </a>
                            </div>
                            <?php if (!empty($entry->task_type)): ?>
                                <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                                    <?= htmlspecialchars($entry->task_type) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                
                <!-- Project -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">
                        <?= htmlspecialchars($entry->project_name ?? 'No Project') ?>
                    </div>
                </td>
                
                <!-- User -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8">
                            <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <?= strtoupper(substr($entry->first_name ?? 'U', 0, 1) . substr($entry->last_name ?? 'N', 0, 1)) ?>
                                </span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                <?= htmlspecialchars(($entry->first_name ?? '') . ' ' . ($entry->last_name ?? '')) ?>
                            </div>
                        </div>
                    </div>
                </td>
                
                <!-- Start Time -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">
                        <?= date('M j, Y', strtotime($entry->start_time)) ?>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        <?= date('g:i A', strtotime($entry->start_time)) ?>
                    </div>
                </td>
                
                <!-- Duration -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        <?= Time::formatSeconds($entry->duration ?? 0) ?>
                    </div>
                    <?php if (($entry->duration ?? 0) > 0): ?>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <?= number_format(($entry->duration ?? 0) / 3600, 2) ?> hours
                        </div>
                    <?php endif; ?>
                </td>
                
                <!-- Billable -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $entry->is_billable ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' ?>">
                        <?php if ($entry->is_billable): ?>
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Billable
                        <?php else: ?>
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 12M6 6l12 12"></path>
                            </svg>
                            Non-billable
                        <?php endif; ?>
                    </span>
                </td>
                
                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-3">
                        <a href="/time-tracking/edit/<?= $entry->id ?>" 
                           class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors"
                           title="Edit time entry">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        <button onclick="deleteTimeEntry(<?= $entry->id ?>)" 
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                title="Delete time entry">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No time entries found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No time entries match your current filters.</p>
                    <div class="mt-6">
                        <a href="/time-tracking" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Clear Filters
                        </a>
                    </div>
                </div>
            </td>
        </tr>
    <?php endif; ?>
</tbody>