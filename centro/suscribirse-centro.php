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

// 🔥 DOS PLANES PARA CENTROS
$links = [
    1 => "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=bc987ac37a2e491cb23f87ea9b7b8540", // Plan hasta 4 profesionales
    2 => "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=297c7d39a7d247f38ff65790644e9333"  // Plan adicional (el que vos quieras)
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
