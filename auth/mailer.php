<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function enviarEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    // PRODUCCIÓN: sin debug
    $mail->SMTPDebug = 0;

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'turnospro2@gmail.com';
        $mail->Password = 'ybuuunbdkeyeziql'; // App Password

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('turnospro2@gmail.com', 'TurnosAura');
        $mail->addAddress($to);

        $mail->isHTML(true); // IMPORTANTE
        $mail->Subject = $subject;
        $mail->Body    = nl2br($body);

        return $mail->send();

    } catch (Exception $e) {
        error_log("ERROR SMTP: " . $e->getMessage());
        return false;
    }
}