<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| 1) Validar que la sesión exista
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user_id']) || !isset($_SESSION['account_type'])) {
    header("Location: /auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$account_type = $_SESSION['account_type'];

/*
|--------------------------------------------------------------------------
| 2) Solo profesionales pueden acceder al panel /pro
|--------------------------------------------------------------------------
*/
if ($account_type !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| 3) Permitir acceso a páginas de pago aunque la cuenta esté inactiva
|--------------------------------------------------------------------------
|
| IMPORTANTE:
| Agregamos "suscribirse-pro.php" porque tu botón apunta a:
| /pro/suscribirse-pro.php?plan=1
|
| basename() devuelve SOLO "suscribirse-pro.php"
|--------------------------------------------------------------------------
*/
$allowed_pages = [
    'planes.php',
    'suscribirse-pro.php',   // ← ESTA ES LA CLAVE
    'pago-preferencia-sus.php',
    'pago-exitoso-sus.php',
    'pago-fallido-sus.php',
    'pago-pendiente-sus.php'
];

$current_page = basename($_SERVER['PHP_SELF']);

if (in_array($current_page, $allowed_pages)) {
    return; // permitir acceso sin restricciones
}

require __DIR__ . '/../includes/db.php';

/*
|--------------------------------------------------------------------------
| 4) Obtener datos del usuario
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
| 5) Validar suscripción
|--------------------------------------------------------------------------
*/
$today = strtotime(date('Y-m-d'));
$end = $user['subscription_end'] ? strtotime($user['subscription_end']) : 0;

if ($end < $today || $user['is_active'] == 0) {
    header("Location: /pro/planes.php?expired=1");
    exit;
}
