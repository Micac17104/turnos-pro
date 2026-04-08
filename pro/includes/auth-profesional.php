<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || $_SESSION['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

$allowed = [
    'planes.php',
    'pago-preferencia-sus.php',
    'suscripcion-vencida.php',
    'pago-exitoso.php'
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
    header("Location: /pro/suscripcion-vencida.php");
    exit;
}
