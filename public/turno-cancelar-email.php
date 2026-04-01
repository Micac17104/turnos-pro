<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../auth/mailer.php'; // ← USAMOS EL MAILER BUENO

$turno_id = $_GET['id'] ?? null;
$token    = $_GET['token'] ?? null;

if (!$turno_id || !$token) {
    die("Datos incompletos.");
}

// Obtener turno + profesional + notificaciones
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        c.name AS paciente_nombre,
        c.email AS paciente_email,
        u.name AS profesional_nombre,
        u.email AS profesional_email,
        u.phone AS profesional_telefono,
        ns.notify_professional_email,
        ns.notify_professional_whatsapp,
        ns.professional_message
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    JOIN users u ON a.user_id = u.id
    LEFT JOIN notification_settings ns ON ns.user_id = a.user_id
    WHERE a.id = ? AND a.email_token = ?
");
$stmt->execute([$turno_id, $token]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    die("Token inválido o turno no encontrado.");
}

// Si ya estaba cancelado, mostrar mensaje
if ($turno['status'] === 'cancelled') {
    echo "<h2>Este turno ya estaba cancelado.</h2>";
    exit;
}

// Cancelar turno
$stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
$stmt->execute([$turno_id]);

// -----------------------------
// NOTIFICAR PROFESIONAL (PHPMailer)
// -----------------------------
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

    enviarEmail($turno['profesional_email'], "Turno cancelado por el paciente", nl2br($msgPro));
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
                $turno['paciente_nombre'],
                date('d/m/Y', strtotime($turno['date'])),
                substr($turno['time'], 0, 5)
            ],
            $turno['professional_message']
        );

        $url = "https://api.callmebot.com/whatsapp.php?phone={$telefonoPro}&text=" . urlencode($msgProWhats);
        @file_get_contents($url);
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Turno cancelado</title>
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
</head>

<body class="bg-slate-100 flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded-xl shadow border max-w-md text-center">

    <h1 class="text-2xl font-bold text-slate-900 mb-4">
        ✖ Turno cancelado
    </h1>

    <p class="text-slate-700 mb-6">
        El turno fue cancelado correctamente.<br>
        Gracias por avisar.
    </p>

    <div class="p-4 bg-slate-50 border rounded-lg mb-6 text-left">
        <p class="text-sm text-slate-500">Profesional</p>
        <p class="font-semibold text-slate-900"><?= htmlspecialchars($turno['profesional_nombre']) ?></p>

        <p class="text-sm text-slate-500 mt-4">Fecha</p>
        <p class="font-semibold text-slate-900"><?= date("d/m/Y", strtotime($turno['date'])) ?></p>

        <p class="text-sm text-slate-500 mt-4">Hora</p>
        <p class="font-semibold text-slate-900"><?= substr($turno['time'], 0, 5) ?> hs</p>
    </div>

    <a href="https://www.turnosaura.com"
       class="block w-full py-3 bg-slate-900 text-white rounded-lg font-semibold hover:bg-slate-800 transition">
        Volver al sitio
    </a>

</div>

</body>
</html>