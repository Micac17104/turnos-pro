<?php
require 'config.php';

$input = json_decode(file_get_contents("php://input"), true);

if ($input['type'] === 'payment') {
    $payment_id = $input['data']['id'];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.mercadopago.com/v1/payments/$payment_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer TU_ACCESS_TOKEN_GLOBAL"
        ]
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);

    if ($data['status'] === 'approved') {
        $external_reference = $data['external_reference'];

        $stmt = $pdo->prepare("UPDATE appointments SET payment_status = 'pagado' WHERE id = ?");
        $stmt->execute([$external_reference]);
    }
}