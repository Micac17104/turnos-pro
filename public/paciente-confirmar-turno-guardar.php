<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

$pro_id = $_POST['user_id'] ?? null;
$date   = $_POST['fecha'] ?? null;
$time   = $_POST['hora'] ?? null;

$paciente_id = $_SESSION['paciente_id'] ?? null;

// Si el paciente NO está logueado → usa los datos del formulario
if (!$paciente_id) {
    $name  = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['telefono'] ?? '');

    if (!$pro_id || !$date || !$time || !$name || !$email) {
        die("Datos incompletos.");
    }

} else {
    // Paciente logueado → obtener datos desde la base
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

// Obtener profesional
$stmt = $pdo->prepare("SELECT id, name, email, profession, parent_center_id FROM users WHERE id = ?");
$stmt->execute([$pro_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    die("Profesional no encontrado.");
}

$center_id = $pro['parent_center_id'] ?: null;

// Si el paciente no estaba logueado, buscarlo por email
if (!$paciente_id) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
    $stmt->execute([$email]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        $stmt = $pdo->prepare("
            INSERT INTO clients (name, email, phone, center_id)
            VALUES (?, ?, ?, NULL)
        ");
        $stmt->execute([$name, $email, $phone]);
        $paciente_id = $pdo->lastInsertId();
        $_SESSION['paciente_id'] = $paciente_id;
    } else {
        $paciente_id = $paciente['id'];
    }
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

// Notificaciones
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

$_SESSION['last_booking'] = [
    'pro_name' => $pro['name'],
    'date'     => $date,
    'time'     => $time,
    'whatsapp_enabled' => !empty($config['whatsapp_enabled']),
    'telefono_normalizado' => $telefono_normalizado,
    'mensaje_final' => $mensaje_final
];

header("Location: gracias.php");
exit;