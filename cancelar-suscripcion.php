<?php
session_start();
require __DIR__ . '/pro/includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$account_type = $_SESSION['account_type'] ?? null;

if (!$user_id || !$account_type) {
    header("Location: /auth/login.php");
    exit;
}

// Cancelar suscripción
$stmt = $pdo->prepare("
    UPDATE users
    SET 
        subscription_end = CURDATE(),
        is_active = 0
    WHERE id = ?
");
$stmt->execute([$user_id]);

// Cerrar sesión para bloquear acceso inmediato
session_destroy();

// Redirigir a login con mensaje
header("Location: /auth/login.php?cancelada=1");
exit;