<?php
require __DIR__ . '/../auth/mailer.php';

$email = $_POST['email'];
$body = $_POST['body'];

if (enviarEmail($email, "Recordatorio de turno", $body)) {
    header("Location: recordatorio.php?ok=1");
} else {
    header("Location: recordatorio.php?error=1");
}
exit;
