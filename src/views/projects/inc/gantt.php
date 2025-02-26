<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<!-- Gantt Chart View -->
<div class="bg-gray-800 rounded-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Project Gantt Chart</h2>
        
        <div class="flex space-x-4">
            <button class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600">Week</button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-md">Month</button>
            <button class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600">Quarter</button>
            <button class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600">Year</button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <div class="min-w-[1200px]">
            <!-- Gantt header -->
            <div class="flex">
                <div class="w-64 p-2 font-medium">Project</div>
                <?php
                // Generate month columns for the current year
                $currentYear = date('Y');
                $months = [];
                
                for ($month = 1; $month <= 12; $month++) {
                    $monthName = date('M', strtotime("{$currentYear}-{$month}-01"));
                    $months[] = $monthName;
                }
                
                foreach ($months as $month): 
                ?>
                <div class="w-24 p-2 text-center border-l border-gray-700 font-medium"><?= $month ?></div>
                <?php endforeach; ?>
            </div>
            
            <!-- Projects Gantt bars -->
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $project): ?>
                    <div class="flex border-t border-gray-700">
                        <div class="w-64 p-2">
                            <div class="font-medium truncate"><?= htmlspecialchars($project->name) ?></div>
                            <div class="text-sm text-gray-400 truncate"><?= htmlspecialchars($project->company_name ?? '') ?></div>
                        </div>
                        
                        <?php
                        // Calculate project position in the timeline
                        $startDate = isset($project->start_date) ? strtotime($project->start_date) : null;
                        $endDate = isset($project->end_date) ? strtotime($project->end_date) : null;
                        
                        // Get status color
                        $statusColor = '';
                        switch ($project->status_id) {
                            case 1: // Ready
                                $statusColor = 'bg-blue-600';
                                break;
                            case 2: // In Progress
                                $statusColor = 'bg-yellow-600';
                                break;
                            case 3: // Completed
                                $statusColor = 'bg-green-600';
                                break;
                            case 4: // On Hold
                                $statusColor = 'bg-purple-600';
                                break;
                            case 5: // Delayed
                                $statusColor = 'bg-red-600';
                                break;
                            case 6: // Cancelled
                                $statusColor = 'bg-gray-600';
                                break;
                            default:
                                $statusColor = 'bg-gray-600';
                        }
                        
                        // Determine which months the project spans
                        $projectMonths = [];
                        $startMonth = $startDate ? date('n', $startDate) : 1;
                        $endMonth = $endDate ? date('n', $endDate) : 12;
                        
                        // Handle projects spanning across years
                        if ($startDate && date('Y', $startDate) < $currentYear) {
                            $startMonth = 1;
                        }
                        if ($endDate && date('Y', $endDate) > $currentYear) {
                            $endMonth = 12;
                        }
                        
                        for ($month = 1; $month <= 12; $month++):
                            $isActive = ($month >= $startMonth && $month <= $endMonth);
                            $isStart = ($month == $startMonth);
                            $isEnd = ($month == $endMonth);
                            
                            $borderRadius = '';
                            if ($isStart && $isEnd) {
                                $borderRadius = 'rounded-md';
                            } elseif ($isStart) {
                                $borderRadius = 'rounded-l-md';
                            } elseif ($isEnd) {
                                $borderRadius = 'rounded-r-md';
                            }
                        ?>
                        <div class="w-24 p-2 border-l border-gray-700 relative">
                            <?php if ($isActive): ?>
                                <div class="h-8 <?= $statusColor ?> <?= $borderRadius ?> opacity-75"></div>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php 
                    // Display tasks for each project
                    if (isset($project->tasks) && !empty($project->tasks)):
                        foreach($project->tasks as $task):
                            // Calculate task position
                            $taskStartDate = isset($task->start_date) ? strtotime($task->start_date) : null;
                            $taskEndDate = isset($task->due_date) ? strtotime($task->due_date) : null;
                            
                            // Determine task status color
                            $taskStatusColor = 'bg-blue-400';
                            if ($task->status_id == 6) { // Completed
                                $taskStatusColor = 'bg-green-400';
                            } elseif ($task->status_id == 3) { // On Hold
                                $taskStatusColor = 'bg-purple-400';
                            } elseif ($task->status_id == 7) { // Cancelled
                                $taskStatusColor = 'bg-gray-400';
                            }
                            
                            // Determine which months the task spans
                            $taskStartMonth = $taskStartDate ? date('n', $taskStartDate) : $startMonth;
                            $taskEndMonth = $taskEndDate ? date('n', $taskEndDate) : $startMonth;
                    ?>
                    <div class="flex border-t border-gray-700 bg-gray-750">
                        <div class="w-64 p-2 pl-8">
                            <div class="text-sm truncate"><?= htmlspecialchars($task->title) ?></div>
                        </div>
                        
                        <?php for ($month = 1; $month <= 12; $month++):
                            $isTaskActive = ($month >= $taskStartMonth && $month <= $taskEndMonth);
                            $isTaskStart = ($month == $taskStartMonth);
                            $isTaskEnd = ($month == $taskEndMonth);
                            
                            $taskBorderRadius = '';
                            if ($isTaskStart && $isTaskEnd) {
                                $taskBorderRadius = 'rounded-md';
                            } elseif ($isTaskStart) {
                                $taskBorderRadius = 'rounded-l-md';
                            } elseif ($isTaskEnd) {
                                $taskBorderRadius = 'rounded-r-md';
                            }
                        ?>
                        <div class="w-24 p-2 border-l border-gray-700 relative">
                            <?php if ($isTaskActive): ?>
                                <div class="h-6 <?= $taskStatusColor ?> <?= $taskBorderRadius ?> opacity-75"></div>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <?php
                        endforeach;
                    endif;
                    ?>
            <?php else: ?>
                <div class="p-6 text-center text-gray-400 border-t border-gray-700">
                    No projects found. Create your first project to visualize the Gantt chart.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>