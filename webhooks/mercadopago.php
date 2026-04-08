<?php
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

// TOKEN DE PRODUCCIÓN
SDK::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX");

// -----------------------------
// LOG DE TODO LO QUE LLEGA
// -----------------------------
$raw = file_get_contents("php://input");
file_put_contents(__DIR__ . "/log.txt", date("Y-m-d H:i:s") . " - RAW: " . $raw . "\n", FILE_APPEND);

$data = json_decode($raw, true);

// Si no viene nada válido → ignorar
if (!isset($data["type"])) {
    http_response_code(200);
    exit;
}

// -----------------------------
// SOLO PROCESAMOS PREAPPROVAL
// -----------------------------
if ($data["type"] !== "preapproval") {
    http_response_code(200);
    exit;
}

// Acciones válidas que Mercado Pago envía
$acciones_validas = ["authorized", "created", "updated", "cancelled"];

if (!in_array($data["action"], $acciones_validas)) {
    file_put_contents(__DIR__ . "/log.txt", "IGNORADO action=" . $data["action"] . "\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

$preapproval_id = $data["data"]["id"] ?? null;

if (!$preapproval_id) {
    file_put_contents(__DIR__ . "/log.txt", "SIN preapproval_id\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

// Buscar la suscripción en Mercado Pago
$preapproval = Preapproval::find_by_id($preapproval_id);

if (!$preapproval) {
    file_put_contents(__DIR__ . "/log.txt", "NO SE ENCONTRÓ PREAPPROVAL\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

// El external_reference es el user_id
$user_id = $preapproval->external_reference;

if (!$user_id) {
    file_put_contents(__DIR__ . "/log.txt", "SIN external_reference\n", FILE_APPEND);
    http_response_code(200);
    exit;
}

// -----------------------------
// PROCESAR ESTADOS
// -----------------------------

$status = $preapproval->status;
file_put_contents(__DIR__ . "/log.txt", "STATUS: $status para user_id=$user_id\n", FILE_APPEND);

// ---------------------------------------
// 1) PAGO APROBADO (primer pago o renovación)
// ---------------------------------------
if ($status === "authorized" || $status === "active") {

    // Traer fecha de fin actual
    $stmt = $pdo->prepare("SELECT subscription_end FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $today = strtotime(date('Y-m-d'));
    $end   = $user && $user['subscription_end'] ? strtotime($user['subscription_end']) : 0;

    // Si todavía tenía días → sumar 1 mes desde el final
    if ($end > $today) {
        $new_end = date('Y-m-d', strtotime($user['subscription_end'] . ' +1 month'));
    } else {
        // Si estaba vencido → 1 mes desde hoy
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

// ---------------------------------------
// 2) CANCELADA DESDE MERCADO PAGO
// ---------------------------------------
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
