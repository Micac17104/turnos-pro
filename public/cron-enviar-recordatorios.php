<?php
// /cron/enviar-recordatorios.php

session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

// CONFIGURACIÓN
$BASE_URL = 'https://www.turnosaura.com'; // ⚠️ CAMBIAR por tu dominio real

// Buscamos turnos próximos con recordatorio habilitado
// Regla: faltan aproximadamente X horas (config del profesional o 24 por defecto)
// y aún no se envió el recordatorio (reminder_sent = 0)

$sql = "
    SELECT 
        a.id,
        a.user_id,
        a.client_id,
        a.date,
        a.time,
        a.status,
        a.email_token,
        c.name AS paciente_nombre,
        c.email AS paciente_email,
        u.name AS profesional_nombre,
        u.email AS profesional_email,
        ns.reminder_enabled,
        COALESCE(ns.reminder_hours_before, 24) AS horas_antes,
        ns.reminder_message
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    JOIN users u ON a.user_id = u.id
    LEFT JOIN notification_settings ns ON ns.user_id = a.user_id
    WHERE 
        a.status IN ('pending', 'confirmed')
        AND (ns.reminder_enabled = 1)
        AND a.reminder_sent = 0
        AND c.email IS NOT NULL
        AND c.email <> ''
";

$stmt = $pdo->query($sql);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ahora = new DateTime('now');

foreach ($turnos as $t) {
    $fechaHoraTurno = new DateTime($t['date'] . ' ' . $t['time']);

    $diffMin = ($fechaHoraTurno->getTimestamp() - $ahora->getTimestamp()) / 60;

// MODO PRUEBA: enviar recordatorio si el turno es dentro de los próximos 5 minutos
if ($diffMin <= 5 && $diffMin >= 0) {

        // Generar token si no existe
        if (empty($t['email_token'])) {
            $token = generate_token(32);
            $upd = $pdo->prepare("UPDATE appointments SET email_token = ? WHERE id = ?");
            $upd->execute([$token, $t['id']]);
        } else {
            $token = $t['email_token'];
        }

        $id = $t['id'];
        $pacienteNombre = $t['paciente_nombre'];
        $pacienteEmail  = $t['paciente_email'];
        $profesional    = $t['profesional_nombre'];
        $fecha          = (new DateTime($t['date']))->format('d/m/Y');
        $hora           = substr($t['time'], 0, 5);

        // Mensaje base
        if (!empty($t['reminder_message'])) {
            $mensaje = str_replace(
                ['{nombre}', '{fecha}', '{hora}', '{profesional}'],
                [$pacienteNombre, $fecha, $hora, $profesional],
                $t['reminder_message']
            );
        } else {
            $mensaje = "Hola $pacienteNombre, te recordamos tu turno el $fecha a las $hora con $profesional.";
        }

        // Links de confirmación / cancelación
        $confirmUrl = $BASE_URL . "/turno-confirmar-email.php?id={$id}&token={$token}";
        $cancelUrl  = $BASE_URL . "/turno-cancelar-email.php?id={$id}&token={$token}";

        $body = "
Hola {$pacienteNombre},

{$mensaje}

Por favor confirmá tu asistencia:

Confirmar turno: {$confirmUrl}
Cancelar turno: {$cancelUrl}

Gracias.
";

        // Enviar email (podés reemplazar @mail por tu PHPMailer si querés)
        @mail($pacienteEmail, "Recordatorio de turno", $body);

        // Marcar recordatorio como enviado
        $upd2 = $pdo->prepare("UPDATE appointments SET reminder_sent = 1 WHERE id = ?");
        $upd2->execute([$id]);
    }
}

echo "OK";