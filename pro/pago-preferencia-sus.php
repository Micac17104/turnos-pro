<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
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

$precio = $precios[$plan];

MercadoPago\SDK::setAccessToken("APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297");

// Crear preferencia
$preference = new MercadoPago\Preference();

$item = new MercadoPago\Item();
$item->title = "Suscripción mensual profesional - Plan $plan";
$item->quantity = 1;
$item->unit_price = (float)$precio;

$preference->items = [$item];

// Metadata
$preference->metadata = [
    "user_id"   => $user_id,
    "plan"      => $plan,
    "user_type" => "professional",
];

$baseUrl = "https://www.turnosaura.com";

$preference->back_urls = [
    "success" => $baseUrl . "/pro/pago-exitoso-sus.php",
    "failure" => $baseUrl . "/pro/pago-fallido-sus.php",
    "pending" => $baseUrl . "/pro/pago-pendiente-sus.php",
];

$preference->auto_return = "approved";
$preference->notification_url = $baseUrl . "/webhooks/mercadopago.php";

$preference->save();

header("Location: " . $preference->init_point);
exit;