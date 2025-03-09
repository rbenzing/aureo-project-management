<?php
//file: Views/Projects/inc/charts.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Helper function to get a distinct color for each data point
function getChartColor($index) {
    $colors = [
        '#3B82F6', // blue
        '#F59E0B', // yellow
        '#10B981', // green
        '#8B5CF6', // purple
        '#EF4444', // red
        '#6B7280', // gray
        '#EC4899', // pink
        '#14B8A6', // teal
        '#F97316', // orange
        '#8B5CF6'  // indigo
    ];
    
    return $colors[$index % count($colors)];
}

// Prepare data for the charts
// Get status names from the project_statuses table in database
$statusLabels = ['Ready', 'In Progress', 'Completed', 'On Hold', 'Delayed', 'Cancelled'];
$statusCounts = array_fill(0, count($statusLabels), 0);
$statusColors = [
    '#3B82F6', // blue
    '#F59E0B', // yellow
    '#10B981', // green
    '#8B5CF6', // purple
    '#EF4444', // red
    '#6B7280'  // gray
];

// Prepare data for project budgets
// Since budget field might not be in your schema, we'll adapt
$projectBudgets = [];
$projectSpent = [];
$projectNames = [];

// Prepare data for project durations
$completedProjectDurations = [];
$inProgressProjectDurations = [];

// Process projects data
if (!empty($projects)) {
    foreach ($projects as $project) {
        // Count by status
        $statusId = (int)($project->status_id ?? 0);
        if ($statusId > 0 && $statusId <= count($statusLabels)) {
            $statusCounts[$statusId - 1]++;
        }
        
        // For budget, we'll use estimated time values from tasks as a proxy if no budget field
        $projectName = htmlspecialchars($project->name ?? 'Unnamed Project');
        $projectNames[] = $projectName;
        
        // Calculate total estimated time as "budget"
        $estimatedTime = 0;
        $spentTime = 0;
        
        // Get associated tasks for this project
        $projectId = $project->id ?? 0;
        $tasks = [];
        
        // If tasks are already loaded with the project
        if (isset($project->tasks)) {
            $tasks = $project->tasks;
        } else {
            // You may need to fetch tasks for this project from the database here
            // For example: $tasks = fetchTasksForProject($projectId);
        }
        
        // Calculate estimated and spent time
        if (!empty($tasks)) {
            foreach ($tasks as $task) {
                $estimatedTime += (float)($task->estimated_time ?? 0);
                $spentTime += (float)($task->time_spent ?? 0);
            }
        }
        
        $projectBudgets[] = $estimatedTime;
        $projectSpent[] = $spentTime;
        
        // Project duration calculation
        if (isset($project->start_date) && !empty($project->start_date)) {
            $startDate = new DateTime($project->start_date);
            
            if (isset($project->end_date) && !empty($project->end_date)) {
                // Completed projects with actual duration
                $endDate = new DateTime($project->end_date);
                $duration = $startDate->diff($endDate)->days;
                
                if ($statusId == 3) { // Completed status
                    $completedProjectDurations[] = [
                        'name' => $projectName,
                        'duration' => $duration
                    ];
                }
            } else {
                // In-progress projects with current duration
                $today = new DateTime();
                $duration = $startDate->diff($today)->days;
                
                if ($statusId == 2) { // In Progress status
                    $inProgressProjectDurations[] = [
                        'name' => $projectName,
                        'duration' => $duration
                    ];
                }
            }
        }
    }
}

// Sort duration arrays by duration (descending)
usort($completedProjectDurations, function($a, $b) {
    return $b['duration'] - $a['duration'];
});

usort($inProgressProjectDurations, function($a, $b) {
    return $b['duration'] - $a['duration'];
});

// Limit to top 5 for display
$completedProjectDurations = array_slice($completedProjectDurations, 0, 5);
$inProgressProjectDurations = array_slice($inProgressProjectDurations, 0, 5);

// Calculate clients with most projects
$clientProjectCounts = [];
if (!empty($projects)) {
    foreach ($projects as $project) {
        $companyId = $project->company_id ?? 0;
        
        // Fetch company name if it's not already included
        $companyName = 'Unknown Company';
        
        if (isset($project->company_name)) {
            $companyName = $project->company_name;
        } else if (isset($project->company) && isset($project->company->name)) {
            $companyName = $project->company->name;
        } else {
            // You might need to fetch company information from the database
            // For example: $companyName = fetchCompanyName($companyId);
        }
        
        if (!isset($clientProjectCounts[$companyName])) {
            $clientProjectCounts[$companyName] = 0;
        }
        $clientProjectCounts[$companyName]++;
    }
    
    // Sort by count descending
    arsort($clientProjectCounts);
    
    // Get top 5 clients
    $clientProjectCounts = array_slice($clientProjectCounts, 0, 5, true);
}
?>

<!-- Charts Container -->
<div class="space-y-6">
    <!-- Top Title Section -->
    <div class="bg-white dark:bg-gray-800 rounded-md shadow-sm p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Project Analytics</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">
            Data visualizations to help understand project distribution, client relationships, and resource allocation.
        </p>
    </div>

    <!-- First Row - Status & Budget -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Projects by Status Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-sm p-6 overflow-hidden">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Projects by Status</h2>
            
            <?php if (array_sum($statusCounts) > 0): ?>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                    No status data available.
                </div>
            <?php endif; ?>
        </div>

        <!-- Budget vs. Spent Chart (using task time estimates) -->
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-sm p-6 overflow-hidden">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Estimated vs. Actual Hours</h2>
            
            <?php if (!empty($projectNames) && !empty($projectBudgets)): ?>
                <div class="h-64">
                    <canvas id="budgetChart"></canvas>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                    No time data available.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Second Row - Project Duration & Client Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Project Duration Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-sm p-6 overflow-hidden">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Project Durations (Days)</h2>
            
            <?php if (!empty($completedProjectDurations) || !empty($inProgressProjectDurations)): ?>
                <div class="h-64">
                    <canvas id="durationChart"></canvas>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                    No duration data available.
                </div>
            <?php endif; ?>
        </div>

        <!-- Top Clients Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-sm p-6 overflow-hidden">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Top Clients by Project Count</h2>
            
            <?php if (!empty($clientProjectCounts)): ?>
                <div class="h-64">
                    <canvas id="clientChart"></canvas>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                    No client data available.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Set theme-aware defaults based on dark mode state
