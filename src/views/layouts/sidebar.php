<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Define menu items in logical groups
$mainItems = [
    ['label' => 'Dashboard', 'path' => '/dashboard', 'icon' => '🏠']
];

$projectItems = [
    ['label' => 'Projects', 'path' => '/projects', 'icon' => '📋'],
    ['label' => 'Sprints', 'path' => '/sprints', 'icon' => '🏃'],
    ['label' => 'Milestones', 'path' => '/milestones', 'icon' => '📍']
];

$taskItems = [
    ['label' => 'Backlog', 'path' => '/tasks', 'icon' => '📝'],
    ['label' => 'My Tasks', 'path' => '/tasks/assigned/'.$_SESSION['user']['id'], 'icon' => '📌'],
    ['label' => 'Time Tracking', 'path' => '/time-tracking', 'icon' => '🕒']
];

$adminItems = [
    ['label' => 'Companies', 'path' => '/companies', 'icon' => '🏢'],
    ['label' => 'Users', 'path' => '/users', 'icon' => '👥'],
    ['label' => 'Roles', 'path' => '/roles', 'icon' => '🔑']
];

// Get the current path
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<aside class="bg-gray-800 text-white w-64 min-h-screen shadow-lg fixed top-0 left-0 z-20 transition-transform duration-300 ease-in-out transform -translate-x-full overflow-y-auto" id="sidebar" aria-label="Sidebar">
    <div class="p-4">
        <!-- Sidebar Header with Logo -->
        <div class="flex flex-row justify-between">
            <h2 class="text-lg font-bold mb-4">Menu</h2>
            <!-- Close Button -->
            <button id="sidebar-close" class="block bg-none w-4 h-4 text-white hover:text-gray-300 focus:outline-none" aria-label="Close Sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 mt-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Main Navigation -->
        <nav class="space-y-6">
            <!-- Main Items -->
            <div>
                <ul class="space-y-1">
                    <?php
                    foreach ($mainItems as $item) {
                        $isActive = $currentPath === $item['path'] ? 'bg-indigo-600' : '';
                        echo '<li>';
                        echo '<a href="' . htmlspecialchars($item['path']) . '" class="flex items-center p-2 rounded-md hover:bg-gray-700 transition duration-150 ' . $isActive . '" aria-current="' . ($isActive ? 'page' : 'false') . '">';
                        echo '<span class="mr-3">' . $item['icon'] . '</span>';
                        echo '<span>' . htmlspecialchars($item['label']) . '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- Project Management Section -->
            <div>
                <p class="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Project Management</p>
                <div class="mt-2 border-t border-gray-700"></div>
                <ul class="mt-2 space-y-1">
                    <?php
                    foreach ($projectItems as $item) {
                        $isActive = str_starts_with($currentPath, $item['path']) ? 'bg-indigo-600' : '';
                        echo '<li>';
                        echo '<a href="' . htmlspecialchars($item['path']) . '" class="flex items-center p-2 rounded-md hover:bg-gray-700 transition duration-150 ' . $isActive . '" aria-current="' . ($isActive ? 'page' : 'false') . '">';
                        echo '<span class="mr-3">' . $item['icon'] . '</span>';
                        echo '<span>' . htmlspecialchars($item['label']) . '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- Task Management Section -->
            <div>
                <p class="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Task Management</p>
                <div class="mt-2 border-t border-gray-700"></div>
                <ul class="mt-2 space-y-1">
                    <?php
                    foreach ($taskItems as $item) {
                        $isActive = str_starts_with($currentPath, $item['path']) ? 'bg-indigo-600' : '';
                        echo '<li>';
                        echo '<a href="' . htmlspecialchars($item['path']) . '" class="flex items-center p-2 rounded-md hover:bg-gray-700 transition duration-150 ' . $isActive . '" aria-current="' . ($isActive ? 'page' : 'false') . '">';
                        echo '<span class="mr-3">' . $item['icon'] . '</span>';
                        echo '<span>' . htmlspecialchars($item['label']) . '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- Administration Section -->
            <div>
                <p class="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administration</p>
                <div class="mt-2 border-t border-gray-700"></div>
                <ul class="mt-2 space-y-1">
                    <?php
                    foreach ($adminItems as $item) {
                        $isActive = str_starts_with($currentPath, $item['path']) ? 'bg-indigo-600' : '';
                        echo '<li>';
                        echo '<a href="' . htmlspecialchars($item['path']) . '" class="flex items-center p-2 rounded-md hover:bg-gray-700 transition duration-150 ' . $isActive . '" aria-current="' . ($isActive ? 'page' : 'false') . '">';
                        echo '<span class="mr-3">' . $item['icon'] . '</span>';
                        echo '<span>' . htmlspecialchars($item['label']) . '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    ?>
                </ul>
            </div>
        </nav>
    </div>

    <!-- User Profile Section -->
    <div class="border-t border-gray-700 p-4 mt-6">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                <?php
                $userInitials = '';
                if (isset($_SESSION['user']['profile'])) {
                    $firstName = $_SESSION['user']['profile']['first_name'] ?? '';
                    $lastName = $_SESSION['user']['profile']['last_name'] ?? '';
                    $userInitials = substr($firstName, 0, 1) . substr($lastName, 0, 1);
                }
                echo htmlspecialchars($userInitials);
                ?>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-white">
                    <?php
                    if (isset($_SESSION['user']['profile'])) {
                        echo htmlspecialchars($_SESSION['user']['profile']['first_name'] . ' ' . $_SESSION['user']['profile']['last_name']);
                    } else {
                        echo 'Guest User';
                    }
                    ?>
                </p>
                <p class="text-xs text-gray-400">
                    <?php
                    if (isset($_SESSION['user']['roles']) && !empty($_SESSION['user']['roles'])) {
                        echo htmlspecialchars($_SESSION['user']['roles'][0]);
                    } else {
                        echo 'No role assigned';
                    }
                    ?>
                </p>
            </div>
            <a href="/logout" class="ml-auto p-1 rounded-full hover:bg-gray-700" title="Logout">
                <svg class="w-5 h-5 text-gray-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </a>
        </div>
    </div>
</aside>