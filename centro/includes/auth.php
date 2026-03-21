<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || 
   !in_array($_SESSION['account_type'], ['center', 'secretary'])) {

    header("Location: /auth/login.php");
    exit;
}

// RUTA CORRECTA A LA DB
require __DIR__ . '/../../pro/includes/db.php';

$center_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT is_active, subscription_end FROM users WHERE id = ?");
$stmt->execute([$center_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: /auth/login.php");
    exit;
}

$today = strtotime(date('Y-m-d'));
$end   = strtotime($user['subscription_end']);

if ($end < $today || $user['is_active'] == 0) {
    header("Location: /centro/planes.php?expired=1");
    exit;
}