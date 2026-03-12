<?php
session_start();
require '../config.php';
require __DIR__ . '/../vendor/autoload.php';

MercadoPago\SDK::setAccessToken("APP_USR-936741788731989-031211-5eed533a498e365afb70fd29c65ad0bc-3260786753");

$center_id = $_SESSION['user_id'] ?? null;
if (!$center_id || ($_SESSION['account_type'] !== 'center' && $_SESSION['account_type'] !== 'secretary')) {
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

$precio = $precios[$plan];

$preference = new MercadoPago\Preference();

$item = new MercadoPago\Item();
$item->title = "Suscripción mensual centro - Plan $plan profesionales";
$item->quantity = 1;
$item->unit_price = $precio;

$preference->items = [$item];

$preference->metadata = [
    "user_id"   => $center_id,
    "plan"      => $plan,
    "user_type" => "center",
];

$baseUrl = "https://turnos-pro-production.up.railway.app";

$preference->back_urls = [
    "success" => $baseUrl . "/centro/pago-exitoso.php",
    "failure" => $baseUrl . "/centro/pago-fallido.php",
    "pending" => $baseUrl . "/centro/pago-pendiente.php",
];

$preference->auto_return = "approved";
$preference->notification_url = $baseUrl . "/webhooks/mercadopago.php";

$preference->save();

header("Location: " . $preference->init_point);
exit;