<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

$pro_id = $_POST['user_id'] ?? null;
$date   = $_POST['fecha'] ?? null;
$time   = $_POST['hora'] ?? null;

$paciente_id = $_SESSION['paciente_id'] ?? null;

// Obtener profesional
$stmt = $pdo->prepare("SELECT id, name, email, profession, parent_center_id FROM users WHERE id = ?");
$stmt->execute([$pro_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Profesional no encontrado.");
}

// Si el profesional pertenece a un centro → ese es el center_id del turno y del paciente nuevo
$center_id = $pro['parent_center_id'] ?: null;

// -----------------------------
// PACIENTE NO LOGUEADO
// -----------------------------
if (!$paciente_id) {

    $name  = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['telefono'] ?? '');

    if (!$pro_id || !$date || !$time || !$name || !$email) {
        die("Datos incompletos.");
    }

    // Buscar paciente por email
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
    $stmt->execute([$email]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        // Crear paciente nuevo y ASOCIARLO AL CENTRO si corresponde
        $stmt = $pdo->prepare("
            INSERT INTO clients (name, email, phone, center_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$name, $email, $phone, $center_id]);

        $paciente_id = $pdo->lastInsertId();
        $_SESSION['paciente_id'] = $paciente_id;

    } else {
        $paciente_id = $paciente['id'];
        $name  = $paciente['name'];
        $email = $paciente['email'];
        $phone = $paciente['phone'];
    }

// -----------------------------
// PACIENTE LOGUEADO
// -----------------------------
} else {

    $stmt = $pdo->prepare("SELECT name, email, phone FROM clients WHERE id = ?");
    $stmt->execute([$paciente_id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        die("Paciente no encontrado.");
    }

    $name  = $paciente['name'];
    $email = $paciente['email'];
    $phone = $paciente['phone'];
}

// -----------------------------
// VERIFICAR QUE EL TURNO SIGA LIBRE
// -----------------------------
$stmt = $pdo->prepare("
    SELECT id FROM appointments
    WHERE user_id = ? AND date = ? AND time = ? AND status IN ('confirmed','pending')
");
$stmt->execute([$pro_id, $date, $time]);

if ($stmt->fetch()) {
    die("El turno ya fue tomado. Volvé atrás y elegí otro horario.");
}

// -----------------------------
// CREAR TURNO
// -----------------------------
$stmt = $pdo->prepare("
    INSERT INTO appointments (user_id, center_id, client_id, date, time, status, reminder_sent)
    VALUES (?, ?, ?, ?, ?, 'confirmed', 0)
");
$stmt->execute([$pro_id, $center_id, $paciente_id, $date, $time]);

$turno_id = $pdo->lastInsertId();

// -----------------------------
// NOTIFICACIONES
// -----------------------------
$stmt = $pdo->prepare("SELECT * FROM notification_settings WHERE user_id = ?");
$stmt->execute([$pro_id]);
$config = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$telefono_normalizado = preg_replace('/\D/', '', $phone);

if (!empty($config['confirm_message'])) {
    $mensaje_final = str_replace(
        ['{nombre}', '{fecha}', '{hora}', '{profesional}'],
        [$name, $date, $time, $pro['name']],
        $config['confirm_message']
    );
} else {
    $mensaje_final = "Hola $name, tu turno con {$pro['name']} fue confirmado para el $date a las $time.";
}

if (!empty($config['email_enabled'])) {
    @mail($email, "Confirmación de turno", $mensaje_final);
}

if (!empty($config['notify_professional_email']) && !empty($config['professional_message'])) {
    $msg_pro = str_replace(
        ['{paciente}', '{fecha}', '{hora}'],
        [$name, $date, $time],
        $config['professional_message']
    );
    @mail($pro['email'], "Nuevo turno reservado", $msg_pro);
}

// -----------------------------
// GUARDAR DATOS PARA PANTALLA DE GRACIAS
// -----------------------------
$_SESSION['last_booking'] = [
    'pro_name' => $pro['name'],
    'date'     => $date,
    'time'     => $time,
    'whatsapp_enabled' => !empty($config['whatsapp_enabled']),
    'telefono_normalizado' => $telefono_normalizado,
    'mensaje_final' => $mensaje_final
];

// -----------------------------
// REDIRIGIR
// -----------------------------
header("Location: gracias.php");
exit;