<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

// Verificar login del paciente
if (!isset($_SESSION['paciente_id'])) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "NO_AUTH"]);
    exit;
}

$paciente_id = $_SESSION['paciente_id'];
$turno_id = $_GET['id'] ?? null;

if (!$turno_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "NO_ID"]);
    exit;
}

// Obtener turno + profesional + notificaciones
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        u.name AS profesional_nombre,
        u.email AS profesional_email,
        u.phone AS profesional_telefono,
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
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "NOT_FOUND"]);
    exit;
}

// Cancelar turno
$stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
$stmt->execute([$turno_id]);

// -----------------------------
// EMAIL AL PROFESIONAL
// -----------------------------
if (!empty($turno['notify_professional_email']) && !empty($turno['professional_message'])) {

    $msgPro = str_replace(
        ['{paciente}', '{fecha}', '{hora}'],
        [
            $_SESSION['paciente_nombre'] ?? 'Paciente',
            date('d/m/Y', strtotime($turno['date'])),
            substr($turno['time'], 0, 5)
        ],
        $turno['professional_message']
    );

    @mail($turno['profesional_email'], "Turno cancelado por el paciente", $msgPro);
}

// -----------------------------
// WHATSAPP AL PROFESIONAL
// -----------------------------
if (!empty($turno['notify_professional_whatsapp'])) {

    $telefonoPro = preg_replace('/\D/', '', $turno['profesional_telefono'] ?? '');

    if (!empty($telefonoPro)) {

        $msgProWhats = str_replace(
            ['{paciente}', '{fecha}', '{hora}'],
            [
                $_SESSION['paciente_nombre'] ?? 'Paciente',
                date('d/m/Y', strtotime($turno['date'])),
                substr($turno['time'], 0, 5)
            ],
            $turno['professional_message']
        );

        $url = "https://api.callmebot.com/whatsapp.php?phone={$telefonoPro}&text=" . urlencode($msgProWhats);
        @file_get_contents($url);
    }
}

echo json_encode(["status" => "ok"]);