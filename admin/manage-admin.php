<?php
ob_start();  // START OUTPUT BUFFERING - Add this FIRST!

require_once '../inc/session-config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config.php';

// This page is now used to redirect to the current admin's profile
// Since the system only has one admin, we redirect to edit profile for the current user
$admin_id = intval($_SESSION['admin_id']);
ob_end_clean();
header("Location: edit-admin.php?id=$admin_id");
exit();
