<?php
//file: Views/time-tracking/inc/filters.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<!-- Time Tracking Filters -->
<div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <form id="time-filters" method="GET" action="/time-tracking" class="space-y-4">
            <!-- Filter Options -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Date Range Filter -->
                <div>
                    <label for="date_range" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
                    <select id="date_range" 
                            name="date_range" 
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="today" <?= ($filters['date_range'] ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="yesterday" <?= ($filters['date_range'] ?? '') === 'yesterday' ? 'selected' : '' ?>>Yesterday</option>
                        <option value="this_week" <?= ($filters['date_range'] ?? '') === 'this_week' ? 'selected' : '' ?>>This Week</option>
                        <option value="last_week" <?= ($filters['date_range'] ?? '') === 'last_week' ? 'selected' : '' ?>>Last Week</option>
                        <option value="this_month" <?= ($filters['date_range'] ?? '') === 'this_month' ? 'selected' : '' ?>>This Month</option>
                        <option value="last_month" <?= ($filters['date_range'] ?? '') === 'last_month' ? 'selected' : '' ?>>Last Month</option>
                    </select>
                </div>

                <!-- Project Filter -->
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project</label>
                    <select id="project_id" 
                            name="project_id" 
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Projects</option>
                        <?php foreach ($projects ?? [] as $project): ?>
                            <option value="<?= $project->id ?>" <?= ($filters['project_id'] ?? '') == $project->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($project->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- User Filter -->
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User</label>
                    <select id="user_id" 
                            name="user_id" 
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Users</option>
                        <?php foreach ($users ?? [] as $user): ?>
                            <option value="<?= $user->id ?>" <?= ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Billable Only Filter -->
                <div>
                    <label for="billable_only" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Show Only</label>
                    <select id="billable_only" 
                            name="billable_only" 
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Entries</option>
                        <option value="1" <?= ($filters['billable_only'] ?? false) ? 'selected' : '' ?>>Billable Only</option>
                    </select>
                </div>
            </div>

            <!-- Filter Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <?php if (isset($pagination) && $pagination['total'] > 0): ?>
                        Showing <?= number_format($pagination['start']) ?> 
                        to <?= number_format($pagination['end']) ?> 
                        of <?= number_format($pagination['total']) ?> time entries
                    <?php else: ?>
                        No time entries found
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center space-x-2">
                    <a href="/time-tracking" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Clear Filters
                    </a>
                    
                    <button type="submit" 
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>