<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

require __DIR__ . '/../includes/db.php';

$user_id = $_SESSION['user_id'];

// Verificar suscripción activa
$stmt = $pdo->prepare("SELECT is_active, subscription_end FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe el usuario (raro pero seguro)
if (!$user) {
    session_destroy();
    header("Location: /auth/login.php");
    exit;
}

// Si la suscripción está vencida
if ($user['is_active'] == 0) {
    echo "<h2 style='text-align:center;margin-top:50px;font-family:sans-serif'>
            Tu suscripción venció el {$user['subscription_end']}<br><br>
            Por favor realizá el pago para continuar usando el sistema.
          </h2>";
    exit;
}