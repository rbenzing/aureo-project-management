<?php
// file: Views/Milestones/inc/table_header.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Calculate summary counts
$totalCount = count($milestones);
$inProgressCount = 0;
$atRiskCount = 0;
$completedCount = 0;

if (!empty($milestones)) {
    foreach ($milestones as $milestone) {
        if ($milestone->status_id == 2) { // In Progress status
            $inProgressCount++;
        } elseif ($milestone->status_id == 3) { // Completed status
            $completedCount++;
        } elseif ($milestone->status_id == 5 || // Delayed status
               ($milestone->status_id != 3 && !empty($milestone->due_date) && strtotime($milestone->due_date) < time())) {
            $atRiskCount++;
        }
    }
}

// Permission checks
$canEditMilestones = isset($_SESSION['user']['permissions']) && in_array('edit_milestones', $_SESSION['user']['permissions']);
$canDeleteMilestones = isset($_SESSION['user']['permissions']) && in_array('delete_milestones', $_SESSION['user']['permissions']);
$canCreateMilestones = isset($_SESSION['user']['permissions']) && in_array('create_milestones', $_SESSION['user']['permissions']);
?>

<!-- Milestone Stats Summary -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
        <div class="rounded-full bg-blue-100 dark:bg-blue-900 p-3 mr-4">
            <svg class="w-6 h-6 text-blue-500 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                <?= $totalCount ?>
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
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">In Progress</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                <?= $inProgressCount ?>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
        <div class="rounded-full bg-red-100 dark:bg-red-900 p-3 mr-4">
            <svg class="w-6 h-6 text-red-500 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">At Risk/Delayed</div>
            <div class="text-xl font-semibold text-red-600 dark:text-red-400">
                <?= $atRiskCount ?>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center">
        <div class="rounded-full bg-green-100 dark:bg-green-900 p-3 mr-4">
            <svg class="w-6 h-6 text-green-500 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</div>
            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                <?= $completedCount ?>
            </div>
        </div>
    </div>
</div>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Milestones</h1>
    
    <div class="flex space-x-4">
        <!-- Filter Dropdown -->
        <div class="relative">
            <select id="milestone-filter" class="appearance-none w-40 px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="all">All Milestones</option>
                <option value="epic">Epics Only</option>
                <option value="milestone">Regular Milestones</option>
                <option value="overdue">Overdue</option>
                <option value="in-progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="on-hold">On Hold</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>

        <!-- Project Filter (If applicable) -->
        <?php if (!empty($projects)): ?>
        <div class="relative">
            <select id="project-filter" class="appearance-none w-48 px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Projects</option>
                <?php foreach ($projects as $project): ?>
                <option value="<?= $project->id ?>"><?= htmlspecialchars($project->name) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search -->
        <div class="relative">
            <input 
                type="search" 
                id="milestone-search"
                placeholder="Search milestones..." 
                class="w-64 px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <!-- New Milestone Button -->
        <?php if ($canCreateMilestones): ?>
        <a 
            href="/milestones/create" 
            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
            + New Milestone
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- View Toggle -->
<div class="mb-4 border-b border-gray-200 dark:border-gray-700">
    <ul class="flex flex-wrap -mb-px">
        <li class="mr-2">
            <button id="table-view-btn" class="inline-block py-2 px-4 text-sm font-medium text-center text-indigo-600 border-b-2 border-indigo-600 active">
                <svg class="w-5 h-5 mr-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Table View
            </button>
        </li>
        <li class="mr-2">
            <button id="timeline-view-btn" class="inline-block py-2 px-4 text-sm font-medium text-center text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                <svg class="w-5 h-5 mr-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Timeline View
            </button>
        </li>
        <li class="mr-2">
            <button id="card-view-btn" class="inline-block py-2 px-4 text-sm font-medium text-center text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                <svg class="w-5 h-5 mr-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                Card View
            </button>
        </li>
    </ul>
</div>