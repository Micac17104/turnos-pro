<?php
// pro/crear-plan-profesional.php

// SOLO EJECUTAR UNA VEZ

$accessToken = "APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297";

$body = [
    "reason" => "Suscripción mensual profesional - TurnosAura",
    "auto_recurring" => [
        "frequency" => 1,
        "frequency_type" => "months",
        "transaction_amount" => 8000,
        "currency_id" => "ARS"
    ],
    "back_url" => "https://www.turnosaura.com/pro/dashboard.php",
    "external_reference" => "plan-profesional-mensual"
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.mercadopago.com/preapproval_plan",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ],
    CURLOPT_POSTFIELDS => json_encode($body)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    "http_code" => $httpCode,
    "error" => $error ?: null,
    "response" => json_decode($response, true)
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);