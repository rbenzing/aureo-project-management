<?php
//file: Views/Tasks/index.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
use App\Utils\Time;

// Determine the context based on URL
$isMyTasksView = isset($userId); // This would be set in the controller when /:user_id is present
$currentUserId = $_SESSION['user']['profile']['id'] ?? null;
$viewingOwnTasks = $isMyTasksView && $userId == $currentUserId;
$viewTitle = $isMyTasksView ? ($viewingOwnTasks ? 'My Tasks' : 'User Tasks') : 'All Tasks';

// Set up filter options based on context
$filterOptions = [
    'all' => 'All Statuses',
    'overdue' => 'Overdue',
    'today' => 'Due Today',
    'week' => 'Due This Week',
    'in-progress' => 'In Progress',
    'completed' => 'Completed'
];

// If viewing another user's tasks, we may want to add more filters
if ($isMyTasksView && !$viewingOwnTasks) {
    $filterOptions['assigned'] = 'Assigned';
    $filterOptions['unassigned'] = 'Unassigned';
}

// Include helper functions
include __DIR__ . '/inc/helper_functions.php';
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $viewTitle ?> - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header and Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6 flex-grow">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>
        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <!-- Task Stats Summary -->
        <?php include __DIR__ . '/inc/stats.php'; ?>

        <!-- Page Header with Filters -->
        <?php include __DIR__ . '/inc/filters.php'; ?>
        
        <!-- Tasks Table -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <?php include __DIR__ . '/inc/table_header.php'; ?>
                    <?php include __DIR__ . '/inc/table.php'; ?>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php include __DIR__ . '/inc/pagination.php'; ?>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <!-- JavaScript for Task Filtering -->
    <script>
        <?php include __DIR__ . '/inc/task_filtering.js'; ?>
        
        // Additional script for active timer if needed
        <?php if (isset($activeTimer)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const timerDisplay = document.getElementById('timer-display');
            let seconds = <?= $activeTimer['duration'] ?? 0 ?>;
            
            setInterval(function() {
                seconds++;
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const secs = seconds % 60;
                
                timerDisplay.textContent = 
                    (hours < 10 ? '0' + hours : hours) + ':' +
                    (minutes < 10 ? '0' + minutes : minutes) + ':' +
                    (secs < 10 ? '0' + secs : secs);
            }, 1000);
        });
        <?php endif; ?>
    </script>
</body>
</html>