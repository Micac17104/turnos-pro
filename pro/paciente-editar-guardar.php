<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$patient_id = $_POST['patient_id'] ?? null;
$name       = trim($_POST['name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$dni        = trim($_POST['dni'] ?? '');

if (!$patient_id) {
    die("Paciente no encontrado.");
}

// Verificar que el paciente pertenece al profesional
$stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);
if (!$stmt->fetch()) {
    die("Paciente no pertenece a este profesional.");
}

// Actualizar datos personales
$stmt = $pdo->prepare("
    UPDATE clients
    SET name = ?, email = ?, phone = ?, dni = ?
    WHERE id = ?
");
$stmt->execute([$name, $email, $phone, $dni, $patient_id]);

header("Location: paciente-historia.php?id=" . $patient_id);
exit;
