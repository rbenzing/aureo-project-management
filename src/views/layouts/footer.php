<?php
//file: Views/Layouts/footer.php
declare(strict_types=1);

// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use \App\Core\Config;
?>

<footer class="bg-gray-800 text-white py-4 mt-auto">
    <div class="container mx-auto px-4 text-center text-sm">
        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(Config::get('company_name')); ?>, All rights reserved.
    </div>
</footer>

<script type="text/javascript" src="/assets/js/scripts.js"></script>