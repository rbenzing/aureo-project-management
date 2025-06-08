<?php
//file: Views/Sprints/index.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;

// Include helper functions
include_once __DIR__ . '/inc/helpers.php';
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprints - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6 flex-grow">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <!-- Project Selection or Project-Specific Content -->
        <?php if (empty($project)): ?>
            <?php include __DIR__ . '/inc/project_selection.php'; ?>
        <?php else: ?>
            <!-- Project is selected - display sprints -->
            
            <!-- Sprint Stats Summary -->
            <?php include __DIR__ . '/inc/sprint_stats.php'; ?>

            <!-- Project Header with Navigation -->
            <?php include __DIR__ . '/inc/project_header.php'; ?>

            <!-- Page Header with Filters -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                <div class="flex items-center">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Sprints</h2>
                    <?php if (!empty($sprints) && $activeSprintCount > 0): ?>
                        <span class="ml-3 px-2.5 py-0.5 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 text-xs font-medium rounded-full">
                            Active Sprint
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="flex flex-col md:flex-row gap-3">
                    <!-- Status Filter -->
                    <div class="relative min-w-[160px]">
                        <select id="status-filter" class="h-10 appearance-none w-full px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-10 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="all">All Statuses</option>
                            <option value="1">Planning</option>
                            <option value="2">Active</option>
                            <option value="3">Delayed</option>
                            <option value="4">Completed</option>
                            <option value="5">Cancelled</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                        </div>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="relative flex-grow min-w-[200px]">
                        <input
                            id="sprint-search"
                            type="search"
                            placeholder="Search sprints..."
                            class="h-10 w-full appearance-none py-2 pr-10 pl-10 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Sprint Panel (if exists) -->
            <?php 
            $activeSprint = null;
            if (!empty($sprints)) {
                foreach ($sprints as $sprint) {
                    if (isset($sprint->status_id) && $sprint->status_id == 2) { // Active status
                        $activeSprint = $sprint;
                        break;
                    }
                }
            }
            
            if ($activeSprint): 
                include __DIR__ . '/inc/active_sprint.php';
            endif; 
            ?>

            <!-- All Sprints List -->
            <?php include __DIR__ . '/inc/sprint_list.php'; ?>

            <!-- Pagination (if needed) -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="mt-6 flex justify-between items-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Page <?= $page ?? 1 ?> of <?= $totalPages ?>
                    </div>
                    <div class="flex space-x-2">
                        <?php if (($page ?? 1) > 1): ?>
                            <a 
                                href="/sprints/project/<?= $project->id ?? 0 ?>&page=<?= ($page ?? 1) - 1 ?>" 
                                class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Previous
                            </a>
                        <?php endif; ?>
                        <?php if (($page ?? 1) < $totalPages): ?>
                            <a 
                                href="/sprints/project/<?= $project->id ?? 0 ?>&page=<?= ($page ?? 1) + 1 ?>" 
                                class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <!-- JavaScript for Filtering -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('status-filter');
            const searchInput = document.getElementById('sprint-search');
            const sprintRows = document.querySelectorAll('.sprint-row');
            
            function filterSprints() {
                const statusValue = statusFilter ? statusFilter.value : 'all';
                const searchText = searchInput ? searchInput.value.toLowerCase() : '';
                
                sprintRows.forEach(row => {
                    let show = true;
                    
                    // Status filter
                    if (statusValue !== 'all' && row.dataset.status !== statusValue) {
                        show = false;
                    }
                    
                    // Search filter
                    if (searchText && !row.dataset.name.toLowerCase().includes(searchText)) {
                        show = false;
                    }
                    
                    row.style.display = show ? '' : 'none';
                });
                
                // Check if any visible rows
                const visibleRows = Array.from(sprintRows).filter(row => row.style.display !== 'none');
                const noResultsRow = document.getElementById('no-results-row');
                
                if (visibleRows.length === 0) {
                    if (!noResultsRow) {
                        const tbody = document.getElementById('sprints-list');
                        if (tbody) {
                            const newRow = document.createElement('tr');
                            newRow.id = 'no-results-row';
                            newRow.innerHTML = `
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No sprints match your filters. <a href="#" onclick="resetFilters(); return false;" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Reset filters</a>
                                </td>
                            `;
                            tbody.appendChild(newRow);
                        }
                    }
                } else if (noResultsRow) {
                    noResultsRow.remove();
                }
            }
            
            // Add event listeners
            if (statusFilter) {
                statusFilter.addEventListener('change', filterSprints);
            }
            
            if (searchInput) {
                searchInput.addEventListener('input', filterSprints);
            }
            
            // Function to reset filters (for the "Reset filters" link)
            window.resetFilters = function() {
                if (statusFilter) statusFilter.value = 'all';
                if (searchInput) searchInput.value = '';
                filterSprints();
            };
        });
    </script>
</body>
</html>