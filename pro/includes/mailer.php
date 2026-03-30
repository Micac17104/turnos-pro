<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar PHPMailer desde /auth/PHPMailer/src/
require __DIR__ . '/../auth/PHPMailer/src/Exception.php';
require __DIR__ . '/../auth/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../auth/PHPMailer/src/SMTP.php';

function enviarEmail($destinatario, $asunto, $mensajeHtml) {
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // 👉 REEMPLAZAR ESTO POR TU EMAIL
        $mail->Username = 'turnospro2@gmail.com';

        // 👉 REEMPLAZAR ESTO POR TU APP PASSWORD
        $mail->Password = 'ybuuunbdkeyeziql';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente (debe ser el mismo email del Username)
        $mail->setFrom('turnospro2@gmail.com', 'TurnosAura');

        // Destinatario
        $mail->addAddress($destinatario);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensajeHtml;

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}