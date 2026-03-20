<?php
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

MercadoPago\SDK::setAccessToken("APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297");

// Leer el cuerpo del webhook
$body = file_get_contents("php://input");
$data = json_decode($body, true);

// Validar ID de pago
if (!isset($data["data"]["id"])) {
    http_response_code(400);
    exit;
}

$payment_id = $data["data"]["id"];
$payment = MercadoPago\Payment::find_by_id($payment_id);

// Solo procesar pagos aprobados
if (!$payment || $payment->status !== "approved") {
    http_response_code(200);
    exit;
}

// Metadata enviada desde pago-preferencia-sus.php
$user_id   = $payment->metadata->user_id ?? null;
$plan      = $payment->metadata->plan ?? null;
$user_type = $payment->metadata->user_type ?? null;

if (!$user_id) {
    http_response_code(200);
    exit;
}

// Obtener fecha actual y fecha de vencimiento actual
$stmt = $pdo->prepare("SELECT subscription_end FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$today = strtotime(date('Y-m-d'));
$end   = $user && $user['subscription_end'] ? strtotime($user['subscription_end']) : 0;

// Calcular nueva fecha de vencimiento
if ($end > $today) {
    $new_end = date('Y-m-d', strtotime($user['subscription_end'] . ' +1 month'));
} else {
    $new_end = date('Y-m-d', strtotime('+1 month'));
}

// Actualizar suscripción
$stmt2 = $pdo->prepare("
    UPDATE users
    SET 
        subscription_start = CURDATE(),
        subscription_end   = ?,
        is_active          = 1,
        last_payment       = CURDATE()
    WHERE id = ?
");

$stmt2->execute([$new_end, $user_id]);

http_response_code(200);