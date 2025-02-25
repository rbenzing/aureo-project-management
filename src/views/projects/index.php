<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-white dark:text-gray-100 min-h-screen flex flex-col">

    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <main class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl font-medium">Client Projects</h1>
                <button class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </button>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/create_project" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">+ New Project</a>
            </div>
        </div>

        <div class="flex items-center space-x-6 border-b border-gray-200 mb-6">
            <button class="px-4 py-2 text-blue-600 border-b-2 border-blue-600">Main Table</button>
            <button class="px-4 py-2 text-gray-600">Timeline</button>
            <button class="px-4 py-2 text-gray-600">Charts</button>
            <button class="px-4 py-2 text-gray-600">Pivot Board</button>
            <button class="px-4 py-2 text-gray-600">Gantt</button>
        </div>

        <div class="flex items-center space-x-4 mb-6">
            <button class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                + New Task
            </button>
            <div class="relative">
                <input type="text" placeholder="Search" class="pl-10 pr-4 py-2 border border-gray-300 rounded-md">
                <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <button class="px-4 py-2 text-white border border-gray-300 rounded-md hover:text-gray-600 hover:bg-gray-50">
            ☑ Filter
            </button>
            <button class="px-4 py-2 text-white border border-gray-300 rounded-md hover:text-gray-600 hover:bg-gray-50">
            ↕ Sort
            </button>
        </div>

        <div class="space-y-8">
        <?php foreach ($projects as $project): ?>
            <div class="text-white shadow border-b border-gray-200">
                <div class="py-4">
                    <div class="flex items-center">
                        <div class="w-1 h-6 bg-gray-600 rounded-full mr-4"></div>
                        <h2 class="text-lg font-medium"><?= htmlspecialchars($project->name) ?></h2>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-sm text-gray-500 border-b border-gray-500">
                                <th class="px-6 py-3 font-medium"></th>
                                <th class="px-6 py-3 font-medium">Task</th>
                                <th class="px-6 py-3 font-medium">Owner</th>
                                <th class="px-6 py-3 font-medium">Client</th>
                                <th class="px-6 py-3 font-medium">Priority</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 font-medium">Timeline</th>
                                <th class="px-6 py-3 font-medium">Actual Time</th>
                                <th class="px-6 py-3 font-medium">Hourly rate</th>
                                <th class="px-6 py-3 font-medium">Billable amount</th>
                                <th class="px-6 py-3 font-medium">Files</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-600">
                            <?php foreach ($project->tasks as $task): ?>
                            <tr>
                                <td class="pr-6 px-0">
                                    <div class="w-1 h-6 bg-purple-500 rounded-full"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="#" class="text-white"><?= htmlspecialchars($task->title) ?></a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex -space-x-2">
                                        <img class="inline-block size-4 rounded-full ring-2 ring-white" src="https://images.unsplash.com/photo-1491528323818-fdd1faba62cc?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-white"><?= htmlspecialchars($project->company_name) ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex text-yellow-400">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <svg class="w-5 h-5" fill="<?= $i < $task->priority ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-sm <?= $task->status === 'Done' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?> rounded-full">
                                        <?= htmlspecialchars($task->status) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-sm <?= isset($task->complete_date) && $task->complete_date < $task->due_date ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?> rounded-full">
                                        <?= htmlspecialchars($task->start_date ?? 'Not Started') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-white"><?= htmlspecialchars($task->time_spent) ?></td>
                                <td class="px-6 py-4 text-white">$<?= htmlspecialchars($task->hourly_rate ?? '0') ?></td>
                                <td class="px-6 py-4 text-white">$<?= htmlspecialchars(number_format($task->billable_time ?? '0', 2)) ?></td>
                                <td class="px-6 py-4">
                                    <svg class="w-5 h-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
                                    </svg>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <tr class="bg-gray-500">
                                <td colspan="7" class="px-6 py-1 text-white">0 / 0</td>
                                <td class="px-6 py-1 text-white">0</td>
                                <td class="px-6 py-1 text-white">$0</td>
                                <td class="px-6 py-1 text-white">$0</td>
                                <td class="px-6 py-1">
                                    <div class="flex items-center">
                                        <span class="bg-gray-200 rounded-full px-2 py-1 text-blue-800 text-xs">0</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <div class="mt-4">
            <?php if (isset($pagination)): ?>
                <nav class="flex justify-between items-center">
                    <?php if ($pagination['prev_page']): ?>
                        <a href="/projects/page/<?= htmlspecialchars($pagination['prev_page']) ?>" class="text-indigo-600 hover:text-indigo-900">&laquo; Previous</a>
                    <?php endif; ?>
                    <?php if ($pagination['next_page']): ?>
                        <a href="/projects/page/<?= htmlspecialchars($pagination['next_page']) ?>" class="text-indigo-600 hover:text-indigo-900">Next &raquo;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>

    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>