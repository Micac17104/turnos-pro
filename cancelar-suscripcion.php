<?php
session_start();
require __DIR__ . '/pro/includes/db.php';
require __DIR__ . '/vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

$user_id = $_SESSION['user_id'] ?? null;
$account_type = $_SESSION['account_type'] ?? null;

if (!$user_id || !$account_type) {
    header("Location: /auth/login.php");
    exit;
}

// Obtener preapproval_id
$stmt = $pdo->prepare("SELECT mp_preapproval_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$preapproval_id = $user['mp_preapproval_id'] ?? null;

// Cancelar en Mercado Pago SOLO si existe preapproval_id
if (!empty($preapproval_id)) {

    SDK::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX");

    $preapproval = Preapproval::find_by_id($preapproval_id);

    if ($preapproval && isset($preapproval->status)) {
        $preapproval->status = "cancelled";
        $preapproval->update();
    }
}

// Cancelar en tu base
$stmt2 = $pdo->prepare("
    UPDATE users
    SET 
        is_active = 0,
        subscription_end = CURDATE(),
        mp_subscription_status = 'cancelled',
        mp_preapproval_id = NULL
    WHERE id = ?
");
$stmt2->execute([$user_id]);

session_destroy();

header("Location: /auth/login.php?cancelada=1");
exit;