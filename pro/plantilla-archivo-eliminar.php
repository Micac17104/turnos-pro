<?php
// /pro/plantilla-archivo-eliminar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$file_id = require_param($_GET, 'id', 'Archivo no encontrado.');

// Obtener archivo + verificar permisos
$stmt = $pdo->prepare("
    SELECT f.file_path, r.client_id, r.user_id
    FROM clinical_template_files f
    JOIN clinical_template_records r ON r.id = f.record_id
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

$stmt = $pdo->prepare("DELETE FROM clinical_template_files WHERE id = ?");
$stmt->execute([$file_id]);

redirect("paciente-historia.php?id=" . $file['client_id']);