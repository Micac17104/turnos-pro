<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

if (!isset($_GET['id'])) {
    die("ID inválido");
}

$id = intval($_GET['id']);

// Obtener usuario
$stmt = $pdo->prepare("SELECT subscription_end FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuario no encontrado");
}

$today = date('Y-m-d');
$start_date = $user['subscription_end'] ?: $today;

$start_ts = strtotime($start_date);
$today_ts = strtotime($today);

// Si la fecha actual es mayor, reiniciamos desde hoy
if ($start_ts > $today_ts) {
    $new_end = date('Y-m-d', strtotime($start_date . ' +30 days'));
} else {
    $new_end = date('Y-m-d', strtotime('+30 days'));
}

// Actualizar suscripción
$stmt2 = $pdo->prepare("
    UPDATE users
    SET 
        is_active = 1,
        mp_subscription_status = 'active',
        subscription_start = ?,
        subscription_end = ?,
        last_payment = CURDATE()
    WHERE id = ?
");

$log = $pdo->prepare("
    INSERT INTO subscription_logs (user_id, action, details)
    VALUES (?, 'admin_mark_paid', 'Pago registrado manualmente por el administrador')
");
$log->execute([$id]);


$stmt2->execute([$today, $new_end, $id]);

header("Location: pagos.php?ok=1");
exit;
