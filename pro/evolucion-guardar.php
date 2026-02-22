<?php
// --- FIX DEFINITIVO PARA RAILWAY ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    @chmod($path, 0777);
}

session_save_path($path);
session_start();
// -----------------------------------

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$patient_id     = $_POST['patient_id'] ?? null;
$motivo         = trim($_POST['motivo'] ?? '');
$evolucion      = trim($_POST['evolucion'] ?? '');
$indicaciones   = trim($_POST['indicaciones'] ?? '');
$diagnostico    = trim($_POST['diagnostico'] ?? '');
$appointment_id = $_POST['appointment_id'] ?? null;

if (!$patient_id || $evolucion === '') {
    die("Datos incompletos.");
}

// Verificar que el paciente pertenece al profesional
$stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);

if (!$stmt->fetch()) {
    die("Paciente no pertenece a este profesional.");
}

$fecha = date("Y-m-d H:i:s");

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

// Redirección corregida (ruta relativa)
redirect("paciente-historia.php?id=" . $patient_id);