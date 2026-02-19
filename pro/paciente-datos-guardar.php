<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$patient_id   = $_POST['patient_id'] ?? null;
$antecedentes = trim($_POST['antecedentes'] ?? '');
$alergias     = trim($_POST['alergias'] ?? '');
$medicacion   = trim($_POST['medicacion'] ?? '');
$patologias   = trim($_POST['patologias'] ?? '');
$obra_social  = trim($_POST['obra_social'] ?? '');
$nro_afiliado = trim($_POST['nro_afiliado'] ?? '');

if (!$patient_id) {
    die("Paciente no encontrado.");
}

$stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);
if (!$stmt->fetch()) {
    die("Paciente no pertenece a este profesional.");
}

$stmt = $pdo->prepare("SELECT id FROM patients_extra WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$existe = $stmt->fetch();

if ($existe) {
    $stmt = $pdo->prepare("
        UPDATE patients_extra
        SET antecedentes=?, alergias=?, medicacion=?, patologias=?, obra_social=?, nro_afiliado=?
        WHERE patient_id=?
    ");
    $stmt->execute([$antecedentes,$alergias,$medicacion,$patologias,$obra_social,$nro_afiliado,$patient_id]);
} else {
    $stmt = $pdo->prepare("
        INSERT INTO patients_extra
        (patient_id, antecedentes, alergias, medicacion, patologias, obra_social, nro_afiliado)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$patient_id,$antecedentes,$alergias,$medicacion,$patologias,$obra_social,$nro_afiliado]);
}

header("Location: /turnos-pro/pro/paciente-historia.php?id=" . $patient_id);
exit;