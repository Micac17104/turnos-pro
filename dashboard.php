<?php
echo "<pre>";
echo "Dashboard path: " . __DIR__ . "\n";
echo "Auth path: " . __DIR__ . "/includes/auth.php\n";
echo "File exists? ";
echo file_exists(__DIR__ . "/includes/auth.php") ? "YES" : "NO";
echo "</pre>";
exit;