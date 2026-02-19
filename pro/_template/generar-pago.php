<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$appointment_id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
$stmt->execute([$appointment_id, $user_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT mp_access_token FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$token = $user['mp_access_token'];

if (!$token) {
    exit("El profesional no configuró Mercado Pago.");
}

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.mercadopago.com/checkout/preferences",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        "items" => [
            [
                "title" => "Turno médico",
                "quantity" => 1,
                "unit_price" => 10000 // después lo hacemos dinámico
            ]
        ],
        "back_urls" => [
            "success" => "http://localhost/turnos-pro/profiles/$user_id/pago-exitoso.php?id=$appointment_id"
        ]
    ]),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
$link = $data['init_point'] ?? null;

if ($link) {
    $stmt = $pdo->prepare("UPDATE appointments SET payment_link = ? WHERE id = ?");
    $stmt->execute([$link, $appointment_id]);
}

echo "<h2>Link de pago generado:</h2>";
echo "<a href='$link' target='_blank'>$link</a>";