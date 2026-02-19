<?php
require __DIR__ . '/includes/db.php';

// Validar parámetros mínimos
if (!isset($_GET['pro_id']) || !isset($_GET['payment_id']) || !isset($_GET['status'])) {
    echo "<h1>Error</h1>";
    echo "<p>Datos incompletos. No se puede validar el pago.</p>";
    exit;
}

$pro_id = $_GET['pro_id'];
$payment_id = $_GET['payment_id'];
$status = $_GET['status'];

// Solo activar si Mercado Pago devolvió "approved"
if ($status !== 'approved') {
    echo "<h1>Pago no aprobado</h1>";
    echo "<p>Mercado Pago no confirmó el pago.</p>";
    exit;
}

// Activar plan
$stmt = $pdo->prepare("UPDATE users SET plan_activo = 1 WHERE id = ?");
$stmt->execute([$pro_id]);

echo "<h1>Pago aprobado</h1>";
echo "<p>Tu plan ya está activo.</p>";
echo "<a href='/turnos-pro/pro/dashboard.php'>Volver al panel</a>";