<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// DEBUG: verificar si PHPMailer se cargó
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    die("PHPMailer NO se cargó. Revisá la carpeta /auth/PHPMailer/src");
}

function enviarEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    // DEBUG: activar logs SMTP
    $mail->SMTPDebug = 2;

    try {
        // CONFIG SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'turnospro2@gmail.com';
        $mail->Password = 'ybuuunbdkeyeziql';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('turnospro2@gmail.com', 'TurnosAura');
        $mail->addAddress($to);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        echo "ERROR SMTP: " . $e->getMessage();
        return false;
    }
}