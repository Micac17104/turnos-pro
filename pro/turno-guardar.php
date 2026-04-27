<?php
// /pro/turno-guardar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

// Si viene turno_id → estamos editando
$turno_id = $_POST['turno_id'] ?? null;

// client_id puede ser NULL
$client_id = $_POST['client_id'] ?? null;

$date = require_param($_POST, 'date');
$time = require_param($_POST, 'time');
$motivo = trim($_POST['motivo'] ?? '');


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

    redirect('agenda.php?edit=1');
}

// --------------------------------------
// BLOQUEAR SI NO TIENE SESIONES DISPONIBLES
// --------------------------------------
if (!empty($client_id)) {

    $stmt = $pdo->prepare("
        SELECT pc.id AS pc_id, p.total_sessions, pc.sessions_used
        FROM packs_clients pc
        JOIN packs p ON p.id = pc.pack_id
        WHERE pc.client_id = ?
          AND p.owner_type = 'professional'
          AND p.owner_id = ?
        ORDER BY pc.id DESC
        LIMIT 1
    ");
    $stmt->execute([$client_id, $user_id]);
    $pack = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pack) {
        $restantes = $pack['total_sessions'] - $pack['sessions_used'];

        if ($restantes <= 0) {
            die("Este paciente no tiene sesiones disponibles en su pack.");
        }
    }
}


// SI ES NUEVO → INSERT
$stmt = $pdo->prepare("
 INSERT INTO appointments (user_id, client_id, date, time, motivo)
VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$user_id, $client_id, $date, $time, $motivo]);

// --------------------------------------
// DESCONTAR SESIÓN DE PACK (si aplica)
// --------------------------------------
if (!empty($client_id)) {

    // Buscar pack activo del paciente
    $stmt = $pdo->prepare("
        SELECT pc.id AS pc_id, p.total_sessions, pc.sessions_used
        FROM packs_clients pc
        JOIN packs p ON p.id = pc.pack_id
        WHERE pc.client_id = ?
          AND p.owner_type = 'professional'
          AND p.owner_id = ?
        ORDER BY pc.id DESC
        LIMIT 1
    ");
    $stmt->execute([$client_id, $user_id]);
    $pack = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pack) {
        $restantes = $pack['total_sessions'] - $pack['sessions_used'];

        if ($restantes > 0) {
            // Descontar sesión
            $stmt = $pdo->prepare("
                UPDATE packs_clients
                SET sessions_used = sessions_used + 1
                WHERE id = ?
            ");
            $stmt->execute([$pack['pc_id']]);
        }
        // Si no hay sesiones restantes, NO rompemos nada.
        // Podés decidir bloquear la reserva si querés.
    }
}


// --------------------------------------
// ENVIAR EMAIL AL PACIENTE (si tiene email)
// --------------------------------------
if (!empty($client_id)) {

    // Obtener datos del paciente
    $stmt = $pdo->prepare("SELECT name, email FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    // 🔥 AGREGADO: obtener video_link del profesional
    $stmt = $pdo->prepare("SELECT video_link FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $pro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($paciente && !empty($paciente['email'])) {

        require __DIR__ . '/../auth/mailer.php';

        $asunto = "Nuevo turno asignado - TurnosAura";

        $mensaje = "
            Hola {$paciente['name']},<br><br>
            Tu profesional te asignó un turno:<br><br>
            <strong>Fecha:</strong> " . date('d/m/Y', strtotime($date)) . "<br>
            <strong>Hora:</strong> " . substr($time, 0, 5) . " hs<br><br>
        ";

        // 🔥 AGREGADO: link de videollamada
        if (!empty($pro['video_link'])) {
            $mensaje .= "
                <strong>Link de videollamada:</strong><br>
                <a href='{$pro['video_link']}'>{$pro['video_link']}</a><br><br>
            ";
        }

        $mensaje .= "Gracias por usar TurnosAura.";

        enviarEmail($paciente['email'], $asunto, $mensaje);
    }
}

redirect('agenda.php?ok=1');
