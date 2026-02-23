<?php
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) mkdir($path, 0777, true);
if (!is_writable($path)) @chmod($path, 0777);

session_save_path($path);
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

echo "<h1 style='background:#d1ffd1;padding:20px'>TEST 1 OK</h1>";
exit;