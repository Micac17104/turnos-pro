<?php
// centro/suscribirse-centro.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'center') {
    header("Location: /auth/login.php");
    exit;
}

if (!isset($_GET['plan'])) {
    die("Plan inválido");
}

$plan = (int) $_GET['plan'];

$plan_ids = [
    1 => "7f1ce42b25614fbf82a8de0cd86634d7",
    2 => "c72a2d4ced534679a0c48f8bb84cd732",
    3 => "03243356a81d4ba3b38ccfcc8f44c7d8",
    4 => "e1ae4c66754c4f698861d36232bf4d75",
    5 => "e5926fccaa294ee28b91f0b7dcae5814"
];

if (!isset($plan_ids[$plan])) {
    die("Plan no encontrado");
}

$plan_id = $plan_ids[$plan];

// Checkout oficial de suscripción
$checkout_url = "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=" . $plan_id;

// Redirigir al checkout
header("Location: " . $checkout_url);
exit;