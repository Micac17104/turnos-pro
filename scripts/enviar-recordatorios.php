<?php
// ===============================================
//  Script global de recordatorios automáticos
//  Ubicación recomendada: /turnos-pro/scripts/
//  Ejecutar con CRON, no desde navegador
// ===============================================

require __DIR__ . '/../config.php';

// Evitar ejecución desde navegador
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse por CLI o CRON.\n");
}

// Fecha de mañana
$mañana = date("Y-m-d", strtotime("+1 day"));

// Obtener turnos de mañana que aún NO fueron recordados
$stmt = $pdo->prepare("
    SELECT a.*, u.phone, u.name 
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.date = ?
      AND a.reminder_sent = 0
");
$stmt->execute([$mañana]);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si no hay turnos, terminar
if (empty($turnos)) {
    echo "No hay recordatorios pendientes.\n";
    exit;
}

foreach ($turnos as $t) {

    // Mensaje del recordatorio
    $mensaje = "Recordatorio: Tenés un turno mañana a las {$t['time']} con {$t['name']}.";

    // ===============================================
    //  EJEMPLO DE ENVÍO REAL (descomentá y configurá)
    // ===============================================
    /*
    $url = "https://api.ultramsg.com/instanceXXXX/messages/chat";
    $data = [
        "token" => "TU_TOKEN",
        "to" => $t['phone'],
        "body" => $mensaje
    ];

    $response = file_get_contents($url . "?" . http_build_query($data));
    */

    // Simulación de envío (para pruebas)
    echo "Recordatorio enviado a {$t['phone']} ({$t['name']})\n";

    // Marcar como enviado
    $update = $pdo->prepare("UPDATE appointments SET reminder_sent = 1 WHERE id = ?");
    $update->execute([$t['id']]);
}

echo "Proceso finalizado.\n";