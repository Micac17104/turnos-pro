<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

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

redirect("/turnos-pro/pro/paciente-historia.php?id=" . $file['patient_id']);