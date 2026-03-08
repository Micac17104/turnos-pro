<?php
require __DIR__ . '/config.php';

// Buscar turnos de mañana que aún no enviaron recordatorio
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.date,
        a.time,
        c.name AS paciente,
        c.phone AS telefono,
        u.name AS profesional
    FROM appointments a
    JOIN clients c ON c.id = a.client_id
    JOIN users u ON u.id = a.user_id
    WHERE a.date = CURDATE() + INTERVAL 1 DAY
      AND a.recordatorio_enviado = 0
");
$stmt->execute();
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($turnos as $t) {

    $confirmar = "https://turnos-pro-production.up.railway.app/confirmar.php?id=" . $t['id'];
    $cancelar  = "https://turnos-pro-production.up.railway.app/cancelar.php?id=" . $t['id'];

    $mensaje = "Hola {$t['paciente']} 👋

Te recordamos tu turno mañana a las " . substr($t['time'],0,5) . " con {$t['profesional']}.

Confirmar:
$confirmar

Cancelar:
$cancelar";

    enviarWhatsapp($t['telefono'], $mensaje);

    // Marcar como enviado
    $upd = $pdo->prepare("UPDATE appointments SET recordatorio_enviado = 1 WHERE id = ?");
    $upd->execute([$t['id']]);
}

echo "Recordatorios enviados";


// ----------------------
// Función para enviar WhatsApp (CallMeBot)
// ----------------------
function enviarWhatsapp($telefono, $mensaje) {
    $telefono = preg_replace('/\D/', '', $telefono);

    $apikey = "6534626"; // tu API key
    $url = "https://api.callmebot.com/whatsapp.php?phone=$telefono&text=" . urlencode($mensaje) . "&apikey=$apikey";

    file_get_contents($url);
}