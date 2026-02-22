<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Editar turno';
$current    = 'agenda';

$turno_id = require_param($_GET, 'id', 'Turno no encontrado.');

// Obtener turno
$stmt = $pdo->prepare("
    SELECT a.*, c.name AS paciente
    FROM appointments a
    LEFT JOIN clients c ON c.id = a.client_id
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->execute([$turno_id, $user_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    die("No tienes permiso para editar este turno.");
}

// Obtener pacientes
$stmt = $pdo->prepare("
    SELECT id, name
    FROM clients
    WHERE user_id = ?
    ORDER BY name ASC
");
$stmt->execute([$user_id]);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Editar turno</h1>

    <form method="post" action="turno-guardar-agenda.php"
          class="space-y-6 bg-white p-6 rounded-xl shadow-sm border border-slate-200">

        <input type="hidden" name="turno_id" value="<?= $turno_id ?>">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Paciente</label>
            <select name="client_id"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">

                <option value="">Paciente no registrado</option>

                <?php foreach ($pacientes as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $turno['client_id'] ? 'selected' : '' ?>>
                        <?= h($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                <input type="date" name="date" required
                       value="<?= h($turno['date']) ?>"
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Hora</label>
                <input type="text" name="time" id="edit_time" required
                       value="<?= substr($turno['time'], 0, 5) ?>"
                       placeholder="HH:MM" maxlength="5" pattern="[0-9]{2}:[0-9]{2}"
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
            <select name="status"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                <option value="pending"   <?= $turno['status']=='pending'?'selected':'' ?>>Pendiente</option>
                <option value="confirmed" <?= $turno['status']=='confirmed'?'selected':'' ?>>Confirmado</option>
                <option value="attended"  <?= $turno['status']=='attended'?'selected':'' ?>>Atendido</option>
                <option value="cancelled" <?= $turno['status']=='cancelled'?'selected':'' ?>>Cancelado</option>
            </select>
        </div>

        <div class="flex justify-between items-center pt-4">
            <a href="pago-editar.php?id=<?= $turno_id ?>"
               class="text-sm text-blue-600 hover:underline">
                Editar pago →
            </a>

            <div class="flex gap-3">
                <a href="agenda.php"
                   class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm hover:bg-slate-300">
                    Cancelar
                </a>

                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800">
                    Guardar cambios
                </button>
            </div>
        </div>

    </form>

</main>

<script>
document.querySelectorAll('#edit_time').forEach(input => {
    input.addEventListener('input', e => {
        let v = e.target.value.replace(/[^0-9]/g, '');
        if (v.length >= 3) v = v.slice(0,2) + ':' + v.slice(2,4);
        e.target.value = v;
    });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>