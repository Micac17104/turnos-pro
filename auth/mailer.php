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

        // Acá va tu API KEY (sin comillas extras, tal cual)
        $mail->Password = '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';

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


