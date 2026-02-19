<?php
session_start();
require __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$id        = $_POST['id'] ?? null;
$client_id = $_POST['client_id'] ?? null;
$date      = $_POST['date'] ?? null;
$time      = $_POST['time'] ?? null;
$status    = $_POST['status'] ?? null;

if (!$id) {
    die("Turno no encontrado.");
}

$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    die("No tenés permiso para editar este turno.");
}

$stmt = $pdo->prepare("
    UPDATE appointments
    SET client_id = ?, date = ?, time = ?, status = ?
    WHERE id = ?
");
$stmt->execute([$client_id, $date, $time, $status, $id]);

header("Location: /turnos-pro/profiles/$user_id/agenda.php?fecha=$date&view=week");
exit;