<?php
session_start();
require __DIR__ . '/../../config.php';

// Detectar si estoy en _template o en un tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : ($_SESSION['user_id'] ?? null);

// Verificar login del profesional
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

$client_id = $_POST['client_id'] ?? null;
$date      = $_POST['date'] ?? null;
$time      = $_POST['time'] ?? null;

if (!$client_id || !$date || !$time) {
    die("Datos incompletos.");
}

// Guardar turno
$stmt = $pdo->prepare("
    INSERT INTO appointments (user_id, client_id, date, time)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$user_id, $client_id, $date, $time]);

// Obtener datos del profesional
$stmtUser = $pdo->prepare("
    SELECT name, email, telefono, notify_whatsapp, notify_email
    FROM users
    WHERE id = ?
");
$stmtUser->execute([$user_id]);
$profesional = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Obtener datos del cliente
$stmtClient = $pdo->prepare("
    SELECT name, phone, email
    FROM clients
    WHERE id = ?
");
$stmtClient->execute([$client_id]);
$cliente = $stmtClient->fetch(PDO::FETCH_ASSOC);

/* ============================================================
   NOTIFICACIÓN AL CLIENTE
============================================================ */

$mensajeCliente =
    "Hola {$cliente['name']}, tu turno fue reservado:\n" .
    "Fecha: " . date("d/m/Y", strtotime($date)) . "\n" .
    "Hora: {$time}";

// WhatsApp al cliente (solo abre WhatsApp Web)
if (!empty($cliente['phone'])) {
    $telefono = preg_replace('/[^0-9]/', '', $cliente['phone']);
    $mensaje  = urlencode($mensajeCliente);
    $whatsapp_url = "https://api.whatsapp.com/send?phone=$telefono&text=$mensaje";

    echo "<script>window.open('$whatsapp_url', '_blank');</script>";
}

// Email al cliente
if (!empty($cliente['email'])) {
    $to      = $cliente['email'];
    $subject = "Confirmación de turno";
    $headers = "From: notificaciones@turnospro.com";
    @mail($to, $subject, $mensajeCliente, $headers);
}

/* ============================================================
   NOTIFICACIÓN AL PROFESIONAL
============================================================ */

$mensajeProfesional =
    "Nuevo turno reservado:\n" .
    "Paciente: {$cliente['name']}\n" .
    "Fecha: " . date("d/m/Y", strtotime($date)) . "\n" .
    "Hora: {$time}";

// WhatsApp al profesional
if ($profesional['notify_whatsapp']) {
    $telefono = preg_replace('/[^0-9]/', '', $profesional['telefono']);
    if (!empty($telefono)) {
        $mensaje = urlencode($mensajeProfesional);
        $whatsapp_url = "https://api.whatsapp.com/send?phone=$telefono&text=$mensaje";
        echo "<script>window.open('$whatsapp_url', '_blank');</script>";
    }
}

// Email al profesional
if ($profesional['notify_email']) {
    $to      = $profesional['email'];
    $subject = "Nuevo turno reservado";
    $headers = "From: notificaciones@turnospro.com";
    @mail($to, $subject, $mensajeProfesional, $headers);
}

// Redirección correcta
header("Location: /turnos-pro/profiles/$user_id/dashboard.php?turno_creado=1");
exit;