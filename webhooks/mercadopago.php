<?php
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

SDK::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX");

// LOG RAW
$raw = file_get_contents("php://input");
file_put_contents(__DIR__ . "/log.txt", date("Y-m-d H:i:s") . " RAW: $raw\n", FILE_APPEND);

$data = json_decode($raw, true);

// Detectar tipo real
$tipo = $data["type"] ?? $data["topic"] ?? null;

// Aceptar todos los eventos de suscripción
if (!in_array($tipo, ["preapproval", "subscription_preapproval"])) {
    file_put_contents(__DIR__ . "/log.txt", "IGNORADO type=$tipo\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

// Obtener preapproval_id
$preapproval_id =
    $data["data"]["id"]
    ?? $data["data"]["preapproval_id"]
    ?? $data["resource"]
    ?? null;

if (!$preapproval_id) {
    file_put_contents(__DIR__ . "/log.txt", "SIN preapproval_id\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

// Buscar suscripción
$preapproval = Preapproval::find_by_id($preapproval_id);

if (!$preapproval) {
    file_put_contents(__DIR__ . "/log.txt", "NO SE ENCONTRÓ PREAPPROVAL\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

$user_id = $preapproval->external_reference;

if (!$user_id) {
    file_put_contents(__DIR__ . "/log.txt", "SIN external_reference\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

$status = $preapproval->status;
file_put_contents(__DIR__ . "/log.txt", "STATUS=$status user_id=$user_id\n", FILE_APPEND);

// ACTIVAR
if (in_array($status, ["authorized", "active"])) {

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

    file_put_contents(__DIR__ . "/log.txt", "ACTIVADO user_id=$user_id hasta $new_end\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

// CANCELAR
if ($status === "cancelled") {

    $stmt3 = $pdo->prepare("
        UPDATE users
        SET 
            is_active = 0,
            mp_subscription_status = 'inactive'
        WHERE id = ?
    ");
    $stmt3->execute([$user_id]);

    file_put_contents(__DIR__ . "/log.txt", "CANCELADO user_id=$user_id\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

http_response_code(200);
exit;
