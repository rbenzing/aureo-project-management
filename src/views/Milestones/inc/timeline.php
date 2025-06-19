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
        <!-- Timeline Navigation -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200">Timeline View</h3>
                <div class="flex items-center space-x-2">
                    <button id="timeline-prev" class="timeline-nav-button p-2 rounded-md" title="Previous Month">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <span id="timeline-current-period" class="text-sm font-medium text-gray-700 dark:text-gray-300 min-w-[120px] text-center">
                        <!-- Will be populated by JavaScript -->
                    </span>
                    <button id="timeline-next" class="timeline-nav-button p-2 rounded-md" title="Next Month">
                        <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19l7-7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <button id="timeline-today" class="px-3 py-1 text-xs bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-800 transition-colors">
                Today
            </button>
        </div>

        <!-- Timeline Header -->
        <div class="flex mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
            <div class="w-1/4 font-medium text-gray-500 dark:text-gray-400">Milestone</div>
            <div id="timeline-months" class="w-3/4 flex">
                <!-- Month headers will be populated by JavaScript -->
            </div>
        </div>

        <div id="timeline-milestones">
            <div id="timeline-no-data" class="py-8 text-center text-gray-500 dark:text-gray-400" style="display: none;">
                No milestones found in the current time period.
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
                     data-overdue="<?= (!empty($milestone->due_date) && strtotime($milestone->due_date) < time() && $milestone->status_id != 3) ? '1' : '0' ?>"
                     data-start-date="<?= $milestone->start_date ?? '' ?>"
                     data-due-date="<?= $milestone->due_date ?? '' ?>"
                     data-start-timestamp="<?= $startTimestamp ?>"
                     data-end-timestamp="<?= $endTimestamp ?>"
                     data-completion-rate="<?= $milestone->completion_rate ?? 0 ?>"
                     data-bg-color="<?= $bgColor ?>">
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
                                    <a href="/milestones/view/<?= htmlspecialchars((string)$milestone->id) ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        <?= htmlspecialchars($milestone->title) ?>
                                    </a>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <?= htmlspecialchars($milestone->project_name ?? 'Unassigned') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-3/4 relative">
                        <a href="/milestones/view/<?= htmlspecialchars((string)$milestone->id) ?>" class="block">
                            <div class="milestone-timeline-bar absolute h-6 rounded-md <?= $bgColor ?> hover:opacity-80 transition-opacity tooltip"
                                 style="display: none;"
                                 data-tooltip="<?= htmlspecialchars($milestone->title) ?>">
                                <div class="h-full flex items-center justify-center text-xs text-white font-medium overflow-hidden px-2">
                                    <span class="milestone-percentage">
                                        <?= isset($milestone->completion_rate) ? number_format((float)$milestone->completion_rate, 1) : '0' ?>%
                                    </span>
                                </div>
                            </div>
                        </a>
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
</div>

<style>
.milestone-timeline-bar {
    transition: all 0.3s ease;
}

.milestone-timeline-bar:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

#timeline-current-period {
    letter-spacing: 0.025em;
}

