<?php
// file: src/Views/Milestones/view.php
declare(strict_types=1);

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
    <title><?= htmlspecialchars($milestone->title) ?> - Milestone Details - <?= htmlspecialchars(Config::get('company_name', 'Slimbooks')) ?></title>
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

        <div class="container mx-auto px-4 py-8">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <!-- Milestone Header -->
                <div class="bg-gray-100 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($milestone->title) ?></h1>
                        <span class="text-sm text-gray-600">
                            <?= htmlspecialchars($milestone->milestone_type === 'epic' ? 'Epic' : 'Milestone') ?>
                        </span>
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($milestone->milestone_type === 'epic'): ?>
                            <a href="/milestones/create?epic_id=<?= $milestone->id ?>" 
                            class="btn btn-sm btn-primary">
                                Add Milestone
                            </a>
                        <?php endif; ?>
                        <a href="/milestones/edit/<?= $milestone->id ?>" 
                        class="btn btn-sm btn-secondary">
                            Edit
                        </a>
                    </div>
                </div>

                <!-- Milestone Details -->
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold mb-4">Milestone Information</h2>
                        <div class="space-y-2">
                            <p><strong>Project:</strong> 
                                <a href="/projects/view/<?= $project->id ?>" class="text-blue-600">
                                    <?= htmlspecialchars($project->name) ?>
                                </a>
                            </p>
                            <p><strong>Status:</strong> 
                                <span class="badge <?= $milestone->status_name === 'Completed' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= htmlspecialchars($milestone->status_name) ?>
                                </span>
                            </p>
                            <p><strong>Type:</strong> 
                                <?= htmlspecialchars(ucfirst($milestone->milestone_type)) ?>
                            </p>
                            <?php if ($milestone->epic_id && isset($epic)): ?>
                                <p><strong>Parent Epic:</strong> 
                                    <a href="/milestones/view/<?= $epic->id ?>" class="text-blue-600">
                                        <?= htmlspecialchars($epic->title) ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold mb-4">Timeline</h2>
                        <div class="space-y-2">
                            <p><strong>Start Date:</strong> 
                                <?= $milestone->start_date ? date('F j, Y', strtotime($milestone->start_date)) : 'Not Set' ?>
                            </p>
                            <p><strong>Due Date:</strong> 
                                <?= $milestone->due_date ? date('F j, Y', strtotime($milestone->due_date)) : 'Not Set' ?>
                            </p>
                            <?php if ($milestone->complete_date): ?>
                                <p><strong>Completed On:</strong> 
                                    <?= date('F j, Y', strtotime($milestone->complete_date)) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="p-6 border-t border-gray-200">
                    <h2 class="text-lg font-semibold mb-4">Description</h2>
                    <p class="text-gray-700">
                        <?= $milestone->description ? htmlspecialchars($milestone->description) : 'No description provided.' ?>
                    </p>
                </div>

                <!-- Related Sections -->
                <div class="p-6 border-t border-gray-200">
                    <!-- Tasks Section -->
                    <h2 class="text-lg font-semibold mb-4">Associated Tasks</h2>
                    <?php if (!empty($milestone->tasks)): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-2 text-left">Title</th>
                                        <th class="p-2 text-left">Status</th>
                                        <th class="p-2 text-left">Assigned To</th>
                                        <th class="p-2 text-left">Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($milestone->tasks as $task): ?>
                                        <tr class="border-b">
                                            <td class="p-2">
                                                <a href="/tasks/view/<?= $task->id ?>" class="text-blue-600">
                                                    <?= htmlspecialchars($task->title) ?>
                                                </a>
                                            </td>
                                            <td class="p-2"><?= htmlspecialchars($task->status_name) ?></td>
                                            <td class="p-2">
                                                <?= $task->first_name && $task->last_name 
                                                    ? htmlspecialchars("{$task->first_name} {$task->last_name}") 
                                                    : 'Unassigned' ?>
                                            </td>
                                            <td class="p-2">
                                                <?= $task->due_date ? date('F j, Y', strtotime($task->due_date)) : 'Not Set' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">No tasks associated with this milestone.</p>
                    <?php endif; ?>

                    <!-- Related Milestones for Epic -->
                    <?php if ($milestone->milestone_type === 'epic' && !empty($relatedMilestones)): ?>
                        <div class="mt-6">
                            <h2 class="text-lg font-semibold mb-4">Related Milestones</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($relatedMilestones as $related): ?>
                                    <div class="bg-gray-50 p-4 rounded-md">
                                        <a href="/milestones/view/<?= $related->id ?>" class="text-blue-600 font-semibold">
                                            <?= htmlspecialchars($related->title) ?>
                                        </a>
                                        <p class="text-sm text-gray-600">
                                            Status: <?= htmlspecialchars($related->status_name) ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Delete Milestone Modal (Hidden by default) -->
            <div id="deleteMilestoneModal" class="modal hidden">
                <div class="modal-content">
                    <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
                    <p>Are you sure you want to delete this milestone? This action cannot be undone.</p>
                    <div class="flex justify-end space-x-2 mt-4">
                        <button id="cancelDelete" class="btn btn-secondary">Cancel</button>
                        <form action="/milestones/delete/<?= $milestone->id ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>
</body>
</html>