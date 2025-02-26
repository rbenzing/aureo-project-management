<?php
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
            <?php if (isset($_SESSION['user']['permissions']) && in_array('create_projects', $_SESSION['user']['permissions'])): ?>
                <a href="/projects/create" class="inline-block px-3 py-2 bg-indigo-100 dark:bg-indigo-900 text-black dark:text-white text-sm rounded-md hover:bg-indigo-700 ml-4">+ New Project</a>
            <?php endif; ?>
        </div>
        <nav class="space-x-4 flex flex-row items-center">
            <?php if (isset($_SESSION['user']['profile']['id'])): ?>
                <a href="#" class="hover:text-indigo-200">📄 Activity</a>

                <a href="/logout" class="hover:text-indigo-200">🔓 Logout</a>

                <div class="h-14 w-14 rounded-full bg-indigo-100 dark:bg-indigo-900 flex-shrink-0 flex items-center justify-center">
                    <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                        <?= htmlspecialchars(substr($_SESSION['user']['profile']['first_name'], 0, 1) . substr($_SESSION['user']['profile']['last_name'], 0, 1)) ?>
                    </span>
                </div>
            <?php else: ?>
                <a href="/login" class="hover:text-indigo-200">🔐 Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>