<?php
// file: Views/Milestones/create.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;
?>

<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Milestone - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <!-- Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="container mx-auto p-6 flex-grow">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>

        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create New Milestone</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Add a new milestone or epic to track progress on your project
            </p>
        </div>

        <!-- Create Milestone Form -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
            <form action="/milestones/create" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <!-- Milestone Type Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Milestone Type
                        </label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="milestone_type" value="milestone" class="form-radio h-4 w-4 text-indigo-600" checked>
                                <span class="ml-2 text-gray-700 dark:text-gray-300">Milestone</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="milestone_type" value="epic" class="form-radio h-4 w-4 text-indigo-600">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">Epic</span>
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Epics are large bodies of work that can be broken down into multiple milestones
                        </p>
                    </div>

                    <!-- Project Selection -->
                    <div>
                        <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Project <span class="text-red-600">*</span>
                        </label>
                        <select
                            id="project_id"
                            name="project_id"
                            class="form-select block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"
                            required
                            data-epic-url="/api/projects/{id}/epics">
                            <option value="">Select Project</option>
                            <?php foreach ($projects['records'] as $project): ?>
                                <option value="<?= htmlspecialchars((string)$project->id) ?>"><?= htmlspecialchars($project->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Epic Selection (conditionally displayed for milestones) -->
                <div id="epic-selection" class="hidden">
                    <label for="epic_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Parent Epic
                    </label>
                    <select
                        id="epic_id"
                        name="epic_id"
                        class="form-select block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                        <option value="">None (Top-level milestone)</option>
                        <?php if (!empty($epics)): ?>
                            <?php foreach ($epics as $epic): ?>
                                <option value="<?= htmlspecialchars((string)$epic->id) ?>"><?= htmlspecialchars($epic->title) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Optional: Associate this milestone with a parent epic
                    </p>
                </div>

                <!-- Title and Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Title <span class="text-red-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-input block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"
                            required>
                    </div>

                    <div>
                        <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Status <span class="text-red-600">*</span>
                        </label>
                        <select
                            id="status_id"
                            name="status_id"
                            class="form-select block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"
                            required>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= htmlspecialchars((string)$status->id) ?>"><?= htmlspecialchars($status->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Description
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="form-textarea block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"></textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Optional: Provide a detailed description of the milestone
                    </p>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Start Date
                        </label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            class="form-input block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                    </div>

                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Due Date
                        </label>
                        <input
                            type="date"
                            id="due_date"
                            name="due_date"
                            class="form-input block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3">
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a
                        href="/milestones"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Milestone
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <!-- JavaScript for Form Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const milestoneTypeRadios = document.querySelectorAll('input[name="milestone_type"]');
            const epicSelection = document.getElementById('epic-selection');
            const projectSelect = document.getElementById('project_id');
            const epicSelect = document.getElementById('epic_id');

            // Toggle Epic Selection visibility based on milestone type
            function toggleEpicSelection() {
                const selectedType = document.querySelector('input[name="milestone_type"]:checked').value;
                if (selectedType === 'milestone') {
                    epicSelection.classList.remove('hidden');
                } else {
                    epicSelection.classList.add('hidden');
                    epicSelect.value = '';
                }
            }

            // Load epics when project changes
            async function loadEpics() {
                const projectId = projectSelect.value;
                if (!projectId) {
                    epicSelect.innerHTML = '<option value="">None (Top-level milestone)</option>';
                    return;
                }

                try {
                    const url = projectSelect.dataset.epicUrl.replace('{id}', projectId);
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Failed to load epics');

                    const epics = await response.json();

                    epicSelect.innerHTML = '<option value="">None (Top-level milestone)</option>';
                    epics.forEach(epic => {
                        const option = document.createElement('option');
                        option.value = epic.id;
                        option.textContent = epic.title;
                        epicSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error loading epics:', error);
                }
            }

            // Add event listeners
            milestoneTypeRadios.forEach(radio => {
                radio.addEventListener('change', toggleEpicSelection);
            });

            projectSelect.addEventListener('change', loadEpics);

            // Initial state
            toggleEpicSelection();
        });
    </script>
</body>
</html>