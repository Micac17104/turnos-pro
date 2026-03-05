<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$user_id = $_SESSION['user_id']; // Necesario para el WHERE

$turno_id       = require_param($_POST, 'turno_id');
$payment_status = trim($_POST['payment_status'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');
$amount         = trim($_POST['amount'] ?? null); // ← FALTABA ESTO

// Validar turno
$stmt = $pdo->prepare("
    SELECT id FROM appointments
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$turno_id, $user_id]);
if (!$stmt->fetch()) {
    die("No tienes permiso para editar este pago.");
}

// Actualizar pago COMPLETO
$stmt = $pdo->prepare("
    UPDATE appointments
    SET payment_status = ?, payment_method = ?, amount = ?
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$payment_status, $payment_method, $amount, $turno_id, $user_id]);

redirect('agenda.php?pay=1');
