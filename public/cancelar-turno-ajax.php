<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../auth/mailer.php';

function debug_log($msg) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO debug_logs (message) VALUES (?)");
    $stmt->execute([$msg]);
}

header('Content-Type: application/json');

$paciente_id = $_SESSION['paciente_id'] ?? null;
$turno_id    = $_POST['id'] ?? null;

debug_log("=== cancelar-turno-ajax.php ===");
debug_log("Paciente ID: " . var_export($paciente_id, true));
debug_log("Turno ID: " . var_export($turno_id, true));

if (!$turno_id) {
    debug_log("ERROR: turno_id faltante");
    echo json_encode(['status' => 'error', 'message' => 'ID de turno faltante']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        a.*,
        u.name AS profesional,
        u.email AS profesional_email,
        u.phone AS profesional_telefono,
        u.parent_center_id,
        c.name AS paciente_nombre,
        ns.notify_professional_email,
        ns.notify_professional_whatsapp,
        ns.professional_message
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN clients c ON c.id = a.client_id
    LEFT JOIN notification_settings ns ON ns.user_id = a.user_id
    WHERE a.id = ?
");
$stmt->execute([$turno_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

debug_log("Turno encontrado: " . json_encode($turno));

if (!$turno) {
    debug_log("ERROR: turno no encontrado");
    echo json_encode(['status' => 'error', 'message' => 'Turno no encontrado']);
    exit;
}

if ($paciente_id && $turno['client_id'] && $turno['client_id'] != $paciente_id) {
    debug_log("ERROR: paciente no autorizado");
    echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para cancelar este turno']);
    exit;
}

$stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
$stmt->execute([$turno_id]);
debug_log("Turno cancelado en DB");

$paciente = $turno['paciente_nombre'] ?: ($_SESSION['paciente_nombre'] ?? 'Paciente');
$fecha = date('d/m/Y', strtotime($turno['date']));
$hora = substr($turno['time'], 0, 5);

$isCentro = !empty($turno['parent_center_id']);

debug_log("Es centro: " . ($isCentro ? "SI" : "NO"));
debug_log("Email profesional: " . $turno['profesional_email']);
debug_log("Mensaje profesional: " . var_export($turno['professional_message'], true));

if ($isCentro) {

    debug_log("Enviando email a profesional (centro)");

    $msg = "
        Hola {$turno['profesional']},<br><br>
        El paciente <strong>{$paciente}</strong> canceló su turno:<br><br>
        <strong>Fecha:</strong> {$fecha}<br>
        <strong>Hora:</strong> {$hora}<br><br>
        TurnosAura
    ";

    $ok = enviarEmail($turno['profesional_email'], "Turno cancelado por el paciente", $msg);
    debug_log("Resultado enviarEmail: " . var_export($ok, true));

} else {

    if (!empty($turno['notify_professional_email']) && !empty($turno['professional_message'])) {

        debug_log("Enviando email a profesional (individual)");

        $msgPro = str_replace(
            ['{paciente}', '{fecha}', '{hora}'],
            [$paciente, $fecha, $hora],
            $turno['professional_message']
        );

        $ok = enviarEmail($turno['profesional_email'], "Turno cancelado por el paciente", nl2br($msgPro));
        debug_log("Resultado enviarEmail: " . var_export($ok, true));
    }
}

echo json_encode(['status' => 'ok']);
exit;
