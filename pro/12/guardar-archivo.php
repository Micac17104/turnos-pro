<?php
session_start();
require __DIR__ . '/../../config.php';

// Validar login
if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Detectar tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$record_id = $_POST['record_id'] ?? null;

if (!$record_id) {
    die("Evolución no encontrada.");
}

// Obtener evolución + paciente
$stmt = $pdo->prepare("
    SELECT cr.*, c.id AS patient_id
    FROM clinical_records cr
    JOIN clients c ON cr.patient_id = c.id
    WHERE cr.id = ? AND cr.user_id = ?
");
$stmt->execute([$record_id, $user_id]);
$evolucion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$evolucion) {
    die("Evolución no pertenece a este profesional.");
}

$patient_id = $evolucion['patient_id'];

// Validar archivo
if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    die("Error al subir el archivo.");
}

$archivo = $_FILES['archivo'];

// Extensiones permitidas
$permitidos = ['pdf', 'jpg', 'jpeg', 'png'];
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $permitidos)) {
    die("Formato no permitido. Solo PDF, JPG, JPEG, PNG.");
}

// Carpeta uploads
$upload_dir = __DIR__ . "/../../uploads/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Nombre único
$nombre_final = uniqid("file_") . "." . $ext;
$ruta_final = $upload_dir . $nombre_final;

// Mover archivo
if (!move_uploaded_file($archivo['tmp_name'], $ruta_final)) {
    die("No se pudo guardar el archivo.");
}

// Guardar en la base
$stmt = $pdo->prepare("
    INSERT INTO clinical_files (record_id, file_path, file_name)
    VALUES (?, ?, ?)
");
$stmt->execute([
    $record_id,
    $nombre_final,
    $archivo['name']
]);

// Redirigir a historia clínica
header("Location: /turnos-pro/profiles/$user_id/paciente-historia.php?id=$patient_id&archivo_ok=1");
exit;