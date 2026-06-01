<?php
// QUICK VERIFICATION TEST
require_once 'inc/session-config.php';
session_start();
require_once 'config.php';

// Check if we can do redirects without headers already sent error
$test_passed = true;
$messages = [];

// Test 1: Check if session_start() worked
if (session_status() === PHP_SESSION_ACTIVE) {
    $messages[] = "✅ Session started successfully";
} else {
    $messages[] = "❌ Session failed to start";
    $test_passed = false;
}

// Test 2: Check database connection
if ($conn && !$conn->connect_error) {
    $messages[] = "✅ Database connected successfully";
} else {
    $messages[] = "❌ Database connection failed";
    $test_passed = false;
}

// Test 3: Try a simple query
$result = $conn->query("SELECT 1");
if ($result) {
    $messages[] = "✅ Database query test passed";
} else {
    $messages[] = "❌ Database query test failed";
    $test_passed = false;
}

// Test 4: Check if we can set session variables
$_SESSION['test'] = 'value';
if ($_SESSION['test'] === 'value') {
    $messages[] = "✅ Session variables work";
    unset($_SESSION['test']);
} else {
    $messages[] = "❌ Session variables failed";
    $test_passed = false;
}

// Output results
header('Content-Type: application/json');
echo json_encode([
    'success' => $test_passed,
    'tests' => $messages,
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'session_name' => session_name(),
    'session_id' => session_id()
], JSON_PRETTY_PRINT);

