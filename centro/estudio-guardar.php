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

    // Carpeta donde realmente guardás los archivos
    $dir = __DIR__ . '/../public/uploads/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    // Nombre original
    $original = $_FILES['archivo']['name'];

    // Limpieza del nombre (sin espacios, acentos ni caracteres raros)
    $limpio = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $original);

    // Prefijo único para evitar duplicados
    $archivo = time() . '_' . $limpio;

    // Guardar archivo
    move_uploaded_file($_FILES['archivo']['tmp_name'], $dir . $archivo);
}

if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $dir . $archivo)) {
    die("ERROR: No se pudo guardar el archivo");
}


$stmt = $pdo->prepare("
    INSERT INTO estudios_medicos (client_id, professional_id, titulo, descripcion, archivo, fecha, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$patient_id, $center_id, $titulo, $descripcion, $archivo, $fecha]);

header("Location: estudios.php?id=" . $patient_id);
exit;
