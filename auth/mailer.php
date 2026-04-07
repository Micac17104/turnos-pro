<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function enviarEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    


    $mail->SMTPDebug = 0;

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // 1) Intentar variable de entorno (funciona en tu PC)
        $password = getenv('GMAIL_APP_PASSWORD');

        // 2) Si no existe (servidor), cargar secret.php
        if (!$password || trim($password) === '') {
            $password = include __DIR__ . '/secret.php';
        }

        // 3) Si sigue sin contraseña → no enviar
        if (!$password || trim($password) === '') {
            error_log("No hay contraseña SMTP disponible");
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
