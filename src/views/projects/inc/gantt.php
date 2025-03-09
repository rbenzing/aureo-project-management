<?php
//file: Views/Projects/inc/gnatt.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Get current time period filter from query string (default to quarter)
$timePeriod = $_GET['period'] ?? 'quarter';
$currentYear = date('Y');
?>

<!-- Timeline View -->
<div class="bg-white dark:bg-gray-800 rounded-md shadow-sm p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Gantt Chart</h2>
        
        <!-- Time period filters -->
        <div class="flex space-x-2">
            <a href="?view=gantt&period=year" class="px-4 py-2 rounded-md text-sm font-medium <?= $timePeriod === 'year' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
                Year
            </a>
            <a href="?view=gantt&period=quarter" class="px-4 py-2 rounded-md text-sm font-medium <?= $timePeriod === 'quarter' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
                Quarter
            </a>
            <a href="?view=gantt&period=month" class="px-4 py-2 rounded-md text-sm font-medium <?= $timePeriod === 'month' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
                Month
            </a>
            <a href="?view=gantt&period=week" class="px-4 py-2 rounded-md text-sm font-medium <?= $timePeriod === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600' ?>">
                Week
            </a>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <div class="min-w-[1200px]">
            <!-- Timeline header -->
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <div class="w-48 p-3 font-medium text-gray-700 dark:text-gray-300">Project</div>
                
                <?php if ($timePeriod === 'quarter'): ?>
                    <?php
                    // Generate gantt columns (Q1-Q4 for the current year)
                    $quarters = ['Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'];
                    foreach ($quarters as $quarter): 
                    ?>
                        <div class="flex-1 p-3 text-center border-l border-gray-200 dark:border-gray-700 font-medium text-gray-700 dark:text-gray-300">
                            <?= $quarter ?> <?= $currentYear ?>
                        </div>
                    <?php endforeach; ?>
                
                <?php elseif ($timePeriod === 'month'): ?>
                    <?php
                    // Generate gantt columns for months
                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    foreach ($months as $month): 
                    ?>
                        <div class="flex-1 p-3 text-center border-l border-gray-200 dark:border-gray-700 font-medium text-gray-700 dark:text-gray-300">
                            <?= $month ?> <?= $currentYear ?>
                        </div>
                    <?php endforeach; ?>
                
                <?php elseif ($timePeriod === 'year'): ?>
                    <?php
                    // Generate gantt columns for years (current year -1, current, +1, +2)
                    $years = [$currentYear-1, $currentYear, $currentYear+1, $currentYear+2];
                    foreach ($years as $year): 
                    ?>
                        <div class="flex-1 p-3 text-center border-l border-gray-200 dark:border-gray-700 font-medium text-gray-700 dark:text-gray-300">
                            <?= $year ?>
                        </div>
                    <?php endforeach; ?>
                
                <?php elseif ($timePeriod === 'week'): ?>
                    <?php
                    // Generate gantt columns for weeks (current month weeks)
                    $currentMonth = date('n');
                    $monthName = date('F');
                    $daysInMonth = date('t');
                    $weeksInMonth = ceil($daysInMonth / 7);
                    
                    for ($i = 1; $i <= $weeksInMonth; $i++): 
                        $weekStart = ($i - 1) * 7 + 1;
                        $weekEnd = min($weekStart + 6, $daysInMonth);
                    ?>
                        <div class="flex-1 p-3 text-center border-l border-gray-200 dark:border-gray-700 font-medium text-gray-700 dark:text-gray-300">
                            <?= $monthName ?> <?= $weekStart ?>-<?= $weekEnd ?>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
            
            <!-- Projects gantt -->
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $project): 
                    // Ensure we have the necessary fields from your SQL schema
                    $projectId = $project->id ?? null;
                    $projectName = $project->name ?? '';
                    $companyName = $project->company_name ?? '';
                    $startDate = $project->start_date ?? null;
                    $endDate = $project->end_date ?? null;
                    $statusId = $project->status_id ?? 0;
                    
                    // Skip if essential fields are missing
                    if (!$projectId) continue;
                ?>
                    <div class="flex border-b border-gray-200 dark:border-gray-700 hover:bg-gray-10 dark:hover:bg-gray-750">
                        <div class="w-48 p-3">
                            <a href="/projects/view/<?= $project->id ?>" class="group">
                                <div class="font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                    <?= htmlspecialchars($project->name) ?>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($project->company_name) ?>
                                </div>
                            </a>
                        </div>
                        
                        <?php if ($timePeriod === 'quarter'): ?>
                            <?php
                            // Calculate project position in quarterly gantt
                            // If start_date is NULL, assume it starts at beginning of year
                            $startDate = $project->start_date ? strtotime($project->start_date) : strtotime(date('Y-01-01'));
                            // If end_date is NULL, assume it's ongoing (end of year)
                            $endDate = $project->end_date ? strtotime($project->end_date) : strtotime(date('Y-12-31'));
                            
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
                            
                            // Determine project status color
                            $statusColor = getStatusColor($project->status_id);
                            
                            foreach ($quarters as $quarter):
                                $inQuarter = ($startDate <= $quarter['end'] && $endDate >= $quarter['start']);
                            ?>
                                <div class="flex-1 p-3 border-l border-gray-200 dark:border-gray-700 relative">
                                    <?php if ($inQuarter): ?>
                                        <a href="/projects/view/<?= $project->id ?>" class="block">
                                            <div class="h-6 <?= $statusColor ?> rounded-md hover:opacity-80 transition-opacity" title="<?= htmlspecialchars($project->name) ?>"></div>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                        <?php elseif ($timePeriod === 'month'): ?>
                            <?php
                            // Calculate project position in monthly gantt
                            // If start_date is NULL, assume it starts at beginning of year
                            $startDate = $project->start_date ? strtotime($project->start_date) : strtotime(date('Y-01-01'));
                            // If end_date is NULL, assume it's ongoing (end of year)
                            $endDate = $project->end_date ? strtotime($project->end_date) : strtotime(date('Y-12-31'));
                            
                            // Determine project status color
                            $statusColor = getStatusColor($project->status_id);
                            
                            for ($m = 1; $m <= 12; $m++):
                                $monthStart = strtotime("{$currentYear}-{$m}-01");
                                $monthEnd = strtotime(date('Y-m-t', $monthStart));
                                $inMonth = ($startDate <= $monthEnd && $endDate >= $monthStart);
                            ?>
                                <div class="flex-1 p-3 border-l border-gray-200 dark:border-gray-700 relative">
                                    <?php if ($inMonth): ?>
                                        <a href="/projects/view/<?= $project->id ?>" class="block">
                                            <div class="h-6 <?= $statusColor ?> rounded-md hover:opacity-80 transition-opacity" title="<?= htmlspecialchars($project->name) ?>"></div>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                            
                        <?php elseif ($timePeriod === 'year'): ?>
                            <?php
                            // Calculate project position in yearly gantt
                            // If start_date is NULL, assume it starts at beginning of year
                            $startDate = $project->start_date ? strtotime($project->start_date) : strtotime(date('Y-01-01'));
                            // If end_date is NULL, assume it's ongoing (end of year)
                            $endDate = $project->end_date ? strtotime($project->end_date) : strtotime(date('Y-12-31'));
                            
                            // Determine project status color
                            $statusColor = getStatusColor($project->status_id);
                            
                            $years = [$currentYear-1, $currentYear, $currentYear+1, $currentYear+2];
                            foreach ($years as $year):
                                $yearStart = strtotime("{$year}-01-01");
                                $yearEnd = strtotime("{$year}-12-31");
                                $inYear = ($startDate <= $yearEnd && $endDate >= $yearStart);
                            ?>
                                <div class="flex-1 p-3 border-l border-gray-200 dark:border-gray-700 relative">
                                    <?php if ($inYear): ?>
                                        <a href="/projects/view/<?= $project->id ?>" class="block">
                                            <div class="h-6 <?= $statusColor ?> rounded-md hover:opacity-80 transition-opacity" title="<?= htmlspecialchars($project->name) ?>"></div>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                        <?php elseif ($timePeriod === 'week'): ?>
                            <?php
                            // Calculate project position in weekly gantt
                            // If start_date is NULL, assume it starts at beginning of year
                            $startDate = $project->start_date ? strtotime($project->start_date) : strtotime(date('Y-01-01'));
                            // If end_date is NULL, assume it's ongoing (end of year)
                            $endDate = $project->end_date ? strtotime($project->end_date) : strtotime(date('Y-12-31'));
                            
                            // Determine project status color
                            $statusColor = getStatusColor($project->status_id);
                            
                            $currentMonth = date('n');
                            $daysInMonth = date('t');
                            $weeksInMonth = ceil($daysInMonth / 7);
                            
                            for ($i = 1; $i <= $weeksInMonth; $i++): 
                                $weekStart = ($i - 1) * 7 + 1;
                                $weekEnd = min($weekStart + 6, $daysInMonth);
                                
                                $weekStartDate = strtotime("{$currentYear}-{$currentMonth}-{$weekStart}");
                                $weekEndDate = strtotime("{$currentYear}-{$currentMonth}-{$weekEnd}");
                                $inWeek = ($startDate <= $weekEndDate && $endDate >= $weekStartDate);
                            ?>
                                <div class="flex-1 p-3 border-l border-gray-200 dark:border-gray-700 relative">
                                    <?php if ($inWeek): ?>
                                        <a href="/projects/view/<?= $project->id ?>" class="block">
                                            <div class="h-6 <?= $statusColor ?> rounded-md hover:opacity-80 transition-opacity" title="<?= htmlspecialchars($project->name) ?>"></div>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-6 text-center text-gray-400">
                    No projects found. Create your first project to visualize the gantt.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
/**
 * Helper function to determine status color class based on project status ID
 * Make sure these status IDs align with your database schema
 * 
 * @param int $statusId The status ID from the database
 * @return string The CSS class for the status color
 */
function getStatusColor($statusId) {
    // Convert to integer to ensure proper comparison
    $statusId = (int) $statusId;
    
    switch ($statusId) {
        case 1: // Ready
            return 'bg-blue-500';
        case 2: // In Progress
            return 'bg-yellow-500';
        case 3: // Completed
            return 'bg-green-500';
        case 4: // On Hold
            return 'bg-purple-500';
        case 5: // Delayed
            return 'bg-red-500';
        case 6: // Cancelled
            return 'bg-gray-500';
        default:
            return 'bg-gray-500';
    }
}
?>