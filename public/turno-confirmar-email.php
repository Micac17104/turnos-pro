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
        ns.notify_professional_email
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN clients c ON c.id = a.client_id
    LEFT JOIN notification_settings ns ON ns.user_id = a.user_id
    WHERE a.id = ? AND a.confirm_token = ?
");
$stmt->execute([$turno_id, $token]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    die("Token inválido o turno no encontrado.");
}

$estado_actual = $turno['status'];

if ($estado_actual === 'confirmed') {
    echo "<h2>Este turno ya estaba confirmado anteriormente.</h2>";
    exit;
}

if ($estado_actual === 'cancelled') {
    echo "<h2>Este turno ya había sido cancelado, no se puede confirmar.</h2>";
    exit;
}

// Si estaba pending → confirmar AHORA
$stmt = $pdo->prepare("UPDATE appointments SET status='confirmed' WHERE id=?");
$stmt->execute([$turno_id]);

$paciente = $turno['paciente_nombre'] ?: 'Paciente';
$fecha = date('d/m/Y', strtotime($turno['date']));
$hora = substr($turno['time'], 0, 5);

if (!empty($turno['notify_professional_email'])) {
    $msgPro = "
        Hola {$turno['profesional']},<br><br>
        El paciente <strong>{$paciente}</strong> confirmó su turno:<br><br>
        <strong>Fecha:</strong> {$fecha}<br>
        <strong>Hora:</strong> {$hora}<br><br>
        TurnosAura
    ";
    enviarEmail($turno['profesional_email'], "Turno confirmado por el paciente", $msgPro);
}

echo "<h2>Tu turno fue confirmado correctamente.</h2>";
echo "<a href='/'>Volver al inicio</a>";
