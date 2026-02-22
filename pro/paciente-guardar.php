<?php
// --- FIX DEFINITIVO PARA RAILWAY ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    @chmod($path, 0777);
}

session_save_path($path);
session_start();
// -----------------------------------

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

// Obtener datos del formulario
$name  = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validación básica
if ($name === '' || $phone === '') {
    die("Datos incompletos.");
}

// Guardar paciente
$stmt = $pdo->prepare("
    INSERT INTO clients (user_id, name, phone, email)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$user_id, $name, $phone, $email]);

// Redirección corregida (ruta relativa)
redirect("pacientes.php");