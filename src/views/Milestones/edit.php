<?php
// file: Views/Milestones/edit.php
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
    <title>Edit Milestone - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit Milestone</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update the milestone details
            </p>
        </div>

        <!-- Edit Milestone Form -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg p-6">
            <form action="/milestones/update" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$milestone->id) ?>">
                
                <!-- Milestone Type Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Milestone Type
                        </label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input 
                                    type="radio" 
                                    name="milestone_type" 
                                    value="milestone" 
                                    class="form-radio h-4 w-4 text-indigo-600"
                                    <?= ($milestone->milestone_type === 'milestone') ? 'checked' : '' ?>
                                >
                                <span class="ml-2 text-gray-700 dark:text-gray-300">Milestone</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input 
                                    type="radio" 
                                    name="milestone_type" 
                                    value="epic" 
                                    class="form-radio h-4 w-4 text-indigo-600"
                                    <?= ($milestone->milestone_type === 'epic') ? 'checked' : '' ?>
                                >
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
                            data-epic-url="/api/projects/{id}/epics"
                        >
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $project): ?>
                                <option 
                                    value="<?= htmlspecialchars((string)$project->id) ?>"
                                    <?= ($project->id == $milestone->project_id) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($project->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Epic Selection (conditionally displayed for milestones) -->
                <div id="epic-selection" class="<?= ($milestone->milestone_type === 'epic') ? 'hidden' : '' ?>">
                    <label for="epic_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Parent Epic
                    </label>
                    <select 
                        id="epic_id" 
                        name="epic_id" 
                        class="form-select block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"
                    >
                        <option value="">None (Top-level milestone)</option>
                        <?php if (!empty($epics)): ?>
                            <?php foreach ($epics as $epic): ?>
                                <?php if ($epic->id != $milestone->id): // Prevent self-reference ?>
                                    <option 
                                        value="<?= htmlspecialchars((string)$epic->id) ?>"
                                        <?= ($epic->id == $milestone->epic_id) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($epic->title) ?>
                                    </option>
                                <?php endif; ?>
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
                            value="<?= htmlspecialchars($milestone->title) ?>"
                            class="form-input block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"
                            required
                        >
                    </div>
                    
                    <div>
                        <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Status <span class="text-red-600">*</span>
                        </label>
                        <select 
                            id="status_id" 
                            name="status_id" 
                            class="form-select block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"
                            required
                        >
                            <?php foreach ($statuses as $status): ?>
                                <option 
                                    value="<?= htmlspecialchars($status->id) ?>"
                                    <?= ($status->id == $milestone->status_id) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($status->name) ?>
                                </option>
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
                        class="form-textarea block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"
                    ><?= htmlspecialchars($milestone->description ?? '') ?></textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Optional: Provide a detailed description of the milestone
                    </p>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Start Date
                        </label>
                        <input 
                            type="date" 
                            id="start_date" 
                            name="start_date" 
                            value="<?= htmlspecialchars($milestone->start_date ?? '') ?>"
                            class="form-input block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"
                        >
                    </div>
                    
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Due Date
                        </label>
                        <input 
                            type="date" 
                            id="due_date" 
                            name="due_date" 
                            value="<?= htmlspecialchars($milestone->due_date ?? '') ?>"
                            class="form-input block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"
                        >
                    </div>
                    
                    <div>
                        <label for="complete_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Completion Date
                        </label>
                        <input 
                            type="date" 
                            id="complete_date" 
                            name="complete_date" 
                            value="<?= htmlspecialchars($milestone->complete_date ?? '') ?>"
                            <?= ($milestone->status_id != 3) ? 'disabled' : '' ?>
                            class="form-input block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 px-3"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Only editable when status is Completed
                        </p>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a 
                        href="/milestones/view/<?= htmlspecialchars($milestone->id) ?>" 
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Update Milestone
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
            const statusSelect = document.getElementById('status_id');
            const completeDateInput = document.getElementById('complete_date');
            
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
                    const currentEpicId = '<?= $milestone->epic_id ?>'; // Get current epic ID
                    const milestoneId = '<?= $milestone->id ?>'; // Current milestone ID
                    
                    epicSelect.innerHTML = '<option value="">None (Top-level milestone)</option>';
                    epics.forEach(epic => {
                        // Skip adding itself as a parent option to prevent circular references
                        if (epic.id != milestoneId) {
                            const option = document.createElement('option');
                            option.value = epic.id;
                            option.textContent = epic.title;
                            if (epic.id == currentEpicId) {
                                option.selected = true;
                            }
                            epicSelect.appendChild(option);
                        }
                    });
                } catch (error) {
                    console.error('Error loading epics:', error);
                }
            }
            
            // Toggle completion date field based on status
            function toggleCompletionDate() {
                const statusId = statusSelect.value;
                if (statusId == 3) { // Completed status
                    completeDateInput.disabled = false;
                    if (!completeDateInput.value) {
                        completeDateInput.value = new Date().toISOString().split('T')[0]; // Set to current date
                    }
                } else {
                    completeDateInput.disabled = true;
                }
            }
            
            // Add event listeners
            milestoneTypeRadios.forEach(radio => {
                radio.addEventListener('change', toggleEpicSelection);
            });
            
            projectSelect.addEventListener('change', loadEpics);
            statusSelect.addEventListener('change', toggleCompletionDate);
            
            // Initial state
            toggleEpicSelection();
            toggleCompletionDate();
        });
    </script>
</body>
</html>