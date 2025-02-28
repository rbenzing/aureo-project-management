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
    <title>Milestone Details</title>
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

        <h1 class="text-2xl font-bold mb-6"><?= htmlspecialchars($milestone->title) ?></h1>
        <!-- Milestone Details -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Milestone Details</h2>
            <ul class="space-y-2">
                <li><strong>Description:</strong> <?= htmlspecialchars($milestone->description ?? 'No description available') ?></li>
                <li><strong>Due Date:</strong> <?= htmlspecialchars($milestone->due_date ?? 'No due date set') ?></li>
                <li><strong>Complete Date:</strong> <?= htmlspecialchars($milestone->complete_date ?? 'Not completed') ?></li>
                <li><strong>Status:</strong> <?= htmlspecialchars($milestoneStatus->name) ?></li>
                <li><strong>Project:</strong> 
                    <a href="/projects/view/<?= htmlspecialchars($project->id) ?>" class="text-indigo-600 hover:text-indigo-900">
                        <?= htmlspecialchars($project->name) ?>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Actions -->
        <div class="mt-6 flex gap-4">
            <a href="/milestones" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Back to Milestones</a>
            <a href="/milestones/edit/<?= htmlspecialchars($milestone->id) ?>" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Edit</a>
            <a href="/milestones/delete/<?= htmlspecialchars($milestone->id) ?>" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
               onclick="return confirm('Are you sure you want to delete this milestone?')">Delete Milestone</a>
        </div>
    </main>
    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>