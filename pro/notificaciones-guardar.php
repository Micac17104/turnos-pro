<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Notificaciones';
$current    = 'notificaciones';

// Obtener configuración actual
$stmt = $pdo->prepare("
    SELECT *
    FROM notification_settings
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe, crear configuración por defecto
if (!$settings) {
    $stmt = $pdo->prepare("
        INSERT INTO notification_settings (user_id)
        VALUES (?)
    ");
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare("
        SELECT *
        FROM notification_settings
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Notificaciones</h1>

    <form method="post" action="/turnos-pro/pro/notificaciones-guardar.php"
          class="space-y-10">

        <!-- NOTIFICACIONES AL PACIENTE -->
        <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Notificaciones al paciente</h2>

            <div class="space-y-4">

                <!-- WhatsApp -->
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="whatsapp_enabled"
                           <?= $settings['whatsapp_enabled'] ? 'checked' : '' ?>
                           class="w-4 h-4">
                    <span class="text-sm text-slate-700">Enviar WhatsApp de confirmación</span>
                </label>

                <!-- Email -->
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="email_enabled"
                           <?= $settings['email_enabled'] ? 'checked' : '' ?>
                           class="w-4 h-4">
                    <span class="text-sm text-slate-700">Enviar email de confirmación</span>
                </label>

                <!-- Mensaje de confirmación -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Mensaje de confirmación
                    </label>
                    <textarea name="confirm_message" rows="3"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm"><?= h($settings['confirm_message']) ?></textarea>
                </div>

                <!-- Recordatorio -->
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="reminder_enabled"
                           <?= $settings['reminder_enabled'] ? 'checked' : '' ?>
                           class="w-4 h-4">
                    <span class="text-sm text-slate-700">Enviar recordatorio antes del turno</span>
                </label>

                <!-- Horas antes -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Horas antes del turno
                    </label>
                    <input type="number" name="reminder_hours_before"
                           value="<?= h($settings['reminder_hours_before']) ?>"
                           class="w-full max-w-xs px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>

                <!-- Mensaje de recordatorio -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Mensaje de recordatorio
                    </label>
                    <textarea name="reminder_message" rows="3"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm"><?= h($settings['reminder_message']) ?></textarea>
                </div>

            </div>
        </section>

        <!-- NOTIFICACIONES AL PROFESIONAL -->
        <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Notificaciones al profesional</h2>

            <div class="space-y-4">

                <!-- WhatsApp -->
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="notify_professional_whatsapp"
                           <?= $settings['notify_professional_whatsapp'] ? 'checked' : '' ?>
                           class="w-4 h-4">
                    <span class="text-sm text-slate-700">Notificar por WhatsApp cuando se reserva un turno</span>
                </label>

                <!-- Email -->
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="notify_professional_email"
                           <?= $settings['notify_professional_email'] ? 'checked' : '' ?>
                           class="w-4 h-4">
                    <span class="text-sm text-slate-700">Notificar por email cuando se reserva un turno</span>
                </label>

                <!-- Mensaje al profesional -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Mensaje al profesional
                    </label>
                    <textarea name="professional_message" rows="3"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm"><?= h($settings['professional_message']) ?></textarea>
                </div>

            </div>
        </section>

        <!-- BOTONES -->
        <div class="flex justify-end gap-3">
            <a href="/turnos-pro/pro/index.php"
               class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm hover:bg-slate-300">
                Cancelar
            </a>

            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800">
                Guardar cambios
            </button>
        </div>

    </form>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>