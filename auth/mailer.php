<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function enviarEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // CONFIG SMTP GMAIL
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // ⚠️ REEMPLAZAR ESTO POR TU EMAIL Y CONTRASEÑA DE APLICACIÓN
        $mail->Username = 'turnospro2@gmail.com';
        $mail->Password = 'uebk ejts gydl zyhb';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente
        $mail->setFrom('no-reply@turnospro.com', 'TurnosPro');

        // Destinatario
        $mail->addAddress($to);

        // Contenido
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}