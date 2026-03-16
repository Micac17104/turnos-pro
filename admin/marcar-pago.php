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

// Si subscription_end es NULL o vacío, usar hoy
$subscription_end = $user['subscription_end'] ?: date('Y-m-d');

$start = strtotime($subscription_end);
$today = strtotime(date('Y-m-d'));

// Calcular nueva fecha
if ($start > $today) {
    $new_end = date('Y-m-d', strtotime($subscription_end . ' +1 month'));
} else {
    $new_end = date('Y-m-d', strtotime('+1 month'));
}

// Actualizar
$stmt2 = $pdo->prepare("
    UPDATE users
    SET subscription_end = ?, is_active = 1, last_payment = CURDATE()
    WHERE id = ?
");

$stmt2->execute([$new_end, $id]);

header("Location: pagos.php?ok=1");
exit;