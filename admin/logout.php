<?php
// Configure session for domain compatibility
require_once '../inc/session-config.php';
session_start();
session_destroy();
header("Location: ../login.php");
exit();
?>

