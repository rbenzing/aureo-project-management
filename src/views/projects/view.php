<?php
//file: Views/Projects/view.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;

// Map status IDs to human-readable labels
$statusLabels = [
    1 => 'Ready',
    2 => 'In Progress',
    3 => 'Completed',
    4 => 'On Hold',
    6 => 'Delayed',
    7 => 'Cancelled'
];

// Map status IDs to CSS classes
$statusClasses = [
    1 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    2 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    3 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    4 => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    6 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    7 => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project->name) ?> - Project Details - <?= htmlspecialchars(Config::get('company_name', 'Slimbooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>
    
    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($project->name) ?></h1>
                <div class="flex items-center mt-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClasses[$project->status_id] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' ?>">
                        <?= $statusLabels[$project->status_id] ?? 'Unknown Status' ?>
                    </span>
                    <?php if (isset($project->company_name)): ?>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                            <?= htmlspecialchars($project->company_name) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <?php if (isset($_SESSION['user']['permissions']) && in_array('create_tasks', $_SESSION['user']['permissions'])): ?>
                    <a href="/tasks/create?project_id=<?= $project->id ?>" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Task
                    </a>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user']['permissions']) && in_array('edit_projects', $_SESSION['user']['permissions'])): ?>
                    <a href="/projects/edit/<?= $project->id ?>" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-700">
                        <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Edit Project
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Project Overview -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg mb-6">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Project Overview</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <?php if (!empty($project->description)): ?>
                            <div class="prose dark:prose-invert">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Description</h3>
                                <div class="text-gray-700 dark:text-gray-300">
                                    <?= nl2br(htmlspecialchars($project->description)) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-gray-500 dark:text-gray-400">No description available</div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Project Details</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Project Owner</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <?= isset($project->owner_firstname) ? htmlspecialchars($project->owner_firstname . ' ' . $project->owner_lastname) : 'Not assigned' ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Company</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <?= isset($project->company_name) ? htmlspecialchars($project->company_name) : 'Not assigned' ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <?= isset($project->start_date) && !empty($project->start_date) ? date('M j, Y', strtotime($project->start_date)) : 'Not set' ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <?= isset($project->end_date) && !empty($project->end_date) ? date('M j, Y', strtotime($project->end_date)) : 'Not set' ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <?= isset($project->created_at) ? date('M j, Y', strtotime($project->created_at)) : 'Unknown' ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <?= isset($project->updated_at) ? date('M j, Y', strtotime($project->updated_at)) : 'Unknown' ?>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Board -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg mb-6">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Tasks</h2>
                <?php if (isset($_SESSION['user']['permissions']) && in_array('create_tasks', $_SESSION['user']['permissions'])): ?>
                    <a href="/tasks/create?project_id=<?= $project->id ?>" class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                        + Add Task
                    </a>
                <?php endif; ?>
            </div>
            <div class="overflow-x-auto">
                <div class="inline-block min-w-full">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
                        <!-- Open Tasks -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-4">
                            <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3">Open</h3>
                            <?php if (isset($tasksByStatus['open']) && !empty($tasksByStatus['open'])): ?>
                                <div class="space-y-3">
                                    <?php foreach ($tasksByStatus['open'] as $task): ?>
                                        <div class="bg-white dark:bg-gray-800 rounded-md p-3 shadow-sm">
                                            <a href="/tasks/view/<?= $task->id ?>" class="block font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mb-1">
                                                <?= htmlspecialchars($task->title) ?>
                                            </a>
                                            <?php if (isset($task->assigned_to) && !empty($task->assigned_to)): ?>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    Assigned to: <?= htmlspecialchars($task->first_name . ' ' . $task->last_name) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (isset($task->due_date) && !empty($task->due_date)): ?>
                                                <div class="mt-2 text-xs <?= strtotime($task->due_date) < time() ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' ?>">
                                                    Due: <?= date('M j, Y', strtotime($task->due_date)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                                    No open tasks
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- In Progress Tasks -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-4">
                            <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3">In Progress</h3>
                            <?php if (isset($tasksByStatus['in_progress']) && !empty($tasksByStatus['in_progress'])): ?>
                                <div class="space-y-3">
                                    <?php foreach ($tasksByStatus['in_progress'] as $task): ?>
                                        <div class="bg-white dark:bg-gray-800 rounded-md p-3 shadow-sm">
                                            <a href="/tasks/view/<?= $task->id ?>" class="block font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mb-1">
                                                <?= htmlspecialchars($task->title) ?>
                                            </a>
                                            <?php if (isset($task->assigned_to) && !empty($task->assigned_to)): ?>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    Assigned to: <?= htmlspecialchars($task->first_name . ' ' . $task->last_name) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (isset($task->due_date) && !empty($task->due_date)): ?>
                                                <div class="mt-2 text-xs <?= strtotime($task->due_date) < time() ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' ?>">
                                                    Due: <?= date('M j, Y', strtotime($task->due_date)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                                    No tasks in progress
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Completed Tasks -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-4">
                            <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-3">Completed</h3>
                            <?php if (isset($tasksByStatus['completed']) && !empty($tasksByStatus['completed'])): ?>
                                <div class="space-y-3">
                                    <?php foreach ($tasksByStatus['completed'] as $task): ?>
                                        <div class="bg-white dark:bg-gray-800 rounded-md p-3 shadow-sm">
                                            <a href="/tasks/view/<?= $task->id ?>" class="block font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mb-1">
                                                <?= htmlspecialchars($task->title) ?>
                                            </a>
                                            <?php if (isset($task->assigned_to) && !empty($task->assigned_to)): ?>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    Assigned to: <?= htmlspecialchars($task->first_name . ' ' . $task->last_name) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (isset($task->complete_date) && !empty($task->complete_date)): ?>
                                                <div class="mt-2 text-xs text-green-500 dark:text-green-400">
                                                    Completed: <?= date('M j, Y', strtotime($task->complete_date)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                                    No completed tasks
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Milestones and Timeline -->
        <?php if (isset($project->milestones) && !empty($project->milestones)): ?>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg mb-6">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Milestones</h2>
                <?php if (isset($_SESSION['user']['permissions']) && in_array('create_milestones', $_SESSION['user']['permissions'])): ?>
                    <a href="/milestones/create?project_id=<?= $project->id ?>" class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                        + Add Milestone
                    </a>
                <?php endif; ?>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <?php foreach ($project->milestones as $milestone): ?>
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <div class="flex-shrink-0 w-2.5 h-2.5 rounded-full <?= $milestone->status_id == 3 ? 'bg-green-500' : ($milestone->status_id == 2 ? 'bg-yellow-500' : 'bg-blue-500') ?>"></div>
                            </div>
                            <div class="ml-4 flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    <a href="/milestones/view/<?= $milestone->id ?>" class="hover:underline">
                                        <?= htmlspecialchars($milestone->title) ?>
                                    </a>
                                </div>
                                <?php if (isset($milestone->due_date) && !empty($milestone->due_date)): ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Due: <?= date('M j, Y', strtotime($milestone->due_date)) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($milestone->description)): ?>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        <?= htmlspecialchars($milestone->description) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClasses[$milestone->status_id] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' ?>">
                                    <?= isset($milestone->status_name) ? htmlspecialchars($milestone->status_name) : 'Unknown' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Team Members -->
        <?php if (isset($project->team_members) && !empty($project->team_members)): ?>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Team Members</h2>
            </div>
            <div class="p-6">
                <ul class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($project->team_members as $member): ?>
                        <li class="col-span-1 bg-white dark:bg-gray-700 rounded-lg shadow divide-y divide-gray-200 dark:divide-gray-600">
                            <div class="w-full flex items-center justify-between p-4">
                                <div>
                                    <h3 class="text-gray-900 dark:text-white text-sm font-medium truncate">
                                        <?= htmlspecialchars($member->first_name . ' ' . $member->last_name) ?>
                                    </h3>
                                    <?php if (isset($member->role_name)): ?>
                                        <p class="text-gray-500 dark:text-gray-400 text-xs">
                                            <?= htmlspecialchars($member->role_name) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <?php if (isset($_SESSION['user']['permissions']) && in_array('view_users', $_SESSION['user']['permissions'])): ?>
                                    <a href="/users/view/<?= $member->id ?>" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">
                                        View
                                    </a>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>
</body>
</html>