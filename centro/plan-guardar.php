<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'];
$nombre = trim($_POST['nombre']);
$total = intval($_POST['total_sesiones']);

$stmt = $pdo->prepare("
    INSERT INTO planes (client_id, center_id, nombre, total_sesiones)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$patient_id, $center_id, $nombre, $total]);

$plan_id = $pdo->lastInsertId();

// Crear sesiones
for ($i = 1; $i <= $total; $i++) {
    $stmt = $pdo->prepare("
        INSERT INTO plan_sesiones (plan_id, numero)
        VALUES (?, ?)
    ");
    $stmt->execute([$plan_id, $i]);
}

header("Location: planes-estetica.php?id=" . $patient_id);
exit;
