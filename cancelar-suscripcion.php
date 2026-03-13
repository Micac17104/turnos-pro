<?php
session_start();
require __DIR__ . '/pro/includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$account_type = $_SESSION['account_type'] ?? null;

if (!$user_id || !$account_type) {
    header("Location: /auth/login.php");
    exit;
}

$stmt = $pdo->prepare("
    UPDATE users
    SET subscription_end = CURDATE(),
        is_active = 0
    WHERE id = ?
");
$stmt->execute([$user_id]);

if ($account_type === 'professional') {
    header("Location: /pro/dashboard.php?cancelada=1");
    exit;
}

if ($account_type === 'center' || $account_type === 'secretary') {
    header("Location: /centro/centro-dashboard.php?cancelada=1");
    exit;
}

header("Location: /auth/login.php");
exit;