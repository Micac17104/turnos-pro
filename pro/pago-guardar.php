<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$id     = $_POST['id'];
$status = $_POST['payment_status'];
$method = $_POST['payment_method'];
$amount = $_POST['amount'] ?: null;

$stmt = $pdo->prepare("
    UPDATE appointments
    SET payment_status = ?, payment_method = ?, amount = ?
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$status, $method, $amount, $id, $user_id]);

// Redirección corregida (ruta relativa)
redirect("pagos.php?ok=1");