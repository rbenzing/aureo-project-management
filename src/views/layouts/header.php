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
        </div>
        <nav class="space-x-4 flex flex-row items-center">
            <?php if (isset($_SESSION['user']['profile']['id'])): ?>
                <a href="#" class="hover:text-indigo-200">📄 Activity</a>

                <a href="/logout" class="hover:text-indigo-200">🔓 Logout</a>

                <div class="max-h-24 rounded-3xl bg-purple-600 flex items-center space-x-2 justify-center align-center p-3">
                <?= ucfirst($_SESSION['user']['profile']['first_name'])[0] . ucfirst($_SESSION['user']['profile']['last_name'])[0] ?>
                </div>
            <?php else: ?>
                <a href="/login" class="hover:text-indigo-200">🔐 Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>