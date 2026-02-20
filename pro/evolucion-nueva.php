<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Nueva evolución';
$current    = 'pacientes';

$patient_id = require_param($_GET, 'patient_id', 'Paciente no encontrado.');

// Verificar que el paciente pertenece al profesional
$stmt = $pdo->prepare("
    SELECT id, name
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

    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">
            Nueva evolución de <?= h($paciente['name']) ?>
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Registrá la evolución clínica del paciente.
        </p>
    </div>

    <form method="post" action="/turnos-pro/pro/evolucion-guardar.php"
          class="space-y-6 bg-white p-6 rounded-xl shadow-sm border border-slate-200">

        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Motivo de consulta</label>
            <textarea name="motivo" rows="2" required
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Evolución</label>
            <textarea name="evolucion" rows="4" required
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Indicaciones</label>
            <textarea name="indicaciones" rows="3"
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Diagnóstico</label>
            <input type="text" name="diagnostico"
                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80">
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="/turnos-pro/pro/paciente-historia.php?id=<?= $patient_id ?>"
               class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm hover:bg-slate-300">
                Cancelar
            </a>

            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">
                Guardar evolución
            </button>
        </div>

    </form>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>