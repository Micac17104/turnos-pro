<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../vendor/autoload.php';

MercadoPago\SDK::setAccessToken("APP_USR-936741788731989-031211-5eed533a498e365afb70fd29c65ad0bc-3260786753");

// Validar plan
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

// Validar usuario
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: /auth/login.php");
    exit;
}

// Crear preferencia
$preference = new MercadoPago\Preference();

$item = new MercadoPago\Item();
$item->title = "Suscripción mensual profesional - Plan $plan";
$item->quantity = 1;
$item->unit_price = $precio;

$preference->items = [$item];

// Metadata para el webhook
$preference->metadata = [
    "user_id"   => $user_id,
    "plan"      => $plan,
    "user_type" => "professional",
];

$baseUrl = "https://turnos-pro-production.up.railway.app";

// URLs de retorno CORRECTAS
$preference->back_urls = [
    "success" => $baseUrl . "/pro/pago-exitoso-sus.php",
    "failure" => $baseUrl . "/pro/pago-fallido-sus.php",
    "pending" => $baseUrl . "/pro/pago-pendiente-sus.php",
];

// Redirigir SIEMPRE, no solo cuando está aprobado
$preference->auto_return = "all";

// Webhook
$preference->notification_url = $baseUrl . "/webhooks/mercadopago.php";

// Guardar preferencia
$preference->save();

// Redirigir a MercadoPago
header("Location: " . $preference->init_point);
exit;