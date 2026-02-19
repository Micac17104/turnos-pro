<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/../vendor/autoload.php'; // ← RUTA CORREGIDA

// TU ACCESS TOKEN DE MERCADO PAGO
MercadoPago\SDK::setAccessToken("TU_ACCESS_TOKEN_AQUI");

// Datos del profesional
$pro_id = $user_id; // viene del login del profesional
$pro_name = $user['name'];

// Crear preferencia
$preference = new MercadoPago\Preference();

$item = new MercadoPago\Item();
$item->title = "Plan Profesional - Turnos PRO";
$item->quantity = 1;
$item->unit_price = 5000; // precio en ARS

$preference->items = [$item];

// URLs de retorno
$preference->back_urls = [
    "success" => "http://localhost/turnos-pro/pro/pago-exitoso.php?pro_id=$pro_id",
    "failure" => "http://localhost/turnos-pro/pro/pago-fallido.php",
    "pending" => "http://localhost/turnos-pro/pro/pago-pendiente.php"
];

$preference->auto_return = "approved";

$preference->save();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pagar plan</title>
</head>
<body>

<h1>Plan Profesional</h1>
<p>Precio: $5000</p>

<a href="<?= $preference->init_point ?>"
   class="px-4 py-2 bg-sky-600 text-white rounded-lg">
    Pagar con Mercado Pago
</a>

</body>
</html>