<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

// Verificar login
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$name     = $_POST['name'] ?? '';
$email    = $_POST['email'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$password = $_POST['password'] ?? '';

// Si no cambia contraseña
if (empty($password)) {

    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, email = ?, telefono = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $email, $telefono, $user_id]);

} else {

    // Encriptar contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, email = ?, telefono = ?, password = ?
        WHERE id = ?
    ");
    $stmt->execute([$name, $email, $telefono, $hash, $user_id]);
}

// Redirigir correctamente
header("Location: /turnos-pro/profiles/$user_id/editar-perfil.php?ok=1");
exit;