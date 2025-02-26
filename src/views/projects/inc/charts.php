<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Project Status Chart -->
    <div class="bg-gray-800 rounded-md p-6">
        <h2 class="text-xl font-semibold mb-4">Projects by Status</h2>
        
        <?php
        // Count projects by status
        $statusCounts = [];
        $statusColors = [
            'ready' => '#3B82F6', // blue
            'in_progress' => '#F59E0B', // yellow
            'completed' => '#10B981', // green
            'on_hold' => '#8B5CF6', // purple
            'delayed' => '#EF4444', // red
            'cancelled' => '#6B7280', // gray
        ];
        
        if (!empty($projects)) {
            foreach ($projects as $project) {
                $status = $project->status ?? 'unknown';
                if (!isset($statusCounts[$status])) {
                    $statusCounts[$status] = 0;
                }
                $statusCounts[$status]++;
            }
        }
        ?>
        
        <div class="h-64 flex items-end space-x-4">
            <?php foreach ($statusCounts as $status => $count): ?>
                <?php 
                $height = ($count / count($projects)) * 100;
                $color = $statusColors[$status] ?? '#6B7280';
                ?>
                <div class="flex flex-col items-center flex-1">
                    <div class="w-full bg-gray-700 rounded-t-md relative" style="height: <?= $height ?>%;">
                        <div class="absolute bottom-2 left-0 right-0 text-center text-white font-bold">
                            <?= $count ?>
                        </div>
                    </div>
                    <div class="text-xs mt-2 text-center"><?= ucfirst(htmlspecialchars($status)) ?></div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($statusCounts)): ?>
                <div class="w-full flex items-center justify-center">
                    <p class="text-gray-400">No data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Project Timeline Chart -->
    <div class="bg-gray-800 rounded-md p-6">
        <h2 class="text-xl font-semibold mb-4">Projects Timeline</h2>
        
        <?php
        // Group projects by month
        $projectsByMonth = [];
        $currentYear = date('Y');
        
        for ($month = 1; $month <= 12; $month++) {
            $monthName = date('M', strtotime("{$currentYear}-{$month}-01"));
            $projectsByMonth[$monthName] = 0;
        }
        
        if (!empty($projects)) {
            foreach ($projects as $project) {
                if (isset($project->start_date) && !empty($project->start_date)) {
                    $startDate = new DateTime($project->start_date);
                    if ($startDate->format('Y') == $currentYear) {
                        $monthName = $startDate->format('M');
                        $projectsByMonth[$monthName]++;
                    }
                }
            }
        }
        ?>
        
        <div class="h-64 flex items-end space-x-2">
            <?php foreach ($projectsByMonth as $month => $count): ?>
                <?php 
                $maxCount = max(array_values($projectsByMonth));
                $height = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                ?>
                <div class="flex flex-col items-center flex-1">
                    <div class="w-full bg-blue-600 rounded-t-md relative" style="height: <?= $height ?>%;">
                        <div class="absolute bottom-2 left-0 right-0 text-center text-white font-bold">
                            <?= $count ?>
                        </div>
                    </div>
                    <div class="text-xs mt-2 text-center"><?= $month ?></div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($projectsByMonth)): ?>
                <div class="w-full flex items-center justify-center">
                    <p class="text-gray-400">No data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Task Completion Chart -->
    <div class="bg-gray-800 rounded-md p-6">
        <h2 class="text-xl font-semibold mb-4">Task Completion Rate</h2>
        
        <?php
        // Calculate task completion rate per project
        $taskStats = [];
        
        if (!empty($projects)) {
            foreach ($projects as $project) {
                if (isset($project->tasks) && !empty($project->tasks)) {
                    $totalTasks = count($project->tasks);
                    $completedTasks = 0;
                    
                    foreach ($project->tasks as $task) {
                        if ($task->status_id == 6) { // Completed status
                            $completedTasks++;
                        }
                    }
                    
                    $completionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                    $taskStats[$project->name] = [
                        'total' => $totalTasks,
                        'completed' => $completedTasks,
                        'rate' => $completionRate
                    ];
                }
            }
        }
        ?>
        
        <div class="space-y-4">
            <?php foreach ($taskStats as $projectName => $stats): ?>
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm"><?= htmlspecialchars($projectName) ?></span>
                        <span class="text-sm"><?= $stats['completed'] ?>/<?= $stats['total'] ?> (<?= round($stats['rate']) ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?= $stats['rate'] ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($taskStats)): ?>
                <div class="text-center text-gray-400">
                    <p>No task data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Billable Hours Chart -->
    <div class="bg-gray-800 rounded-md p-6">
        <h2 class="text-xl font-semibold mb-4">Billable Hours by Project</h2>
        
        <?php
        // Calculate billable hours per project
        $billableStats = [];
        
        if (!empty($projects)) {
            foreach ($projects as $project) {
                $totalHours = 0;
                $billableHours = 0;
                
                if (isset($project->tasks) && !empty($project->tasks)) {
                    foreach ($project->tasks as $task) {
                        $totalHours += ($task->time_spent ?? 0) / 3600; // Convert seconds to hours
                        
                        if ($task->is_hourly && $task->billable_time) {
                            $billableHours += ($task->billable_time ?? 0) / 3600; // Convert seconds to hours
                        }
                    }
                }
                
                if ($totalHours > 0) {
                    $billableStats[$project->name] = [
                        'total' => $totalHours,
                        'billable' => $billableHours,
                        'rate' => ($billableHours / $totalHours) * 100
                    ];
                }
            }
        }
        ?>
        
        <div class="space-y-4">
            <?php foreach ($billableStats as $projectName => $stats): ?>
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm"><?= htmlspecialchars($projectName) ?></span>
                        <span class="text-sm"><?= round($stats['billable'], 1) ?>/<?= round($stats['total'], 1) ?> hrs (<?= round($stats['rate']) ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2.5">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: <?= $stats['rate'] ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($billableStats)): ?>
                <div class="text-center text-gray-400">
                    <p>No billable hours data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>