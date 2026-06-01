<?php
require_once '../inc/session-config.php';
session_start();
require_once '../config.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic Report</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .report { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        h2 { color: #333; border-bottom: 2px solid #083a9c; padding-bottom: 10px; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>

<h1>🔍 Registration System Diagnostic Report</h1>

<div class="report">
    <h2>1. PHP Configuration</h2>
    <p>PHP Version: <code><?php echo phpversion(); ?></code></p>
    <p>Error Reporting: <code><?php echo ini_get('display_errors') ? 'Enabled' : 'Disabled'; ?></code></p>
    <p>Error Log: <code><?php echo ini_get('error_log'); ?></code></p>
</div>

<div class="report">
    <h2>2. Database Connection</h2>
    <?php
    if ($conn->connect_error) {
        echo '<p class="error">❌ Database Connection Failed: ' . htmlspecialchars($conn->connect_error) . '</p>';
    } else {
        echo '<p class="success">✅ Database Connected Successfully</p>';
        echo '<p>Database Name: <code>' . DB_NAME . '</code></p>';
        echo '<p>Server Version: <code>' . mysqli_get_server_info($conn) . '</code></p>';
    }
    ?>
</div>

<div class="report">
    <h2>3. Session Information</h2>
    <?php
    if (isset($_SESSION['admin_id'])) {
        echo '<p class="success">✅ User is logged in</p>';
        echo '<p>Admin ID: <code>' . htmlspecialchars($_SESSION['admin_id']) . '</code></p>';
        echo '<p>Admin Name: <code>' . htmlspecialchars($_SESSION['admin_name'] ?? 'Not set') . '</code></p>';
        echo '<p>Admin Email: <code>' . htmlspecialchars($_SESSION['admin_email'] ?? 'Not set') . '</code></p>';
    } else {
        echo '<p class="warning">⚠️ No user logged in (This is expected if you accessed this directly)</p>';
    }
    ?>
</div>

<div class="report">
    <h2>4. File Paths</h2>
    <p>Root Directory: <code><?php echo __DIR__; ?></code></p>
    <p>Admin UI JS Exists:
        <?php
        $js_path = __DIR__ . '/../assets/js/admin-ui.js';
        if (file_exists($js_path)) {
            echo '<span class="success">✅ Yes</span>';
        } else {
            echo '<span class="error">❌ No - File not found at: ' . htmlspecialchars($js_path) . '</span>';
        }
        ?>
    </p>
</div>

<div class="report">
    <h2>5. Error Log</h2>
    <?php
    $error_log = __DIR__ . '/../error.log';
    if (file_exists($error_log)) {
        $log_size = filesize($error_log);
        if ($log_size > 0) {
            echo '<p class="warning">📋 Error log exists (' . round($log_size / 1024, 2) . ' KB)</p>';
            echo '<p>Last 20 lines:</p>';
            echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; max-height: 300px; font-size: 12px;">';
            $lines = array_slice(file($error_log), -20);
            echo implode('', array_map('htmlspecialchars', $lines));
            echo '</pre>';
        } else {
            echo '<p class="success">✅ Error log is empty (no errors)</p>';
        }
    } else {
        echo '<p class="warning">⚠️ Error log not found yet</p>';
    }
    ?>
</div>

<div class="report">
    <h2>6. Action Items</h2>
    <ol>
        <li>Try logging in again and check if you see this page or the dashboard</li>
        <li>If you still see a blank page, check the browser console (F12) for JavaScript errors</li>
        <li>Check the error log above for any PHP errors</li>
        <li>If database connection fails, verify your database credentials in config.php</li>
    </ol>
</div>

<div style="text-align: center; margin-top: 40px; padding: 20px; background: #e3f2fd; border-radius: 5px;">
    <a href="dashboard.php" style="display: inline-block; padding: 10px 20px; background: #083a9c; color: white; text-decoration: none; border-radius: 5px;">← Back to Dashboard</a>
    <a href="../login.php" style="display: inline-block; padding: 10px 20px; background: #d73322; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">Go to Login</a>
</div>

</body>
</html>

