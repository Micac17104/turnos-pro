<?php
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

SDK::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX");

// Leer webhook
$body = file_get_contents("php://input");
$data = json_decode($body, true);

// Validar estructura mínima
if (!isset($data["type"])) {
    http_response_code(400);
    exit;
}

/*
|--------------------------------------------------------------------------
| 1) EVENTO DE SUSCRIPCIÓN AUTOMÁTICA (preapproval)
|--------------------------------------------------------------------------
|
| Mercado Pago envía:
| type: "preapproval"
| action: "updated"
|
| Dentro viene:
| data.id = preapproval_id
|--------------------------------------------------------------------------
*/
if ($data["type"] === "preapproval" && $data["action"] === "updated") {

    $preapproval_id = $data["data"]["id"];

    // Obtener la suscripción completa desde MP
    $preapproval = Preapproval::find_by_id($preapproval_id);

    if (!$preapproval) {
        http_response_code(200);
        exit;
    }

    // user_id viene en external_reference
    $user_id = $preapproval->external_reference;

    if (!$user_id) {
        http_response_code(200);
        exit;
    }

    // Si el pago fue aprobado (authorized)
    if ($preapproval->status === "authorized") {

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
    }

    // Si la suscripción fue cancelada desde Mercado Pago
    if ($preapproval->status === "cancelled") {

        $stmt3 = $pdo->prepare("
            UPDATE users
            SET 
                is_active = 0,
                mp_subscription_status = 'cancelled'
            WHERE id = ?
        ");

        $stmt3->execute([$user_id]);
    }

    http_response_code(200);
    exit;
}

/*
|--------------------------------------------------------------------------
| 2) OTROS EVENTOS
|--------------------------------------------------------------------------
*/
http_response_code(200);
exit;