<?php
$password = "Admin2026";
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Bcrypt Hash for 'Admin2026':<br>";
echo "<strong>" . $hash . "</strong><br><br>";
echo "Copy this hash and paste it in phpMyAdmin";
?>