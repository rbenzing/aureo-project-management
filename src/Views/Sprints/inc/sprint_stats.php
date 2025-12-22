<?php
// file: Views/Sprints/inc/sprint_stats.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Calculate sprint counts by status
$activeSprintCount = 0;
$planningCount = 0;
$completedSprintCount = 0;
$totalVelocity = 0;
$velocityCount = 0;

if (!empty($sprints)) {
    foreach ($sprints as $sprint) {
        if (isset($sprint->status_id)) {
            switch ($sprint->status_id) {
                case 1: // Planning
                    $planningCount++;

                    break;
                case 2: // Active
                    $activeSprintCount++;

                    break;
                case 4: // Completed
                    $completedSprintCount++;
                    if (isset($sprint->velocity_percentage)) {
                        $totalVelocity += $sprint->velocity_percentage;
                        $velocityCount++;
                    }

                    break;
            }
        }
    }
}

// Calculate average velocity
$avgVelocity = $velocityCount > 0 ? round($totalVelocity / $velocityCount) : 0;
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
        <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3 mr-4">
            <svg class="w-6 h-6 text-blue-500 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sprints</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                <?= !empty($sprints) ? count($sprints) : 0 ?>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
        <div class="rounded-full bg-green-100 dark:bg-green-900 p-3 mr-4">
            <svg class="w-6 h-6 text-green-500 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Sprint</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                <?= $activeSprintCount ?>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
        <div class="rounded-full bg-yellow-100 dark:bg-yellow-900 p-3 mr-4">
            <svg class="w-6 h-6 text-yellow-500 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Planning</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                <?= $planningCount ?>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
        <div class="rounded-full bg-purple-100 dark:bg-purple-900 p-3 mr-4">
            <svg class="w-6 h-6 text-purple-500 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Velocity</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                <?= $completedSprintCount > 0 ? $avgVelocity . '%' : 'N/A' ?>
            </div>
        </div>
    </div>
</div>