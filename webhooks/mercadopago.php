<?php
require __DIR__ . '/../pro/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

MercadoPago\SDK::setAccessToken("APP_USR-936741788731989-031211-5eed533a498e365afb70fd29c65ad0bc-3260786753");

$body = file_get_contents("php://input");
$data = json_decode($body, true);

if (!isset($data["data"]["id"])) {
    http_response_code(400);
    exit;
}

$payment_id = $data["data"]["id"];

$payment = MercadoPago\Payment::find_by_id($payment_id);

if (!$payment || $payment->status !== "approved") {
    http_response_code(200);
    exit;
}

$user_id = $payment->metadata->user_id ?? null;
$plan    = $payment->metadata->plan ?? null;

if (!$user_id) {
    http_response_code(200);
    exit;
}

$stmt = $pdo->prepare("SELECT subscription_end FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$today = strtotime(date('Y-m-d'));
$end   = $user && $user['subscription_end'] ? strtotime($user['subscription_end']) : 0;

if ($end > $today) {
    $new_end = date('Y-m-d', strtotime($user['subscription_end'] . ' +1 month'));
} else {
    $new_end = date('Y-m-d', strtotime('+1 month'));
}

$stmt2 = $pdo->prepare("
    UPDATE users
    SET subscription_start = IF(subscription_start IS NULL, CURDATE(), subscription_start),
        subscription_end   = ?,
        is_active          = 1,
        last_payment       = CURDATE()
    WHERE id = ?
");
$stmt2->execute([$new_end, $user_id]);

http_response_code(200);