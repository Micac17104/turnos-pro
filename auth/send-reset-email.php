<?php
require '../config.php';

$email = trim($_POST['email'] ?? '');

if ($email === '') {
    header("Location: forgot-password.php?error=1");
    exit;
}

// Verificar si existe en users o center_staff
$stmt = $pdo->prepare("
    SELECT email FROM users WHERE email = ?
    UNION
    SELECT email FROM center_staff WHERE email = ?
");
$stmt->execute([$email, $email]);
$exists = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exists) {
    header("Location: forgot-password.php?sent=1");
    exit;
}

// Crear token
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Guardar token
$stmt = $pdo->prepare("
    INSERT INTO password_resets (email, token, expires_at)
    VALUES (?, ?, ?)
");
$stmt->execute([$email, $token, $expires]);

// Link de recuperación
$reset_link = "https://TU-DOMINIO.com/turnos-pro/auth/reset-password.php?token=$token";

// Enviar email
$subject = "Restablecer contraseña - TurnosPro";
$message = "Hola, hacé clic en este enlace para restablecer tu contraseña:\n\n$reset_link\n\nEste enlace vence en 1 hora.";
$headers = "From: no-reply@turnospro.com";

mail($email, $subject, $message, $headers);

// Redirigir
header("Location: forgot-password.php?sent=1");
exit;