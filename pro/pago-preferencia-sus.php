<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || $_SESSION['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

// Traer email
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

MercadoPago\SDK::setAccessToken("APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297");

$baseUrl = "https://www.turnosaura.com";

/*
|--------------------------------------------------------------------------
| 1) Cancelar suscripción previa si existe
|--------------------------------------------------------------------------
*/
if (!empty($user['mp_preapproval_id'])) {
    $old = MercadoPago\Preapproval::find_by_id($user['mp_preapproval_id']);
    if ($old && isset($old->status) && $old->status !== "cancelled") {
        $old->status = "cancelled";
        $old->update();
    }
}

/*
|--------------------------------------------------------------------------
| 2) Crear nueva suscripción automática
|--------------------------------------------------------------------------
*/
$preapproval = new MercadoPago\Preapproval();
$preapproval->payer_email = $user['email'];
$preapproval->back_url = $baseUrl . "/pro/pago-exitoso-sus.php";
$preapproval->reason = "Suscripción mensual profesional - Plan $plan";
$preapproval->external_reference = (string)$user_id;

$preapproval->auto_recurring = [
    "frequency" => 1,
    "frequency_type" => "months",
    "transaction_amount" => $precio,
    "currency_id" => "ARS"
];

if ($preapproval->save()) {

    // Guardar nuevo preapproval_id
    $stmt2 = $pdo->prepare("
        UPDATE users
        SET mp_preapproval_id = ?, mp_subscription_status = 'active'
        WHERE id = ?
    ");
    $stmt2->execute([$preapproval->id, $user_id]);

    header("Location: " . $preapproval->init_point);
    exit;

} else {
    die("No se pudo crear la suscripción. Intentalo más tarde.");
}