.timeline-nav-button {
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.timeline-nav-button:hover {
    transform: scale(1.05);
    border-color: #d1d5db;
    background-color: #f3f4f6 !important;
}

.dark .timeline-nav-button:hover {
    border-color: #4b5563;
    background-color: #374151 !important;
}

.timeline-nav-button:active {
    transform: scale(0.95);
}

.timeline-nav-button svg {
    transition: color 0.2s ease;
}

.timeline-nav-button:hover svg {
    color: #374151;
}

.dark .timeline-nav-button:hover svg {
    color: #f9fafb;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Timeline navigation functionality
    let currentTimelineStart = new Date();
    currentTimelineStart.setMonth(currentTimelineStart.getMonth() - 3); // Start 3 months ago
    currentTimelineStart.setDate(1); // First day of month

    const monthsToShow = 9;

    function updateTimelineView() {
        // Update month headers
        const monthsContainer = document.getElementById('timeline-months');
        const currentPeriodSpan = document.getElementById('timeline-current-period');

        if (!monthsContainer || !currentPeriodSpan) return;

        monthsContainer.innerHTML = '';

        // Calculate timeline range
        const timelineStart = new Date(currentTimelineStart);
        const timelineEnd = new Date(currentTimelineStart);
        timelineEnd.setMonth(timelineEnd.getMonth() + monthsToShow);

        // Update current period display
        const startMonth = timelineStart.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        const endMonth = new Date(timelineEnd.getTime() - 24 * 60 * 60 * 1000).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        currentPeriodSpan.textContent = `${startMonth} - ${endMonth}`;

        // Generate month headers
        for (let i = 0; i < monthsToShow; i++) {
            const monthDate = new Date(currentTimelineStart);
            monthDate.setMonth(monthDate.getMonth() + i);

            const monthDiv = document.createElement('div');
            monthDiv.className = 'flex-1 text-center text-xs font-medium text-gray-500 dark:text-gray-400';
            monthDiv.textContent = monthDate.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            monthsContainer.appendChild(monthDiv);
        }

        // Update milestone visibility and positioning
        updateMilestonePositions(timelineStart.getTime(), timelineEnd.getTime());
    }

    function updateMilestonePositions(timelineStartMs, timelineEndMs) {
        const milestoneRows = document.querySelectorAll('.milestone-timeline-row');
        const timelineWidth = timelineEndMs - timelineStartMs;
        let visibleCount = 0;

        milestoneRows.forEach(row => {
            const startTimestamp = parseInt(row.dataset.startTimestamp) * 1000;
            const endTimestamp = parseInt(row.dataset.endTimestamp) * 1000;
            const completionRate = parseFloat(row.dataset.completionRate);
            const bgColor = row.dataset.bgColor;
            const isFilteredOut = row.dataset.filteredOut === 'true';

            const timelineBar = row.querySelector('.milestone-timeline-bar');
            const percentageSpan = row.querySelector('.milestone-percentage');

            if (!timelineBar || !startTimestamp || !endTimestamp || isFilteredOut) {
                row.style.display = 'none';
                return;
            }

            // Check if milestone overlaps with current timeline view
            const overlapsTimeline = (startTimestamp < timelineEndMs && endTimestamp > timelineStartMs);

            if (!overlapsTimeline) {
                row.style.display = 'none';
                return;
            }

            // Show the row
            row.style.display = '';
            visibleCount++;

            // Calculate position and width
            const startPos = Math.max(0, (startTimestamp - timelineStartMs) / timelineWidth * 100);
            const endPos = Math.min(100, (endTimestamp - timelineStartMs) / timelineWidth * 100);
            const width = Math.max(5, endPos - startPos); // Minimum 5% width for visibility

            // Update timeline bar
            timelineBar.style.left = startPos + '%';
            timelineBar.style.width = width + '%';
            timelineBar.style.display = 'block';

            // Show/hide percentage based on width
            if (percentageSpan) {
                if (width > 5) {
                    percentageSpan.style.display = '';
                } else {
                    percentageSpan.style.display = 'none';
                }
            }
        });

        // Show/hide no data message
        const noDataDiv = document.getElementById('timeline-no-data');
        if (noDataDiv) {
            noDataDiv.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    // Navigation event listeners
    document.getElementById('timeline-prev')?.addEventListener('click', function() {
        currentTimelineStart.setMonth(currentTimelineStart.getMonth() - 1);
        updateTimelineView();
    });

    document.getElementById('timeline-next')?.addEventListener('click', function() {
        currentTimelineStart.setMonth(currentTimelineStart.getMonth() + 1);
        updateTimelineView();
    });

    document.getElementById('timeline-today')?.addEventListener('click', function() {
        currentTimelineStart = new Date();
        currentTimelineStart.setMonth(currentTimelineStart.getMonth() - 3);
        currentTimelineStart.setDate(1);
        updateTimelineView();
    });

    // Listen for filter changes from main page
    document.addEventListener('timelineRefresh', function() {
        const timelineStart = new Date(currentTimelineStart);
        const timelineEnd = new Date(currentTimelineStart);
        timelineEnd.setMonth(timelineEnd.getMonth() + monthsToShow);
        updateMilestonePositions(timelineStart.getTime(), timelineEnd.getTime());
    });

    // Initialize timeline view
    updateTimelineView();
});
</script>