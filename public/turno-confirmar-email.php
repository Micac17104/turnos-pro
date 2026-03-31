<?php
session_save_path(__DIR__ . '/sessions');
session_start();

require __DIR__ . '/config.php';
require __DIR__ . '/pro/includes/helpers.php';

$id    = $_GET['id'] ?? null;
$token = $_GET['token'] ?? null;

if (!$id || !$token) {
    die("Link inválido.");
}

// Buscar turno con token
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        c.name  AS paciente_nombre,
        c.email AS paciente_email,
        u.name  AS profesional_nombre,
        u.email AS profesional_email,
        ns.notify_professional_email,
        ns.professional_message
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    JOIN users u   ON a.user_id = u.id
    LEFT JOIN notification_settings ns ON ns.user_id = a.user_id
    WHERE a.id = ? AND a.email_token = ?
");
$stmt->execute([$id, $token]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    die("Turno no encontrado o link inválido.");
}

// Marcar como confirmado
$upd = $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ?");
$upd->execute([$id]);

// Notificar al profesional por email si corresponde
if (!empty($turno['notify_professional_email']) && !empty($turno['professional_message'])) {
    $msgPro = str_replace(
        ['{paciente}', '{fecha}', '{hora}'],
        [
            $turno['paciente_nombre'],
            date('d/m/Y', strtotime($turno['date'])),
            substr($turno['time'], 0, 5)
        ],
        $turno['professional_message']
    );

    @mail($turno['profesional_email'], "Turno confirmado por el paciente", $msgPro);
}

// Notificar al profesional por WhatsApp si corresponde
if (!empty($turno['notify_professional_whatsapp'])) {

    // Normalizar teléfono del profesional
    $telefonoPro = preg_replace('/\D/', '', $turno['profesional_telefono'] ?? '');

    if (!empty($telefonoPro)) {

        // Mensaje al profesional (si querés usar el mismo que el email)
        $msgProWhats = str_replace(
            ['{paciente}', '{fecha}', '{hora}'],
            [
                $turno['paciente_nombre'],
                date('d/m/Y', strtotime($turno['date'])),
                substr($turno['time'], 0, 5)
            ],
            $turno['professional_message']
        );

        // Enviar WhatsApp usando tu API
        $url = "https://api.callmebot.com/whatsapp.php?phone={$telefonoPro}&text=" . urlencode($msgProWhats);

        @file_get_contents($url);
    }
}
// Pantalla simple de confirmación
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Turno confirmado</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif; background:#f1f5f9; padding:40px; }
        .card { max-width:480px; margin:40px auto; background:white; padding:30px; border-radius:16px; box-shadow:0 10px 30px rgba(15,23,42,0.12); }
        h1 { font-size:24px; margin-bottom:10px; color:#0f172a; }
        p { color:#475569; margin-bottom:8px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Turno confirmado</h1>
    <p>Tu turno fue confirmado correctamente.</p>
    <p><strong>Profesional:</strong> <?= h($turno['profesional_nombre']) ?></p>
    <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($turno['date'])) ?></p>
    <p><strong>Hora:</strong> <?= substr($turno['time'], 0, 5) ?> hs</p>
</div>
</body>
</html>