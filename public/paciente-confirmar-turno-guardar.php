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
$dni       = trim($_POST['dni'] ?? '');   // ← AGREGADO
$center_id = !empty($_POST['center_id']) ? (int)$_POST['center_id'] : null;
$motivo    = trim($_POST['motivo'] ?? '');

if (!$pro_id || !$date || !$time || !$name || !$email || !$dni) {
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

    // 🔥 INSERT CORREGIDO CON DNI
    $stmt = $pdo->prepare("
        INSERT INTO clients (name, email, phone, dni, center_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $phone, $dni, $center_id]);

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

// --------------------------------------
// BLOQUEAR SI NO TIENE SESIONES DISPONIBLES
// --------------------------------------
$stmt = $pdo->prepare("
    SELECT pc.id AS pc_id, p.total_sessions, pc.sessions_used
    FROM packs_clients pc
    JOIN packs p ON p.id = pc.pack_id
    WHERE pc.client_id = ?
      AND p.owner_type = 'professional'
      AND p.owner_id = ?
    ORDER BY pc.id DESC
    LIMIT 1
");
$stmt->execute([$paciente_id, $pro_id]);
$pack = $stmt->fetch(PDO::FETCH_ASSOC);

if ($pack) {
    $restantes = $pack['total_sessions'] - $pack['sessions_used'];

    if ($restantes <= 0) {
        die("No tenés sesiones disponibles en tu pack. Contactá a tu profesional.");
    }
}


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

// --------------------------------------
// DESCONTAR SESIÓN DE PACK (si aplica)
// --------------------------------------
$client_id = $paciente_id; // para unificar nombres

$stmt = $pdo->prepare("
    SELECT pc.id AS pc_id, p.total_sessions, pc.sessions_used
    FROM packs_clients pc
    JOIN packs p ON p.id = pc.pack_id
    WHERE pc.client_id = ?
      AND p.owner_type = 'professional'
      AND p.owner_id = ?
    ORDER BY pc.id DESC
    LIMIT 1
");
$stmt->execute([$client_id, $pro_id]);
$pack = $stmt->fetch(PDO::FETCH_ASSOC);

if ($pack) {
    $restantes = $pack['total_sessions'] - $pack['sessions_used'];

    if ($restantes > 0) {
        $stmt = $pdo->prepare("
            UPDATE packs_clients
            SET sessions_used = sessions_used + 1
            WHERE id = ?
        ");
        $stmt->execute([$pack['pc_id']]);
    }
}


$turno_id = $pdo->lastInsertId();

// Config notificaciones
$stmt = $pdo->prepare("SELECT * FROM notification_settings WHERE user_id = ?");
$stmt->execute([$pro_id]);
$config = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$telefono_normalizado = preg_replace('/\D/', '', $phone);

// -----------------------------
// EMAIL AL PACIENTE (CON LINKS)
// -----------------------------

$confirm_link = "https://www.turnosaura.com/public/turno-confirmar-email.php?id=$turno_id&token=$confirm_token";
$cancel_link  = "https://www.turnosaura.com/public/turno-cancelar-email.php?id=$turno_id&token=$cancel_token";

$mensaje_final = "
    Hola $name,<br><br>
    Tu turno con <strong>{$pro['name']}</strong> fue registrado para el <strong>$date</strong> a las <strong>$time</strong>.<br><br>

    Para continuar, elegí una opción:<br><br>

    👉 <a href='$confirm_link'>Confirmar turno</a><br>
    ❌ <a href='$cancel_link'>Cancelar turno</a><br><br>

    Si no realizás ninguna acción, el turno quedará pendiente.<br><br>
";

// 🔥 AGREGADO: LINK DE VIDEOLLAMADA
if (!empty($pro['video_link'])) {
    $mensaje_final .= "
        <br><strong>Link de videollamada:</strong><br>
        <a href='{$pro['video_link']}'>{$pro['video_link']}</a><br><br>
    ";
}

$mensaje_final .= "TurnosAura";

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
