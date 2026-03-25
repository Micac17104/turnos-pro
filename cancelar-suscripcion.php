<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/pro/includes/db.php';
require '/app/vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("No estás logueado.");
}

$stmt = $pdo->prepare("SELECT mp_preapproval_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || empty($user['mp_preapproval_id'])) {
    die("No tenés una suscripción activa.");
}

// TOKEN DE PRODUCCIÓN
SDK::setAccessToken("APP_USR-2199782378550930-031211-bfa15acd1e956caebb1a5640da125884-745664297");

try {
    $preapproval = Preapproval::find_by_id($user['mp_preapproval_id']);

    if ($preapproval && isset($preapproval->status) && $preapproval->status !== "cancelled") {
        $preapproval->status = "cancelled";
        $preapproval->update();
    }

    // Actualizar base
    $stmt2 = $pdo->prepare("
        UPDATE users
        SET mp_preapproval_id = NULL,
            mp_subscription_status = 'inactive'
        WHERE id = ?
    ");
    $stmt2->execute([$user_id]);

    echo "Suscripción cancelada correctamente.";

} catch (Exception $e) {
    die("Error al cancelar: " . $e->getMessage());
}