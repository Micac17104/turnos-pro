<?php
// /pro/plantilla-guardar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$title      = trim($_POST['title'] ?? '');
$fields_raw = trim($_POST['fields_raw'] ?? '');

if ($title === '' || $fields_raw === '') {
    die("Datos incompletos.");
}

// Convertir líneas en campos
$lines = array_filter(array_map('trim', explode("\n", $fields_raw)));
if (empty($lines)) {
    die("Debes definir al menos un campo.");
}

$fields = [];
foreach ($lines as $label) {
    $fields[] = [
        'label' => $label,
        'type'  => 'textarea' // simple por ahora
    ];
}

// Guardar plantilla
$stmt = $pdo->prepare("
    INSERT INTO clinical_templates (user_id, center_id, title, fields)
    VALUES (?, NULL, ?, ?)
");
$stmt->execute([
    $user_id,
    $title,
    json_encode($fields, JSON_UNESCAPED_UNICODE)
]);

redirect("paciente-historia.php?id=" . ($_GET['id'] ?? ''));