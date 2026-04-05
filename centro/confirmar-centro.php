<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../pro/includes/db.php';

// Validar que el centro esté logueado
$center_id = $_SESSION['user_id'] ?? null;

if (!$center_id || ($_SESSION['account_type'] !== 'center' && $_SESSION['account_type'] !== 'secretary')) {
    header("Location: /auth/login.php");
    exit;
}

// Mercado Pago devuelve el preapproval_id en GET
$preapproval_id = $_GET['preapproval_id'] ?? null;

if (!$preapproval_id) {
    echo "<h1>Error</h1>";
    echo "<p>No se recibió el ID de suscripción.</p>";
    exit;
}

// Guardar el preapproval_id sin validar email del pagador
$stmt = $pdo->prepare("
    UPDATE users
    SET mp_preapproval_id = ?, mp_subscription_status = 'active'
    WHERE id = ?
");
$stmt->execute([$preapproval_id, $center_id]);

// Redirigir al mensaje de éxito
header("Location: /centro/suscripcion-exitosa.php");
exit;
