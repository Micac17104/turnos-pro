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

        // La API KEY ahora viene de una variable de entorno
        $mail->Password = getenv('SENDGRID_API_KEY');

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('turnospro2@gmail.com', 'TurnosAura');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br($body);

        return $mail->send();

    } catch (Exception $e) {
        error_log("ERROR SMTP: " . $e->getMessage());
        return false;
    }
}

