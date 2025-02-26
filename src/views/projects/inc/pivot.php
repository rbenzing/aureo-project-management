<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<!-- Pivot Board View -->
<div class="bg-gray-800 rounded-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Project Pivot Board</h2>
        
        <div class="flex space-x-4">
            <select class="bg-gray-700 border border-gray-600 text-white rounded-md px-4 py-2">
                <option value="status">Group by Status</option>
                <option value="company">Group by Company</option>
                <option value="owner">Group by Owner</option>
                <option value="priority">Group by Priority</option>
            </select>
        </div>
    </div>
    
    <?php
    // Group projects by status
    $groupedProjects = [];
    $statusLabels = [
        1 => 'Ready',
        2 => 'In Progress',
        3 => 'Completed',
        4 => 'On Hold',
        5 => 'Delayed',
        6 => 'Cancelled'
    ];
    
    $statusColors = [
        1 => 'bg-blue-600',
        2 => 'bg-yellow-600',
        3 => 'bg-green-600',
        4 => 'bg-purple-600',
        5 => 'bg-red-600',
        6 => 'bg-gray-600'
    ];
    
    foreach ($statusLabels as $statusId => $statusLabel) {
        $groupedProjects[$statusId] = [];
    }
    
    if (!empty($projects)) {
        foreach ($projects as $project) {
            $status = $project->status_id ?? 1;
            $groupedProjects[$status][] = $project;
        }
    }
    ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($groupedProjects as $statusId => $statusProjects): ?>
            <div class="bg-gray-700 rounded-md overflow-hidden">
                <div class="<?= $statusColors[$statusId] ?? 'bg-gray-600' ?> px-4 py-2 font-medium">
                    <?= $statusLabels[$statusId] ?? 'Unknown' ?> (<?= count($statusProjects) ?>)
                </div>
                
                <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
                    <?php if (!empty($statusProjects)): ?>
                        <?php foreach ($statusProjects as $project): ?>
                            <div class="bg-gray-800 p-3 rounded-md hover:bg-gray-600 cursor-pointer">
                                <div class="font-medium"><?= htmlspecialchars($project->name) ?></div>
                                <div class="text-sm text-gray-400"><?= htmlspecialchars($project->company_name ?? 'No Company') ?></div>
                                <?php if (!empty($project->tasks)): ?>
                                    <div class="mt-2 text-xs text-gray-400">
                                        <?php
                                        $totalTasks = count($project->tasks);
                                        $completedTasks = 0;
                                        
                                        foreach ($project->tasks as $task) {
                                            if ($task->status_id == 6) {
                                                $completedTasks++;
                                            }
                                        }
                                        
                                        $completionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                                        ?>
                                        <div class="flex justify-between mb-1">
                                            <span><?= $completedTasks ?>/<?= $totalTasks ?> tasks</span>
                                            <span><?= round($completionRate) ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-700 rounded-full h-1">
                                            <div class="bg-blue-600 h-1 rounded-full" style="width: <?= $completionRate ?>%"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-gray-400 py-4">
                            No projects
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>