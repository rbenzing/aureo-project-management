<?php
/**
 * Batch Refactoring Script - Include Statements
 * Replaces include statements with $this->render() calls
 */

$controllersPath = __DIR__ . '/src/Controllers';
$files = glob($controllersPath . '/*.php');

$stats = [
    'files_processed' => 0,
    'includes_replaced' => 0,
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
    $changes = 0;

    // Pattern: include BASE_PATH . '/../Views/Path/To/View.php';
    // or: include BASE_PATH . '/../src/Views/Path/To/View.php';
    if (preg_match_all("/include\s+BASE_PATH\s*\.\s*'\/\.\.\/(?:src\/)?Views\/([^']+)\.php';/", $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $fullMatch = $match[0];
            $viewPath = $match[1];

            // Try to find compact() or variables defined before the include
            // Look backwards from the include statement
            $pos = strpos($content, $fullMatch);
            $before = substr($content, max(0, $pos - 1000), 1000);

            // Look for compact() calls
            if (preg_match("/compact\(([^)]+)\)/", $before, $compactMatch)) {
                $compactArgs = $compactMatch[1];
                $replacement = "\$this->render('$viewPath', compact($compactArgs));";
            } else {
                // Look for variable assignments in the lines before
                $lines = explode("\n", $before);
                $vars = [];

                foreach (array_reverse($lines) as $line) {
                    // Stop at function/method boundaries
                    if (preg_match('/function\s+\w+/', $line) || preg_match('/^\s*}/', $line)) {
                        break;
                    }

                    // Find variable assignments
                    if (preg_match('/\$(\w+)\s*=/', $line, $varMatch)) {
                        $vars[] = "'" . $varMatch[1] . "'";
                    }
                }

                if (!empty($vars)) {
                    $vars = array_unique($vars);
                    $varsStr = implode(', ', $vars);
                    $replacement = "\$this->render('$viewPath', compact($varsStr));";
                } else {
                    $replacement = "\$this->render('$viewPath');";
                }
            }

            $content = str_replace($fullMatch, $replacement, $content);
            $changes++;
        }
    }

    // Save if changes were made
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $stats['files_processed']++;
        $stats['includes_replaced'] += $changes;
        echo "  ✓ Updated ($changes includes replaced)\n";
    } else {
        echo "  - No includes found\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "INCLUDE REFACTORING COMPLETE\n";
echo str_repeat('=', 60) . "\n";
echo "Files processed: {$stats['files_processed']}\n";
echo "Includes replaced: {$stats['includes_replaced']}\n";
echo "\n";
