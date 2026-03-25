<?php
require __DIR__ . '/../pro/includes/db.php';

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

if ($password !== $password2) {
    header("Location: reset-password.php?token=$token&error=1");
    exit;
}

// Buscar token válido
$stmt = $pdo->prepare("
    SELECT email 
    FROM password_resets 
    WHERE token = ? AND expires_at > NOW()
");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    die("Token inválido o vencido.");
}

$email = $reset['email'];
$hash = password_hash($password, PASSWORD_BCRYPT);

// Actualizar en users
$stmt = $pdo->prepare("UPDATE users SET password=? WHERE email=?");
$stmt->execute([$hash, $email]);

// Actualizar en center_staff
$stmt = $pdo->prepare("UPDATE center_staff SET password=? WHERE email=?");
$stmt->execute([$hash, $email]);

// Borrar token
$stmt = $pdo->prepare("DELETE FROM password_resets WHERE email=?");
$stmt->execute([$email]);

header("Location: login.php?reset=1");
exit;