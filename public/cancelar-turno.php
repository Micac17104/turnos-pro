<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';
require __DIR__ . '/../auth/mailer.php'; // ← USAMOS EL MAILER BUENO

function debug_log($msg) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO debug_logs (message) VALUES (?)");
    $stmt->execute([$msg]);
}



$paciente_id = $_SESSION['paciente_id'] ?? null;
$turno_id    = $_GET['id'] ?? null;

if (!$turno_id) {
    echo "<p class='text-red-600'>Turno no especificado.</p>";
    echo "</main></div></body></html>";
    exit;
}

// 🔥 NUEVO: obtener turno SIN exigir client_id
$stmt = $pdo->prepare("
    SELECT 
        a.*,
        u.name AS profesional,
        u.profession,
        u.email AS profesional_email,
        u.phone AS profesional_telefono,
        ns.notify_professional_email,
        ns.notify_professional_whatsapp,
        ns.professional_message,
        c.name AS paciente_nombre
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN clients c ON c.id = a.client_id
    LEFT JOIN notification_settings ns ON ns.user_id = a.user_id
    WHERE a.id = ?
");
$stmt->execute([$turno_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    echo "<p class='text-red-600'>No se encontró el turno.</p>";
    echo "</main></div></body></html>";
    exit;
}

// 🔥 NUEVO: validar que el turno pertenece al paciente SOLO si está logueado
if ($paciente_id && $turno['client_id'] && $turno['client_id'] != $paciente_id) {
    echo "<p class='text-red-600'>No tienes permiso para cancelar este turno.</p>";
    echo "</main></div></body></html>";
    exit;
}

// Cancelar turno
$stmt = $pdo->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
$stmt->execute([$turno_id]);

// Datos del turno
$paciente = $turno['paciente_nombre'] ?: ($_SESSION['paciente_nombre'] ?? 'Paciente');
$fecha = date('d/m/Y', strtotime($turno['date']));
$hora = substr($turno['time'], 0, 5);

// -----------------------------
// EMAIL AL PROFESIONAL
// -----------------------------
if (!empty($turno['notify_professional_email']) && !empty($turno['professional_message'])) {

    $msgPro = str_replace(
        ['{paciente}', '{fecha}', '{hora}'],
        [$paciente, $fecha, $hora],
        $turno['professional_message']
    );
debug_log("Email del profesional: " . $turno['profesional_email']);
debug_log("Mensaje profesional: " . ($turno['professional_message'] ?? 'NULL'));
debug_log("Entrando al bloque de envío de email");

    enviarEmail($turno['profesional_email'], "Turno cancelado por el paciente", nl2br($msgPro));
}

// -----------------------------
// WHATSAPP AL PROFESIONAL
// -----------------------------
if (!empty($turno['notify_professional_whatsapp'])) {

    $telefonoPro = preg_replace('/\D/', '', $turno['profesional_telefono'] ?? '');

    if (!empty($telefonoPro)) {

        $msgProWhats = str_replace(
            ['{paciente}', '{fecha}', '{hora}'],
            [$paciente, $fecha, $hora],
            $turno['professional_message']
        );

        $url = "https://api.callmebot.com/whatsapp.php?phone={$telefonoPro}&text=" . urlencode($msgProWhats);
        @file_get_contents($url);
    }
}
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Turno cancelado</h1>

<div class="bg-white p-8 rounded-xl shadow border max-w-md">

    <p class="text-slate-700 mb-4">
        Tu turno fue cancelado correctamente.
    </p>

    <div class="p-4 bg-slate-50 border rounded-lg mb-6">
        <p class="text-sm text-slate-500">Profesional</p>
        <p class="font-semibold text-slate-900"><?= htmlspecialchars($turno['profesional']) ?></p>
        <p class="text-sm text-slate-600"><?= htmlspecialchars($turno['profession']) ?></p>

        <p class="text-sm text-slate-500 mt-4">Fecha original</p>
        <p class="font-semibold text-slate-900"><?= date("d/m/Y", strtotime($turno['date'])) ?></p>

        <p class="text-sm text-slate-500 mt-4">Hora original</p>
        <p class="font-semibold text-slate-900"><?= substr($turno['time'], 0, 5) ?> hs</p>
    </div>

    <a href="paciente-dashboard.php"
       class="block w-full text-center py-3 bg-slate-900 text-white rounded-lg font-semibold hover:bg-slate-800 transition">
        Volver al panel
    </a>

</div>

<?php
echo "</main></div></body></html>";
?>
