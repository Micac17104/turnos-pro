<?php
// /centro/suscribirse-centro.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'center') {
    header("Location: /auth/login.php");
    exit;
}

$center_id = (int)$_SESSION['user_id'];

if (!isset($_GET['plan'])) {
    die("Plan inválido");
}

$plan = (int) $_GET['plan'];

// 🔥 LINKS DIRECTOS DE MERCADO PAGO (sin API)
$links = [
    1 => "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=7f1ce42b25614fbf82a8de0cd86634d7",
    2 => "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=c72a2d4ced534679a0c48f8bb84cd732",
    3 => "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=03243356a81d4ba3b38ccfcc8f44c7d8",
    4 => "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=e1ae4c66754c4f698861d36232bf4d75",
    5 => "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=e5926fccaa294ee28b91f0b7dcae5814"
];

if (!isset($links[$plan])) {
    die("Plan no encontrado");
}

// Guardar el plan elegido (opcional)
$stmt = $pdo->prepare("UPDATE users SET chosen_plan = ? WHERE id = ?");
$stmt->execute([$plan, $center_id]);

// Redirigir directo al checkout de Mercado Pago
header("Location: " . $links[$plan]);
exit;
