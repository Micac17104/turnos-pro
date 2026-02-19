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

$patient_id = $_POST['patient_id'] ?? null;
$motivo = $_POST['motivo'] ?? null;
$evolucion = $_POST['evolucion'] ?? null;
$indicaciones = $_POST['indicaciones'] ?? null;
$diagnostico = $_POST['diagnostico'] ?? null;

if (!$patient_id || !$evolucion) {
    die("Datos incompletos.");
}

// Guardar evolución
$stmt = $pdo->prepare("
    INSERT INTO evolutions (user_id, patient_id, motivo, evolucion, indicaciones, diagnostico, fecha)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$user_id, $patient_id, $motivo, $evolucion, $indicaciones, $diagnostico]);

header("Location: /turnos-pro/profiles/$user_id/paciente.php?id=$patient_id");
exit;