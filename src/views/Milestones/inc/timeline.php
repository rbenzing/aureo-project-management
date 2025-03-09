<?php
// file: Views/Milestones/inc/timeline.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<div id="timeline-view" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 hidden">
    <div class="relative">
        <!-- Timeline Header -->
        <div class="flex mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
            <div class="w-1/4 font-medium text-gray-500 dark:text-gray-400">Milestone</div>
            <div class="w-3/4 flex">
                <?php 
                // Generate months for timeline (3 months in past, 6 months in future)
                $startDate = strtotime('-3 months');
                $monthsToShow = 9;
                
                for ($i = 0; $i < $monthsToShow; $i++) {
                    $monthTime = strtotime("+{$i} months", $startDate);
                    echo '<div class="flex-1 text-center text-xs font-medium text-gray-500 dark:text-gray-400">';
                    echo date('M Y', $monthTime);
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <?php if (!empty($milestones)): ?>
            <?php foreach ($milestones as $milestone): ?>
                <?php
                // Skip if no dates
                if (empty($milestone->start_date) && empty($milestone->due_date)) continue;
                
                // For positioning in timeline
                $startTimestamp = !empty($milestone->start_date) ? strtotime($milestone->start_date) : null;
                $endTimestamp = !empty($milestone->due_date) ? strtotime($milestone->due_date) : null;
                
                // Default to current date if no start date
                if (!$startTimestamp && $endTimestamp) {
                    $startTimestamp = $endTimestamp;
                }
                
                // Default to start date if no end date
                if ($startTimestamp && !$endTimestamp) {
                    $endTimestamp = $startTimestamp;
                }
                
                // Skip if still no dates
                if (!$startTimestamp || !$endTimestamp) continue;
                
                // Calculate position and width
                $timelineStart = strtotime('-3 months');
                $timelineEnd = strtotime('+6 months');
                $timelineWidth = $timelineEnd - $timelineStart;
                
                $startPos = max(0, ($startTimestamp - $timelineStart) / $timelineWidth * 100);
                $endPos = min(100, ($endTimestamp - $timelineStart) / $timelineWidth * 100);
                $width = max(5, $endPos - $startPos); // Minimum 5% width for visibility
                
                // Determine color based on status
                $bgColor = match($milestone->status_id) {
                    1 => 'bg-gray-400 dark:bg-gray-600', // Not Started
                    2 => 'bg-blue-400 dark:bg-blue-600', // In Progress
                    3 => 'bg-green-400 dark:bg-green-600', // Completed
                    4 => 'bg-yellow-400 dark:bg-yellow-600', // On Hold
                    5 => 'bg-red-400 dark:bg-red-600', // Delayed
                    default => 'bg-gray-400 dark:bg-gray-600'
                };
                ?>
                <div class="flex items-center py-3 border-b border-gray-200 dark:border-gray-700 milestone-timeline-row"
                     data-status="<?= $milestone->status_id ?>" 
                     data-project="<?= $milestone->project_id ?>"
                     data-type="<?= $milestone->milestone_type ?? 'milestone' ?>"
                     data-title="<?= htmlspecialchars($milestone->title) ?>"
                     data-overdue="<?= (!empty($milestone->due_date) && strtotime($milestone->due_date) < time() && $milestone->status_id != 3) ? '1' : '0' ?>">
                    <div class="w-1/4 pr-4">
                        <div class="flex items-center">
                            <?php if (isset($milestone->milestone_type) && $milestone->milestone_type === 'epic'): ?>
                                <div class="h-6 w-6 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-2">
                                    <svg class="h-4 w-4 text-purple-500 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                    <?= htmlspecialchars($milestone->title) ?>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($milestone->project_name ?? 'Unassigned') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-3/4 relative">
                        <div class="absolute h-6 rounded-md <?= $bgColor ?>" 
                             style="left: <?= $startPos ?>%; width: <?= $width ?>%;">
                            <div class="h-full flex items-center justify-center text-xs text-white font-medium overflow-hidden px-2">
                                <?php if ($width > 10): ?>
                                    <?= ($milestone->completion_rate ?? 0) ?>%
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="py-4 text-center text-gray-500 dark:text-gray-400">
                No milestones found with date information.
            </div>
        <?php endif; ?>
    </div>
</div>