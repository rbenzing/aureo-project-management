<?php
// Ensure this view is not directly accessible via the web
if (!defined('BASE_PATH')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

use App\Utils\Breadcrumb;

// Get the current route
$uri = $_SERVER['REQUEST_URI'];
$route = parse_url($uri, PHP_URL_PATH);
$route = ltrim($route, '/');

// Get parameters (e.g., ID)
$params = [];
foreach ($data ?? [] as $key => $value) {
    if (is_string($key) && !is_numeric($key)) {
        $params[$key] = $value;
    }
}

// Render the breadcrumbs
echo Breadcrumb::render($route, $params);
?>