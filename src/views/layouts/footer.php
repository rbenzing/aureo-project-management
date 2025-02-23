<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use \App\Core\Config;
?>

<footer class="bg-gray-800 text-white py-4 mt-auto">
    <div class="container mx-auto px-4 text-center text-sm">
        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(Config::$app['company_name']); ?>, All rights reserved.
    </div>
</footer>