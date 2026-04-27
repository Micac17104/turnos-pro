<?php
// /pro/paciente-historia.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Historia clínica';
$current    = 'pacientes';

$patient_id = require_param($_GET, 'id', 'Paciente no encontrado.');

// Obtener paciente
$stmt = $pdo->prepare("
    SELECT id, name, phone
    FROM clients
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$patient_id, $user_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no pertenece a este profesional.");
}

// Preguntas personalizadas del profesional
$stmt = $pdo->prepare("
    SELECT id, question_text, type, required
    FROM clinical_questions
    WHERE professional_id = ?
    ORDER BY id ASC
");
$stmt->execute([$user_id]);
$preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Respuestas del paciente a esas preguntas
$answers = [];
if (!empty($preguntas)) {
    $stmt = $pdo->prepare("
        SELECT question_id, answer
        FROM clinical_answers
        WHERE client_id = ? AND professional_id = ?
    ");
    $stmt->execute([$patient_id, $user_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $answers[$row['question_id']] = $row['answer'];
    }
}


// Datos clínicos extra
$stmt = $pdo->prepare("
    SELECT antecedentes, alergias, medicacion, patologias, obra_social, nro_afiliado
    FROM patients_extra
    WHERE patient_id = ?
");
$stmt->execute([$patient_id]);
$extra = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Evoluciones + archivos (clásico)
$stmt = $pdo->prepare("
    SELECT cr.id AS record_id, cr.fecha, cr.motivo, cr.evolucion, cr.indicaciones, cr.diagnostico,
           cf.id AS file_id, cf.file_name, cf.file_path
    FROM clinical_records cr
    LEFT JOIN clinical_files cf ON cf.record_id = cr.id
    WHERE cr.patient_id = ? AND cr.user_id = ?
    ORDER BY cr.fecha DESC
");
$stmt->execute([$patient_id, $user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar archivos por evolución
$evoluciones = [];
foreach ($rows as $r) {
    $id = $r['record_id'];
    if (!isset($evoluciones[$id])) {
        $evoluciones[$id] = [
            'id'          => $id,
            'fecha'       => $r['fecha'],
            'motivo'      => $r['motivo'],
            'evolucion'   => $r['evolucion'],
            'indicaciones'=> $r['indicaciones'],
            'diagnostico' => $r['diagnostico'],
            'archivos'    => []
        ];
    }
    if ($r['file_id']) {
        $evoluciones[$id]['archivos'][] = [
            'id'        => $r['file_id'],
            'file_name' => $r['file_name'],
            'file_path' => $r['file_path']
        ];
    }
}
// Plantillas del profesional
$stmt = $pdo->prepare("
    SELECT id, title
    FROM clinical_templates
    WHERE user_id = ? AND center_id IS NULL
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$plantillas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Registros de plantillas para este paciente
$stmt = $pdo->prepare("
    SELECT r.id, r.created_at, r.data, t.title
    FROM clinical_template_records r
    JOIN clinical_templates t ON t.id = r.template_id
    WHERE r.client_id = ? AND r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$patient_id, $user_id]);
$tpl_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Archivos de registros de plantillas
$tpl_files = [];
if (!empty($tpl_rows)) {
    $ids = array_column($tpl_rows, 'id');
    $in  = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT id, record_id, file_name, file_path
        FROM clinical_template_files
        WHERE record_id IN ($in)
    ");
    $stmt->execute($ids);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($files as $f) {
        $tpl_files[$f['record_id']][] = $f;
    }
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Historia clínica de <?= h($paciente['name']) ?>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Teléfono: <?= h($paciente['phone']) ?>
            </p>
        </div>

        <a href="pacientes.php"
           class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm hover:bg-slate-300">
            ← Volver
        </a>

        <a href="paciente-editar.php?id=<?= $patient_id ?>"
   class="px-3 py-2 bg-slate-900 text-white rounded-lg text-sm">
    Editar datos personales
</a>

    </div>

    


    <!-- DATOS CLÍNICOS FIJOS -->
    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Datos clínicos</h2>

            <a href="paciente-datos-editar.php?id=<?= $patient_id ?>"
               class="px-3 py-1 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
                Editar
            </a>
        </div>

        <div class="space-y-2 text-sm text-slate-700">
            <p><strong>Antecedentes:</strong> <?= nl2br(h($extra['antecedentes'] ?? 'No registrado')) ?></p>
            <p><strong>Alergias:</strong> <?= nl2br(h($extra['alergias'] ?? 'No registrado')) ?></p>
            <p><strong>Medicación:</strong> <?= nl2br(h($extra['medicacion'] ?? 'No registrado')) ?></p>
            <p><strong>Patologías:</strong> <?= nl2br(h($extra['patologias'] ?? 'No registrado')) ?></p>
            <p><strong>Obra social:</strong> <?= h($extra['obra_social'] ?? 'No registrado') ?></p>
            <p><strong>Nro afiliado:</strong> <?= h($extra['nro_afiliado'] ?? 'No registrado') ?></p>
        </div>
    </section>

    <?php
$stmt = $pdo->prepare("
    SELECT pc.id, p.name, p.total_sessions, pc.sessions_used
    FROM packs_clients pc
    JOIN packs p ON p.id = pc.pack_id
    WHERE pc.client_id = ?
      AND p.owner_type = 'professional'
      AND p.owner_id = ?
    ORDER BY pc.id DESC
");
$stmt->execute([$patient_id, $user_id]);
$packs_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($packs_activos)): ?>
<section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
    <h2 class="text-lg font-semibold text-slate-900 mb-4">Packs de sesiones</h2>

    <?php foreach ($packs_activos as $p): ?>
        <?php $restantes = $p['total_sessions'] - $p['sessions_used']; ?>
        <p class="text-sm text-slate-700">
            <strong><?= h($p['name']) ?>:</strong>
            <?= $p['sessions_used'] ?>/<?= $p['total_sessions'] ?> usadas
            (<?= $restantes ?> restantes)
        </p>
    <?php endforeach; ?>
</section>
<?php endif; ?>


        <!-- PREGUNTAS PERSONALIZADAS -->
    <?php if (!empty($preguntas)): ?>
    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Datos clínicos personalizados</h2>
        </div>

        <form method="post" action="paciente-preguntas-guardar.php" class="space-y-4">
            <input type="hidden" name="patient_id" value="<?= $patient_id ?>">

            <?php foreach ($preguntas as $q): ?>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        <?= h($q['question_text']) ?>
                        <?php if ($q['required']): ?>
                            <span class="text-red-500">*</span>
                        <?php endif; ?>
                    </label>

                    <?php
                        $field_name = 'q_' . $q['id'];
                        $value = $answers[$q['id']] ?? '';
                    ?>

                    <?php if ($q['type'] === 'textarea'): ?>
                        <textarea name="<?= $field_name ?>" rows="3"
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"
                                  <?= $q['required'] ? 'required' : '' ?>><?= h($value) ?></textarea>

                    <?php elseif ($q['type'] === 'number'): ?>
                        <input type="number" name="<?= $field_name ?>"
                               value="<?= h($value) ?>"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"
                               <?= $q['required'] ? 'required' : '' ?>>

                    <?php else: ?>
                        <input type="text" name="<?= $field_name ?>"
                               value="<?= h($value) ?>"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-slate-900/80"
                               <?= $q['required'] ? 'required' : '' ?>>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="flex justify-end pt-2">
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800">
                    Guardar datos personalizados
                </button>
            </div>
        </form>
    </section>
    <?php endif; ?>

        <!-- HISTORIA CLÍNICA PERSONALIZADA -->
    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Historia clínica personalizada</h2>

            <div class="flex gap-2">
                <a href="plantilla-nueva.php"
                   class="px-3 py-1 rounded-lg bg-slate-200 text-slate-700 text-sm hover:bg-slate-300">
                    Nueva plantilla
                </a>

                <?php if (!empty($plantillas)): ?>
                    <div class="relative">
                        <details class="group">
                            <summary class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-sm cursor-pointer hover:bg-emerald-700">
                                Registrar con plantilla
                            </summary>
                            <div class="absolute mt-2 bg-white border border-slate-200 rounded-lg shadow-lg z-10 min-w-[220px]">
                                <?php foreach ($plantillas as $p): ?>
                                    <a href="plantilla-usar.php?patient_id=<?= $patient_id ?>&template_id=<?= $p['id'] ?>"
                                       class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                        <?= h($p['title']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($tpl_rows)): ?>
            <p class="text-sm text-slate-500">Todavía no registraste historias clínicas personalizadas para este paciente.</p>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($tpl_rows as $r): ?>
                    <?php $data = json_decode($r['data'], true) ?: []; ?>
                    <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl">

                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-sm font-semibold text-slate-900">
                                <?= date("d/m/Y H:i", strtotime($r['created_at'])) ?> — <?= h($r['title']) ?>
                            </h3>

                            <a href="plantilla-registro-eliminar.php?id=<?= $r['id'] ?>"
                               class="text-red-600 text-xs"
                               onclick="return confirm('¿Eliminar este registro?')">
                                Eliminar registro
                            </a>
                        </div>

                        <div class="space-y-1 text-sm text-slate-700">
                            <?php foreach ($data as $item): ?>
                                <p>
                                    <strong><?= h($item['label']) ?>:</strong>
                                    <?= nl2br(h($item['value'])) ?>
                                </p>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($tpl_files[$r['id']])): ?>
                            <div class="mt-4">
                                <strong>Archivos adjuntos:</strong>
                                <ul class="list-disc ml-6 mt-2 text-blue-700 text-sm">
                                    <?php foreach ($tpl_files[$r['id']] as $f): ?>
                                        <li>
                                            <a href="../../uploads/<?= h($f['file_path']) ?>" target="_blank">
                                                <?= h($f['file_name']) ?>
                                            </a>
                                            <a href="plantilla-archivo-eliminar.php?id=<?= $f['id'] ?>"
                                               class="text-red-600 ml-2 text-xs"
                                               onclick="return confirm('¿Eliminar archivo?')">
                                                Eliminar
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
        <!-- EVOLUCIONES CLÁSICAS -->
    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">Evoluciones</h2>

            <a href="evolucion-nueva.php?patient_id=<?= $patient_id ?>"
               class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-sm hover:bg-emerald-700">
                Nueva evolución
            </a>
        </div>

        <?php if (empty($evoluciones)): ?>
            <p class="text-sm text-slate-500">No hay evoluciones registradas.</p>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($evoluciones as $e): ?>
                    <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl">
                        <h3 class="text-sm font-semibold text-slate-900 mb-2">
                            <?= date("d/m/Y H:i", strtotime($e['fecha'])) ?>
                        </h3>

                        <p><strong>Motivo:</strong> <?= nl2br(h($e['motivo'])) ?></p>
                        <p><strong>Evolución:</strong> <?= nl2br(h($e['evolucion'])) ?></p>
                        <p><strong>Indicaciones:</strong> <?= nl2br(h($e['indicaciones'])) ?></p>
                        <p><strong>Diagnóstico:</strong> <?= h($e['diagnostico']) ?></p>

                        <?php if (!empty($e['archivos'])): ?>
                            <div class="mt-4">
                                <strong>Archivos adjuntos:</strong>
                                <ul class="list-disc ml-6 mt-2 text-blue-700 text-sm">
                                    <?php foreach ($e['archivos'] as $f): ?>
                                        <li>
                                            <a href="archivo-ver.php?id=<?= $f['id'] ?>" target="_blank">
                                                <?= h($f['file_name']) ?>
                                            </a>

                                            <a href="archivo-eliminar.php?id=<?= $f['id'] ?>"
                                               class="text-red-600 ml-2 text-xs"
                                               onclick="return confirm('¿Eliminar archivo?')">
                                                Eliminar
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="archivo-subir.php" method="post" enctype="multipart/form-data" class="mt-4 flex items-center gap-2 text-xs">
                            <input type="hidden" name="record_id" value="<?= $e['id'] ?>">
                            <input type="file" name="archivo" class="text-xs">
                            <button type="submit"
                                    class="px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300">
                                Adjuntar archivo
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
