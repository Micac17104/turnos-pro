<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function enviarEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    $mail->SMTPDebug = 0; // no mostrar nada en pantalla

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // 🔒 Recuperar contraseña de entorno
        $password = getenv('GMAIL_APP_PASSWORD');

        // ❗ Si no existe, NO llamar a PHPMailer → evitar warnings
        if (!$password || trim($password) === '') {
            error_log("GMAIL_APP_PASSWORD no está definida");
            return false;
        }

        $mail->Username   = 'turnospro2@gmail.com';
        $mail->Password   = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('turnospro2@gmail.com', 'TurnosPro');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error enviando email: " . $mail->ErrorInfo);
        return false;
    }
}
