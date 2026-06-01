<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tiir_registration');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    die("Database connection failed. Please contact administrator.");
}

// Set charset
$conn->set_charset("utf8");

// Helper Functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_phone($phone) {
    // Basic phone validation - adjust regex as needed
    return preg_match('/^[\d\s\-\+\(\)]{7,}$/', $phone);
}

function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function upload_file($file, $upload_dir = 'uploads/members/')
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Normalize upload dir (relative to project root)
    $upload_dir = rtrim($upload_dir, '/') . '/';

    // Server path where files will be saved
    $server_upload_dir = __DIR__ . '/' . $upload_dir;

    // Create directory if not exists
    if (!is_dir($server_upload_dir)) {
        mkdir($server_upload_dir, 0755, true);
    }

    // Validate file using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif'
    ];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!array_key_exists($mime, $allowed_types)) {
        return false;
    }

    if ($file['size'] > $max_size) {
        return false;
    }

    // Generate unique filename
    $extension = $allowed_types[$mime];
    $filename = uniqid('img_', true) . '.' . $extension;
    $server_filepath = $server_upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $server_filepath)) {
        return false;
    }

    // Return path relative to project root (NO leading slash)
    return $upload_dir . $filename;
}

// Role helper functions
function get_admin_role($admin_id = null)
{
    global $conn;
    if ($admin_id === null && isset($_SESSION['admin_id'])) {
        $admin_id = intval($_SESSION['admin_id']);
    }
    if (!$admin_id) return null;

    $stmt = $conn->prepare("SELECT role FROM admin_users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $stmt->close();
            return $row['role'];
        }
        $stmt->close();
    }
    return null;
}

function is_super_admin()
{
    if (!isset($_SESSION['admin_id'])) return false;
    $role = get_admin_role(intval($_SESSION['admin_id']));
    return $role === 'super';
}

function require_role($required_role = 'admin')
{
    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../login.php");
        exit();
    }
    $role = get_admin_role(intval($_SESSION['admin_id']));
    if ($required_role === 'super' && $role !== 'super') {
        http_response_code(403);
        echo "403 Forbidden - insufficient permissions";
        exit();
    }
}

function log_audit($admin_id, $action, $table_name = null, $record_id = null, $details = null) {
    global $conn;
    $details_json = json_encode($details);
    $query = "INSERT INTO audit_log (admin_id, action, table_name, record_id, details)
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("issss", $admin_id, $action, $table_name, $record_id, $details_json);
        $stmt->execute();
        $stmt->close();
    }
}

function get_error_message($error_code) {
    $errors = [
        'invalid_credentials' => 'Invalid username or password',
        'email_exists' => 'Email already registered',
        'phone_exists' => 'Phone number already registered',
        'invalid_email' => 'Please enter a valid email',
        'invalid_phone' => 'Please enter a valid phone number',
        'upload_failed' => 'File upload failed',
        'invalid_file_type' => 'Invalid file type. Only images allowed.',
        'database_error' => 'Database error occurred',
        'session_expired' => 'Your session has expired. Please login again.'
    ];

    return $errors[$error_code] ?? 'An error occurred';
}

