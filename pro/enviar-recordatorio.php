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

// Obtener turno
$stmt = $pdo->prepare("
    SELECT t.date, t.time, c.name, c.email
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
$message = "Hola {$turno['name']}, te recordamos tu turno el {$turno['date']} a las {$turno['time']}.";

$ok = enviarEmail($turno['email'], $subject, $message);

if ($ok) {
    redirect("turnos-manana.php?sent=1");
} else {
    redirect("turnos-manana.php?sent=0");
}

redirect("turnos-manana.php?sent=1");