<?php
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../pro/includes/auth-centro.php';
require __DIR__ . '/../pro/includes/db.php';

$center_id  = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'];
$titulo     = trim($_POST['titulo']);
$descripcion= trim($_POST['descripcion']);
$fecha      = $_POST['fecha'];

$archivo = null;

if (!empty($_FILES['archivo']['name'])) {
    $dir = __DIR__ . '/../uploads/estudios/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $archivo = time() . "_" . basename($_FILES['archivo']['name']);
    move_uploaded_file($_FILES['archivo']['tmp_name'], $dir . $archivo);
}

$stmt = $pdo->prepare("
    INSERT INTO estudios_medicos (client_id, professional_id, titulo, descripcion, archivo, fecha, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$patient_id, $center_id, $titulo, $descripcion, $archivo, $fecha]);

header("Location: estudios.php?id=" . $patient_id);
exit;
