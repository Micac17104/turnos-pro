<?php
require __DIR__ . '/../pro/includes/db.php';

$email = trim(strtolower($_POST['email'] ?? ''));

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

// Si no existe, igual decimos "enviado" por seguridad
if (!$exists) {
    header("Location: forgot-password.php?sent=1");
    exit;
}

// Borrar tokens viejos
$stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
$stmt->execute([$email]);

// Crear token
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Guardar token
$stmt = $pdo->prepare("
    INSERT INTO password_resets (email, token, expires_at)
    VALUES (?, ?, ?)
");
$stmt->execute([$email, $token, $expires]);

// URL correcta del reset
$reset_link = "https://www.turnosaura.com/auth/reset-password.php?token=$token";

// Enviar email
$subject = "Restablecer contraseña - TurnosAura";
$message = "Hola, hacé clic en este enlace para restablecer tu contraseña:\n\n$reset_link\n\nEste enlace vence en 1 hora.";

require __DIR__ . '/mailer.php';
enviarEmail($email, $subject, $message);

// Redirigir
header("Location: forgot-password.php?sent=1");
exit;