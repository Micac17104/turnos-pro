<?php
file_put_contents(__DIR__ . "/cron-log.txt", "Ejecutado: " . date("Y-m-d H:i:s") . "\n", FILE_APPEND);
require __DIR__ . '/../config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "Cron ejecutado a las " . date("Y-m-d H:i:s") . "\n";

// 1) Recordatorio 24 horas antes
$stmt = $pdo->prepare("
    SELECT a.*, c.email, c.name AS paciente
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    WHERE a.date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
      AND a.reminder_24h = 0
      AND a.status = 'confirmed'
");
$stmt->execute();
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($turnos as $t) {
    $to = $t['email'];
    $subject = "Recordatorio de turno - Mi Salud";
    $message = "Hola {$t['paciente']},\n\nTe recordamos que tenés un turno mañana a las {$t['time']}.\n\nSaludos,\nMi Salud";

    mail($to, $subject, $message);

    $update = $pdo->prepare("UPDATE appointments SET reminder_24h = 1 WHERE id = ?");
    $update->execute([$t['id']]);
}

// 2) Recordatorio 2 horas antes
$stmt = $pdo->prepare("
    SELECT a.*, c.email, c.name AS paciente
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    WHERE a.date = CURDATE()
      AND a.time = DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 2 HOUR), '%H:%i:00')
      AND a.reminder_2h = 0
      AND a.status = 'confirmed'
");
$stmt->execute();
$turnos2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($turnos2 as $t) {
    $to = $t['email'];
    $subject = "Recordatorio de turno - Mi Salud";
    $message = "Hola {$t['paciente']},\n\nTe recordamos que tu turno es en 2 horas.\n\nSaludos,\nMi Salud";

    mail($to, $subject, $message);

    $update = $pdo->prepare("UPDATE appointments SET reminder_2h = 1 WHERE id = ?");
    $update->execute([$t['id']]);
}

echo "Recordatorios enviados.";