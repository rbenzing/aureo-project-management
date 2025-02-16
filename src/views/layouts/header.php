<header class="bg-indigo-600 text-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <h1 class="text-lg font-bold">Project Management System</h1>
        <nav class="space-x-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/dashboard.php" class="hover:text-indigo-200">Dashboard</a>
                <a href="/logout.php" class="hover:text-indigo-200">Logout</a>
            <?php else: ?>
                <a href="/" class="hover:text-indigo-200">Login</a>
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