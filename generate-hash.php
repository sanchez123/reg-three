<?php
$password = "admin123";
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Bcrypt Hash for 'admin123':<br>";
echo "<strong>" . $hash . "</strong><br><br>";
echo "Copy this hash and paste it in phpMyAdmin";
?>