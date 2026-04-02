<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function enviarEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    $mail->SMTPDebug = 0;

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.sendgrid.net';
        $mail->SMTPAuth = true;

        // SendGrid SIEMPRE usa "apikey" como usuario
        $mail->Username = 'apikey';

        // La API KEY viene de variable de entorno
        $mail->Password = getenv('SENDGRID_API_KEY');

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // TU EMAIL REAL
        $mail->setFrom('turnospro2@gmail.com', 'TurnosPro');

        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error enviando email: " . $mail->ErrorInfo);
        return false;
    }
}

