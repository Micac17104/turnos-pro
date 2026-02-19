<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    exit("No autorizado.");
}

$title = trim($_POST['title'] ?? '');
$date = $_POST['date'] ?? date('Y-m-d');
$time = $_POST['time'] ?: null;
$description = trim($_POST['description'] ?? '');

if (!$title) {
    header("Location: /turnos-pro/profiles/$user_id/agenda.php?fecha=$date");
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO professional_tasks (user_id, title, description, date, time)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$user_id, $title, $description, $date, $time]);

header("Location: /turnos-pro/profiles/$user_id/agenda.php?fecha=$date");
exit;