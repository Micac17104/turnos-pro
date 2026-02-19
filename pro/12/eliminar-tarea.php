<?php
session_start();
require __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Tarea no encontrada.");
}

$stmt = $pdo->prepare("DELETE FROM professional_tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);

header("Location: /turnos-pro/profiles/$user_id/agenda.php?view=week");
exit;