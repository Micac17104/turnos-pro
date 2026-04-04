<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../auth/mailer.php';

$paciente_id = $_SESSION['paciente_id'] ?? null;
$turno_id    = $_GET['id'] ?? null;

if (!$turno_id) {
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

if (!$turno) {
    echo "<p class='text-red-600'>No se encontró el turno.</p>";
    exit;
}

if ($paciente_id && $turno['client_id'] && $turno['client_id'] != $paciente_id) {
    echo "<p class='text-red-600'>No tienes permiso para cancelar este turno.</p>";
    exit;
}

$stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
$stmt->execute([$turno_id]);

$paciente = $turno['paciente_nombre'] ?: ($_SESSION['paciente_nombre'] ?? 'Paciente');
$fecha = date('d/m/Y', strtotime($turno['date']));
$hora = substr($turno['time'], 0, 5);

if (!empty($turno['notify_professional_email']) && !empty($turno['professional_message'])) {

    $msgPro = str_replace(
        ['{paciente}', '{fecha}', '{hora}'],
        [$paciente, $fecha, $hora],
        $turno['professional_message']
    );

    enviarEmail($turno['profesional_email'], "Turno cancelado por el paciente", nl2br($msgPro));
}

if (!empty($turno['notify_professional_whatsapp'])) {

    $telefonoPro = preg_replace('/\D/', '', $turno['profesional_telefono'] ?? '');

    if (!empty($telefonoPro)) {

        $msgProWhats = str_replace(
            ['{paciente}', '{fecha}', '{hora}'],
            [$paciente, $fecha, $hora],
            $turno['professional_message']
        );

        $url = "https://api.callmebot.com/whatsapp.php?phone={$telefonoPro}&text=" . urlencode($msgProWhats);
        @file_get_contents($url);
    }
}

echo "<h2 class='text-green-600 text-xl font-bold mb-4'>Turno cancelado correctamente</h2>";
echo "<a href='/public/panel.php' class='text-blue-600 underline'>Volver al panel</a>";
