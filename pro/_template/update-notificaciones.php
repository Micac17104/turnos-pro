<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    exit("No autorizado.");
}

$notify_whatsapp = isset($_POST['notify_whatsapp']) ? 1 : 0;
$notify_email = isset($_POST['notify_email']) ? 1 : 0;

$stmt = $pdo->prepare("
    UPDATE users
    SET notify_whatsapp = ?, notify_email = ?
    WHERE id = ?
");
$stmt->execute([$notify_whatsapp, $notify_email, $user_id]);

// No redirigir a ninguna página
// Simplemente recargar el dashboard
header("Location: dashboard.php?ok=1");
exit;