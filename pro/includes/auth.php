<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header("Location: /auth/logout.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

/*
|--------------------------------------------------------------------------
| 1) Permitir acceso a páginas de pago aunque la cuenta esté inactiva
|--------------------------------------------------------------------------
*/
$allowed_pages = [
    'planes.php',
    'pago-preferencia-sus.php',
    'pago-exitoso-sus.php',
    'pago-fallido-sus.php',
    'pago-pendiente-sus.php'
];

$current_page = basename($_SERVER['PHP_SELF']);

if (in_array($current_page, $allowed_pages)) {
    return;
}

/*
|--------------------------------------------------------------------------
| 2) Solo profesionales pueden acceder al panel
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user']['id']) || $_SESSION['user']['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

require __DIR__ . '/../includes/db.php';

$user_id = $_SESSION['user']['id'];

/*
|--------------------------------------------------------------------------
| 3) Obtener datos del usuario
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT is_active, subscription_end FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: /auth/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| 4) Validar suscripción
|--------------------------------------------------------------------------
*/
$today = strtotime(date('Y-m-d'));
$end   = strtotime($user['subscription_end']);

if ($end < $today || $user['is_active'] == 0) {
    header("Location: /pro/planes.php?expired=1");
    exit;
}
