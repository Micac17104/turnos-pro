<?php
$path = __DIR__ . '/../sessions';
if (!is_dir($path)) mkdir($path, 0777, true);
session_save_path($path);
session_start();

require __DIR__ . '/includes/auth.php';

echo "<h1 style='background:#d1ffd1;padding:20px'>TEST A OK (auth.php existe)</h1>";
exit;