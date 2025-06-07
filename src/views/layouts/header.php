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
        <div id="header-timer-indicator" class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg px-3 py-1 cursor-pointer hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors" onclick="if(typeof showFloatingTimer === 'function') showFloatingTimer();" title="Click to show timer">
            <div class="flex items-center">
                <svg class="w-3 h-3 text-indigo-500 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs font-medium text-indigo-700 dark:text-indigo-300">
                    Timer Active - Click to Show
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