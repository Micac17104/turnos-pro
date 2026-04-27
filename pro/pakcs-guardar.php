<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$name = trim($_POST['name']);
$total = intval($_POST['total_sessions']);
$price = floatval($_POST['price']);

$stmt = $pdo->prepare("
    INSERT INTO packs (owner_type, owner_id, name, total_sessions, price)
    VALUES ('professional', ?, ?, ?, ?)
");
$stmt->execute([$user_id, $name, $total, $price]);

header("Location: packs.php");
exit;
