<?php
require __DIR__ . '/pro/includes/db.php';
require __DIR__ . '/mailer.php'; // para enviar emails

$today = date('Y-m-d');

// 1) Avisar 5 días antes del vencimiento
$stmt = $pdo->query("
    SELECT id, email, name, subscription_end 
    FROM users 
    WHERE is_active = 1
      AND subscription_end = DATE_ADD(CURDATE(), INTERVAL 5 DAY)
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $u) {

    $asunto = "Tu suscripción vence pronto";
    $mensaje = "
        Hola {$u['name']},<br><br>
        Tu suscripción vence el <b>{$u['subscription_end']}</b>.<br>
        Para continuar usando TurnosPro, debés realizar el pago antes de esa fecha.<br><br>
        Gracias por usar TurnosPro.
    ";

    enviarEmail($u['email'], $asunto, $mensaje);
}


// 2) Suspender cuentas vencidas
$stmt2 = $pdo->query("
    UPDATE users
    SET is_active = 0
    WHERE subscription_end < CURDATE()
      AND is_active = 1
");

echo "Cron ejecutado correctamente";