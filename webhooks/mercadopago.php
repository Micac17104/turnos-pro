<?php
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

MercadoPago\SDK::setAccessToken("APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297");

// Leer webhook
$body = file_get_contents("php://input");
$data = json_decode($body, true);

// Validar estructura
if (!isset($data["type"])) {
    http_response_code(400);
    exit;
}

/*
|--------------------------------------------------------------------------
| 1) PAGOS RECURRENTES (preapproval)
|--------------------------------------------------------------------------
*/
if ($data["type"] === "preapproval" && $data["action"] === "payment.created") {

    $payment_id = $data["data"]["id"];

    $payment = MercadoPago\Payment::find_by_id($payment_id);

    if (!$payment || $payment->status !== "approved") {
        http_response_code(200);
        exit;
    }

    // user_id viene en external_reference
    $user_id = $payment->external_reference;

    if (!$user_id) {
        http_response_code(200);
        exit;
    }

    // Obtener fecha actual y fecha de vencimiento
    $stmt = $pdo->prepare("SELECT subscription_end FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $today = strtotime(date('Y-m-d'));
    $end   = $user && $user['subscription_end'] ? strtotime($user['subscription_end']) : 0;

    // Extender 1 mes
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
            mp_subscription_status = 'active'
        WHERE id = ?
    ");

    $stmt2->execute([$new_end, $user_id]);

    http_response_code(200);
    exit;
}

/*
|--------------------------------------------------------------------------
| 2) OTROS WEBHOOKS (por si los necesitás)
|--------------------------------------------------------------------------
*/
http_response_code(200);
exit;