<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

// Si viene turno_id → estamos editando
$turno_id = $_POST['turno_id'] ?? null;

// client_id puede ser NULL
$client_id = $_POST['client_id'] ?? null;

$date   = require_param($_POST, 'date');
$time   = require_param($_POST, 'time');
$status = $_POST['status'] ?? 'pending';

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
        die("Paciente no pertenece a este profesional.");
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
        SET client_id = ?, date = ?, time = ?, status = ?
        WHERE id = ?
    ");
    $stmt->execute([$client_id, $date, $time, $status, $turno_id]);

    header("Location: /turnos-pro/pro/agenda.php?view=day&fecha=" . urlencode($date));
    exit;
}

// SI ES NUEVO → INSERT
$stmt = $pdo->prepare("
    INSERT INTO appointments (user_id, client_id, date, time, status)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$user_id, $client_id, $date, $time, $status]);

header("Location: /turnos-pro/pro/agenda.php?view=day&fecha=" . urlencode($date));
exit;