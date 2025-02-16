<header class="bg-indigo-600 text-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex flex-row items-center">
            <button id="sidebar-toggle" class="block bg-indigo-600 text-white p-2 rounded z-10" aria-label="Toggle Sidebar">
                ☰
            </button>
            <h1 class="text-lg font-bold ml-2">Slimbooks</h1>
        </div>
        <nav class="space-x-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/logout" class="hover:text-indigo-200">Logout</a>
            <?php else: ?>
                <a href="/login" class="hover:text-indigo-200">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php
// Display errors or success messages
if (isset($_SESSION['error'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']);
}
?>