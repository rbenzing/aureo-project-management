<?php
/**
 * Batch Refactoring Script
 * Replaces header redirects with BaseController methods
 */

$controllersPath = __DIR__ . '/src/Controllers';
$files = glob($controllersPath . '/*.php');

$stats = [
    'files_processed' => 0,
    'redirects_replaced' => 0,
    'includes_replaced' => 0,
    'permissions_replaced' => 0,
];

foreach ($files as $file) {
    $filename = basename($file);

    // Skip BaseController itself
    if ($filename === 'BaseController.php') {
        continue;
    }

    echo "Processing: $filename\n";

    $content = file_get_contents($file);
    $originalContent = $content;

    // Pattern 1: $_SESSION['error'] = '...'; header('Location: ...'); exit;
    $pattern1 = "/\\\$_SESSION\['error'\]\s*=\s*([^;]+);\s*\n?\s*header\('Location:\s*([^']+)'\);\s*\n?\s*exit;/";
    $replacement1 = "\$this->redirectWithError($2, $1);";
    $content = preg_replace($pattern1, $replacement1, $content, -1, $count1);
    $stats['redirects_replaced'] += $count1;

    // Pattern 2: $_SESSION['success'] = '...'; header('Location: ...'); exit;
    $pattern2 = "/\\\$_SESSION\['success'\]\s*=\s*([^;]+);\s*\n?\s*header\('Location:\s*([^']+)'\);\s*\n?\s*exit;/";
    $replacement2 = "\$this->redirectWithSuccess($2, $1);";
    $content = preg_replace($pattern2, $replacement2, $content, -1, $count2);
    $stats['redirects_replaced'] += $count2;

    // Pattern 3: $_SESSION['info'] = '...'; header('Location: ...'); exit;
    $pattern3 = "/\\\$_SESSION\['info'\]\s*=\s*([^;]+);\s*\n?\s*header\('Location:\s*([^']+)'\);\s*\n?\s*exit;/";
    $replacement3 = "\$this->redirectWithInfo($2, $1);";
    $content = preg_replace($pattern3, $replacement3, $content, -1, $count3);
    $stats['redirects_replaced'] += $count3;

    // Pattern 4: header('Location: ...'); exit; (without session)
    $pattern4 = "/header\('Location:\s*([^']+)'\);\s*\n?\s*exit;/";
    $replacement4 = "\$this->redirect($1);";
    $content = preg_replace($pattern4, $replacement4, $content, -1, $count4);
    $stats['redirects_replaced'] += $count4;

    // Pattern 5: $this->authMiddleware->hasPermission('...')
    $pattern5 = "/\\\$this->authMiddleware->hasPermission\('([^']+)'\)/";
    $replacement5 = "\$this->requirePermission('$1')";
    $content = preg_replace($pattern5, $replacement5, $content, -1, $count5);
    $stats['permissions_replaced'] += $count5;

    // Save if changes were made
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $stats['files_processed']++;
        echo "  ✓ Updated ($count1 error redirects, $count2 success redirects, $count3 info redirects, $count4 plain redirects, $count5 permissions)\n";
    } else {
        echo "  - No changes needed\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "REFACTORING COMPLETE\n";
echo str_repeat('=', 60) . "\n";
echo "Files processed: {$stats['files_processed']}\n";
echo "Redirects replaced: {$stats['redirects_replaced']}\n";
echo "Permissions replaced: {$stats['permissions_replaced']}\n";
echo "\n";