const isDarkMode = document.documentElement.classList.contains('dark');
const fontColor = '#fff'; //isDarkMode ? '#E5E7EB' : '#374151';
const gridColor = '#374151'; //isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

Chart.defaults.color = fontColor;
Chart.defaults.scale.grid.color = gridColor;

// Common options
const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 20,
                boxWidth: 12
            }
        },
        tooltip: {
            backgroundColor: isDarkMode ? '#374151' : 'white',
            titleColor: isDarkMode ? '#F3F4F6' : '#111827',
            bodyColor: isDarkMode ? '#D1D5DB' : '#374151',
            borderColor: isDarkMode ? '#4B5563' : '#E5E7EB',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 6,
            displayColors: true,
            usePointStyle: true
        }
    }
};

// 1. Status Chart (Pie)
<?php if (array_sum($statusCounts) > 0): ?>
document.addEventListener('DOMContentLoaded', function() {
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($statusLabels) ?>,
            datasets: [{
                data: <?= json_encode($statusCounts) ?>,
                backgroundColor: <?= json_encode($statusColors) ?>,
                borderColor: isDarkMode ? '#1F2937' : 'white',
                borderWidth: 2
            }]
        },
        options: {
            ...commonOptions,
            cutout: '30%',
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    ...commonOptions.plugins.tooltip,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
<?php endif; ?>

// 2. Budget vs. Spent Chart (Bar) - Now showing task hours
<?php if (!empty($projectNames) && !empty($projectBudgets)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const budgetCtx = document.getElementById('budgetChart').getContext('2d');
    const budgetChart = new Chart(budgetCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($projectNames) ?>,
            datasets: [
                {
                    label: 'Estimated Hours',
                    data: <?= json_encode($projectBudgets) ?>,
                    backgroundColor: '#3B82F6', // blue
                    borderColor: isDarkMode ? '#1F2937' : 'white',
                    borderWidth: 1
                },
                {
                    label: 'Actual Hours',
                    data: <?= json_encode($projectSpent) ?>,
                    backgroundColor: '#10B981', // green
                    borderColor: isDarkMode ? '#1F2937' : 'white',
                    borderWidth: 1
                }
            ]
        },
        options: {
            ...commonOptions,
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Hours'
                    }
                }
            },
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    ...commonOptions.plugins.tooltip,
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const value = context.raw || 0;
                            return `${label}: ${value} hours`;
                        }
                    }
                }
            }
        }
    });
});
<?php endif; ?>

// 3. Project Durations Chart (Horizontal Bar)
<?php if (!empty($completedProjectDurations) || !empty($inProgressProjectDurations)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const durationCtx = document.getElementById('durationChart').getContext('2d');
    
    // Format data for chart
    const completedNames = <?= json_encode(array_column($completedProjectDurations, 'name')) ?>;
    const completedDurations = <?= json_encode(array_column($completedProjectDurations, 'duration')) ?>;
    
    const inProgressNames = <?= json_encode(array_column($inProgressProjectDurations, 'name')) ?>;
    const inProgressDurations = <?= json_encode(array_column($inProgressProjectDurations, 'duration')) ?>;
    
    // Combine completed and in-progress for unified view
    const allNames = [...completedNames, ...inProgressNames];
    
    const durationChart = new Chart(durationCtx, {
        type: 'bar',
        data: {
            labels: allNames,
            datasets: [
                {
                    label: 'Completed Projects',
                    data: [...completedDurations, ...Array(inProgressNames.length).fill(0)],
                    backgroundColor: '#10B981', // green
                    borderColor: isDarkMode ? '#1F2937' : 'white',
                    borderWidth: 1
                },
                {
                    label: 'In-Progress Projects',
                    data: [...Array(completedNames.length).fill(0), ...inProgressDurations],
                    backgroundColor: '#F59E0B', // yellow
                    borderColor: isDarkMode ? '#1F2937' : 'white',
                    borderWidth: 1
                }
            ]
        },
        options: {
            ...commonOptions,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Days'
                    }
                }
            }
        }
    });
});
<?php endif; ?>

// 4. Top Clients Chart (Doughnut)
<?php if (!empty($clientProjectCounts)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const clientCtx = document.getElementById('clientChart').getContext('2d');
    
    const clientLabels = <?= json_encode(array_keys($clientProjectCounts)) ?>;
    const clientData = <?= json_encode(array_values($clientProjectCounts)) ?>;
    
    // Generate colors for each client
    const clientColors = Array.from(
        { length: clientLabels.length }, 
        (_, i) => getChartColor(i)
    );
    
    const clientChart = new Chart(clientCtx, {
        type: 'doughnut',
        data: {
            labels: clientLabels,
            datasets: [{
                data: clientData,
                backgroundColor: clientColors,
                borderColor: isDarkMode ? '#1F2937' : 'white',
                borderWidth: 2
            }]
        },
        options: {
            ...commonOptions,
            cutout: '50%',
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    ...commonOptions.plugins.tooltip,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} projects (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
<?php endif; ?>
</script>