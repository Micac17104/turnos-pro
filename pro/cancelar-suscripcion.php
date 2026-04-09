<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/db.php';
require __DIR__ . '/../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT mp_preapproval_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || empty($user['mp_preapproval_id'])) {
    die("No hay suscripción activa.");
}

SDK::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX");

try {
    $pre = Preapproval::find_by_id($user['mp_preapproval_id']);
    if ($pre && $pre->status !== "cancelled") {
        $pre->status = "cancelled";
        $pre->update();
    }
} catch (Exception $e) {}

// Desactivar en Aura
$stmt2 = $pdo->prepare("
    UPDATE users
    SET 
        is_active = 0,
        mp_subscription_status = 'inactive'
    WHERE id = ?
");
$stmt2->execute([$user_id]);

header("Location: /pro/suscripcion-cancelada.php");
exit;
