<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../../pro/includes/db.php';

// Si no está logueado, afuera
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

// Traer datos del usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe el usuario, afuera
if (!$user) {
    session_destroy();
    header("Location: /auth/login.php");
    exit;
}

// Si no es centro, afuera
if ($user['account_type'] !== 'center') {
    header("Location: /auth/login.php");
    exit;
}

/* ---------------------------
   FIX: evitar warnings de strtotime()
--------------------------- */

$vence = !empty($user['subscription_end']) ? strtotime($user['subscription_end']) : 0;

/* ---------------------------------------------------------
   PERMITIR ESTAS PÁGINAS AUNQUE LA SUSCRIPCIÓN ESTÉ VENCIDA
--------------------------------------------------------- */
$allowed_pages = [
    '/centro/suscripcion-vencida.php',
    '/centro/planes.php',
    '/centro/pago-preferencia.php',
    '/centro/suscribirse-centro.php'
];

$current_page = $_SERVER['PHP_SELF'];

/* ---------------------------------------------------------
   SI ESTÁ VENCIDO Y NO ESTÁ EN LAS PÁGINAS PERMITIDAS → REDIRIGE
--------------------------------------------------------- */
if (($user['is_active'] != 1 || $vence < time()) && !in_array($current_page, $allowed_pages)) {
    header("Location: /centro/suscripcion-vencida.php");
    exit;
}
?>
