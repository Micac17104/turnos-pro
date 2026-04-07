<?php
// Asegurar que no haya doble sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: /auth/login.php");
    exit;
}

// Páginas permitidas sin suscripción activa
$allowed = [
    'planes.php',
    'pago-preferencia-sus.php',
    'suscripcion-vencida.php',
    'pago-exitoso.php',
    'pago-fallido.php',
    'pago-pendiente.php'
];

$current = basename($_SERVER['PHP_SELF']);

if (in_array($current, $allowed)) {
    return;
}

$stmt = $pdo->prepare("
    SELECT mp_subscription_status, subscription_end, is_active 
    FROM users WHERE id=?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$today = date('Y-m-d');

// Bloqueo por suscripción inactiva, vencida o suspendida
if (
    !$user ||
    $user['mp_subscription_status'] !== 'active' ||
    $user['subscription_end'] < $today ||
    $user['is_active'] == 0
) {
    header("Location: /pro/suscripcion-vencida.php");
    exit;
}
