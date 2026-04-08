<?php

function mp_log($data) {
    $logFile = __DIR__ . "/mp-log-centro.txt";
    $entry = "[" . date("Y-m-d H:i:s") . "] " . print_r($data, true) . "\n\n";
    file_put_contents($logFile, $entry, FILE_APPEND);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || $_SESSION['account_type'] !== 'center') {
    header("Location: /auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT email, mp_preapproval_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_GET['plan'])) {
    die("Plan inválido");
}

$plan = $_GET['plan'];

$precios = [
    "basico"  => 8000,
    "pro"     => 15000,
    "premium" => 25000
];

if (!isset($precios[$plan])) {
    die("Plan no encontrado");
}

$precio = (float)$precios[$plan];

SDK::setAccessToken("APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297");

$baseUrl = "https://www.turnosaura.com";

// cancelar suscripción anterior si existe
if (!empty($user['mp_preapproval_id'])) {
    try {
        $old = Preapproval::find_by_id($user['mp_preapproval_id']);
        if ($old && $old->status !== "cancelled") {
            $old->status = "cancelled";
            $old->update();
        }
    } catch (Exception $e) {}
}

try {

    $preapproval = new Preapproval();
    $preapproval->payer_email = $user['email'];
    $preapproval->back_url = $baseUrl . "/centro/confirmar-centro.php";
    $preapproval->reason = "Suscripción mensual centro - Plan $plan";
    $preapproval->external_reference = (string)$user_id;

    $preapproval->auto_recurring = [
        "frequency" => 1,
        "frequency_type" => "months",
        "transaction_amount" => $precio,
        "currency_id" => "ARS"
    ];

    $saved = $preapproval->save();

    if ($saved && isset($preapproval->id) && isset($preapproval->init_point)) {

        // ACTIVAR AL TOQUE
        $today = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+1 month'));

        $stmt2 = $pdo->prepare("
            UPDATE users
            SET 
                mp_preapproval_id = ?,
                mp_subscription_status = 'active',
                is_active = 1,
                subscription_start = ?,
                subscription_end = ?
            WHERE id = ?
        ");
        $stmt2->execute([$preapproval->id, $today, $end, $user_id]);

        header("Location: " . $preapproval->init_point);
        exit;

    } else {
        die("No se pudo crear la suscripción. Intentalo más tarde.");
    }

} catch (Exception $e) {
    die("Error al procesar la suscripción.");
}
