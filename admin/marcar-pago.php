<?php
require __DIR__ . '/auth-admin.php';
require __DIR__ . '/../pro/includes/db.php';

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

// Calcular nueva fecha
$start = strtotime($user['subscription_end']);
$today = strtotime(date('Y-m-d'));

if ($start > $today) {
    // Si aún no venció, sumar desde la fecha actual de vencimiento
    $new_end = date('Y-m-d', strtotime($user['subscription_end'] . ' +1 month'));
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

header("Location: suscripciones.php?ok=1");
exit;