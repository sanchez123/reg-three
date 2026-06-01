<?php
ob_start();

require_once '../inc/session-config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

// Add-admin functionality has been disabled - system only has one admin
$_SESSION['flash_error'] = 'The system only has one administrator and cannot add new admins.';
ob_end_clean();
header('Location: edit-admin.php?id=' . intval($_SESSION['admin_id']));
exit();
