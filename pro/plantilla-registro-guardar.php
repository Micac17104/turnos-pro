<?php
// /pro/plantilla-registro-guardar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$patient_id  = $_POST['patient_id'] ?? null;
$template_id = $_POST['template_id'] ?? null;
$fields_data = $_POST['fields'] ?? [];

if (!$patient_id || !$template_id) {
    die("Datos incompletos.");
}

// Verificar paciente
$stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ? AND user_id = ?");
$stmt->execute([$patient_id, $user_id]);
if (!$stmt->fetch()) {
    die("Paciente no pertenece a este profesional.");
}

// Verificar plantilla
$stmt = $pdo->prepare("SELECT id, fields FROM clinical_templates WHERE id = ? AND user_id = ?");
$stmt->execute([$template_id, $user_id]);
$plantilla = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$plantilla) {
    die("Plantilla no encontrada.");
}

$fields_def = json_decode($plantilla['fields'], true) ?: [];
$data = [];

foreach ($fields_def as $index => $def) {
    $label = $def['label'] ?? ('Campo ' . ($index + 1));
    $value = trim($fields_data[$index] ?? '');
    $data[] = [
        'label' => $label,
        'value' => $value
    ];
}

// Guardar registro
$stmt = $pdo->prepare("
    INSERT INTO clinical_template_records (template_id, client_id, user_id, center_id, data)
    VALUES (?, ?, ?, NULL, ?)
");
$stmt->execute([$template_id, $patient_id, $user_id, json_encode($data, JSON_UNESCAPED_UNICODE)]);

$record_id = $pdo->lastInsertId();

// Manejo de archivos
if (!empty($_FILES['archivos']['name'][0])) {
    $upload_dir = __DIR__ . '/../../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_ext  = ['pdf', 'jpg', 'jpeg', 'png'];
    $allowed_mime = ['application/pdf', 'image/jpeg', 'image/png'];

    foreach ($_FILES['archivos']['name'] as $i => $name) {
        if ($_FILES['archivos']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $tmp_name = $_FILES['archivos']['tmp_name'][$i];
        $ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            continue;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $tmp_name);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_mime)) {
            continue;
        }

        $new_name = uniqid('tpl_', true) . '.' . $ext;
        $destino  = $upload_dir . $new_name;

        if (move_uploaded_file($tmp_name, $destino)) {
            $stmt = $pdo->prepare("
                INSERT INTO clinical_template_files (record_id, file_name, file_path)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$record_id, $name, $new_name]);
        }
    }
}

redirect("paciente-historia.php?id=" . $patient_id);