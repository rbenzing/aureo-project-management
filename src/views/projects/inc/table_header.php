<!-- Toolbar for Table View -->
<div class="flex flex-wrap items-center gap-4">
    <?php if (isset($_SESSION['user']['permissions']) && in_array('create_projects', $_SESSION['user']['permissions'])): ?>
        <a href="/projects/create" class="px-4 py-2 bg-indigo-100 dark:bg-indigo-700 text-black dark:text-white rounded-md hover:bg-indigo-800">+ New Project</a>
    <?php endif; ?>
    <form action="/projects" method="GET" class="flex-grow sm:flex-grow-0 flex gap-4">
        <input type="hidden" name="view" value="table">
        <div class="relative flex-grow sm:flex-grow-0">
            <input type="text" name="search" placeholder="Search projects" 
                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                class="pl-10 pr-4 py-2 appearance-none w-60 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <div class="relative">
            <select name="by" class="appearance-none w-40 px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="projects">View By Project</option>
                <option value="epics">View By Epic</option>
                <option value="milestones">View By Milestone</option>
                <option value="sprint">View By Sprint</option>
                <option selected value="tasks">View By Task</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
        <?php if (isset($_GET['search']) || isset($_GET['status']) || isset($_GET['company_id'])): ?>
            <a href="/projects?view=table" class="px-4 py-2 bg-gray-800 text-white border border-gray-300 rounded-md hover:bg-gray-600">
                Clear Search
            </a>
        <?php endif; ?>
    </form>

    
    
    <div class="dropdown relative">
        <button class="px-4 py-2 text-white border border-gray-300 bg-gray-800 rounded-md hover:bg-gray-700">
            ☑ Filter
        </button>
        <div class="dropdown-menu hidden absolute right-0 mt-2 w-56 bg-gray-800 border border-gray-700 rounded-md shadow-lg z-10">
            <form action="/projects" method="GET" class="p-4">
                <input type="hidden" name="view" value="table">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-400 mb-1">Status</label>
                    <select name="status" class="appearance-none w-40 px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="ready">Ready</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="on_hold">On Hold</option>
                        <option value="delayed">Delayed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-400 mb-1">Company</label>
                    <select name="company_id" class="appearance-none w-40 px-4 py-2 dark:text-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Companies</option>
                        <?php 
                        // Ideally, this would come from a CompanyModel query in the controller
                        if (isset($companies) && is_array($companies)): 
                            foreach ($companies as $company): 
                        ?>
                            <option value="<?= $company->id ?>"><?= htmlspecialchars($company->name) ?></option>
                        <?php 
                            endforeach; 
                        endif; 
                        ?>
                    </select>
                </div>
                
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Apply Filters
                </button>
            </form>
        </div>
    </div>
    
    <div class="dropdown relative">
        <button class="px-4 py-2 text-white border border-gray-300 rounded-md bg-gray-800 hover:bg-gray-700">
            ↕ Sort
        </button>
        <div class="dropdown-menu hidden absolute right-0 mt-2 w-56 bg-gray-800 border border-gray-700 rounded-md shadow-lg z-10">
            <div class="p-2">
                <a href="/projects?view=table&sort=name&dir=asc" class="block px-4 py-2 text-white hover:bg-gray-700 rounded-md">Name (A-Z)</a>
                <a href="/projects?view=table&sort=name&dir=desc" class="block px-4 py-2 text-white hover:bg-gray-700 rounded-md">Name (Z-A)</a>
                <a href="/projects?view=table&sort=created_at&dir=desc" class="block px-4 py-2 text-white hover:bg-gray-700 rounded-md">Newest First</a>
                <a href="/projects?view=table&sort=created_at&dir=asc" class="block px-4 py-2 text-white hover:bg-gray-700 rounded-md">Oldest First</a>
                <a href="/projects?view=table&sort=end_date&dir=asc" class="block px-4 py-2 text-white hover:bg-gray-700 rounded-md">End Date (Soonest)</a>
            </div>
        </div>
    </div>
</div>