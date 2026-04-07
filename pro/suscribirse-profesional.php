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

// Verificar si ya tiene suscripción activa
$stmt = $pdo->prepare("SELECT mp_subscription_status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $user['mp_subscription_status'] === 'active') {
    die("Ya tenés una suscripción activa.");
}

// ID DEL PLAN PROFESIONAL
$plan_id = "2de9bafc8c3143f385aea398afcbbea9";

$checkout_url = "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=" . $plan_id;

header("Location: " . $checkout_url);
exit;
