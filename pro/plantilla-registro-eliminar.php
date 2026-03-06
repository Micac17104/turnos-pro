<?php
// /pro/plantilla-registro-eliminar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$record_id = require_param($_GET, 'id', 'Registro no encontrado.');

// Obtener registro + validar permisos
$stmt = $pdo->prepare("
    SELECT r.id, r.client_id, r.user_id
    FROM clinical_template_records r
    WHERE r.id = ?
");
$stmt->execute([$record_id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro || $registro['user_id'] != $user_id) {
    die("No tienes permiso para eliminar este registro.");
}

$patient_id = $registro['client_id'];

// Obtener archivos asociados
$stmt = $pdo->prepare("
    SELECT id, file_path
    FROM clinical_template_files
    WHERE record_id = ?
");
$stmt->execute([$record_id]);
$archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Eliminar archivos físicos
foreach ($archivos as $a) {
    $ruta = __DIR__ . '/../../uploads/' . $a['file_path'];
    if (file_exists($ruta)) {
        unlink($ruta);
    }
}

// Eliminar archivos de la BD
$stmt = $pdo->prepare("DELETE FROM clinical_template_files WHERE record_id = ?");
$stmt->execute([$record_id]);

// Eliminar el registro
$stmt = $pdo->prepare("DELETE FROM clinical_template_records WHERE id = ?");
$stmt->execute([$record_id]);

redirect("paciente-historia.php?id=" . $patient_id);