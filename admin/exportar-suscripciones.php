<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=suscripciones.csv");

$output = fopen("php://output", "w");

fputcsv($output, [
    "ID", "Email", "Tipo", "Inicio", "Vence", "Estado", "Último pago"
]);

$stmt = $pdo->query("
    SELECT id, email, account_type, subscription_start, subscription_end, is_active, mp_subscription_status, last_payment
    FROM users
");

while ($u = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $today = strtotime(date('Y-m-d'));
    $end = $u['subscription_end'] ? strtotime($u['subscription_end']) : null;

    if ($u['mp_subscription_status'] === 'inactive') {
        $estado = "Cancelada";
    } elseif ($end !== null && $end < $today) {
        $estado = "Vencida";
    } elseif ($u['is_active'] == 0) {
        $estado = "Suspendida";
    } elseif (empty($u['subscription_start'])) {
        $estado = "Sin suscripción";
    } else {
        $estado = "Activa";
    }

    fputcsv($output, [
        $u['id'],
        $u['email'],
        $u['account_type'],
        $u['subscription_start'],
        $u['subscription_end'],
        $estado,
        $u['last_payment']
    ]);
}

fclose($output);
exit;
