<?php
// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'You must be logged in to access this page.';
    header('Location: /login');
    exit;
}

// Fetch data for the dashboard
require_once __DIR__ . '/../Controllers/ProjectController.php';
require_once __DIR__ . '/../Controllers/TaskController.php';

$projectController = new \App\Controllers\ProjectController();
$taskController = new \App\Controllers\TaskController();

$projects = $projectController->index(); // Fetch all projects
$tasks = $taskController->index();       // Fetch tasks assigned to the user
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard | Project Management System</title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow md:ml-64 p-6">
        <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

        <!-- Welcome Message -->
        <div class="mb-6">
            <p class="text-lg">Welcome back, <?php echo htmlspecialchars($_SESSION['user']['first_name']); ?>!</p>
            <p class="text-sm text-gray-500">Here's an overview of your projects and tasks.</p>
        </div>

        <!-- Projects Overview -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Active Projects</h2>
            <?php if (!empty($projects)): ?>
                <ul class="space-y-2">
                    <?php foreach ($projects as $project): ?>
                        <li class="flex justify-between items-center">
                            <span><?php echo htmlspecialchars($project['name']); ?></span>
                            <a href="/projects/view.php?id=<?php echo $project['id']; ?>" class="text-indigo-600 hover:text-indigo-900">View</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No active projects found.</p>
            <?php endif; ?>
        </div>

        <!-- Tasks Overview -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Your Tasks</h2>
            <?php if (!empty($tasks)): ?>
                <ul class="space-y-2">
                    <?php foreach ($tasks as $task): ?>
                        <li class="flex justify-between items-center">
                            <span><?php echo htmlspecialchars($task['title']); ?></span>
                            <span class="text-sm text-gray-500"><?php echo ucfirst($task['status']); ?></span>
                            <a href="/tasks/edit.php?id=<?php echo $task['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No tasks assigned to you.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>