<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../auth/mailer.php';

$BASE_URL = 'https://www.turnosaura.com';

$sql = "
    SELECT 
        a.id,
        a.user_id,
        a.client_id,
        a.date,
        a.time,
        a.status,
        a.confirm_token,
        a.cancel_token,
        c.name AS paciente_nombre,
        c.email AS paciente_email,
        u.name AS profesional_nombre,
        u.email AS profesional_email,
        u.video_link AS video_link,   -- 🔥 AGREGADO EN SELECT
        ns.reminder_enabled,
        COALESCE(ns.reminder_hours_before, 24) AS horas_antes,
        ns.reminder_message
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    JOIN users u ON a.user_id = u.id
    LEFT JOIN notification_settings ns ON ns.user_id = a.user_id
    WHERE 
        a.status IN ('pending', 'confirmed')
        AND ns.reminder_enabled = 1
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

    $minAntes = $t['horas_antes'] * 60;

    if ($diffMin <= $minAntes && $diffMin >= $minAntes - 5) {

        // 🔥 UNIFICACIÓN DE TOKENS
        $confirm_token = $t['confirm_token'] ?: bin2hex(random_bytes(16));
        $cancel_token  = $t['cancel_token']  ?: bin2hex(random_bytes(16));

        $upd = $pdo->prepare("
            UPDATE appointments 
            SET confirm_token = ?, cancel_token = ?
            WHERE id = ?
        ");
        $upd->execute([$confirm_token, $cancel_token, $t['id']]);

        $id = $t['id'];
        $pacienteNombre = $t['paciente_nombre'];
        $pacienteEmail  = $t['paciente_email'];
        $profesional    = $t['profesional_nombre'];
        $fecha          = (new DateTime($t['date']))->format('d/m/Y');
        $hora           = substr($t['time'], 0, 5);

        // Mensaje
        if (!empty($t['reminder_message'])) {
            $mensaje = str_replace(
                ['{nombre}', '{fecha}', '{hora}', '{profesional}'],
                [$pacienteNombre, $fecha, $hora, $profesional],
                $t['reminder_message']
            );
        } else {
            $mensaje = "Hola $pacienteNombre, te recordamos tu turno el $fecha a las $hora con $profesional.";
        }

        // 🔥 LINKS CORRECTOS
        $confirmUrl = $BASE_URL . "/public/turno-confirmar-email.php?id={$id}&token={$confirm_token}";
        $cancelUrl  = $BASE_URL . "/public/turno-cancelar-email.php?id={$id}&token={$cancel_token}";

        // 🔥 AGREGADO EN EL EMAIL
        $video_html = "";
        if (!empty($t['video_link'])) {
            $video_html = "
<br><br>
<strong>Link de videollamada:</strong><br>
<a href='{$t['video_link']}'>{$t['video_link']}</a><br>
";
        }

        $body = "
Hola {$pacienteNombre},<br><br>

{$mensaje}<br><br>

<a href='{$confirmUrl}'>✔ Confirmar turno</a><br>
<a href='{$cancelUrl}'>✖ Cancelar turno</a><br><br>

{$video_html}

Gracias.
";

        enviarEmail($pacienteEmail, "Recordatorio de turno", $body);

        $upd2 = $pdo->prepare("UPDATE appointments SET reminder_sent = 1 WHERE id = ?");
        $upd2->execute([$id]);
    }
}

echo "OK";
