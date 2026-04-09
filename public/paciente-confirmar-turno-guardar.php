<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../auth/mailer.php';   // Mailer correcto

$pro_id    = $_POST['user_id'] ?? null;
$date      = $_POST['fecha'] ?? null;
$time      = $_POST['hora'] ?? null;
$name      = trim($_POST['nombre'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['telefono'] ?? '');
$center_id = !empty($_POST['center_id']) ? (int)$_POST['center_id'] : null;
$motivo    = trim($_POST['motivo'] ?? '');

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

// 🔥 GENERAR TOKENS
$confirm_token = bin2hex(random_bytes(16));
$cancel_token  = bin2hex(random_bytes(16));

// 🔥 CREAR TURNO COMO PENDING
$stmt = $pdo->prepare("
    INSERT INTO appointments 
        (user_id, center_id, client_id, date, time, motivo, status, reminder_sent, confirm_token, cancel_token)
    VALUES 
        (?, ?, ?, ?, ?, ?, 'pending', 0, ?, ?)
");
$stmt->execute([
    $pro_id,
    $center_id,
    $paciente_id,
    $date,
    $time,
    $motivo,
    $confirm_token,
    $cancel_token
]);

$turno_id = $pdo->lastInsertId();

// Config notificaciones
$stmt = $pdo->prepare("SELECT * FROM notification_settings WHERE user_id = ?");
$stmt->execute([$pro_id]);
$config = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$telefono_normalizado = preg_replace('/\D/', '', $phone);

// -----------------------------
// EMAIL AL PACIENTE (CON LINKS)
// -----------------------------

$confirm_link = "https://www.turnosaura.com/turno-confirmar-email.php?id=$turno_id&token=$confirm_token";
$cancel_link  = "https://www.turnosaura.com/turno-cancelar-email.php?id=$turno_id&token=$cancel_token";

$mensaje_final = "
    Hola $name,<br><br>
    Tu turno con <strong>{$pro['name']}</strong> fue registrado para el <strong>$date</strong> a las <strong>$time</strong>.<br><br>

    Para continuar, elegí una opción:<br><br>

    👉 <a href='$confirm_link'>Confirmar turno</a><br>
    ❌ <a href='$cancel_link'>Cancelar turno</a><br><br>

    Si no realizás ninguna acción, el turno quedará pendiente.<br><br>
    TurnosAura
";

enviarEmail($email, "Confirmación de turno", $mensaje_final);

// -----------------------------
// EMAIL AL PROFESIONAL
// -----------------------------

if (!empty($config['notify_professional_email'])) {

    $msg_pro = "
        Hola {$pro['name']},<br><br>
        El paciente <strong>{$name}</strong> reservó un turno:<br><br>
        <strong>Fecha:</strong> {$date}<br>
        <strong>Hora:</strong> {$time}<br><br>
        TurnosAura
    ";

    enviarEmail($pro['email'], "Nuevo turno reservado", $msg_pro);
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
