<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

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

// Redirigir al listado de pacientes
header("Location: /turnos-pro/pro/pacientes.php");
exit;