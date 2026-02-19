<?php
session_start();
require __DIR__ . '/../../config.php';

// Validar login
if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Detectar tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

// Datos recibidos
$client_id = $_POST['client_id'] ?? null;
$date      = $_POST['date'] ?? null;
$time      = $_POST['time'] ?? null;
$status    = $_POST['status'] ?? 'pending';

// Validación mínima
if (!$client_id || !$date || !$time) {
    die("Datos incompletos.");
}

// Validar que el paciente pertenece al profesional
$stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$client_id, $user_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("El paciente no pertenece a este profesional.");
}

// Guardar turno
$stmt = $pdo->prepare("
    INSERT INTO appointments (user_id, client_id, date, time, status)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([
    $user_id,
    $client_id,
    $date,
    $time,
    $status
]);

// Redirigir a la agenda en la fecha del turno
header("Location: /turnos-pro/profiles/$user_id/agenda.php?fecha=$date&view=week");
exit;