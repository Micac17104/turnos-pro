<?php
// --- FIX DEFINITIVO PARA RAILWAY ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    @chmod($path, 0777);
}

session_save_path($path);
session_start();
// -----------------------------------

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Editar datos clínicos';
$current    = 'pacientes';

$patient_id = require_param($_GET, 'id', 'Paciente no encontrado.');

// Obtener paciente
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

// Obtener datos clínicos
$stmt = $pdo->prepare("
    SELECT antecedentes, alergias, medicacion, patologias, obra_social, nro_afiliado
    FROM patients_extra
    WHERE patient_id = ?
");
$stmt->execute([$patient_id]);
$extra = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Normalizar claves faltantes
$extra = array_merge([
    'antecedentes' => '',
    'alergias' => '',
    'medicacion' => '',
    'patologias' => '',
    'obra_social' => '',
    'nro_afiliado' => ''
], $extra);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">
            Editar datos clínicos de <?= h($paciente['name']) ?>
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Información médica relevante del paciente.
        </p>
    </div>

    <form method="post" action="paciente-datos-guardar.php"
          class="space-y-6 bg-white p-6 rounded-xl shadow-sm border border-slate-200">

        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Antecedentes</label>
            <textarea name="antecedentes" rows="3"
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"><?= h($extra['antecedentes']) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Alergias</label>
            <textarea name="alergias" rows="2"
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"><?= h($extra['alergias']) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Medicación</label>
            <textarea name="medicacion" rows="2"
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"><?= h($extra['medicacion']) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Patologías</label>
            <textarea name="patologias" rows="2"
                      class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"><?= h($extra['patologias']) ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Obra social</label>
                <input type="text" name="obra_social"
                       value="<?= h($extra['obra_social']) ?>"
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nro afiliado</label>
                <input type="text" name="nro_afiliado"
                       value="<?= h($extra['nro_afiliado']) ?>"
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="paciente-historia.php?id=<?= $patient_id ?>"
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