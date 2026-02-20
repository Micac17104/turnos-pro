<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$patient_id   = $_POST['patient_id'] ?? null;
$motivo       = trim($_POST['motivo'] ?? '');
$evolucion    = trim($_POST['evolucion'] ?? '');
$indicaciones = trim($_POST['indicaciones'] ?? '');
$diagnostico  = trim($_POST['diagnostico'] ?? '');
$appointment_id = $_POST['appointment_id'] ?? null;

if (!$patient_id || $evolucion === '') die("Datos incompletos.");

$stmt = $pdo->prepare("SELECT id FROM clients WHERE id=? AND user_id=?");
$stmt->execute([$patient_id, $user_id]);
if (!$stmt->fetch()) die("Paciente no pertenece a este profesional.");

$fecha = date("Y-m-d H:i:s");

$stmt = $pdo->prepare("
    INSERT INTO clinical_records
    (user_id, patient_id, appointment_id, fecha, motivo, evolucion, indicaciones, diagnostico)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([$user_id,$patient_id,$appointment_id,$fecha,$motivo,$evolucion,$indicaciones,$diagnostico]);

header("Location: /turnos-pro/pro/paciente-historia.php?id=" . $patient_id);
exit;