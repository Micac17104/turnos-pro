<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT is_active, subscription_end FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$today = date('Y-m-d');

// Si está inactivo o vencido → bloquear
if ($user['is_active'] == 0 || ($user['subscription_end'] && $user['subscription_end'] < $today)) {
    header("Location: /planes.php?inactivo=1");
    exit;
}
