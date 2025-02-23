<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milestones</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <!-- Display Errors or Success Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Header Controls -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-2">
                    <h1 class="text-2xl font-medium">Milestones</h1>
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center space-x-4">

                    <!-- Search Bar -->
                    <div class="relative w-64">
                        <input type="text" 
                            placeholder="Search milestones..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <a href="/milestones/create" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">+ New Milestone</a>
                </div>
            </div>
        </div>

        <!-- Milestone Groups -->
        <?php foreach ($milestones as $group): ?>
        <div class="mb-8">
            <!-- Milestone Group Header -->
            <div class="bg-blue-500 text-white rounded-t-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <h2 class="text-lg font-medium"><?= htmlspecialchars($group['name']) ?></h2>
                        <span class="bg-blue-400 px-2 py-1 rounded-full text-sm">
                            <?= count($group['milestones']) ?> milestones
                        </span>
                    </div>
                    <div class="flex items-center space-x-8">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span><?= $group['completion_rate'] ?>% completed</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><?= $group['time_remaining'] ?> days remaining</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Milestone Table -->
            <div class="bg-white shadow-sm rounded-b-lg overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Milestone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tasks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($group['milestones'] as $milestone): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-1 h-6 bg-indigo-500 rounded-full mr-4"></div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($milestone->title) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($milestone->description) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <?= htmlspecialchars($projects[$milestone->project_id]->name) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= count($milestoneTasks[$milestone->id]) ?> tasks
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex -space-x-2">
                                    <?php foreach (array_slice($milestoneTasks[$milestone->id], 0, 3) as $task): ?>
                                    <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center">
                                        <span class="text-xs text-gray-600"><?= substr($task->title, 0, 2) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (count($milestoneTasks[$milestone->id]) > 3): ?>
                                    <div class="w-8 h-8 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center">
                                        <span class="text-xs text-gray-600">+<?= count($milestoneTasks[$milestone->id]) - 3 ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-sm rounded-full <?= getStatusClasses($milestone->status_id) ?>">
                                    <?= htmlspecialchars($milestoneStatuses[$milestone->status_id]->name) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $milestone->completion_rate ?>%"></div>
                                </div>
                                <span class="text-sm text-gray-600"><?= $milestone->completion_rate ?>%</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm <?= strtotime($milestone->due_date) < time() ? 'text-red-600' : 'text-gray-900' ?>">
                                    <?= date('M j', strtotime($milestone->due_date)) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="flex space-x-3">
                                    <a href="/milestones/edit/<?= $milestone->id ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <a href="/milestones/delete/<?= $milestone->id ?>" 
                                       onclick="return confirm('Are you sure you want to delete this milestone?')"
                                       class="text-red-600 hover:text-red-900">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
        <?php
        function getStatusClasses($statusId) {
            $classes = [
                1 => 'bg-gray-100 text-gray-800', // Not Started
                2 => 'bg-blue-100 text-blue-800', // In Progress
                3 => 'bg-green-100 text-green-800', // Completed
                4 => 'bg-yellow-100 text-yellow-800', // On Hold
                5 => 'bg-red-100 text-red-800', // Blocked
            ];
            return $classes[$statusId] ?? 'bg-gray-100 text-gray-800';
        }
        ?>
        <!-- Pagination -->
        <div class="mt-4">
            <?php if (isset($pagination)): ?>
                <nav class="flex justify-between items-center">
                    <?php if ($pagination['prev_page']): ?>
                        <a href="/milestones/page/<?= htmlspecialchars($pagination['prev_page']) ?>" class="text-indigo-600 hover:text-indigo-900">&laquo; Previous</a>
                    <?php endif; ?>
                    <?php if ($pagination['next_page']): ?>
                        <a href="/milestones/page/<?= htmlspecialchars($pagination['next_page']) ?>" class="text-indigo-600 hover:text-indigo-900">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </main>
    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>