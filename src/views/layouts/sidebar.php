<aside class="bg-gray-800 text-white w-64 min-h-screen fixed top-0 left-0 z-10 transition-transform duration-300 ease-in-out transform -translate-x-full md:translate-x-0" id="sidebar">
    <div class="p-4">
        <h2 class="text-lg font-bold mb-4">Menu</h2>
        <ul class="space-y-2">
            <li><a href="/dashboard.php" class="block hover:bg-gray-700 p-2 rounded">Dashboard</a></li>
            <li><a href="/projects/index.php" class="block hover:bg-gray-700 p-2 rounded">Projects</a></li>
            <li><a href="/tasks/index.php" class="block hover:bg-gray-700 p-2 rounded">Tasks</a></li>
            <li><a href="/users/index.php" class="block hover:bg-gray-700 p-2 rounded">Users</a></li>
        </ul>
    </div>
</aside>

<!-- Sidebar Toggle Button -->
<button id="sidebar-toggle" class="md:hidden fixed top-4 left-4 bg-indigo-600 text-white p-2 rounded">
    ☰
</button>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('sidebar-toggle');

    toggleButton.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
    });
</script>