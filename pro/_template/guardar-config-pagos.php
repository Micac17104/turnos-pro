<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$token = $_POST['mp_access_token'] ?? '';

$stmt = $pdo->prepare("UPDATE users SET mp_access_token = ? WHERE id = ?");
$stmt->execute([$token, $user_id]);

header("Location: config-pagos.php?ok=1");
exit;