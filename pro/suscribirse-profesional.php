<?php
// pro/suscribirse-profesional.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

// ID DEL PLAN PROFESIONAL (el que te devolvió Mercado Pago)
$plan_id = "2de9bafc8c3143f385aea398afcbbea9";

// Checkout oficial de suscripción
$checkout_url = "https://www.mercadopago.com.ar/subscriptions/checkout?preapproval_plan_id=" . $plan_id;

// Redirigir al checkout
header("Location: " . $checkout_url);
exit;