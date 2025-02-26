<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<!-- Timeline View -->
<div class="bg-gray-800 rounded-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold">Project Timeline</h2>
        
        <div class="flex space-x-4">
            <button class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600">Year</button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-md">Quarter</button>
            <button class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600">Month</button>
            <button class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600">Week</button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <div class="min-w-[1200px]">
            <!-- Timeline header -->
            <div class="flex border-b border-gray-700">
                <div class="w-48 p-2 font-medium">Project</div>
                <?php
                // Generate timeline columns (Q1-Q4 for the current year)
                $currentYear = date('Y');
                $quarters = ['Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'];
                foreach ($quarters as $quarter): 
                ?>
                <div class="flex-1 p-2 text-center border-l border-gray-700 font-medium"><?= $quarter ?> <?= $currentYear ?></div>
                <?php endforeach; ?>
            </div>
            
            <!-- Projects timeline -->
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $project): ?>
                    <div class="flex border-b border-gray-700">
                        <div class="w-48 p-2">
                            <div class="font-medium"><?= htmlspecialchars($project->name) ?></div>
                            <div class="text-sm text-gray-400"><?= htmlspecialchars($project->company_name) ?></div>
                        </div>
                        
                        <?php
                        // Calculate project position in the timeline
                        $startDate = strtotime($project->start_date ?? date('Y-01-01'));
                        $endDate = strtotime($project->end_date ?? date('Y-12-31'));
                        
                        $q1Start = strtotime("{$currentYear}-01-01");
                        $q1End = strtotime("{$currentYear}-03-31");
                        $q2Start = strtotime("{$currentYear}-04-01");
                        $q2End = strtotime("{$currentYear}-06-30");
                        $q3Start = strtotime("{$currentYear}-07-01");
                        $q3End = strtotime("{$currentYear}-09-30");
                        $q4Start = strtotime("{$currentYear}-10-01");
                        $q4End = strtotime("{$currentYear}-12-31");
                        
                        $quarters = [
                            ['start' => $q1Start, 'end' => $q1End],
                            ['start' => $q2Start, 'end' => $q2End],
                            ['start' => $q3Start, 'end' => $q3End],
                            ['start' => $q4Start, 'end' => $q4End]
                        ];
                        
                        foreach ($quarters as $index => $quarter):
                            $inQuarter = ($startDate <= $quarter['end'] && $endDate >= $quarter['start']);
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
                        ?>
                        <div class="flex-1 p-2 border-l border-gray-700 relative">
                            <?php if ($inQuarter): ?>
                                <div class="h-6 <?= $statusColor ?> rounded-md mx-2"></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-6 text-center text-gray-400">
                    No projects found. Create your first project to visualize the timeline.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>