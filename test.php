<?php
// This is a diagnostic test page - access it directly to test
require_once 'inc/session-config.php';
session_start();
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Page</title></head><body>";
echo "<h1>System Test</h1>";

// Test 1: Session
echo "<h2>1. Session Test</h2>";
if (isset($_SESSION['admin_id'])) {
    echo "Session exists. Admin ID: " . $_SESSION['admin_id'];
    echo "<br>Admin Name: " . ($_SESSION['admin_name'] ?? 'Not set');
    echo "<br><a href='admin/dashboard.php'>Go to Dashboard</a>";
} else {
    echo "No session found. <a href='login.php'>Go to Login</a>";
}

// Test 2: Database Connection
echo "<h2>2. Database Test</h2>";
if ($conn->connect_error) {
    echo "ERROR: " . $conn->connect_error;
} else {
    echo "Database connected successfully";
    // Try a simple query
    $result = $conn->query("SELECT COUNT(*) as cnt FROM admin_users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<br>Admin users count: " . $row['cnt'];
    }
}

// Test 3: File paths
echo "<h2>3. JavaScript File Check</h2>";
$admin_ui_path = __DIR__ . '/assets/js/admin-ui.js';
if (file_exists($admin_ui_path)) {
    echo "✅ admin-ui.js exists at: " . $admin_ui_path;
} else {
    echo "❌ admin-ui.js NOT found at: " . $admin_ui_path;
}

echo "</body></html>";
?>

