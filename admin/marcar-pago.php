<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

// Incluir header y sidebar para que no desaparezca el menú
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';

if (!isset($_GET['id'])) {
    die("ID inválido");
}

$id = intval($_GET['id']);

// Obtener usuario
$stmt = $pdo->prepare("SELECT subscription_end FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuario no encontrado");
}

// Si subscription_end es NULL o vacío, usar hoy como base
$subscription_end = $user['subscription_end'];

if (!$subscription_end) {
    $subscription_end = date('Y-m-d');
}

$start = strtotime($subscription_end);
$today = strtotime(date('Y-m-d'));

// Calcular nueva fecha
if ($start > $today) {
    // Si aún no venció, sumar desde la fecha actual de vencimiento
    $new_end = date('Y-m-d', strtotime($subscription_end . ' +1 month'));
} else {
    // Si ya venció, sumar desde hoy
    $new_end = date('Y-m-d', strtotime('+1 month'));
}

// Actualizar
$stmt2 = $pdo->prepare("
    UPDATE users
    SET subscription_end = ?, is_active = 1, last_payment = CURDATE()
    WHERE id = ?
");

$stmt2->execute([$new_end, $id]);

// Redirigir sin errores
header("Location: suscripciones.php?ok=1");
exit;
?>