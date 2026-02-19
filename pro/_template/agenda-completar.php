<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    exit("No autorizado.");
}

$id = $_GET['id'] ?? null;
$fecha = $_GET['fecha'] ?? date('Y-m-d');

if (!$id) {
    header("Location: /turnos-pro/profiles/$user_id/agenda.php?fecha=$fecha");
    exit;
}

$stmt = $pdo->prepare("
    UPDATE professional_tasks
    SET completed = 1 - completed
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$id, $user_id]);

header("Location: /turnos-pro/profiles/$user_id/agenda.php?fecha=$fecha");
exit;