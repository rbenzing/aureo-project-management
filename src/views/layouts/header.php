<header class="bg-indigo-600 text-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <h1 class="text-lg font-bold">Project Management System</h1>
        <nav class="space-x-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/dashboard.php" class="hover:text-indigo-200">Dashboard</a>
                <a href="/src/Controllers/UserController.php?action=logout" class="hover:text-indigo-200">Logout</a>
            <?php else: ?>
                <a href="/" class="hover:text-indigo-200">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>