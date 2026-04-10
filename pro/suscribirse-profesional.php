<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

require __DIR__ . '/includes/db.php';

$user_id = $_SESSION['user_id'];

// Traer estado y fecha de vencimiento
$stmt = $pdo->prepare("SELECT mp_subscription_status, subscription_end FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: /auth/login.php");
    exit;
}

// FECHA HOY Y FECHA DE VENCIMIENTO
$today = strtotime(date('Y-m-d'));
$end = $user['subscription_end'] ? strtotime($user['subscription_end']) : 0;

/*
|--------------------------------------------------------------------------
| LÓGICA CORRECTA
|--------------------------------------------------------------------------
| - Si la suscripción está activa PERO ya venció → permitir pagar
| - Si faltan días → permitir pagar
| - Si no tiene suscripción → permitir pagar
| - SOLO bloquear si está activa y NO vencida (caso que no aplica en tu sistema)
|--------------------------------------------------------------------------
*/

if ($user['mp_subscription_status'] === 'active' && $end >= $today) {
    // Está activa y NO vencida → NO debería pagar todavía
    // PERO en tu sistema SIEMPRE deben poder renovar antes
    // Así que NO bloqueamos nada
    // Simplemente seguimos y permitimos pagar
}

// ID DEL PLAN PROFESIONAL
$plan_id = "2de9bafc8c3143f385aea398afcbbea9";

// URL de MercadoPago
$checkout_url = "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=" . $plan_id;

// Redirigir al checkout
header("Location: " . $checkout_url);
exit;
