<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/../config.php';

// Verificar login
if (!isset($_SESSION['paciente_id'])) {
    header("Location: login-paciente.php");
    exit;
}

$paciente_id = $_SESSION['paciente_id'];

// Acepta ambos nombres: id y turno_id
$turno_id = $_GET['turno_id'] ?? $_GET['id'] ?? null;

$nueva_fecha = $_GET['fecha'] ?? null;
$nueva_hora = $_GET['hora'] ?? null;

if (!$turno_id || !$nueva_fecha || !$nueva_hora) {
    die("Datos incompletos.");
}

// Turno original
$stmt = $pdo->prepare("
    SELECT a.*, u.name AS profesional, u.email AS pro_email, u.phone AS pro_phone,
           c.name AS paciente_nombre, c.email AS paciente_email, c.phone AS paciente_phone
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN clients c ON a.client_id = c.id
    WHERE a.id = ? AND a.client_id = ?
");
$stmt->execute([$turno_id, $paciente_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    echo "<h2>Error</h2>";
    echo "<p>No se encontró el turno.</p>";
    exit;
}

// 1) Cancelar turno viejo
$stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
$stmt->execute([$turno_id]);

// 2) Crear turno nuevo
$stmt = $pdo->prepare("
    INSERT INTO appointments (user_id, client_id, date, time, status, reminder_sent)
    VALUES (?, ?, ?, ?, 'confirmed', 0)
");
$stmt->execute([$turno['user_id'], $paciente_id, $nueva_fecha, $nueva_hora]);
$nuevo_turno_id = $pdo->lastInsertId();

// Configuración del profesional
$stmt = $pdo->prepare("SELECT * FROM notification_settings WHERE user_id = ?");
$stmt->execute([$turno['user_id']]);
$config = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Mensaje al paciente
if (!empty($config['confirm_message'])) {
    $mensaje = str_replace(
        ['{nombre}', '{fecha}', '{hora}', '{profesional}'],
        [$turno['paciente_nombre'], $nueva_fecha, $nueva_hora, $turno['profesional']],
        $config['confirm_message']
    );
} else {
    $mensaje = "Hola {$turno['paciente_nombre']}, tu turno fue reprogramado para el $nueva_fecha a las $nueva_hora con {$turno['profesional']}.";
}

// WhatsApp paciente
$telefono = preg_replace('/\D/', '', $turno['paciente_phone'] ?? '');
$url_whatsapp = "https://api.whatsapp.com/send?phone=$telefono&text=" . urlencode($mensaje);

// Email al paciente
if (!empty($config['email_enabled'])) {
    @mail($turno['paciente_email'], "Reprogramación de turno", $mensaje);
}

// Notificar profesional por email
if (!empty($config['notify_professional_email']) && !empty($config['professional_message'])) {
    $msg_pro = str_replace(
        ['{paciente}', '{fecha}', '{hora}'],
        [$turno['paciente_nombre'], $nueva_fecha, $nueva_hora],
        $config['professional_message']
    );
    @mail($turno['pro_email'], "Turno reprogramado", $msg_pro);
}

// Incluir layout premium
require __DIR__ . '/paciente-layout.php';
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Turno reprogramado</h1>

<div class="bg-white p-8 rounded-xl shadow border max-w-md">

    <p class="text-slate-700 mb-4">
        Tu turno fue reprogramado correctamente.
    </p>

    <div class="p-4 bg-slate-50 border rounded-lg mb-6">
        <p class="text-sm text-slate-500">Nueva fecha</p>
        <p class="font-semibold text-slate-900"><?= date("d/m/Y", strtotime($nueva_fecha)) ?></p>

        <p class="text-sm text-slate-500 mt-3">Nueva hora</p>
        <p class="font-semibold text-slate-900"><?= substr($nueva_hora, 0, 5) ?> hs</p>
    </div>

    <?php if (!empty($config['whatsapp_enabled']) && $telefono): ?>
        <a href="<?= $url_whatsapp ?>" target="_blank"
           class="block w-full text-center py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
            Enviar confirmación por WhatsApp
        </a>
    <?php endif; ?>

    <!-- RUTA CORRECTA -->
    <a href="paciente-dashboard.php"
       class="block mt-6 text-slate-600 hover:text-slate-900 text-sm text-center">
        ← Volver al panel
    </a>

</div>

<?php
echo "</main></div></body></html>";
?>