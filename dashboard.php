<?php
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) mkdir($path, 0777, true);
if (!is_writable($path)) @chmod($path, 0777);

session_save_path($path);
session_start();

echo "<h1 style='background:#d1ffd1;padding:20px'>TEST 2 OK (solo sesiones)</h1>";
exit;