<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../../pro/includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || $_SESSION['account_type'] !== 'center') {
    header("Location: /auth/login.php");
    exit;
}

$allowed = [
    'planes.php',
    'pago-preferencia.php',
    'suscripcion-vencida.php'
];

$current = basename($_SERVER['PHP_SELF']);

if (in_array($current, $allowed)) {
    return;
}

$stmt = $pdo->prepare("
    SELECT mp_subscription_status, is_active 
    FROM users WHERE id=?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['mp_subscription_status'] !== 'active' || $user['is_active'] == 0) {
    header("Location: /centro/suscripcion-vencida.php");
    exit;
}
