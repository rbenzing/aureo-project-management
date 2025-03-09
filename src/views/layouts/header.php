<?php
//file: Views/Layouts/header.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
?>
<header class="bg-indigo-600 text-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex flex-row items-center">
            <button id="sidebar-toggle" class="block bg-indigo-600 text-white p-2 rounded z-10" aria-label="Toggle Sidebar">
            ☰
            </button>
            <h1 class="text-lg font-bold ml-2">Slimbooks</h1>
        </div>
        <?php if (!empty($_SESSION['active_timer'])): ?>
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">Active Timer</h2>
            <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-lg p-4 timer-pulse">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="font-medium text-yellow-700 dark:text-yellow-300">
                        <?= htmlspecialchars($_SESSION['active_timer']['task']->title) ?>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <div class="text-2xl font-bold tracking-wide text-yellow-600 dark:text-yellow-400" id="active-timer">
                        <?= gmdate("H:i:s", $_SESSION['active_timer']['duration']) ?>
                    </div>
                    <form action="/tasks/stop-timer/<?= $_SESSION['active_timer']['task_id'] ?>" method="POST" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                            </svg>
                            Stop
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <nav class="space-x-4 flex flex-row items-center">
            <?php if (isset($_SESSION['user']['profile']['id'])): ?>
                <a href="#" class="hover:text-indigo-200">📄 Activity</a>

                <a href="/logout" class="hover:text-indigo-200">🔓 Logout</a>

                <div class="h-11 w-11 rounded-full bg-indigo-100 dark:bg-indigo-900 flex-shrink-0 flex items-center justify-center">
                    <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                        <?= htmlspecialchars(substr($_SESSION['user']['profile']['first_name'], 0, 1) . substr($_SESSION['user']['profile']['last_name'], 0, 1)) ?>
                    </span>
                </div>
            <?php else: ?>
                <a href="/login" class="hover:text-indigo-200">🔐 Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>