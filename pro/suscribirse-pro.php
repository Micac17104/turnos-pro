<?php
// /pro/suscribirse-pro.php

session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// PLAN POR GET
if (!isset($_GET['plan'])) {
    die("Plan inválido");
}

$plan = (int) $_GET['plan'];

// Podés ampliar esto después
$precios = [
    1 => 8000,
];

if (!isset($precios[$plan])) {
    die("Plan no encontrado");
}

$precio = (float)$precios[$plan];

// Traer datos del usuario
$stmt = $pdo->prepare("SELECT email, mp_preapproval_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: /auth/login.php");
    exit;
}

SDK::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX"); // tu token

$baseUrl = "https://www.turnosaura.com";

// SI ES GET → mostrar formulario para email del pagador
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Suscribirse - Profesional</title>
    </head>
    <body>
        <h1>Suscripción profesional - Plan <?= htmlspecialchars($plan) ?></h1>
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

// SI ES POST → crear suscripción
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
    } catch (Exception $e) {
        // podés loguear si querés
    }
}

try {
    $preapproval = new Preapproval();
    $preapproval->payer_email = $payer_email;
    $preapproval->back_url = $baseUrl . "/pro/pago-exitoso-sus.php";
    $preapproval->reason = "Suscripción mensual profesional - Plan $plan";
    $preapproval->external_reference = (string)$user_id;

    $preapproval->auto_recurring = [
        "frequency" => 1,
        "frequency_type" => "months",
        "transaction_amount" => $precio,
        "currency_id" => "ARS",
        "payment_type_id" => "credit_card" // forzamos tarjeta
    ];

    $saved = $preapproval->save();

    if ($saved && isset($preapproval->id) && isset($preapproval->init_point)) {

        // NO activamos acá. De eso se encarga el webhook.
        $stmt2 = $pdo->prepare("
            UPDATE users
            SET 
                mp_preapproval_id = ?,
                mp_subscription_status = 'pending'
            WHERE id = ?
        ");
        $stmt2->execute([$preapproval->id, $user_id]);

        header("Location: " . $preapproval->init_point);
        exit;

    } else {
        die("No se pudo crear la suscripción. Intentalo más tarde.");
    }

} catch (Exception $e) {
    die("Error al procesar la suscripción.");
}
