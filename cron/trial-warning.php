<?php
require __DIR__ . '/../pro/includes/db.php';

// Fecha de hoy
$today = date('Y-m-d');

// Buscar usuarios en prueba que no recibieron aviso
$stmt = $pdo->query("
    SELECT id, email, subscription_end 
    FROM users 
    WHERE mp_subscription_status = 'active'
    AND trial_warning_sent = 0
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $u) {

    $end = strtotime($u['subscription_end']);
    $days_left = ($end - strtotime($today)) / 86400;

    // Si faltan 3 días exactos
    if ($days_left == 3) {

        // Enviar email
        $to = $u['email'];
        $subject = "Tu prueba gratuita termina en 3 días";
        $message = "Hola! Tu mes de prueba en TurnosAura termina en 3 días. 
Si no cancelás, comenzará el cobro automático mensual. 
Si querés cancelar, podés hacerlo desde tu panel.";

        mail($to, $subject, $message);

        // Marcar como enviado
        $stmt2 = $pdo->prepare("
            UPDATE users 
            SET trial_warning_sent = 1 
            WHERE id = ?
        ");
        $stmt2->execute([$u['id']]);
    }
}

echo "Avisos enviados.";