<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$patient_id = require_param($_GET, 'id', 'Paciente no encontrado.');

// Verificar que el paciente pertenece al profesional
$stmt = $pdo->prepare("
    SELECT id, name, email, phone, dni, is_recurring, recurring_day, recurring_time, recurring_until
    FROM clients
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$patient_id, $user_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no pertenece a este profesional.");
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">
        Editar datos personales de <?= h($paciente['name']) ?>
    </h1>

    <form method="post" action="paciente-editar-guardar.php"
          class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">

        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo</label>
            <input type="text" name="name" value="<?= h($paciente['name']) ?>"
                   class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" value="<?= h($paciente['email']) ?>"
                   class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
            <input type="text" name="phone" value="<?= h($paciente['phone']) ?>"
                   class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">DNI</label>
            <input type="text" name="dni" value="<?= h($paciente['dni']) ?>"
                   class="w-full px-3 py-2 border rounded-lg">
        </div>

        <!-- NUEVO: Paciente recurrente -->
        <div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_recurring" value="1"
                       <?= !empty($paciente['is_recurring']) ? 'checked' : '' ?>
                       onclick="document.getElementById('recurrenceFields').classList.toggle('hidden', !this.checked)">
                Paciente recurrente
            </label>
        </div>

        <!-- Campos de recurrencia -->
        <div id="recurrenceFields" class="<?= !empty($paciente['is_recurring']) ? '' : 'hidden' ?>">
            <label>Día de la semana:</label>
            <select name="recurring_day" class="w-full p-3 mb-3 border rounded">
                <option value="">Seleccionar...</option>
                <option value="Monday" <?= $paciente['recurring_day']==='Monday'?'selected':'' ?>>Lunes</option>
                <option value="Tuesday" <?= $paciente['recurring_day']==='Tuesday'?'selected':'' ?>>Martes</option>
                <option value="Wednesday" <?= $paciente['recurring_day']==='Wednesday'?'selected':'' ?>>Miércoles</option>
                <option value="Thursday" <?= $paciente['recurring_day']==='Thursday'?'selected':'' ?>>Jueves</option>
                <option value="Friday" <?= $paciente['recurring_day']==='Friday'?'selected':'' ?>>Viernes</option>
                <option value="Saturday" <?= $paciente['recurring_day']==='Saturday'?'selected':'' ?>>Sábado</option>
                <option value="Sunday" <?= $paciente['recurring_day']==='Sunday'?'selected':'' ?>>Domingo</option>
            </select>

            <label>Hora:</label>
            <input type="time" name="recurring_time"
                   value="<?= h($paciente['recurring_time'] ?? '') ?>"
                   class="w-full p-3 mb-3 border rounded">

            <label>Hasta (opcional):</label>
            <input type="date" name="recurring_until"
                   value="<?= h($paciente['recurring_until'] ?? '') ?>"
                   class="w-full p-3 mb-3 border rounded">
        </div>

        <div class="flex justify-end gap-3">
            <a href="paciente-historia.php?id=<?= $patient_id ?>"
               class="px-4 py-2 bg-slate-200 rounded-lg">Cancelar</a>

            <button class="px-4 py-2 bg-slate-900 text-white rounded-lg">
                Guardar cambios
            </button>
        </div>

    </form>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
