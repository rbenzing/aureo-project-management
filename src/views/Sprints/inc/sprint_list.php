<?php
// file: Views/Sprints/inc/sprint_list.php
// Sprint listing table component
?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sprint</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Duration</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tasks</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Velocity</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700" id="sprints-list">
            <?php if (!empty($sprints)): ?>
                <?php foreach ($sprints as $sprint): 
                    // Safe access to properties with fallbacks
                    $totalTasks = isset($sprint->total_tasks) ? $sprint->total_tasks : 0;
                    $completedTasks = isset($sprint->completed_tasks) ? $sprint->completed_tasks : 0;
                    $taskPercentage = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                    
                    // Calculate days from start to end
                    $sprintDays = 0;
                    $daysLeft = 0;
                    
                    if (isset($sprint->start_date) && isset($sprint->end_date)) {
                        $startDate = new DateTime($sprint->start_date);
                        $endDate = new DateTime($sprint->end_date);
                        $interval = $startDate->diff($endDate);
                        $sprintDays = $interval->days + 1; // +1 to include both start and end dates
                        
                        // Check if sprint is ongoing
                        $now = new DateTime();
                        if (isset($sprint->status_id) && $sprint->status_id == 2 && $now >= $startDate && $now <= $endDate) {
                            $daysLeft = $now->diff($endDate)->days;
                        }
                    }
                ?>
                    <tr class="sprint-row hover:bg-gray-50 dark:hover:bg-gray-700" 
                        data-status="<?= $sprint->status_id ?? 0 ?>"
                        data-name="<?= htmlspecialchars($sprint->name ?? '') ?>">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                    <a href="/sprints/view/<?= $sprint->id ?? 0 ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        <?= htmlspecialchars($sprint->name ?? 'Sprint') ?>
                                    </a>
                                </div>
                                <?php if (!empty($sprint->description)): ?>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                        <?= htmlspecialchars(substr($sprint->description, 0, 60)) . (strlen($sprint->description) > 60 ? '...' : '') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (isset($sprint->start_date) && isset($sprint->end_date)): ?>
                                <div class="text-sm text-gray-900 dark:text-gray-200">
                                    <?= date('M j', strtotime($sprint->start_date)) ?> - <?= date('M j, Y', strtotime($sprint->end_date)) ?>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <?= $sprintDays ?> days
                                    <?php if ($daysLeft > 0): ?>
                                        <span class='ml-2 font-medium text-blue-600 dark:text-blue-400'><?= $daysLeft ?> days left</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Date not set</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getSprintStatusClass($sprint->status_id ?? 0) ?>">
                                <?= getSprintStatusLabel($sprint->status_id ?? 0) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-full max-w-[120px]">
                                    <div class="text-xs mb-1 flex justify-between">
                                        <span class="font-medium text-gray-900 dark:text-gray-200"><?= $completedTasks ?>/<?= $totalTasks ?></span>
                                        <span class="text-gray-500 dark:text-gray-400"><?= round($taskPercentage) ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: <?= $taskPercentage ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if (isset($sprint->status_id) && $sprint->status_id == 4): // Completed ?>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">
                                    <?= isset($sprint->velocity_percentage) ? round($sprint->velocity_percentage) . '%' : 'N/A' ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-500 dark:text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end space-x-3">
                                <a 
                                    href="/sprints/view/<?= $sprint->id ?? 0 ?>" 
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                >
                                    View
                                </a>
                                <a 
                                    href="/sprints/edit/<?= $sprint->id ?? 0 ?>" 
                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                >
                                    Edit
                                </a>
                                <form 
                                    action="/sprints/delete/<?= $sprint->id ?? 0 ?>" 
                                    method="POST" 
                                    onsubmit="return confirm('Are you sure you want to delete this sprint?');"
                                    class="inline"
                                >
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button 
                                        type="submit" 
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                        No sprints found for this project. <a href="/sprints/create?project_id=<?= $project->id ?? 0 ?>" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Create your first sprint</a>.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>