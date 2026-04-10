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

// Fecha de vencimiento
$vence = !empty($user['subscription_end']) ? strtotime($user['subscription_end']) : 0;

// Páginas permitidas
$allowed_pages = [
    'suscripcion-vencida.php',
    'planes.php',
    'pago-preferencia.php',
    'suscribirse-centro.php'
];

// NOMBRE REAL DEL ARCHIVO (SOLUCIÓN DEFINITIVA)
$current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Si está vencido y no está en las permitidas → redirige
if (($user['is_active'] != 1 || $vence < time()) && !in_array($current_page, $allowed_pages)) {
    header("Location: /centro/suscripcion-vencida.php");
    exit;
}
?>
