<?php
session_start();
require __DIR__ . '/../../config.php';

// Validar login
if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

// Datos del formulario
$patient_id   = $_POST['patient_id'] ?? null;
$motivo       = $_POST['motivo'] ?? '';
$evolucion    = $_POST['evolucion'] ?? '';
$indicaciones = $_POST['indicaciones'] ?? '';
$diagnostico  = $_POST['diagnostico'] ?? '';

if (!$patient_id || empty($evolucion)) {
    die("Datos incompletos.");
}

// Verificar que el paciente pertenece al profesional
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no pertenece a este profesional.");
}

// Insertar evolución
$stmt = $pdo->prepare("
    INSERT INTO clinical_records 
    (user_id, patient_id, fecha, motivo, evolucion, indicaciones, diagnostico)
    VALUES (?, ?, NOW(), ?, ?, ?, ?)
");

$stmt->execute([
    $user_id,
    $patient_id,
    $motivo,
    $evolucion,
    $indicaciones,
    $diagnostico
]);

// Redirigir correctamente a la historia clínica
header("Location: /turnos-pro/profiles/$user_id/paciente-historia.php?id=" . $patient_id);
exit;