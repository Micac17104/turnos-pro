<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function enviarEmail($destinatario, $asunto, $mensajeHtml) {

    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'TU_EMAIL@gmail.com'; // tu Gmail
        $mail->Password   = 'lize oeci aalk mvnx'; // contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remitente
        $mail->setFrom('TU_EMAIL@gmail.com', 'Turnos Pro');

        // Destinatario
        $mail->addAddress($destinatario);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $mensajeHtml;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error enviando email: {$mail->ErrorInfo}");
        return false; 
    }
}

