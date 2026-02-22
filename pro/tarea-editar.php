<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$id          = $_POST['id'] ?? null;
$title       = trim($_POST['title'] ?? '');
$date        = $_POST['date'] ?? null;
$time        = $_POST['time'] ?? null;
$description = trim($_POST['description'] ?? '');

if (!$id || $title === '' || !$date) {
    die("Datos incompletos.");
}

$stmt = $pdo->prepare("SELECT * FROM professional_tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$task) {
    die("Tarea no pertenece a este profesional.");
}

$stmt = $pdo->prepare("
    UPDATE professional_tasks
    SET title = ?, date = ?, time = ?, description = ?
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$title, $date, $time ?: null, $description, $id, $user_id]);

// Redirección corregida (ruta relativa)
redirect("agenda.php?view=day&fecha=" . urlencode($date));