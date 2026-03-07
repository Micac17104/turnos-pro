<?php
// /pro/enviar-recordatorio.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/mailer.php';

$turno_id = require_param($_GET, 'turno_id');

// Obtener turno (IMPORTANTE: usa day y time)
$stmt = $pdo->prepare("
    SELECT t.day, t.time, c.name, c.email
    FROM appointments t
    JOIN clients c ON c.id = t.client_id
    WHERE t.id = ? AND t.user_id = ?
");
$stmt->execute([$turno_id, $user_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    die("Turno no encontrado.");
}

$subject = "Recordatorio de turno";
$message = "Hola {$turno['name']}, te recordamos tu turno el {$turno['day']} a las {$turno['time']}.";

enviarEmail($turno['email'], $subject, $message);

redirect("turnos-manana.php?sent=1");