<?php
require __DIR__ . '/../auth/mailer.php';

$email = $_POST['email'] ?? null;
$body  = $_POST['body'] ?? null;

if (!$email || !$body) {
    header("Location: centro-recordatorios.php?error=1");
    exit;
}

if (enviarEmail($email, "Recordatorio de turno", $body)) {
    header("Location: centro-recordatorios.php?ok=1");
} else {
    header("Location: centro-recordatorios.php?error=1");
}
exit;
