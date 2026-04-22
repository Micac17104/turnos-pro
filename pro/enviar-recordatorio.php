<?php
// /pro/enviar-recordatorio.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

// USAR EL MAILER QUE FUNCIONA
require __DIR__ . '/../auth/mailer.php';

$turno_id = require_param($_GET, 'turno_id');

// Obtener turno + 🔥 AGREGADO: video_link
$stmt = $pdo->prepare("
    SELECT 
        t.date, 
        t.time, 
        c.name, 
        c.email,
        u.video_link   -- 🔥 AGREGADO
    FROM appointments t
    JOIN clients c ON c.id = t.client_id
    JOIN users u ON u.id = t.user_id
    WHERE t.id = ? AND t.user_id = ?
");
$stmt->execute([$turno_id, $user_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    die("Turno no encontrado.");
}

$subject = "Recordatorio de turno";
$message = "Hola {$turno['name']}, te recordamos tu turno el {$turno['date']} a las {$turno['time']}.";

// 🔥 AGREGADO: LINK DE VIDEOLLAMADA (solo si existe)
if (!empty($turno['video_link'])) {
    $message .= "<br><br><strong>Link de videollamada:</strong><br>
                 <a href='{$turno['video_link']}'>{$turno['video_link']}</a>";
}

$ok = enviarEmail($turno['email'], $subject, $message);

if ($ok) {
    redirect("turnos-manana.php?sent=1");
} else {
    redirect("turnos-manana.php?sent=0");
}

redirect("turnos-manana.php?sent=1");
