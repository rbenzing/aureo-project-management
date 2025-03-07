<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter dropdown functionality
    const filterToggle = document.querySelector('.filter-dropdown-toggle');
    const filterMenu = document.querySelector('.filter-dropdown-menu');
    
    if (filterToggle && filterMenu) {
        filterToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            filterMenu.classList.toggle('hidden');
            
            // Close other dropdowns if open
            if (sortMenu) sortMenu.classList.add('hidden');
            if (viewMenu) viewMenu.classList.add('hidden');
        });
        
        // Prevent clicks inside the menu from closing it
        filterMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Sort dropdown functionality
    const sortToggle = document.querySelector('.sort-dropdown-toggle');
    const sortMenu = document.querySelector('.sort-dropdown-menu');
    
    if (sortToggle && sortMenu) {
        sortToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sortMenu.classList.toggle('hidden');
            
            // Close other dropdowns if open
            if (filterMenu) filterMenu.classList.add('hidden');
            if (viewMenu) viewMenu.classList.add('hidden');
        });
        
        // Prevent clicks inside the menu from closing it
        sortMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // View dropdown functionality
    const viewToggle = document.querySelector('.view-dropdown-toggle');
    const viewMenu = document.querySelector('.view-dropdown-menu');
    
    if (viewToggle && viewMenu) {
        viewToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            viewMenu.classList.toggle('hidden');
            
            // Close other dropdowns if open
            if (filterMenu) filterMenu.classList.add('hidden');
            if (sortMenu) sortMenu.classList.add('hidden');
        });
        
        // Prevent clicks inside the menu from closing it
        viewMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (filterMenu && !filterToggle.contains(e.target)) {
            filterMenu.classList.add('hidden');
        }
        
        if (sortMenu && !sortToggle.contains(e.target)) {
            sortMenu.classList.add('hidden');
        }
        
        if (viewMenu && !viewToggle.contains(e.target)) {
            viewMenu.classList.add('hidden');
        }
    });
});
</script><?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Get current search parameters for form values
$currentSearch = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$currentView = isset($_GET['by']) ? htmlspecialchars($_GET['by']) : 'tasks';
$currentStatus = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$currentCompany = isset($_GET['company_id']) ? htmlspecialchars($_GET['company_id']) : '';
?>

