<?php
/**
 * Batch Refactoring Script - View Superglobals
 * Replaces $_SESSION access with passed variables
 */

$viewsPath = __DIR__ . '/src/Views';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath),
    RecursiveIteratorIterator::SELF_FIRST
);

$stats = [
    'files_processed' => 0,
    'session_replaced' => 0,
    'get_replaced' => 0,
];

foreach ($files as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') {
        continue;
    }

    $filePath = $file->getPathname();
    $relativePath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $filePath);

    echo "Processing: $relativePath\n";

    $content = file_get_contents($filePath);
    $originalContent = $content;
    $changes = 0;

    // Replace $_SESSION['error'] access
    $patterns = [
        // isset($_SESSION['error'])
        "/isset\(\\\$_SESSION\['error'\]\)/i" => "!empty(\$error)",

        // $_SESSION['error']
        "/\\\$_SESSION\['error'\]/i" => "\$error",

        // unset($_SESSION['error'])
        "/unset\(\\\$_SESSION\['error'\]\);?/i" => "// Flash message auto-cleared",

        // isset($_SESSION['success'])
        "/isset\(\\\$_SESSION\['success'\]\)/i" => "!empty(\$success)",

        // $_SESSION['success']
        "/\\\$_SESSION\['success'\]/i" => "\$success",

        // unset($_SESSION['success'])
        "/unset\(\\\$_SESSION\['success'\]\);?/i" => "// Flash message auto-cleared",

        // isset($_SESSION['info'])
        "/isset\(\\\$_SESSION\['info'\]\)/i" => "!empty(\$info)",

        // $_SESSION['info']
        "/\\\$_SESSION\['info'\]/i" => "\$info",

        // unset($_SESSION['info'])
        "/unset\(\\\$_SESSION\['info'\]\);?/i" => "// Flash message auto-cleared",

        // $_SESSION['csrf_token']
        "/\\\$_SESSION\['csrf_token'\]/i" => "\$csrfToken",

        // $_SESSION['user']
        "/\\\$_SESSION\['user'\]/i" => "\$currentUser",
    ];

    foreach ($patterns as $pattern => $replacement) {
        $newContent = preg_replace($pattern, $replacement, $content, -1, $count);
        if ($count > 0) {
            $content = $newContent;
            $changes += $count;
            $stats['session_replaced'] += $count;
        }
    }

    // Save if changes were made
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $stats['files_processed']++;
        echo "  ✓ Updated ($changes replacements)\n";
    } else {
        echo "  - No changes needed\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "VIEW REFACTORING COMPLETE\n";
echo str_repeat('=', 60) . "\n";
echo "Files processed: {$stats['files_processed']}\n";
echo "SESSION replacements: {$stats['session_replaced']}\n";
echo "\n";
