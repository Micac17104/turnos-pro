<?php
// --- FIX SESSIONS (igual que en agenda.php) ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    @chmod($path, 0777);
}

session_save_path($path);
session_start();
// ----------------------------------------------------

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$record_id = require_param($_POST, 'record_id');

// Verificar que la evolución pertenece al profesional
$stmt = $pdo->prepare("
    SELECT patient_id
    FROM clinical_records
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$record_id, $user_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    die("No tienes permiso para adjuntar archivos.");
}

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    die("Error al subir archivo.");
}

$file = $_FILES['archivo'];

// Validar extensión
$allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_ext)) {
    die("Formato no permitido.");
}

// Validar MIME real
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed_mime = [
    'application/pdf',
    'image/jpeg',
    'image/png'
];

if (!in_array($mime, $allowed_mime)) {
    die("Archivo inválido.");
}

// Crear nombre único
$new_name = uniqid('file_', true) . '.' . $ext;

// Ruta final
$upload_dir = __DIR__ . '/../../uploads/';
$destino    = $upload_dir . $new_name;

if (!move_uploaded_file($file['tmp_name'], $destino)) {
    die("Error al guardar archivo.");
}

// Guardar en DB
$stmt = $pdo->prepare("
    INSERT INTO clinical_files (record_id, file_name, file_path)
    VALUES (?, ?, ?)
");
$stmt->execute([$record_id, $file['name'], $new_name]);

// Redirección corregida (ruta relativa)
redirect("paciente-historia.php?id=" . $record['patient_id']);