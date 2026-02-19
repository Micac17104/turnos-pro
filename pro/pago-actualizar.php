<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$turno_id       = require_param($_POST, 'turno_id');
$payment_status = trim($_POST['payment_status'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');

// Validar turno
$stmt = $pdo->prepare("
    SELECT id FROM appointments
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$turno_id, $user_id]);
if (!$stmt->fetch()) {
    die("No tienes permiso para editar este pago.");
}

// Actualizar
$stmt = $pdo->prepare("
    UPDATE appointments
    SET payment_status = ?, payment_method = ?
    WHERE id = ?
");
$stmt->execute([$payment_status, $payment_method, $turno_id]);

redirect('/turnos-pro/pro/agenda.php?pay=1');