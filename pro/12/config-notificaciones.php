<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Obtener configuración actual
$stmt = $pdo->prepare("SELECT * FROM notification_settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$config = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe, crear registro
if (!$config) {
    $pdo->prepare("INSERT INTO notification_settings (user_id) VALUES (?)")->execute([$user_id]);
    $stmt->execute([$user_id]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Notificaciones</title>

<style>
    body { background:#f5f5f5; font-family:Arial; }
    .container { max-width:700px; margin:40px auto; background:white; padding:25px; border-radius:14px; }
    h2 { color:#0f172a; font-weight:700; margin-bottom:20px; }
    label { display:block; margin-top:15px; font-weight:600; }
    textarea, input[type="number"] {
        width:100%; padding:10px; border-radius:10px;
        border:1px solid #d1d5db; margin-top:5px;
    }
    .btn { margin-top:20px; padding:12px 20px; background:#0ea5e9; color:white; border-radius:999px; border:none; cursor:pointer; }
    .info { background:#eef6ff; padding:10px; border-radius:10px; margin-top:20px; font-size:14px; }
</style>
</head>
<body>

<div class="container">

    <h2>Configuración de notificaciones</h2>

    <?php if (isset($_GET['ok'])): ?>
        <div class="info">Cambios guardados correctamente.</div>
    <?php endif; ?>

    <form method="post" action="/turnos-pro/profiles/<?= $user_id ?>/guardar-notificaciones.php">

        <h3>Notificaciones al paciente</h3>

        <label>
            <input type="checkbox" name="whatsapp_enabled" <?= $config['whatsapp_enabled'] ? 'checked' : '' ?>>
            Enviar WhatsApp al paciente
        </label>

        <label>
            <input type="checkbox" name="email_enabled" <?= $config['email_enabled'] ? 'checked' : '' ?>>
            Enviar Email al paciente
        </label>

        <label>Mensaje de confirmación al paciente</label>
        <textarea name="confirm_message" rows="3"><?= htmlspecialchars($config['confirm_message']) ?></textarea>

        <label>Mensaje de recordatorio</label>
        <textarea name="reminder_message" rows="3"><?= htmlspecialchars($config['reminder_message']) ?></textarea>

        <label>Horas antes del turno para enviar recordatorio</label>
        <input type="number" name="reminder_hours_before" value="<?= $config['reminder_hours_before'] ?>">

        <hr style="margin:30px 0;">

        <h3>Notificaciones al profesional</h3>

        <label>
            <input type="checkbox" name="notify_professional_whatsapp" <?= $config['notify_professional_whatsapp'] ? 'checked' : '' ?>>
            Recibir WhatsApp cuando un paciente reserva/cancela/reprograma
        </label>

        <label>
            <input type="checkbox" name="notify_professional_email" <?= $config['notify_professional_email'] ? 'checked' : '' ?>>
            Recibir Email cuando un paciente reserva/cancela/reprograma
        </label>

        <label>Mensaje para mí (profesional)</label>
        <textarea name="professional_message" rows="3"><?= htmlspecialchars($config['professional_message']) ?></textarea>

        <div class="info">
            Podés usar estos placeholders:<br><br>
            <strong>{paciente}</strong> → nombre del paciente<br>
            <strong>{fecha}</strong> → fecha del turno<br>
            <strong>{hora}</strong> → hora del turno<br>
        </div>

        <button class="btn">Guardar configuración</button>

    </form>

</div>

</body>
</html>