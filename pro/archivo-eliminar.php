<?php
// --- FIX SESSIONS (mismo criterio que agenda.php) ---
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

$file_id = require_param($_GET, 'id');

// Obtener archivo + verificar permisos
$stmt = $pdo->prepare("
    SELECT f.file_path, cr.patient_id, cr.user_id
    FROM clinical_files f
    JOIN clinical_records cr ON cr.id = f.record_id
    WHERE f.id = ?
");
$stmt->execute([$file_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file || $file['user_id'] != $user_id) {
    die("No tienes permiso para eliminar este archivo.");
}

$ruta = __DIR__ . '/../../uploads/' . $file['file_path'];

if (file_exists($ruta)) {
    unlink($ruta);
}

// Eliminar de DB
$stmt = $pdo->prepare("DELETE FROM clinical_files WHERE id = ?");
$stmt->execute([$file_id]);

// Ruta relativa, sin /turnos-pro para que funcione igual en Railway
redirect("paciente-historia.php?id=" . $file['patient_id']);