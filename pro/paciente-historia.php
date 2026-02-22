<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

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

// Datos clínicos extra
$stmt = $pdo->prepare("
    SELECT antecedentes, alergias, medicacion, patologias, obra_social, nro_afiliado
    FROM patients_extra
    WHERE patient_id = ?
");
$stmt->execute([$patient_id]);
$extra = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Evoluciones + archivos
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
    </div>

    <!-- DATOS CLÍNICOS -->
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

    <!-- EVOLUCIONES -->
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

                        <a href="archivo-subir.php?record_id=<?= $e['id'] ?>"
                           class="inline-block mt-4 px-3 py-1 bg-slate-200 text-slate-700 rounded hover:bg-slate-300 text-xs">
                            Adjuntar archivo
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>