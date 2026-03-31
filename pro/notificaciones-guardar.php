<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

// Normalizar checkboxes (si no vienen, valen 0)
$whatsapp_enabled               = isset($_POST['whatsapp_enabled']) ? 1 : 0;
$email_enabled                  = isset($_POST['email_enabled']) ? 1 : 0;
$reminder_enabled               = isset($_POST['reminder_enabled']) ? 1 : 0;
$notify_professional_whatsapp   = isset($_POST['notify_professional_whatsapp']) ? 1 : 0;
$notify_professional_email      = isset($_POST['notify_professional_email']) ? 1 : 0;

// Textos
$confirm_message        = trim($_POST['confirm_message'] ?? '');
$reminder_hours_before  = intval($_POST['reminder_hours_before'] ?? 24);
$reminder_message       = trim($_POST['reminder_message'] ?? '');
$professional_message   = trim($_POST['professional_message'] ?? '');

// Verificar si existe registro
$stmt = $pdo->prepare("SELECT id FROM notification_settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$exists = $stmt->fetchColumn();

if ($exists) {
    // UPDATE
    $stmt = $pdo->prepare("
        UPDATE notification_settings
        SET 
            whatsapp_enabled = ?,
            email_enabled = ?,
            confirm_message = ?,
            reminder_enabled = ?,
            reminder_hours_before = ?,
            reminder_message = ?,
            notify_professional_whatsapp = ?,
            notify_professional_email = ?,
            professional_message = ?
        WHERE user_id = ?
    ");

    $stmt->execute([
        $whatsapp_enabled,
        $email_enabled,
        $confirm_message,
        $reminder_enabled,
        $reminder_hours_before,
        $reminder_message,
        $notify_professional_whatsapp,
        $notify_professional_email,
        $professional_message,
        $user_id
    ]);

} else {
    // INSERT
    $stmt = $pdo->prepare("
        INSERT INTO notification_settings (
            user_id,
            whatsapp_enabled,
            email_enabled,
            confirm_message,
            reminder_enabled,
            reminder_hours_before,
            reminder_message,
            notify_professional_whatsapp,
            notify_professional_email,
            professional_message
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $whatsapp_enabled,
        $email_enabled,
        $confirm_message,
        $reminder_enabled,
        $reminder_hours_before,
        $reminder_message,
        $notify_professional_whatsapp,
        $notify_professional_email,
        $professional_message
    ]);
}

// Redirigir de vuelta
redirect("notificaciones.php?ok=1");