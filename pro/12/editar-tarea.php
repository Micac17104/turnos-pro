<?php
session_start();
require __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$id          = $_POST['id'] ?? null;
$title       = $_POST['title'] ?? '';
$date        = $_POST['date'] ?? '';
$time        = $_POST['time'] ?? null;
$description = $_POST['description'] ?? '';

if (!$id) {
    die("Tarea no encontrada.");
}

$stmt = $pdo->prepare("SELECT * FROM professional_tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    die("No tenés permiso para editar esta tarea.");
}

$stmt = $pdo->prepare("
    UPDATE professional_tasks
    SET title = ?, date = ?, time = ?, description = ?
    WHERE id = ?
");
$stmt->execute([$title, $date, $time, $description, $id]);

header("Location: /turnos-pro/profiles/$user_id/agenda.php?fecha=$date&view=week");
exit;