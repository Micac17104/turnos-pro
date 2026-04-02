<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../auth/mailer.php';

header('Content-Type: application/json');

$paciente_id = $_SESSION['paciente_id'] ?? null;
$turno_id    = $_POST['id'] ?? null;

if (!$paciente_id || !$turno_id) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        a.*,
        u.name AS profesional,
        u.email AS profesional_email,
        u.phone AS profesional_telefono,
        u.parent_center_id,
        ns.notify_professional_email,
        ns.notify_professional_whatsapp,
        ns.professional_message
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN notification_settings ns ON ns.user_id = a.user_id
    WHERE a.id = ? AND a.client_id = ?
");
$stmt->execute([$turno_id, $paciente_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    echo json_encode(['status' => 'error', 'message' => 'Turno no encontrado']);
    exit;
}

$stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
$stmt->execute([$turno_id]);

$isCentro = !empty($turno['parent_center_id']);
$paciente = $_SESSION['paciente_nombre'] ?? 'Paciente';
$fecha = date('d/m/Y', strtotime($turno['date']));
$hora = substr($turno['time'], 0, 5);

// Profesional individual
if (!$isCentro && !empty($turno['notify_professional_email']) && !empty($turno['professional_message'])) {

    $msgPro = str_replace(
        ['{paciente}', '{fecha}', '{hora}'],
        [$paciente, $fecha, $hora],
        $turno['professional_message']
    );

    enviarEmail($turno['profesional_email'], "Turno cancelado por el paciente", nl2br($msgPro));
}

// Profesional del centro
if ($isCentro) {

    $msgCentro = "
        Hola {$turno['profesional']},<br><br>
        El paciente <strong>{$paciente}</strong> canceló su turno:<br><br>
        <strong>Fecha:</strong> {$fecha}<br>
        <strong>Hora:</strong> {$hora}<br><br>
        TurnosAura
    ";

    enviarEmail($turno['profesional_email'], "Turno cancelado por el paciente", $msgCentro);
}

echo json_encode(['status' => 'ok']);
exit;
