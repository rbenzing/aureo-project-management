<?php
// Debug script for activity queries
// Access via: /debug_activity.php

// Start session and load everything needed
session_start();
define('BASE_PATH', __DIR__ . '/public');

require_once __DIR__ . '/vendor/autoload.php';

try {
    // Load configuration
    \App\Core\Config::init();
    
    // Test basic database connection
    echo "<h2>1. Database Connection Test</h2>";
    $db = \App\Core\Database::getInstance();
    echo "✅ Database connection successful<br>";
    
    // Test if activity_logs table exists
    echo "<h2>2. Table Structure Test</h2>";
    $result = $db->executeQuery("SHOW TABLES LIKE 'activity_logs'", []);
    if ($result->rowCount() > 0) {
        echo "✅ activity_logs table exists<br>";
        
        // Show table structure
        $columns = $db->executeQuery("DESCRIBE activity_logs", []);
        echo "<h3>Table Columns:</h3>";
        echo "<ul>";
        while ($col = $columns->fetch()) {
            echo "<li>{$col['Field']} - {$col['Type']} - {$col['Null']}</li>";
        }
        echo "</ul>";
    } else {
        echo "❌ activity_logs table does not exist<br>";
        exit;
    }
    
    // Test enhanced columns
    echo "<h2>3. Enhanced Columns Test</h2>";
    $enhancedCols = ['entity_type', 'entity_id', 'description', 'metadata'];
    foreach ($enhancedCols as $col) {
        $result = $db->executeQuery("SHOW COLUMNS FROM activity_logs LIKE ?", [$col]);
        if ($result->rowCount() > 0) {
            echo "✅ Column '$col' exists<br>";
        } else {
            echo "❌ Column '$col' missing<br>";
        }
    }
    
    // Test basic query
    echo "<h2>4. Basic Query Test</h2>";
    $count = $db->executeQuery("SELECT COUNT(*) FROM activity_logs", []);
    $totalRecords = $count->fetchColumn();
    echo "✅ Total activity records: $totalRecords<br>";
    
    // Test users table join
    echo "<h2>5. Users Join Test</h2>";
    $userJoin = $db->executeQuery("
        SELECT COUNT(*) 
        FROM activity_logs al 
        LEFT JOIN users u ON al.user_id = u.id
    ", []);
    $joinCount = $userJoin->fetchColumn();
    echo "✅ Activity-Users join works: $joinCount records<br>";
    
    // Test the actual query used in ActivityController
    echo "<h2>6. ActivityController Query Test</h2>";
    
    // Check if enhanced columns exist
    $hasEntityType = $db->executeQuery("SHOW COLUMNS FROM activity_logs LIKE 'entity_type'", [])->rowCount() > 0;
    
    if ($hasEntityType) {
        echo "Using enhanced query...<br>";
        $query = "
            SELECT 
                al.*,
                u.first_name,
                u.last_name,
                u.email,
                'test' as entity_name
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE 1=1
            ORDER BY al.created_at DESC 
            LIMIT 5
        ";
    } else {
        echo "Using basic query...<br>";
        $query = "
            SELECT 
                al.*,
                u.first_name,
                u.last_name,
                u.email,
                NULL as entity_type,
                NULL as entity_id,
                NULL as description,
                NULL as metadata,
                NULL as entity_name
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE 1=1
            ORDER BY al.created_at DESC 
            LIMIT 5
        ";
    }
    
    try {
        $result = $db->executeQuery($query, []);
        $activities = $result->fetchAll(PDO::FETCH_OBJ);
        echo "✅ Query executed successfully, found " . count($activities) . " records<br>";
        
        if (count($activities) > 0) {
            echo "<h3>Sample Records:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>User</th><th>Event</th><th>Path</th><th>Created</th></tr>";
            foreach (array_slice($activities, 0, 3) as $activity) {
                echo "<tr>";
                echo "<td>{$activity->id}</td>";
                echo "<td>" . htmlspecialchars(($activity->first_name ?? 'N/A') . ' ' . ($activity->last_name ?? '')) . "</td>";
                echo "<td>{$activity->event_type}</td>";
                echo "<td>{$activity->path}</td>";
                echo "<td>{$activity->created_at}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "❌ Query failed: " . $e->getMessage() . "<br>";
        echo "Query: " . htmlspecialchars($query) . "<br>";
    }
    
    // Test permission system
    echo "<h2>7. Permission Test</h2>";
    if (isset($_SESSION['user'])) {
        echo "✅ User is logged in: " . ($_SESSION['user']['profile']['first_name'] ?? 'Unknown') . "<br>";
        $permissions = $_SESSION['user']['permissions'] ?? [];
        echo "User permissions: " . implode(', ', $permissions) . "<br>";
        
        if (in_array('view_activity', $permissions)) {
            echo "✅ User has view_activity permission<br>";
        } else {
            echo "❌ User missing view_activity permission<br>";
        }
    } else {
        echo "❌ User not logged in<br>";
    }
    
    echo "<h2>8. Complete Test</h2>";
    echo "✅ All tests completed. The activity page should work now.<br>";
    echo "<a href='/activity'>Test Activity Page</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>