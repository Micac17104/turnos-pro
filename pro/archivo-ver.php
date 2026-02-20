<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$file_id = require_param($_GET, 'id', 'Archivo no encontrado.');

// Obtener archivo + verificar permisos
$stmt = $pdo->prepare("
    SELECT f.file_name, f.file_path, cr.user_id
    FROM clinical_files f
    JOIN clinical_records cr ON cr.id = f.record_id
    WHERE f.id = ?
");
$stmt->execute([$file_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file || $file['user_id'] != $user_id) {
    die("No tienes permiso para ver este archivo.");
}

$ruta = __DIR__ . '/../../uploads/' . $file['file_path'];

if (!file_exists($ruta)) {
    die("El archivo no existe.");
}

// Detectar MIME real
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $ruta);
finfo_close($finfo);

header("Content-Type: $mime");
header("Content-Disposition: inline; filename=\"" . $file['file_name'] . "\"");

readfile($ruta);
exit;