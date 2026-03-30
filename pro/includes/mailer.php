<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ajustamos la ruta para cargar PHPMailer desde /auth
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
        $mail->Username = 'TU_EMAIL@gmail.com';
        $mail->Password = 'TU_APP_PASSWORD';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente
        $mail->setFrom('TU_EMAIL@gmail.com', 'TurnosAura');

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