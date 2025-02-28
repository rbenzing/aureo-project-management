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
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold"><?= htmlspecialchars($project->name) ?></h1>
            <a href="/projects/edit/<?= htmlspecialchars($project->id) ?>" class="block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Edit</a>
        </div>

        <!-- Project Details -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6 mb-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Project Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p><strong>Description:</strong> <?= htmlspecialchars($project->description ?? 'No description') ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($project->project_status ?? 'Unknown') ?></p>
                    <p><strong>Start Date:</strong> <?= htmlspecialchars($project->start_date ?? '') ?></p>
                    <p><strong>End Date:</strong> <?= htmlspecialchars($project->end_date ?? '') ?></p>
                </div>
                <div>
                    <p><strong>Company:</strong> <?= htmlspecialchars($project->company_name ?? '') ?></p>
                    <p><strong>Owner:</strong> <?= htmlspecialchars($project->owner_firstname ?? '') ?> <?= htmlspecialchars($project->owner_lastname ?? '') ?></p>
                    <p><strong>Created At:</strong> <?= htmlspecialchars($project->created_at ?? 'Unknown') ?></p>
                </div>
            </div>
        </div>

        <!-- Milestones -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Milestones</h2>
                <a href="/milestones/create?id=<?= htmlspecialchars($project->id) ?>" class="text-indigo-600 hover:text-indigo-900">[+]</a>
            </div>
            <?php if (!empty($milestones)): ?>
                <ul class="space-y-2">
                    <?php foreach ($milestones as $milestone): ?>
                        <li class="flex justify-between items-center">
                            <div>
                                <span class="font-medium"><?= htmlspecialchars($milestone->title) ?></span>
                                <span class="text-sm text-gray-500">(<?= htmlspecialchars($milestoneStatuses[$milestone->status_id] ?? 'Unknown') ?>)</span>
                            </div>
                            <span class="text-sm text-gray-500"><?= htmlspecialchars($milestone->due_date ?? 'No due date') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500 dark:text-gray-400">No milestones found.</p>
            <?php endif; ?>
        </div>

        <!-- Tasks -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($tasksByStatus as $status => $tasks): ?>
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100"><?= ucfirst($status) ?></h3>
                        <button id="add-task-<?= $status ?>" class="text-indigo-600 hover:text-indigo-900 cursor-pointer">[+]</button>
                    </div>
                    <form id="task-form-<?= $status ?>" class="hidden mb-4">
                        <input type="text" name="title" placeholder="Task title" class="w-full p-2 border rounded mb-2">
                        <textarea name="description" placeholder="Task description" class="w-full p-2 border rounded mb-2"></textarea>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Add Task</button>
                    </form>
                    <?php if (!empty($tasks)): ?>
                        <?php foreach ($tasks as $task): ?>
                            <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg mb-4">
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100"><?= htmlspecialchars($task->title) ?></h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($task->description ?? 'No description') ?></p>
                                <?php if (!empty($task->subtasks)): ?>
                                    <ul class="mt-2 space-y-1">
                                        <?php foreach ($task->subtasks as $subtaskId): ?>
                                            <li class="text-sm text-gray-700 dark:text-gray-300">
                                                - Subtask ID: <?= htmlspecialchars($subtaskId) ?>
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
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        // Toggle task forms
        document.querySelectorAll('[id^="add-task-"]').forEach(button => {
            button.addEventListener('click', () => {
                const status = button.id.split('-')[2];
                const form = document.getElementById(`task-form-${status}`);
                form.classList.toggle('hidden');
            });
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