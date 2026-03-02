<?php
// /pro/plantilla-usar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$patient_id  = require_param($_GET, 'patient_id', 'Paciente no encontrado.');
$template_id = require_param($_GET, 'template_id', 'Plantilla no encontrada.');

// Verificar paciente
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

// Verificar plantilla
$stmt = $pdo->prepare("
    SELECT id, title, fields
    FROM clinical_templates
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$template_id, $user_id]);
$plantilla = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$plantilla) {
    die("Plantilla no encontrada.");
}

$fields = json_decode($plantilla['fields'], true) ?: [];

$page_title = 'Historia clínica con plantilla';
$current    = 'pacientes';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">
            <?= h($plantilla['title']) ?> — <?= h($paciente['name']) ?>
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Completá la historia clínica según esta plantilla.
        </p>
    </div>

    <form method="post" action="plantilla-registro-guardar.php" enctype="multipart/form-data"
          class="space-y-6 bg-white p-6 rounded-xl shadow-sm border border-slate-200">

        <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
        <input type="hidden" name="template_id" value="<?= $template_id ?>">

        <?php foreach ($fields as $index => $field): ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    <?= h($field['label']) ?>
                </label>
                <textarea name="fields[<?= $index ?>]" rows="3"
                          class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"></textarea>
            </div>
        <?php endforeach; ?>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Adjuntar archivos (opcional)</label>
            <input type="file" name="archivos[]" multiple
                   class="text-sm text-slate-700">
            <p class="text-xs text-slate-500 mt-1">
                Formatos permitidos: PDF, JPG, JPEG, PNG.
            </p>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="paciente-historia.php?id=<?= $patient_id ?>"
               class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm hover:bg-slate-300">
                Cancelar
            </a>

            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">
                Guardar historia
            </button>
        </div>
    </form>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>