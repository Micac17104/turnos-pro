<?php
// centro/callback-centro.php

require __DIR__ . '/../pro/includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

// Log opcional
file_put_contents(__DIR__ . "/callback-centro-log.txt", print_r($data, true), FILE_APPEND);

// Validar evento
if (!isset($data["type"]) || $data["type"] !== "preapproval") {
    http_response_code(200);
    exit;
}

$subscription_id = $data["data"]["id"] ?? null;

if (!$subscription_id) {
    http_response_code(200);
    exit;
}

// Buscar info de la suscripción
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.mercadopago.com/preapproval/" . $subscription_id,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297"
    ]
]);

$response = curl_exec($ch);
curl_close($ch);

$sub = json_decode($response, true);

$user_id = $sub["external_reference"] ?? null;

if ($user_id) {
    $stmt = $pdo->prepare("
        UPDATE users 
        SET mp_subscription_status = 'active',
            mp_preapproval_id = ?
        WHERE id = ?
    ");
    $stmt->execute([$subscription_id, $user_id]);
}

http_response_code(200);