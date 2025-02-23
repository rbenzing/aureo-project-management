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
    <title>View Task</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Task Details</h1>

        <!-- Task Information -->
        <div class="space-y-4 max-w-md">
            <div>
                <label class="block text-sm font-medium text-gray-500">Title</label>
                <p class="mt-1 block text-lg font-semibold"><?php echo htmlspecialchars($task->title); ?></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500">Description</label>
                <p class="mt-1 block"><?php echo nl2br(htmlspecialchars($task->description ?? '')); ?></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500">Priority</label>
                <p class="mt-1 block"><?php echo ucfirst(htmlspecialchars($task->priority)); ?></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500">Status</label>
                <p class="mt-1 block"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($task->status))); ?></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500">Project</label>
                <p class="mt-1 block">
                    <?php if ($project): ?>
                        <a href="/projects/view.php?id=<?php echo $project->id; ?>" class="text-indigo-600 hover:text-indigo-900">
                            <?php echo htmlspecialchars($project->name); ?>
                        </a>
                    <?php else: ?>
                        <span class="text-gray-500">No project assigned</span>
                    <?php endif; ?>
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500">Estimated Time</label>
                <p class="mt-1 block"><?php echo htmlspecialchars($task->estimated_time ?? 'N/A'); ?> minutes</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500">Time Spent</label>
                <p class="mt-1 block"><?php echo htmlspecialchars($task->time_spent ?? '0'); ?> minutes</p>
            </div>
        </div>

        <!-- Subtasks -->
        <div class="mt-6">
            <h2 class="text-xl font-bold mb-4">Subtasks</h2>
            <?php if (!empty($subtasks)): ?>
                <ul class="space-y-2">
                    <?php foreach ($subtasks as $subtask): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($subtask->title); ?></strong>
                            <span class="text-sm text-gray-500">(<?php echo ucfirst($subtask->status); ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No subtasks found.</p>
            <?php endif; ?>
        </div>

        <!-- Add Subtask Form -->
        <div class="mt-6">
            <h2 class="text-xl font-bold mb-4">Add Subtask</h2>
            <form method="POST" action="/create_subtask?id=<?php echo $task->id; ?>" class="space-y-4 max-w-md">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium">Title</label>
                    <input type="text" id="title" name="title" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium">Description</label>
                    <textarea id="description" name="description"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium">Status</label>
                    <select id="status" name="status"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                </div>

                <!-- Estimated Time -->
                <div>
                    <label for="estimated_time" class="block text-sm font-medium">Estimated Time (minutes)</label>
                    <input type="number" id="estimated_time" name="estimated_time" min="0"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Subtask
                </button>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 space-x-4">
            <a href="/edit_tasks?id=<?= htmlspecialchars($task->id) ?>" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Edit Task</a>
            <a href="/delete_task?id=<?= htmlspecialchars($task->id) ?>" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
               onclick="return confirm('Are you sure you want to delete this task?')">Delete Task</a>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>