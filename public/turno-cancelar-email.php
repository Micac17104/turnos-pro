<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../auth/mailer.php';

$turno_id = $_GET['id'] ?? null;
$token    = $_GET['token'] ?? null;

if (!$turno_id || !$token) {
    die("Datos incompletos.");
}

$stmt = $pdo->prepare("
    SELECT 
        a.*,
        u.name AS profesional,
        u.email AS profesional_email,
        u.parent_center_id,
        c.name AS paciente_nombre,
        ns.notify_professional_email,
        ns.professional_message
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN clients c ON c.id = a.client_id
    LEFT JOIN notification_settings ns ON ns.user_id = a.user_id
    WHERE a.id = ? AND a.cancel_token = ?
");
$stmt->execute([$turno_id, $token]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    die("Token inválido o turno no encontrado.");
}

if ($turno['status'] === 'cancelled') {
    echo "<h2>Este turno ya estaba cancelado.</h2>";
    exit;
}

$stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
$stmt->execute([$turno_id]);

$paciente = $turno['paciente_nombre'] ?: 'Paciente';
$fecha = date('d/m/Y', strtotime($turno['date']));
$hora = substr($turno['time'], 0, 5);

$isCentro = !empty($turno['parent_center_id']);

$msgPro = "
    Hola {$turno['profesional']},<br><br>
    El paciente <strong>{$paciente}</strong> canceló su turno:<br><br>
    <strong>Fecha:</strong> {$fecha}<br>
    <strong>Hora:</strong> {$hora}<br><br>
    TurnosAura
";

if (!empty($turno['notify_professional_email'])) {
    enviarEmail($turno['profesional_email'], "Turno cancelado por el paciente", $msgPro);
}

echo "<h2>Tu turno fue cancelado correctamente.</h2>";
echo "<a href='/'>Volver al inicio</a>";

