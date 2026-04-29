<?php
// /pro/paciente-datos-guardar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Nuevos campos de recurrencia
$is_recurring    = isset($_POST['is_recurring']) ? 1 : 0;
$recurring_day   = trim($_POST['recurring_day'] ?? '');
$recurring_time  = trim($_POST['recurring_time'] ?? '');
$recurring_until = trim($_POST['recurring_until'] ?? '');

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Verificar que el paciente pertenece al profesional
$stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);
if (!$stmt->fetch()) {
    die("Paciente no pertenece a este profesional.");
}

// Guardar/actualizar datos clínicos en patients_extra
$stmt = $pdo->prepare("SELECT id FROM patients_extra WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$existe = $stmt->fetch();

if ($existe) {
    $stmt = $pdo->prepare("
        UPDATE patients_extra
        SET antecedentes=?, alergias=?, medicacion=?, patologias=?, obra_social=?, nro_afiliado=?
        WHERE patient_id=?
    ");
    $stmt->execute([
        $antecedentes,
        $alergias,
        $medicacion,
        $patologias,
        $obra_social,
        $nro_afiliado,
        $patient_id
    ]);
} else {
    $stmt = $pdo->prepare("
        INSERT INTO patients_extra
        (patient_id, antecedentes, alergias, medicacion, patologias, obra_social, nro_afiliado)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $patient_id,
        $antecedentes,
        $alergias,
        $medicacion,
        $patologias,
        $obra_social,
        $nro_afiliado
    ]);
}

// Actualizar datos de recurrencia en clients
$stmt = $pdo->prepare("
    UPDATE clients
    SET is_recurring = ?, recurring_day = ?, recurring_time = ?, recurring_until = ?
    WHERE id = ? AND user_id = ?
");
$stmt->execute([
    $is_recurring,
    $recurring_day !== '' ? $recurring_day : null,
    $recurring_time !== '' ? $recurring_time : null,
    $recurring_until !== '' ? $recurring_until : null,
    $patient_id,
    $user_id
]);

// Redirección
redirect("paciente-historia.php?id=" . $patient_id);
