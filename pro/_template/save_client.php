<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

// Verificar login del profesional
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$name  = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (empty($name) || empty($phone)) {
    die("Datos incompletos.");
}

// Guardar paciente
$stmt = $pdo->prepare("
    INSERT INTO clients (user_id, name, phone)
    VALUES (?, ?, ?)
");
$stmt->execute([$user_id, $name, $phone]);

// Redirigir correctamente al dashboard del profesional
header("Location: /turnos-pro/profiles/$user_id/dashboard.php");
exit;