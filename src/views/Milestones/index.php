<?php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use \App\Core\Config;
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milestones - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6 flex-grow">
        <!-- Notification Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-50 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Milestones</h1>
            
            <div class="flex space-x-4">
                <!-- Search -->
                <div class="relative">
                    <input 
                        type="search" 
                        placeholder="Search milestones..." 
                        class="w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md pl-10 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- New Milestone Button -->
                <a 
                    href="/milestones/create" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    New Milestone
                </a>
            </div>
        </div>

        <!-- Milestones Table -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($milestones as $milestone): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                            <?= htmlspecialchars($milestone->title) ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <?= htmlspecialchars($milestone->description ?? '') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-gray-200">
                                    <?= htmlspecialchars($milestone->project_name ?? 'Unassigned') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getStatusClasses($milestone->status_id) ?>">
                                    <?= htmlspecialchars($milestone->status_name ?? 'Unknown') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                                    <div 
                                        class="bg-blue-600 h-2.5 rounded-full" 
                                        style="width: <?= $milestone->completion_rate ?? 0 ?>%"
                                    ></div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <?= isset($milestone->completion_rate) ? number_format((float)$milestone->completion_rate, 2) : '0' ?>%
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm <?= strtotime($milestone->due_date) < time() ? 'text-red-600' : 'text-gray-900 dark:text-gray-200' ?>">
                                    <?= $milestone->due_date ? date('M j, Y', strtotime($milestone->due_date)) : 'No Due Date' ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end space-x-3">
                                    <a 
                                        href="/milestones/view/<?= $milestone->id ?>" 
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >
                                        View
                                    </a>
                                    <a 
                                        href="/milestones/edit/<?= $milestone->id ?>" 
                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                    >
                                        Edit
                                    </a>
                                    <form 
                                        action="/milestones/delete/<?= $milestone->id ?>" 
                                        method="POST" 
                                        onsubmit="return confirm('Are you sure you want to delete this milestone?');"
                                        class="inline"
                                    >
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button 
                                            type="submit" 
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (isset($pagination)): ?>
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?>
                </div>
                <div class="flex space-x-2">
                    <?php if ($pagination['prev_page']): ?>
                        <a 
                            href="/milestones/page/<?= $pagination['prev_page'] ?>" 
                            class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($pagination['next_page']): ?>
                        <a 
                            href="/milestones/page/<?= $pagination['next_page'] ?>" 
                            class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <?php
    function getStatusClasses($statusId) {
        $classes = [
            1 => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Not Started
            2 => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300', // In Progress
            3 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300', // Completed
            4 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300', // On Hold
            5 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300', // Delayed
        ];
        return $classes[$statusId] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
    ?>
</body>
</html>