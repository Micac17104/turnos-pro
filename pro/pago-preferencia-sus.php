<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';
require '/app/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preapproval\PreapprovalClient;

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || $_SESSION['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

// Traer email y preapproval previo
$stmt = $pdo->prepare("SELECT email, mp_preapproval_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: /auth/login.php");
    exit;
}

if (!isset($_GET['plan'])) {
    die("Plan inválido");
}

$plan = (int) $_GET['plan'];

$precios = [
    1 => 8000,
];

if (!isset($precios[$plan])) {
    die("Plan no encontrado");
}

$precio = (float)$precios[$plan];

// SDK NUEVO
MercadoPagoConfig::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX");
$client = new PreapprovalClient();

$baseUrl = "https://www.turnosaura.com";

/*
|--------------------------------------------------------------------------
| 1) Cancelar suscripción previa si existe
|--------------------------------------------------------------------------
*/
if (!empty($user['mp_preapproval_id'])) {
    try {
        $client->update($user['mp_preapproval_id'], [
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
        "payer_email" => $user['email'],
        "back_url" => $baseUrl . "/pro/pago-exitoso-sus.php",
        "reason" => "Suscripción mensual profesional - Plan $plan",
        "external_reference" => (string)$user_id,
        "auto_recurring" => [
            "frequency" => 1,
            "frequency_type" => "months",
            "transaction_amount" => $precio,
            "currency_id" => "ARS"
        ]
    ]);

    // Guardar ID de suscripción
    $stmt2 = $pdo->prepare("
        UPDATE users
        SET mp_preapproval_id = ?, mp_subscription_status = 'active'
        WHERE id = ?
    ");
    $stmt2->execute([$preapproval->id, $user_id]);

    // Redirigir a Mercado Pago
    header("Location: " . $preapproval->init_point);
    exit;

} catch (Exception $e) {
    die("No se pudo crear la suscripción. Error: " . $e->getMessage());
}