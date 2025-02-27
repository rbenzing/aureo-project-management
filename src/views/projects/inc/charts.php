<?php
// Prevent direct web access:
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>

<div class="space-y-6">

    <!-- 
      DASHBOARD TITLE / INTRO
      You can customize or remove this section if desired.
    -->
    <div class="bg-gray-800 rounded-md p-6">
        <h1 class="text-2xl font-bold mb-2">Project Overview Dashboard</h1>
        <p class="text-gray-400 text-sm">
            A high-level snapshot of ongoing projects, their statuses, and key metrics.
        </p>
    </div>

    <!-- 
      GRID LAYOUT: PROJECT STATUS & TIMELINE
    -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- 
          PROJECTS BY STATUS
        -->
        <section class="bg-gray-800 rounded-md p-6">
        <header class="mb-4">
            <h2 class="text-xl font-semibold">Projects by Status</h2>
            <p class="text-gray-400 text-sm">
                Count of all projects categorized by their current status.
            </p>
        </header>

        <?php
        // Prepare data for Projects by Status
        $statusCounts = [];
        $statusColors = [
            'ready'       => '#3B82F6', // blue
            'in_progress' => '#F59E0B', // yellow
            'completed'   => '#10B981', // green
            'on_hold'     => '#8B5CF6', // purple
            'delayed'     => '#EF4444', // red
            'cancelled'   => '#6B7280', // gray
            'unknown'     => '#9CA3AF', // fallback for unknown status
        ];

        // Count projects by status
        if (!empty($projects)) {
            foreach ($projects as $project) {
                $status = $project->status ?? 'unknown';
                if (!isset($statusCounts[$status])) {
                    $statusCounts[$status] = 0;
                }
                $statusCounts[$status]++;
            }
        }

        // For percentage calculations and to avoid division by zero
        $totalProjects = max(count($projects), 1);
        ?>

        <div class="h-64 flex items-end space-x-4 mb-6">
            <?php if (!empty($statusCounts)): ?>
                <?php foreach ($statusCounts as $status => $count): ?>
                    <?php
                        $height = ($count / $totalProjects) * 100;
                        $color  = $statusColors[$status] ?? '#9CA3AF';
                    ?>
                    <div class="flex flex-col items-center flex-1">
                        <!-- Bar -->
                        <div 
                            class="w-full rounded-t-md relative transition-all duration-300" 
                            style="height: <?= $height ?>%; background-color: <?= $color ?>;" 
                            title="<?= ucfirst(htmlspecialchars($status)) ?>: <?= $count ?> project(s)"
                        >
                            <div class="absolute bottom-2 left-0 right-0 text-center text-white font-bold">
                                <?= $count ?>
                            </div>
                        </div>
                        <!-- Label -->
                        <div class="text-xs mt-2 text-center text-gray-300">
                            <?= ucfirst(htmlspecialchars($status)) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="w-full flex items-center justify-center">
                    <p class="text-gray-400">No data available</p>
                </div>
            <?php endif; ?>
        </div>
        </section>

        <!-- 
          PROJECT TIMELINE (YEARLY DISTRIBUTION) 
        -->
        <section class="bg-gray-800 rounded-md p-6">
            <header class="mb-4">
                <h2 class="text-xl font-semibold">Projects Timeline</h2>
                <p class="text-gray-400 text-sm">
                    Number of projects started each month this year.
                </p>
            </header>

            <?php
            // Group projects by month of the current year
            $projectsByMonth = [];
            $currentYear = date('Y');

            for ($month = 1; $month <= 12; $month++) {
                $monthName = date('M', strtotime("{$currentYear}-{$month}-01"));
                $projectsByMonth[$monthName] = 0;
            }

            if (!empty($projects)) {
                foreach ($projects as $project) {
                    if (!empty($project->start_date)) {
                        $startDate = new DateTime($project->start_date);
                        if ($startDate->format('Y') == $currentYear) {
                            $monthName = $startDate->format('M');
                            $projectsByMonth[$monthName]++;
                        }
                    }
                }
            }

            // Determine max count to scale bars
            $maxCount = (!empty($projectsByMonth)) ? max($projectsByMonth) : 0;
            ?>

            <div class="h-64 flex items-end space-x-2">
                <?php if (!empty($projectsByMonth)): ?>
                    <?php foreach ($projectsByMonth as $month => $count): ?>
                        <?php
                            $height = ($maxCount > 0)
                                ? ($count / $maxCount) * 100
                                : 0;
                        ?>
                        <div class="flex flex-col items-center flex-1">
                            <!-- Bar -->
                            <div 
                                class="w-full bg-blue-600 rounded-t-md relative transition-all duration-300"
                                style="height: <?= $height ?>%"
                                title="<?= $month ?>: <?= $count ?> project(s)"
                            >
                                <div class="absolute bottom-2 left-0 right-0 text-center text-white font-bold">
                                    <?= $count ?>
                                </div>
                            </div>
                            <!-- Label -->
                            <div class="text-xs mt-2 text-center text-gray-300"><?= $month ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="w-full flex items-center justify-center">
                        <p class="text-gray-400">No data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- 
      GRID LAYOUT: TASK COMPLETION & BILLABLE HOURS
    -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- 
          TASK COMPLETION RATE
        -->
        <section class="bg-gray-800 rounded-md p-6">
            <header class="mb-4">
                <h2 class="text-xl font-semibold">Task Completion Rate</h2>
                <p class="text-gray-400 text-sm">
                    For each project, see how many tasks are completed vs. total tasks.
                </p>
            </header>

            <?php
            // Calculate task completion rate per project
            $taskStats = [];

            if (!empty($projects)) {
                foreach ($projects as $project) {
                    if (!empty($project->tasks)) {
                        $totalTasks     = count($project->tasks);
                        $completedTasks = 0;

                        foreach ($project->tasks as $task) {
                            // Status ID 6 = Completed (as per your domain logic)
                            if (isset($task->status_id) && $task->status_id == 6) {
                                $completedTasks++;
                            }
                        }

                        $completionRate = $totalTasks > 0
                            ? ($completedTasks / $totalTasks) * 100
                            : 0;

                        $taskStats[$project->name] = [
                            'total'     => $totalTasks,
                            'completed' => $completedTasks,
                            'rate'      => $completionRate
                        ];
                    }
                }
            }
            ?>

            <div class="space-y-4">
                <?php if (!empty($taskStats)): ?>
                    <?php foreach ($taskStats as $projectName => $stats): ?>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm text-gray-300"><?= htmlspecialchars($projectName) ?></span>
                                <span class="text-sm text-gray-300">
                                    <?= $stats['completed'] ?>/<?= $stats['total'] ?> 
                                    (<?= round($stats['rate']) ?>%)
                                </span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5">
                                <div 
                                    class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                    style="width: <?= $stats['rate'] ?>%"
                                ></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-gray-400">
                        <p>No task data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- 
          BILLABLE HOURS
        -->
        <section class="bg-gray-800 rounded-md p-6">
            <header class="mb-4">
                <h2 class="text-xl font-semibold">Billable Hours by Project</h2>
                <p class="text-gray-400 text-sm">
                    Shows how many hours are billable vs. the total hours logged.
                </p>
            </header>

            <?php
            // Calculate billable hours per project
            $billableStats = [];

            if (!empty($projects)) {
                foreach ($projects as $project) {
                    $totalHours     = 0;
                    $billableHours  = 0;

                    if (!empty($project->tasks)) {
                        foreach ($project->tasks as $task) {
                            $totalHours += ($task->time_spent ?? 0) / 3600; // seconds to hours
                            if (!empty($task->is_hourly) && !empty($task->billable_time)) {
                                $billableHours += ($task->billable_time ?? 0) / 3600; 
                            }
                        }
                    }

                    if ($totalHours > 0) {
                        $billableStats[$project->name] = [
                            'total'    => $totalHours,
                            'billable' => $billableHours,
                            'rate'     => ($billableHours / $totalHours) * 100
                        ];
                    }
                }
            }
            ?>

            <div class="space-y-4">
                <?php if (!empty($billableStats)): ?>
                    <?php foreach ($billableStats as $projectName => $stats): ?>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm text-gray-300">
                                    <?= htmlspecialchars($projectName) ?>
                                </span>
                                <span class="text-sm text-gray-300">
                                    <?= round($stats['billable'], 1) ?>/<?= round($stats['total'], 1) ?> hrs 
                                    (<?= round($stats['rate']) ?>%)
                                </span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5">
                                <div 
                                    class="bg-green-600 h-2.5 rounded-full transition-all duration-300"
                                    style="width: <?= $stats['rate'] ?>%"
                                ></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-gray-400">
                        <p>No billable hours data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
