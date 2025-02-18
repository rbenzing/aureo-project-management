<?php
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to access this page.';
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Project</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <div class="flex justify-between">
            <div class="flex-col">
                <small><a href="/projects" class="text-gray-200 py-4">&laquo; Back to Projects</a></small>
                <h1 class="text-2xl font-bold mb-6"><?= htmlspecialchars($project->name) ?></h1>
            </div>
        </div>
        <!-- Project Details -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Project Details</h2>
            <div class="flex">
                <div class="w-2/3">
                    <strong>Description:</strong><br><?= htmlspecialchars($project->description ?? 'No description available') ?>
                </div>
                <div class="w-1/3">
                    <ul class="space-y-2">
                        <li><span class="mr-2 font-bold">Status:</span> <?= htmlspecialchars($project->status ?? 'Unknown') ?></li>
                        <li><span class="mr-2 font-bold">Created:</span> <?= htmlspecialchars($project->created_at ?? 'Unknown') ?></li>
                        <li><span class="mr-2 font-bold">Company:</span> <?= htmlspecialchars($project->company_name ?? 'None') ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Kanban Board -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- To Do Column -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">To Do</h3>
                    <button id="add-task-to-do" class="text-indigo-600 hover:text-indigo-800 cursor-pointer">[+]</button>
                </div>
                <form id="task-form-to-do" class="hidden mb-4">
                    <input type="text" name="title" placeholder="Task title" class="w-full p-2 border rounded mb-2">
                    <textarea name="description" placeholder="Task description" class="w-full p-2 border rounded mb-2"></textarea>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Add Task</button>
                </form>
                <?php if (!empty($tasks['to_do'])): ?>
                    <?php foreach ($tasks['to_do'] as $task): ?>
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg mb-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($task['title']) ?></h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($task['description'] ?? 'No description') ?></p>
                            <?php if (!empty($task['subtasks'])): ?>
                                <ul class="mt-2 space-y-1">
                                    <?php foreach ($task['subtasks'] as $subtask): ?>
                                        <li class="text-sm text-gray-700 dark:text-gray-300">
                                            - <?= htmlspecialchars($subtask['title']) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400">No tasks in this column.</p>
                <?php endif; ?>
            </div>

            <!-- In Progress Column -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">In Progress</h3>
                    <button id="add-task-in-progress" class="text-indigo-600 hover:text-indigo-800 cursor-pointer">[+]</button>
                </div>
                <form id="task-form-in-progress" class="hidden mb-4">
                    <input type="text" name="title" placeholder="Task title" class="w-full p-2 border rounded mb-2">
                    <textarea name="description" placeholder="Task description" class="w-full p-2 border rounded mb-2"></textarea>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Add Task</button>
                </form>
                <?php if (!empty($tasks['in_progress'])): ?>
                    <?php foreach ($tasks['in_progress'] as $task): ?>
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg mb-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($task['title']) ?></h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($task['description'] ?? 'No description') ?></p>
                            <?php if (!empty($task['subtasks'])): ?>
                                <ul class="mt-2 space-y-1">
                                    <?php foreach ($task['subtasks'] as $subtask): ?>
                                        <li class="text-sm text-gray-700 dark:text-gray-300">
                                            - <?= htmlspecialchars($subtask['title']) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400">No tasks in this column.</p>
                <?php endif; ?>
            </div>

            <!-- Done Column -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Done</h3>
                    <button id="add-task-done" class="text-indigo-600 hover:text-indigo-800 cursor-pointer">[+]</button>
                </div>
                <form id="task-form-done" class="hidden mb-4">
                    <input type="text" name="title" placeholder="Task title" class="w-full p-2 border rounded mb-2">
                    <textarea name="description" placeholder="Task description" class="w-full p-2 border rounded mb-2"></textarea>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Add Task</button>
                </form>
                <?php if (!empty($tasks['done'])): ?>
                    <?php foreach ($tasks['done'] as $task): ?>
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg mb-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($task['title']) ?></h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($task['description'] ?? 'No description') ?></p>
                            <?php if (!empty($task['subtasks'])): ?>
                                <ul class="mt-2 space-y-1">
                                    <?php foreach ($task['subtasks'] as $subtask): ?>
                                        <li class="text-sm text-gray-700 dark:text-gray-300">
                                            - <?= htmlspecialchars($subtask['title']) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 dark:text-gray-400">No tasks in this column.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-between gap-4">
            <a href="/edit_project?id=<?= htmlspecialchars($project->id) ?>" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Edit</a>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        // Toggle task forms
        document.getElementById('add-task-to-do').addEventListener('click', () => {
            const form = document.getElementById('task-form-to-do');
            form.classList.toggle('hidden');
        });

        document.getElementById('add-task-in-progress').addEventListener('click', () => {
            const form = document.getElementById('task-form-in-progress');
            form.classList.toggle('hidden');
        });

        document.getElementById('add-task-done').addEventListener('click', () => {
            const form = document.getElementById('task-form-done');
            form.classList.toggle('hidden');
        });

        // Handle form submissions
        document.querySelectorAll('.task-form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                const status = form.id.split('-')[2]; // Extract status from form ID

                try {
                    const response = await fetch(`/api/tasks`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            title: formData.get('title'),
                            description: formData.get('description'),
                            status: status,
                            project_id: <?= $project->id ?>,
                        }),
                    });

                    if (response.ok) {
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Failed to add task.');
                    }
                } catch (error) {
                    console.error(error);
                    alert('An error occurred while adding the task.');
                }
            });
        });
    </script>
</body>
</html>