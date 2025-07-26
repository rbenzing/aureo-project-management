<?php
//file: Views/TimeTracking/create.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;

// Include helper functions
include BASE_PATH . '/../src/Views/Layouts/view_helpers.php';
include BASE_PATH . '/../src/Views/Layouts/form_components.php';
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Time Entry - <?= htmlspecialchars(Config::get('company_name', 'Aureo')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <!-- Header and Sidebar -->
    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>
    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="w-full px-4 sm:px-6 md:px-8 lg:px-10 xl:px-12 py-6 flex-grow">
        <?php include BASE_PATH . '/../src/Views/Layouts/notifications.php'; ?>
        <?php include BASE_PATH . '/../src/Views/Layouts/breadcrumb.php'; ?>

        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-medium dark:text-white">Add Time Entry</h1>
            <a href="/time-tracking" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm">
                Back to Time Tracking
            </a>
        </div>

        <!-- Create Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">New Time Entry</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Record time spent on a task.</p>
            </div>

            <form action="/time-tracking/store" method="POST" class="p-6">
                <?= renderCSRFToken() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Task Selection -->
                    <div class="md:col-span-2">
                        <?= renderSelect([
                            'name' => 'task_id',
                            'label' => 'Task',
                            'value' => $formData['task_id'] ?? ($_GET['task_id'] ?? ''),
                            'options' => array_column($tasks ?? [], 'title', 'id'),
                            'empty_option' => 'Select a task...',
                            'required' => true,
                            'error' => $errors['task_id'] ?? '',
                            'help_text' => 'Choose the task you worked on.'
                        ]) ?>
                    </div>

                    <!-- Start Date and Time -->
                    <div>
                        <?= renderTextInput([
                            'name' => 'start_date',
                            'type' => 'date',
                            'label' => 'Start Date',
                            'value' => $formData['start_date'] ?? date('Y-m-d'),
                            'required' => true,
                            'error' => $errors['start_date'] ?? ''
                        ]) ?>
                    </div>

                    <div>
                        <?= renderTextInput([
                            'name' => 'start_time',
                            'type' => 'time',
                            'label' => 'Start Time',
                            'value' => $formData['start_time'] ?? '',
                            'required' => true,
                            'error' => $errors['start_time'] ?? ''
                        ]) ?>
                    </div>

                    <!-- End Date and Time -->
                    <div>
                        <?= renderTextInput([
                            'name' => 'end_date',
                            'type' => 'date',
                            'label' => 'End Date',
                            'value' => $formData['end_date'] ?? date('Y-m-d'),
                            'required' => true,
                            'error' => $errors['end_date'] ?? ''
                        ]) ?>
                    </div>

                    <div>
                        <?= renderTextInput([
                            'name' => 'end_time',
                            'type' => 'time',
                            'label' => 'End Time',
                            'value' => $formData['end_time'] ?? '',
                            'required' => true,
                            'error' => $errors['end_time'] ?? ''
                        ]) ?>
                    </div>

                    <!-- Duration (calculated automatically) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Duration
                        </label>
                        <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md">
                            <span id="calculated-duration" class="text-sm text-gray-600 dark:text-gray-400">
                                0h 0m
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Duration is automatically calculated based on start and end times.
                        </p>
                    </div>

                    <!-- Alternative: Manual Duration Entry -->
                    <div class="md:col-span-2">
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Or enter duration manually:
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <?= renderTextInput([
                                    'name' => 'manual_hours',
                                    'type' => 'number',
                                    'label' => 'Hours',
                                    'value' => $formData['manual_hours'] ?? '',
                                    'placeholder' => '0',
                                    'class' => 'text-center',
                                    'help_text' => 'Leave blank to use start/end times'
                                ]) ?>

                                <?= renderTextInput([
                                    'name' => 'manual_minutes',
                                    'type' => 'number',
                                    'label' => 'Minutes',
                                    'value' => $formData['manual_minutes'] ?? '',
                                    'placeholder' => '0',
                                    'class' => 'text-center',
                                    'help_text' => 'Leave blank to use start/end times'
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <?= renderTextarea([
                            'name' => 'notes',
                            'label' => 'Notes',
                            'value' => $formData['notes'] ?? '',
                            'placeholder' => 'Add any notes about this time entry...',
                            'rows' => 3,
                            'error' => $errors['notes'] ?? ''
                        ]) ?>
                    </div>

                    <!-- Billable -->
                    <div class="md:col-span-2">
                        <?= renderCheckbox([
                            'name' => 'is_billable',
                            'label' => 'This time is billable',
                            'checked' => !empty($formData['is_billable']),
                            'help_text' => 'Check this if the time should be billed to the client.'
                        ]) ?>
                    </div>
                </div>

                <!-- Form Buttons -->
                <?= renderFormButtons([
                    'submit_text' => 'Add Time Entry',
                    'cancel_url' => '/time-tracking',
                    'additional_buttons' => [
                        [
                            'type' => 'button',
                            'text' => 'Use Current Time',
                            'class' => 'bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md',
                            'onclick' => 'setCurrentTime()'
                        ]
                    ]
                ]) ?>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>

    <script>
        // Calculate duration when start/end times change
        function calculateDuration() {
            const startDate = document.querySelector('input[name="start_date"]').value;
            const startTime = document.querySelector('input[name="start_time"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            const endTime = document.querySelector('input[name="end_time"]').value;
            
            if (startDate && startTime && endDate && endTime) {
                const start = new Date(startDate + 'T' + startTime);
                const end = new Date(endDate + 'T' + endTime);
                
                if (end > start) {
                    const diffMs = end - start;
                    const diffSeconds = Math.floor(diffMs / 1000);
                    const hours = Math.floor(diffSeconds / 3600);
                    const minutes = Math.floor((diffSeconds % 3600) / 60);
                    
                    document.getElementById('calculated-duration').textContent = hours + 'h ' + minutes + 'm';
                } else {
                    document.getElementById('calculated-duration').textContent = 'Invalid time range';
                }
            }
        }

        // Set current time as end time
        function setCurrentTime() {
            const now = new Date();
            const currentDate = now.toISOString().split('T')[0];
            const currentTime = now.toTimeString().slice(0, 5);
            
            document.querySelector('input[name="end_date"]').value = currentDate;
            document.querySelector('input[name="end_time"]').value = currentTime;
            
            calculateDuration();
        }

        // Clear manual duration when start/end times are used
        function clearManualDuration() {
            document.querySelector('input[name="manual_hours"]').value = '';
            document.querySelector('input[name="manual_minutes"]').value = '';
        }

        // Clear start/end times when manual duration is used
        function clearStartEndTimes() {
            // Don't clear completely, just indicate manual mode
            calculateDuration();
        }

        // Add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const timeInputs = document.querySelectorAll('input[type="date"], input[type="time"]');
            timeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    calculateDuration();
                    clearManualDuration();
                });
            });

            const manualInputs = document.querySelectorAll('input[name="manual_hours"], input[name="manual_minutes"]');
            manualInputs.forEach(input => {
                input.addEventListener('input', clearStartEndTimes);
            });

            // Set default start time to current time if not set
            const startTimeInput = document.querySelector('input[name="start_time"]');
            if (!startTimeInput.value) {
                const now = new Date();
                startTimeInput.value = now.toTimeString().slice(0, 5);
            }
        });
    </script>
</body>
</html>
