<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../auth/mailer.php';   // ← USAMOS EL MAILER BUENO

$pro_id    = $_POST['user_id'] ?? null;
$date      = $_POST['fecha'] ?? null;
$time      = $_POST['hora'] ?? null;
$name      = trim($_POST['nombre'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['telefono'] ?? '');
$center_id = $_POST['center_id'] ?? null;

if (!$pro_id || !$date || !$time || !$name || !$email) {
    die("Datos incompletos.");
}

// Profesional
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$pro_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Profesional no encontrado.");
}

// Detectar si pertenece a un centro
$isCentro = !empty($pro['parent_center_id']);

// Buscar o crear paciente
$paciente_id = $_SESSION['paciente_id'] ?? null;

if ($paciente_id) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$paciente_id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ? AND center_id = ?");
    $stmt->execute([$email, $center_id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$paciente) {
    $stmt = $pdo->prepare("
        INSERT INTO clients (name, email, phone, center_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $phone, $center_id]);
    $paciente_id = $pdo->lastInsertId();
} else {
    $paciente_id = $paciente['id'];
}

// Verificar turno libre
$stmt = $pdo->prepare("
    SELECT id FROM appointments
    WHERE user_id = ? AND date = ? AND time = ? AND status IN ('confirmed','pending')
");
$stmt->execute([$pro_id, $date, $time]);

if ($stmt->fetch()) {
    die("El turno ya fue tomado. Volvé atrás y elegí otro horario.");
}

// Crear turno
$stmt = $pdo->prepare("
    INSERT INTO appointments (user_id, center_id, client_id, date, time, status, reminder_sent)
    VALUES (?, ?, ?, ?, ?, 'confirmed', 0)
");
$stmt->execute([$pro_id, $center_id, $paciente_id, $date, $time]);

$turno_id = $pdo->lastInsertId();

// Config notificaciones
$stmt = $pdo->prepare("SELECT * FROM notification_settings WHERE user_id = ?");
$stmt->execute([$pro_id]);
$config = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$telefono_normalizado = preg_replace('/\D/', '', $phone);

// Mensaje al paciente
if (!empty($config['confirm_message'])) {
    $mensaje_final = str_replace(
        ['{nombre}', '{fecha}', '{hora}', '{profesional}'],
        [$name, $date, $time, $pro['name']],
        $config['confirm_message']
    );
} else {
    $mensaje_final = "Hola $name, tu turno con {$pro['name']} fue confirmado para el $date a las $time.";
}

// -----------------------------
// EMAIL AL PACIENTE
// -----------------------------

// Profesional individual → respeta configuraciones
if (!$isCentro && !empty($config['email_enabled'])) {
    enviarEmail($email, "Confirmación de turno", nl2br($mensaje_final));
}

// Profesional del centro → SIEMPRE enviar email
if ($isCentro) {
    enviarEmail($email, "Confirmación de turno", nl2br($mensaje_final));
}

// -----------------------------
// EMAIL AL PROFESIONAL
// -----------------------------

// Profesional individual → respeta configuraciones
if (!$isCentro && !empty($config['notify_professional_email']) && !empty($config['professional_message'])) {

    $msg_pro = str_replace(
        ['{paciente}', '{fecha}', '{hora}'],
        [$name, $date, $time],
        $config['professional_message']
    );

    enviarEmail($pro['email'], "Nuevo turno reservado", nl2br($msg_pro));
}

// Profesional del centro → SIEMPRE enviar email
if ($isCentro) {

    $msgCentro = "
        Hola {$pro['name']},<br><br>
        El paciente <strong>{$name}</strong> reservó un turno:<br><br>
        <strong>Fecha:</strong> $date<br>
        <strong>Hora:</strong> $time<br><br>
        TurnosAura
    ";

    enviarEmail($pro['email'], "Nuevo turno reservado", $msgCentro);
}

// Guardar datos para pantalla de gracias
$_SESSION['last_booking'] = [
    'pro_name' => $pro['name'],
    'date'     => $date,
    'time'     => $time,
    'whatsapp_enabled' => !empty($config['whatsapp_enabled']),
    'telefono_normalizado' => $telefono_normalizado,
    'mensaje_final' => $mensaje_final
];

// Redirigir
header("Location: gracias.php");
exit;
