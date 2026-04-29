<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$patient_id      = $_POST['patient_id'] ?? null;
$name            = trim($_POST['name'] ?? '');
$email           = trim($_POST['email'] ?? '');
$phone           = trim($_POST['phone'] ?? '');
$dni             = trim($_POST['dni'] ?? '');
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

// Actualizar datos personales + recurrencia
$stmt = $pdo->prepare("
    UPDATE clients
    SET name = ?, email = ?, phone = ?, dni = ?,
        is_recurring = ?, recurring_day = ?, recurring_time = ?, recurring_until = ?
    WHERE id = ? AND user_id = ?
");
$stmt->execute([
    $name,
    $email,
    $phone,
    $dni,
    $is_recurring,
    $recurring_day !== '' ? $recurring_day : null,
    $recurring_time !== '' ? $recurring_time : null,
    $recurring_until !== '' ? $recurring_until : null,
    $patient_id,
    $user_id
]);

header("Location: paciente-historia.php?id=" . $patient_id);
exit;
