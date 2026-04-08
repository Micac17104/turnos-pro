<?php
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

SDK::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX");

$body = file_get_contents("php://input");
$data = json_decode($body, true);

if (!isset($data["type"])) {
    http_response_code(400);
    exit;
}

if ($data["type"] === "preapproval" && $data["action"] === "updated") {

    $preapproval_id = $data["data"]["id"];

    $preapproval = Preapproval::find_by_id($preapproval_id);

    if (!$preapproval) {
        http_response_code(200);
        exit;
    }

    $user_id = $preapproval->external_reference;

    if (!$user_id) {
        http_response_code(200);
        exit;
    }

    // Pago aprobado (renovación / primer cobro)
    if ($preapproval->status === "authorized") {

        $stmt = $pdo->prepare("SELECT subscription_end FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $today = strtotime(date('Y-m-d'));
        $end   = $user && $user['subscription_end'] ? strtotime($user['subscription_end']) : 0;

        if ($end > $today) {
            $new_end = date('Y-m-d', strtotime($user['subscription_end'] . ' +1 month'));
        } else {
            $new_end = date('Y-m-d', strtotime('+1 month'));
        }

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

    // Cancelada desde Mercado Pago
    if ($preapproval->status === "cancelled") {

        $stmt3 = $pdo->prepare("
            UPDATE users
            SET 
                is_active = 0,
                mp_subscription_status = 'inactive'
            WHERE id = ?
        ");
        $stmt3->execute([$user_id]);
    }

    http_response_code(200);
    exit;
}

http_response_code(200);
exit;
