<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

$file_id = $_GET['id'] ?? null;

if (!$file_id) {
    die("Archivo no encontrado.");
}

// Obtener archivo + evolución + paciente
$stmt = $pdo->prepare("
    SELECT f.*, cr.user_id AS owner_id
    FROM clinical_files f
    JOIN clinical_records cr ON f.record_id = cr.id
    WHERE f.id = ?
");
$stmt->execute([$file_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    die("Archivo inexistente.");
}

// Verificar que el archivo pertenece al profesional
if ($file['owner_id'] != $user_id) {
    die("No tienes permiso para ver este archivo.");
}

// Ruta absoluta del archivo físico
$ruta = __DIR__ . "/../../uploads/" . $file['file_path'];

if (!file_exists($ruta)) {
    die("El archivo no existe en el servidor.");
}

// Detectar tipo MIME
$ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

$mime = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png'
];

header("Content-Type: " . ($mime[$ext] ?? 'application/octet-stream'));
header("Content-Disposition: inline; filename=\"" . $file['file_name'] . "\"");

readfile($ruta);
exit;