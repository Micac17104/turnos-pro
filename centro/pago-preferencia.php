<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';
require '/app/vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

$center_id = $_SESSION['user_id'] ?? null;
$account_type = $_SESSION['account_type'] ?? null;

if (!$center_id || !in_array($account_type, ['center', 'secretary'])) {
    header("Location: /auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT email, mp_preapproval_id FROM users WHERE id = ?");
$stmt->execute([$center_id]);
$center = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$center) {
    header("Location: /auth/login.php");
    exit;
}

if (!isset($_GET['plan'])) {
    die("Plan inválido");
}

$plan = (int) $_GET['plan'];

$precios = [
    1 => 8000,
    2 => 13000,
    3 => 18000,
    4 => 23000,
    5 => 28000
];

if (!isset($precios[$plan])) {
    die("Plan no encontrado");
}

$precio = (float)$precios[$plan];

MercadoPagoConfig::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX");

$client = new PreapprovalClient();
$baseUrl = "https://www.turnosaura.com";

/*
|--------------------------------------------------------------------------
| 1) Cancelar suscripción previa si existe
|--------------------------------------------------------------------------
*/
if (!empty($center['mp_preapproval_id'])) {
    try {
        $client->update($center['mp_preapproval_id'], [
            "status" => "cancelled"
        ]);
    } catch (Exception $e) {
        // Si falla, seguimos igual
    }
}

/*
|--------------------------------------------------------------------------
| 2) Crear nueva suscripción automática
|--------------------------------------------------------------------------
*/
try {
    $preapproval = $client->create([
        "payer_email" => $center['email'],
        "back_url" => $baseUrl . "/centro/pago-exitoso.php",
        "reason" => "Suscripción mensual centro - Plan $plan profesionales",
        "external_reference" => (string)$center_id,
        "auto_recurring" => [
            "frequency" => 1,
            "frequency_type" => "months",
            "transaction_amount" => $precio,
            "currency_id" => "ARS"
        ]
    ]);

    $stmt2 = $pdo->prepare("
        UPDATE users
        SET mp_preapproval_id = ?, mp_subscription_status = 'active'
        WHERE id = ?
    ");
    $stmt2->execute([$preapproval->id, $center_id]);

    header("Location: " . $preapproval->init_point);
    exit;

} catch (Exception $e) {
    die("No se pudo crear la suscripción. Error: " . $e->getMessage());
}