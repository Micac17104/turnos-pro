<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo profesionales
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'professional') {
    header("Location: /auth/login.php");
    exit;
}

require __DIR__ . '/../includes/db.php';

$user_id = $_SESSION['user_id'];

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT is_active, subscription_end FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe el usuario
if (!$user) {
    session_destroy();
    header("Location: /auth/login.php");
    exit;
}

// Fecha actual
$today = strtotime(date('Y-m-d'));
$end   = strtotime($user['subscription_end']);

// Si la suscripción está vencida por fecha
if ($end < $today) {
    echo "<h2 style='text-align:center;margin-top:50px;font-family:sans-serif'>
            Tu suscripción venció el {$user['subscription_end']}<br><br>
            Por favor realizá el pago para continuar usando el sistema.
          </h2>";
    exit;
}

// Si la suscripción está marcada como inactiva
if ($user['is_active'] == 0) {
    echo "<h2 style='text-align:center;margin-top:50px;font-family:sans-serif'>
            Tu suscripción venció el {$user['subscription_end']}<br><br>
            Por favor realizá el pago para continuar usando el sistema.
          </h2>";
    exit;
}