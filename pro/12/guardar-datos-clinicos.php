<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

// Validar login del profesional
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Datos del formulario
$patient_id   = $_POST['patient_id'] ?? null;
$antecedentes = $_POST['antecedentes'] ?? '';
$alergias     = $_POST['alergias'] ?? '';
$medicacion   = $_POST['medicacion'] ?? '';
$patologias   = $_POST['patologias'] ?? '';
$obra_social  = $_POST['obra_social'] ?? '';
$nro_afiliado = $_POST['nro_afiliado'] ?? '';

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Verificar que el paciente pertenece al profesional
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no pertenece a este profesional.");
}

// Verificar si ya existen datos clínicos
$stmt = $pdo->prepare("SELECT id FROM patients_extra WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$existe = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existe) {
    // Actualizar
    $stmt = $pdo->prepare("
        UPDATE patients_extra
        SET antecedentes = ?, alergias = ?, medicacion = ?, patologias = ?, obra_social = ?, nro_afiliado = ?
        WHERE patient_id = ?
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
    // Insertar
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

// Redirigir correctamente a la historia clínica
header("Location: /turnos-pro/profiles/$user_id/paciente-historia.php?id=" . $patient_id);
exit;