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
$email = trim($_POST['email'] ?? '');

if (empty($name) || empty($phone) || empty($email)) {
    die("Datos incompletos.");
}

// Verificar si ya existe un paciente con ese email para este profesional
$stmt = $pdo->prepare("
    SELECT id FROM clients 
    WHERE user_id = ? AND email = ?
");
$stmt->execute([$user_id, $email]);
$existe = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existe) {
    die("Ya existe un paciente con ese email.");
}

// Guardar paciente
$stmt = $pdo->prepare("
    INSERT INTO clients (user_id, name, phone, email)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$user_id, $name, $phone, $email]);

// Redirigir correctamente al dashboard del profesional
header("Location: /turnos-pro/profiles/$user_id/dashboard.php");
exit;