<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar tenant
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

// Verificar login
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$patient_id   = $_POST['patient_id'] ?? null;
$motivo       = $_POST['motivo'] ?? null;
$evolucion    = $_POST['evolucion'] ?? null;
$indicaciones = $_POST['indicaciones'] ?? null;
$diagnostico  = $_POST['diagnostico'] ?? null;

// appointment_id opcional
$appointment_id = $_POST['appointment_id'] ?? null;

// fecha obligatoria
$fecha = date("Y-m-d H:i:s");

if (!$patient_id || !$evolucion) {
    die("Datos incompletos.");
}

// Guardar evolución
$stmt = $pdo->prepare("
    INSERT INTO clinical_records 
    (user_id, patient_id, appointment_id, fecha, motivo, evolucion, indicaciones, diagnostico)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $user_id,
    $patient_id,
    $appointment_id,
    $fecha,
    $motivo,
    $evolucion,
    $indicaciones,
    $diagnostico
]);

header("Location: /turnos-pro/profiles/$user_id/paciente-historia.php?id=$patient_id&ok=1");
exit;