<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../pro/includes/db.php';

$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    die("Usuario inválido");
}

$stmt = $pdo->prepare("
    UPDATE users
    SET 
        mp_preapproval_id = NULL,
        mp_subscription_status = 'inactive',
        is_active = 0,
        subscription_start = NULL,
        subscription_end = NULL
    WHERE id = ?
");
$stmt->execute([$user_id]);

header("Location: /admin/usuarios.php?reset=ok");
exit;