<!-- Toolbar for Table View -->
<div class="flex flex-wrap items-center gap-4">
    <!-- New Project Button -->
    <?php if (isset($_SESSION['user']['permissions']) && in_array('create_projects', $_SESSION['user']['permissions'])): ?>
        <a href="/projects/create" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow-sm">
            + New Project
        </a>
    <?php endif; ?>
    
    <!-- Search Form -->
    <form action="/projects" method="GET" class="flex-grow sm:flex-grow-0 flex flex-wrap md:flex-nowrap gap-2">
        <input type="hidden" name="view" value="table">
        
        <!-- Search Box -->
        <div class="relative flex-grow sm:flex-grow-0 min-w-[200px]">
            <input type="text" name="search" placeholder="Search projects" 
                value="<?= $currentSearch ?>"
                class="pl-10 pr-4 py-2 w-full bg-white dark:bg-gray-800 text-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        
        <!-- Search Button -->
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow-sm">
            Search
        </button>
        
        <!-- Clear Button (shows only when filters are active) -->
        <?php if ($currentSearch || $currentStatus || $currentCompany): ?>
            <a href="/projects?view=table" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 shadow-sm">
                Clear
            </a>
        <?php endif; ?>
    </form>
    
    <!-- View By Dropdown -->
    <div class="dropdown relative">
        <button type="button" class="view-dropdown-toggle px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm">
            <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                View: <?= ucfirst($currentView) ?>
            </span>
        </button>
        <div class="view-dropdown-menu hidden absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-10">
            <div class="py-1">
                <a href="/projects?view=table&by=projects<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 <?= $currentView === 'projects' ? 'bg-gray-100 dark:bg-gray-700' : '' ?>">
                    View By Project
                </a>
                <a href="/projects?view=table&by=tasks<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 <?= $currentView === 'tasks' ? 'bg-gray-100 dark:bg-gray-700' : '' ?>">
                    View By Task
                </a>
                <a href="/projects?view=table&by=epics<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 <?= $currentView === 'epics' ? 'bg-gray-100 dark:bg-gray-700' : '' ?>">
                    View By Epic
                </a>
                <a href="/projects?view=table&by=milestones<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 <?= $currentView === 'milestones' ? 'bg-gray-100 dark:bg-gray-700' : '' ?>">
                    View By Milestone
                </a>
                <a href="/projects?view=table&by=sprint<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 <?= $currentView === 'sprint' ? 'bg-gray-100 dark:bg-gray-700' : '' ?>">
                    View By Sprint
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Dropdown -->
    <div class="dropdown relative">
        <button type="button" class="filter-dropdown-toggle px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm">
            <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filter
                <?php if ($currentStatus || $currentCompany): ?>
                    <span class="ml-1.5 flex h-2 w-2 relative">
                        <span class="animate-none absolute inline-flex h-full w-full rounded-full bg-indigo-500"></span>
                    </span>
                <?php endif; ?>
            </span>
        </button>
        <div class="filter-dropdown-menu hidden absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-10">
            <form action="/projects" method="GET" class="p-4">
                <input type="hidden" name="view" value="table">
                <?php if ($currentSearch): ?>
                    <input type="hidden" name="search" value="<?= $currentSearch ?>">
                <?php endif; ?>
                <?php if ($currentView): ?>
                    <input type="hidden" name="by" value="<?= $currentView ?>">
                <?php endif; ?>
                
                <!-- Status Filter -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" class="appearance-none w-full px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="ready" <?= $currentStatus === 'ready' ? 'selected' : '' ?>>Ready</option>
                        <option value="in_progress" <?= $currentStatus === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="on_hold" <?= $currentStatus === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                        <option value="delayed" <?= $currentStatus === 'delayed' ? 'selected' : '' ?>>Delayed</option>
                        <option value="cancelled" <?= $currentStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <!-- Company Filter -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                    <select name="company_id" class="appearance-none w-full px-3 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Companies</option>
                        <?php if (isset($companies) && is_array($companies)): 
                            foreach ($companies as $company): ?>
                                <option value="<?= $company->id ?>" <?= $currentCompany == $company->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($company->name) ?>
                                </option>
                        <?php endforeach; 
                        endif; ?>
                    </select>
                </div>
                
                <!-- Filter Actions -->
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Apply
                    </button>
                    <a href="/projects?view=table" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Sort Dropdown -->
    <div class="dropdown relative">
        <button type="button" class="sort-dropdown-toggle px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-white border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm">
            <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
                Sort
            </span>
        </button>
        <div class="sort-dropdown-menu hidden absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-10">
            <div class="py-1">
                <a href="/projects?view=table&sort=name&dir=asc<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?><?= !empty($currentView) ? '&by=' . urlencode($currentView) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Name (A-Z)
                </a>
                <a href="/projects?view=table&sort=name&dir=desc<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?><?= !empty($currentView) ? '&by=' . urlencode($currentView) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Name (Z-A)
                </a>
                <a href="/projects?view=table&sort=created_at&dir=desc<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?><?= !empty($currentView) ? '&by=' . urlencode($currentView) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Newest First
                </a>
                <a href="/projects?view=table&sort=created_at&dir=asc<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?><?= !empty($currentView) ? '&by=' . urlencode($currentView) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Oldest First
                </a>
                <a href="/projects?view=table&sort=end_date&dir=asc<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?><?= !empty($currentView) ? '&by=' . urlencode($currentView) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Due Date (Soonest)
                </a>
                <a href="/projects?view=table&sort=end_date&dir=desc<?= !empty($currentSearch) ? '&search=' . urlencode($currentSearch) : '' ?><?= !empty($currentStatus) ? '&status=' . urlencode($currentStatus) : '' ?><?= !empty($currentCompany) ? '&company_id=' . urlencode($currentCompany) : '' ?><?= !empty($currentView) ? '&by=' . urlencode($currentView) : '' ?>" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    Due Date (Latest)
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Helper method to build URLs with updated query parameters
if (function_exists('buildUrl')) {
    // Remove function if it exists to avoid conflicts
    function buildUrl($newParams = []) {
        // This function is intentionally left empty for compatibility
        return '';
    }
}
?>