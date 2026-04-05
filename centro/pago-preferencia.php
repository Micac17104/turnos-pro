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

if (!$user_id) {
    header("Location: /auth/login.php");
    exit;
}

// Obtener datos del centro
$stmt = $pdo->prepare("SELECT email, mp_preapproval_id, mp_subscription_status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: /auth/login.php");
    exit;
}

// 🚫 BLOQUEAR SI YA TIENE SUSCRIPCIÓN ACTIVA
if (!empty($user['mp_preapproval_id']) && $user['mp_subscription_status'] === 'active') {
    header("Location: /centro/suscripcion-activa.php");
    exit;
}

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

// ❌ Cancelar suscripción anterior si existe
if (!empty($user['mp_preapproval_id'])) {
    try {
        $old = Preapproval::find_by_id($user['mp_preapproval_id']);
        mp_log(["cancel_previous" => $old]);

        if ($old && isset($old->status) && $old->status !== "cancelled") {
            $old->status = "cancelled";
            $old->update();
        }

    } catch (Exception $e) {
        mp_log(["cancel_error" => $e->getMessage()]);
    }
}

try {

    $preapproval = new Preapproval();
    $preapproval->payer_email = $user['email'];

    // URL que recibe Mercado Pago al aprobar
    $preapproval->back_url = $baseUrl . "/centro/confirmar-centro.php";

    $preapproval->reason = "Suscripción mensual centro - Plan $plan";
    $preapproval->external_reference = (string)$user_id;

    $preapproval->auto_recurring = [
        "frequency" => 1,
        "frequency_type" => "months",
        "transaction_amount" => $precio,
        "currency_id" => "ARS"
    ];

    mp_log(["request_sent" => $preapproval]);

    $saved = $preapproval->save();

    mp_log([
        "save_result" => $saved,
        "response" => $preapproval
    ]);

    if ($saved && isset($preapproval->id) && isset($preapproval->init_point)) {

        // Guardar ID de suscripción
        $stmt2 = $pdo->prepare("
            UPDATE users
            SET mp_preapproval_id = ?, mp_subscription_status = 'active'
            WHERE id = ?
        ");
        $stmt2->execute([$preapproval->id, $user_id]);

        header("Location: " . $preapproval->init_point);
        exit;

    } else {

        mp_log([
            "error_creating_subscription" => $preapproval,
            "mp_error" => $preapproval->error ?? "NO ERROR FIELD"
        ]);

        echo "<pre>";
        print_r($preapproval->error);
        echo "</pre>";
        exit;
    }

} catch (Exception $e) {

    mp_log(["exception" => $e->getMessage()]);
    die("Error al procesar la suscripción.");
}

?>
