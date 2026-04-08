<?php
error_reporting(E_ERROR | E_PARSE);


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

// Traer email y preapproval anterior
$stmt = $pdo->prepare("SELECT email, mp_preapproval_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Validar plan
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

// Configurar MP
SDK::setAccessToken("APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297");

$baseUrl = "https://www.turnosaura.com";

// Cancelar suscripción anterior si existe
if (!empty($user['mp_preapproval_id'])) {
    try {
        $old = Preapproval::find_by_id($user['mp_preapproval_id']);
        if ($old && $old->status !== "cancelled") {
            $old->status = "cancelled";
            $old->update();
        }
    } catch (Exception $e) {
        mp_log("Error cancelando suscripción anterior: " . $e->getMessage());
    }
}

try {

    // Crear nueva suscripción
    $preapproval = new Preapproval();
    $preapproval->payer_email = $user['email'];
    $preapproval->back_url = $baseUrl . "/centro/confirmar-centro.php";
    $preapproval->reason = "Suscripción mensual centro - Plan $plan";
    $preapproval->external_reference = "centro_" . $user_id;

    $preapproval->auto_recurring = [
        "frequency" => 1,
        "frequency_type" => "months",
        "transaction_amount" => $precio,
        "currency_id" => "ARS"
    ];

    $saved = $preapproval->save();

    if ($saved && isset($preapproval->id) && isset($preapproval->init_point)) {

        // Guardar solo el ID de la suscripción (NO activar)
        $stmt2 = $pdo->prepare("
            UPDATE users
            SET 
                mp_preapproval_id = ?,
                mp_subscription_status = 'pending'
            WHERE id = ?
        ");
        $stmt2->execute([$preapproval->id, $user_id]);

        // Redirigir a Mercado Pago
        header("Location: " . $preapproval->init_point);
        exit;

    } else {
        die("No se pudo crear la suscripción. Intentalo más tarde.");
    }

} catch (Exception $e) {
    mp_log("Error general: " . $e->getMessage());
    die("Error al procesar la suscripción.");
}
