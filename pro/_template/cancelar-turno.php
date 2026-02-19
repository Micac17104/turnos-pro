<?php
session_start();
require __DIR__ . '/../../config.php';


// Validar login del profesional
if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}


$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$turno_id = $_GET['id'] ?? null;
$fecha    = $_GET['fecha'] ?? date('Y-m-d');

if (!$turno_id) {
    die("Turno no encontrado.");
}

// Validar que el turno pertenece al profesional
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
$stmt->execute([$turno_id, $user_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    die("Turno no pertenece a este profesional.");
}

// Cancelar turno
$stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
$stmt->execute([$turno_id]);

// Volver a la agenda
header("Location: /turnos-pro/profiles/$user_id/agenda.php?fecha=$fecha&view=week");
exit;