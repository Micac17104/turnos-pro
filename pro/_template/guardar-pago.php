<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$appointment_id = $_POST['appointment_id'];
$status = $_POST['payment_status'];
$method = $_POST['payment_method'];

$stmt = $pdo->prepare("
    UPDATE appointments
    SET payment_status = ?, payment_method = ?
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$status, $method, $appointment_id, $user_id]);

header("Location: /turnos-pro/profiles/$user_id/dashboard.php?pagook=1");
exit;