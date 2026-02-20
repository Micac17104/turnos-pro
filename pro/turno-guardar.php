<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

// Si viene turno_id → estamos editando
$turno_id = $_POST['turno_id'] ?? null;

// client_id puede ser NULL
$client_id = $_POST['client_id'] ?? null;

$date = require_param($_POST, 'date');
$time = require_param($_POST, 'time');

// Si estamos editando → validar que el turno exista
if ($turno_id) {
    $stmt = $pdo->prepare("
        SELECT id FROM appointments
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$turno_id, $user_id]);
    if (!$stmt->fetch()) {
        die("No tienes permiso para editar este turno.");
    }
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
    $client_id = null;
}

// Validar disponibilidad (excepto el mismo turno si estamos editando)
if ($turno_id) {
    $stmt = $pdo->prepare("
        SELECT id FROM appointments
        WHERE user_id = ? AND date = ? AND time = ? AND id != ?
    ");
    $stmt->execute([$user_id, $date, $time, $turno_id]);
} else {
    $stmt = $pdo->prepare("
        SELECT id FROM appointments
        WHERE user_id = ? AND date = ? AND time = ?
    ");
    $stmt->execute([$user_id, $date, $time]);
}

if ($stmt->fetch()) {
    die("Ese horario ya está ocupado.");
}

// SI ES EDICIÓN → UPDATE
if ($turno_id) {
    $stmt = $pdo->prepare("
        UPDATE appointments
        SET client_id = ?, date = ?, time = ?
        WHERE id = ?
    ");
    $stmt->execute([$client_id, $date, $time, $turno_id]);

    redirect('/turnos-pro/pro/agenda.php?edit=1');
}

// SI ES NUEVO → INSERT
$stmt = $pdo->prepare("
    INSERT INTO appointments (user_id, client_id, date, time)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$user_id, $client_id, $date, $time]);

redirect('/turnos-pro/pro/agenda.php?ok=1');