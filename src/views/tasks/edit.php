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
    <title>Edit Task</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Edit Task</h1>

        <!-- Edit Task Form -->
        <form method="POST" action="/tasks/edit.php?id=<?php echo $task->id; ?>" class="space-y-4 max-w-md">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task->title); ?>" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium">Description</label>
                <textarea id="description" name="description"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($task->description ?? ''); ?></textarea>
            </div>

            <!-- Project -->
            <div>
                <label for="project_id" class="block text-sm font-medium">Project</label>
                <select id="project_id" name="project_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Select a project</option>
                    <?php
                    $projects = (new \App\Models\Project())->getAll();
                    foreach ($projects as $project): ?>
                        <option value="<?php echo $project->id; ?>" <?php echo $project->id == $task->project_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($project->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Priority -->
            <div>
                <label for="priority" class="block text-sm font-medium">Priority</label>
                <select id="priority" name="priority" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="low" <?php echo $task->priority === 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo $task->priority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo $task->priority === 'high' ? 'selected' : ''; ?>>High</option>
                </select>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium">Status</label>
                <select id="status" name="status" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="todo" <?php echo $task->status === 'todo' ? 'selected' : ''; ?>>To Do</option>
                    <option value="in_progress" <?php echo $task->status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="done" <?php echo $task->status === 'done' ? 'selected' : ''; ?>>Done</option>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update Task
            </button>
        </form>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>