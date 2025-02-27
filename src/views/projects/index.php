<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Core\Config;

// Determine which view to show based on the tab parameter
$activeTab = $_GET['view'] ?? 'table';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - <?= htmlspecialchars(Config::get('company_name', 'SlimBooks')) ?></title>
    <link href="/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-white dark:text-gray-100 min-h-screen flex flex-col">

    <?php include BASE_PATH . '/../src/Views/Layouts/header.php'; ?>

    <?php include BASE_PATH . '/../src/Views/Layouts/sidebar.php'; ?>

    <main class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl font-medium">Client Projects</h1>
                <button class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </button>
            </div>
            <div class="flex items-center space-x-4">
                <?php
                    switch($activeTab) {
                        case 'table':
                            include BASE_PATH . '/../src/Views/Projects/inc/table_header.php';
                            break;
                        default:
                    }
                ?>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center space-x-6 border-b border-gray-200 mb-6">
            <a href="/projects?view=table" class="px-4 py-2 <?= $activeTab === 'table' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-white' ?>">Table</a>
            <a href="/projects?view=timeline" class="px-4 py-2 <?= $activeTab === 'timeline' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-white' ?>">Timeline</a>
            <a href="/projects?view=charts" class="px-4 py-2 <?= $activeTab === 'charts' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-white' ?>">Charts</a>
            <a href="/projects?view=pivot" class="px-4 py-2 <?= $activeTab === 'pivot' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-white' ?>">Pivot Board</a>
            <a href="/projects?view=gantt" class="px-4 py-2 <?= $activeTab === 'gantt' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-white' ?>">Gantt</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['success'] ?></span>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['error'] ?></span>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php
        // Include the appropriate view based on the active tab
        switch ($activeTab) {
            case 'table':
                include BASE_PATH . '/../src/Views/Projects/inc/table.php';
                break;
            case 'timeline':
                include BASE_PATH . '/../src/Views/Projects/inc/timeline.php';
                break;
            case 'charts':
                include BASE_PATH . '/../src/Views/Projects/inc/charts.php';
                break;
            case 'pivot':
                include BASE_PATH . '/../src/Views/Projects/inc/pivot.php';
                break;
            case 'gantt':
                include BASE_PATH . '/../src/Views/Projects/inc/gantt.php';
                break;
            default:
                include BASE_PATH . '/../src/Views/Projects/inc/table.php';
        }
        ?>
    </main>

    <!-- Footer -->
    <?php include BASE_PATH . '/../src/Views/Layouts/footer.php'; ?>
    
    <script>
        // Simple dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                const button = dropdown.querySelector('button');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                button.addEventListener('click', function() {
                    menu.classList.toggle('hidden');
                });
                
                // Close when clicking outside
                document.addEventListener('click', function(event) {
                    if (!dropdown.contains(event.target)) {
                        menu.classList.add('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>