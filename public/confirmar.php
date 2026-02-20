<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

$pro_id = $_POST['pro']  ?? null;
$date   = $_POST['date'] ?? null;
$time   = $_POST['time'] ?? null;
$name   = trim($_POST['name']  ?? '');
$email  = trim($_POST['email'] ?? '');
$phone  = trim($_POST['phone'] ?? '');

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

// Buscar o crear paciente
$paciente_id = $_SESSION['paciente_id'] ?? null;

if ($paciente_id) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$paciente_id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ? AND user_id = ?");
    $stmt->execute([$email, $pro_id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$paciente) {
    $stmt = $pdo->prepare("
        INSERT INTO clients (user_id, name, email, phone)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$pro_id, $name, $email, $phone]);
    $paciente_id = $pdo->lastInsertId();
} else {
    $paciente_id = $paciente['id'];
}

// Verificar que el turno siga libre
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
    INSERT INTO appointments (user_id, client_id, date, time, status, reminder_sent)
    VALUES (?, ?, ?, ?, 'confirmed', 0)
");
$stmt->execute([$pro_id, $paciente_id, $date, $time]);
$turno_id = $pdo->lastInsertId();

// Notificaciones
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

// Email al paciente
if (!empty($config['email_enabled'])) {
    @mail($email, "Confirmación de turno", $mensaje_final);
}

// Notificar profesional
if (!empty($config['notify_professional_email']) && !empty($config['professional_message'])) {
    $msg_pro = str_replace(
        ['{paciente}', '{fecha}', '{hora}'],
        [$name, $date, $time],
        $config['professional_message']
    );
    @mail($pro['email'], "Nuevo turno reservado", $msg_pro);
}

// Guardar en sesión para la pantalla de gracias
$_SESSION['last_booking'] = [
    'pro_name' => $pro['name'],
    'date'     => $date,
    'time'     => $time,
    'whatsapp_enabled' => !empty($config['whatsapp_enabled']),
    'telefono_normalizado' => $telefono_normalizado,
    'mensaje_final' => $mensaje_final
];

// REDIRECCIÓN CORRECTA PARA RAILWAY
header("Location: gracias.php");
exit;