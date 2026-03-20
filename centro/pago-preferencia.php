<?php
session_start();
require '../config.php';
require __DIR__ . '/../vendor/autoload.php';

// Credenciales reales
MercadoPago\SDK::setAccessToken("APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297");

// Validar usuario del centro o secretaria
$center_id = $_SESSION['user_id'] ?? null;

if (!$center_id || ($_SESSION['account_type'] !== 'center' && $_SESSION['account_type'] !== 'secretary')) {
    header("Location: /auth/login.php");
    exit;
}

// Validar plan
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

// Crear preferencia
$preference = new MercadoPago\Preference();

$item = new MercadoPago\Item();
$item->title = "Suscripción mensual centro - Plan $plan profesionales";
$item->quantity = 1;
$item->unit_price = (float)$precio;

$preference->items = [$item];

// Metadata para el webhook
$preference->metadata = [
    "user_id"   => $center_id,
    "plan"      => $plan,
    "user_type" => "center",
];

// URL base de tu dominio real
$baseUrl = "https://www.turnosaura.com";

$preference->back_urls = [
    "success" => $baseUrl . "/centro/pago-exitoso.php",
    "failure" => $baseUrl . "/centro/pago-fallido.php",
    "pending" => $baseUrl . "/centro/pago-pendiente.php",
];

$preference->auto_return = "approved";

// Webhook compartido
$preference->notification_url = $baseUrl . "/webhooks/mercadopago.php";

$preference->save();

// Redirigir al checkout
header("Location: " . $preference->init_point);
exit;