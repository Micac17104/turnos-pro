<?php
// /centro/suscribirse-centro.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'center') {
    header("Location: /auth/login.php");
    exit;
}

$center_id = (int)$_SESSION['user_id'];

if (!isset($_GET['plan'])) {
    die("Plan inválido");
}

$plan = (int) $_GET['plan'];

// Podés ajustar precios por plan
$precios = [
    1 => 12000,
    2 => 18000,
    3 => 24000,
    4 => 30000,
    5 => 36000,
];

if (!isset($precios[$plan])) {
    die("Plan no encontrado");
}

$precio = (float)$precios[$plan];

$stmt = $pdo->prepare("SELECT email, mp_preapproval_id FROM users WHERE id = ?");
$stmt->execute([$center_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: /auth/login.php");
    exit;
}

SDK::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX");

$baseUrl = "https://www.turnosaura.com";

// GET → formulario email pagador
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Suscribirse - Centro</title>
    </head>
    <body>
        <h1>Suscripción centro - Plan <?= htmlspecialchars($plan) ?></h1>
        <p>Precio: $<?= number_format($precio, 0, ',', '.') ?></p>

        <form method="post">
            <label>
                Email del pagador (cuenta de Mercado Pago):
                <input type="email" name="payer_email" required value="<?= htmlspecialchars($user['email']) ?>">
            </label>
            <button type="submit">Continuar al pago</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// POST → crear suscripción
$payer_email = trim($_POST['payer_email'] ?? '');

if (!$payer_email) {
    die("Falta el email del pagador.");
}

// Cancelar suscripción anterior si existe
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
    $preapproval->payer_email = $payer_email;
    $preapproval->back_url = $baseUrl . "/centro/pago-exitoso-sus.php";
    $preapproval->reason = "Suscripción mensual centro - Plan $plan";
    $preapproval->external_reference = (string)$center_id;

    $preapproval->auto_recurring = [
        "frequency" => 1,
        "frequency_type" => "months",
        "transaction_amount" => $precio,
        "currency_id" => "ARS",
        "payment_type_id" => "credit_card"
    ];

    $saved = $preapproval->save();

    if ($saved && isset($preapproval->id) && isset($preapproval->init_point)) {

        $stmt2 = $pdo->prepare("
            UPDATE users
            SET 
                mp_preapproval_id = ?,
                mp_subscription_status = 'pending'
            WHERE id = ?
        ");
        $stmt2->execute([$preapproval->id, $center_id]);

        header("Location: " . $preapproval->init_point);
        exit;

    } else {
        die("No se pudo crear la suscripción. Intentalo más tarde.");
    }

} catch (Exception $e) {
    die("Error al procesar la suscripción.");
}
