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

// Si la suscripción está vencida → redirigir a la pantalla del centro
if ($user['is_active'] != 1 || $vence < time()) {
    header("Location: /centro/suscripcion-vencida.php");
    exit;
}
?>
