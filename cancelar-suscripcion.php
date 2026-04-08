<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/pro/includes/db.php';
require __DIR__ . '/vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preapproval;

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: /auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT mp_preapproval_id, account_type FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || empty($user['mp_preapproval_id'])) {
    die("No tenés una suscripción activa.");
}

$preapproval_id = $user['mp_preapproval_id'];

SDK::setAccessToken("APP_USR-XXXXXXXXXXXXXXXXXXXXXXXXXXXX");

try {
    $pre = Preapproval::find_by_id($preapproval_id);

    if ($pre && $pre->status !== "cancelled") {
        $pre->status = "cancelled";
        $pre->update();
    }

} catch (Exception $e) {
    // si querés loguear, lo hacés acá
}

$stmt2 = $pdo->prepare("
    UPDATE users
    SET 
        mp_preapproval_id = NULL,
        mp_subscription_status = 'inactive',
        is_active = 0
    WHERE id = ?
");
$stmt2->execute([$user_id]);

if ($user['account_type'] === 'center') {
    header("Location: /centro/suscripcion-vencida.php");
} else {
    header("Location: /pro/suscripcion-vencida.php");
}
exit;
