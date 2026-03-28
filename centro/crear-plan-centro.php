<?php
// centro/crear-plan-centro.php
// SOLO EJECUTAR UNA VEZ

$accessToken = "APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297";

$planes = [
    1 => 8000,
    2 => 13000,
    3 => 18000,
    4 => 23000,
    5 => 28000
];

$results = [];

foreach ($planes as $num => $precio) {

    $body = [
        "reason" => "Suscripción mensual centro - Plan $num profesionales",
        "auto_recurring" => [
            "frequency" => 1,
            "frequency_type" => "months",
            "transaction_amount" => $precio,
            "currency_id" => "ARS"
        ],
        "back_url" => "https://www.turnosaura.com/centro/centro-dashboard.php",
        "external_reference" => "plan-centro-mensual-$num"
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

    $results[$num] = [
        "http_code" => $httpCode,
        "error" => $error ?: null,
        "response" => json_decode($response, true)
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);