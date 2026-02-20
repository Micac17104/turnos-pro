<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Tarea no encontrada.");
}

$stmt = $pdo->prepare("SELECT date FROM professional_tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$task) {
    die("Tarea no pertenece a este profesional.");
}

$stmt = $pdo->prepare("DELETE FROM professional_tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);

header("Location: /turnos-pro/pro/agenda.php?view=day&fecha=" . urlencode($task['date']));
exit;