<aside class="bg-gray-800 text-white w-64 min-h-screen shadow-lg fixed top-0 left-0 z-20 transition-transform duration-300 ease-in-out transform -translate-x-full" id="sidebar" aria-label="Sidebar">
    <div class="p-4">
        <div class="flex flex-row justify-between">
            <h2 class="text-lg font-bold mb-4">Menu</h2>
            <!-- Close Button -->
            <button id="sidebar-close" class="block bg-none w-4 h-4 text-white hover:text-gray-300 focus:outline-none" aria-label="Close Sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 mt-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <ul class="space-y-2">
            <?php
            // Define menu items as an array for scalability
            $menuItems = [
                ['label' => 'Dashboard', 'path' => '/dashboard', 'icon' => '🏠'],
                ['label' => 'Projects', 'path' => '/projects', 'icon' => '📋'],
                ['label' => 'Users', 'path' => '/users', 'icon' => '👥'],
                ['label' => 'Roles', 'path' => '/roles', 'icon' => '🔑'],
                ['label' => 'Companies', 'path' => '/companies', 'icon' => '🏢'],
            ];

            // Get the current path
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            foreach ($menuItems as $item) {
                $isActive = $currentPath === $item['path'] ? 'bg-gray-700' : '';
                echo '<li>';
                echo '<a href="' . htmlspecialchars($item['path']) . '" class="block hover:bg-gray-700 rounded flex items-center gap-2 ' . $isActive . '" aria-current="' . ($isActive ? 'page' : 'false') . '">';
                echo '<span class="p-2">' . $item['icon'] . '</span>';
                echo '<span>' . htmlspecialchars($item['label']) . '</span>';
                echo '</a>';
                echo '</li>';
            }
            ?>
        </ul>
    </div>
</aside>
<script>
    // Sidebar toggle functionality
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('sidebar-toggle');
    const closeButton = document.getElementById('sidebar-close');

    // Open sidebar when clicking the toggle button
    toggleButton.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (event) => {
        if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
            sidebar.classList.add('-translate-x-full');
        }
    });

    // Close sidebar when clicking the close button
    closeButton.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
    });

    // Prevent sidebar from closing when clicking inside it
    sidebar.addEventListener('click', (event) => {
        event.stopPropagation();
    });
</script>