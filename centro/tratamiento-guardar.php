<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/db.php';

require __DIR__ . '/../pro/includes/auth-centro.php';

$center_id = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'] ?? null;

if (!$patient_id) {
    die("Paciente inválido.");
}

$tratamiento = trim($_POST['tratamiento']);
$professional_id = $_POST['professional_id'] ?: null;
$productos = trim($_POST['productos'] ?? '');
$parametros = trim($_POST['parametros'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');

$stmt = $pdo->prepare("
    INSERT INTO tratamientos
    (client_id, center_id, professional_id, tratamiento, productos, parametros, observaciones)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $patient_id, $center_id, $professional_id,
    $tratamiento, $productos, $parametros, $observaciones
]);

header("Location: tratamientos.php?id=" . $patient_id . "&ok=1");
exit;
