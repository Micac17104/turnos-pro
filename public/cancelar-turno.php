<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../auth/mailer.php';

function debug_log($msg) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO debug_logs (message) VALUES (?)");
    $stmt->execute([$msg]);
}

debug_log("=== cancelar-turno.php INICIADO ===");

$paciente_id = $_SESSION['paciente_id'] ?? null;
$turno_id    = $_GET['id'] ?? null;

debug_log("Paciente ID: " . var_export($paciente_id, true));
debug_log("Turno ID recibido: " . var_export($turno_id, true));

if (!$turno_id) {
    debug_log("ERROR: turno_id faltante");
    echo "<p class='text-red-600'>Turno no especificado.</p>";
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        a.*,
        u.name AS profesional,
        u.profession,
        u.email AS profesional_email,
        u.phone AS profesional_telefono,
        ns.notify_professional_email,
        ns.notify_professional_whatsapp,
        ns.professional_message,
        c.name AS paciente_nombre
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
    echo "<p class='text-red-600'>No se encontró el turno.</p>";
    exit;
}

if ($paciente_id && $turno['client_id'] && $turno['client_id'] != $paciente_id) {
    debug_log("ERROR: paciente no autorizado");
    echo "<p class='text-red-600'>No tienes permiso para cancelar este turno.</p>";
    exit;
}

$stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
$stmt->execute([$turno_id]);
debug_log("Turno cancelado en DB: " . $turno_id);

$paciente = $turno['paciente_nombre'] ?: ($_SESSION['paciente_nombre'] ?? 'Paciente');
$fecha = date('d/m/Y', strtotime($turno['date']));
$hora = substr($turno['time'], 0, 5);

debug_log("Email profesional: " . $turno['profesional_email']);
debug_log("Mensaje profesional: " . var_export($turno['professional_message'], true));

if (!empty($turno['notify_professional_email']) && !empty($turno['professional_message'])) {

    debug_log("Enviando email al profesional");

    $msgPro = str_replace(
        ['{paciente}', '{fecha}', '{hora}'],
        [$paciente, $fecha, $hora],
        $turno['professional_message']
    );

    $ok = enviarEmail($turno['profesional_email'], "Turno cancelado por el paciente", nl2br($msgPro));
    debug_log("Resultado enviarEmail: " . var_export($ok, true));
}

if (!empty($turno['notify_professional_whatsapp'])) {

    debug_log("Enviando WhatsApp");

    $telefonoPro = preg_replace('/\D/', '', $turno['profesional_telefono'] ?? '');

    if (!empty($telefonoPro)) {

        $msgProWhats = str_replace(
            ['{paciente}', '{fecha}', '{hora}'],
            [$paciente, $fecha, $hora],
            $turno['professional_message']
        );

        $url = "https://api.callmebot.com/whatsapp.php?phone={$telefonoPro}&text=" . urlencode($msgProWhats);
        @file_get_contents($url);

        debug_log("WhatsApp enviado a: " . $telefonoPro);
    }
}
?>
