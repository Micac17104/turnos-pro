<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    exit("No autorizado.");
}

$whatsapp = isset($_POST['whatsapp_enabled']) ? 1 : 0;
$email = isset($_POST['email_enabled']) ? 1 : 0;

$notify_prof_whatsapp = isset($_POST['notify_professional_whatsapp']) ? 1 : 0;
$notify_prof_email = isset($_POST['notify_professional_email']) ? 1 : 0;

$stmt = $pdo->prepare("
    UPDATE notification_settings
    SET 
        whatsapp_enabled = ?, 
        email_enabled = ?, 
        confirm_message = ?, 
        reminder_message = ?, 
        reminder_hours_before = ?,
        notify_professional_whatsapp = ?,
        notify_professional_email = ?,
        professional_message = ?
    WHERE user_id = ?
");

$stmt->execute([
    $whatsapp,
    $email,
    $_POST['confirm_message'],
    $_POST['reminder_message'],
    $_POST['reminder_hours_before'],
    $notify_prof_whatsapp,
    $notify_prof_email,
    $_POST['professional_message'],
    $user_id
]);

header("Location: /turnos-pro/profiles/$user_id/config-notificaciones.php?ok=1");
exit;