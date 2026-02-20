<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$turno_id  = require_param($_POST, 'turno_id');
$client_id = $_POST['client_id'] ?? null;   // AHORA PERMITE NULL
$date      = require_param($_POST, 'date');
$time      = require_param($_POST, 'time');

// Validar turno
$stmt = $pdo->prepare("
    SELECT id FROM appointments
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$turno_id, $user_id]);
if (!$stmt->fetch()) {
    die("No tienes permiso para editar este turno.");
}

// Validar paciente SOLO si client_id NO es null
if (!empty($client_id)) {
    $stmt = $pdo->prepare("
        SELECT id FROM clients
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$client_id, $user_id]);
    if (!$stmt->fetch()) {
        die("Paciente inválido.");
    }
} else {
    // Si viene vacío, lo guardamos como NULL
    $client_id = null;
}

// Validar disponibilidad (excepto el mismo turno)
$stmt = $pdo->prepare("
    SELECT id FROM appointments
    WHERE user_id = ? AND date = ? AND time = ? AND id != ?
");
$stmt->execute([$user_id, $date, $time, $turno_id]);
if ($stmt->fetch()) {
    die("Ese horario ya está ocupado.");
}

// Actualizar turno
$stmt = $pdo->prepare("
    UPDATE appointments
    SET client_id = ?, date = ?, time = ?
    WHERE id = ?
");
$stmt->execute([$client_id, $date, $time, $turno_id]);

redirect('/turnos-pro/pro/agenda.php?edit=1');