<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Crear turno';
$current    = 'agenda';

// Obtener pacientes del profesional
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

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Crear turno</h1>

    <form method="post" action="/turnos-pro/pro/turno-guardar.php"
          class="space-y-6 bg-white p-6 rounded-xl shadow-sm border border-slate-200">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Paciente</label>
            <select name="client_id" required
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                <option value="">Seleccionar</option>
                <?php foreach ($pacientes as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= h($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                <input type="date" name="date" required
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Hora</label>
                <input type="time" name="time" required
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="/turnos-pro/pro/agenda.php"
               class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm hover:bg-slate-300">
                Cancelar
            </a>

            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800">
                Guardar turno
            </button>
        </div>

    </form>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>