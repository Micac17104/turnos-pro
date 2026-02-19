<?php
require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';

$paciente_id = $_SESSION['paciente_id'];

// Obtener historia clínica desde clinical_records
$stmt = $pdo->prepare("
    SELECT r.*, u.name AS profesional, u.profession
    FROM clinical_records r
    JOIN users u ON r.user_id = u.id
    WHERE r.patient_id = ?
    ORDER BY r.fecha DESC
");
$stmt->execute([$paciente_id]);
$historia = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Historia clínica</h1>

<p class="text-slate-600 mb-8">
    Aquí podés ver todas tus evoluciones, indicaciones y diagnósticos cargados por tus profesionales.
</p>

<div class="space-y-6">

    <?php if (empty($historia)): ?>

        <div class="bg-white p-8 rounded-xl shadow border text-center">
            <p class="text-slate-500">Todavía no tenés registros en tu historia clínica.</p>
        </div>

    <?php else: ?>

        <?php foreach ($historia as $h): ?>
            <div class="bg-white p-6 rounded-xl shadow border">

                <div class="flex justify-between items-center mb-4">
                    <div>
                        <p class="font-semibold text-slate-900">
                            <?= htmlspecialchars($h['profesional']) ?>
                        </p>
                        <p class="text-sm text-slate-500">
                            <?= htmlspecialchars($h['profession']) ?>
                        </p>
                    </div>

                    <div class="text-sm text-slate-500">
                        <?= date("d/m/Y", strtotime($h['fecha'])) ?>
                    </div>
                </div>

                <div class="border-t pt-4 space-y-4">

                    <?php if ($h['motivo']): ?>
                        <div>
                            <p class="text-sm text-slate-500">Motivo de consulta</p>
                            <p class="text-slate-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($h['motivo'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($h['evolucion']): ?>
                        <div>
                            <p class="text-sm text-slate-500">Evolución</p>
                            <p class="text-slate-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($h['evolucion'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($h['indicaciones']): ?>
                        <div>
                            <p class="text-sm text-slate-500">Indicaciones</p>
                            <p class="text-slate-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($h['indicaciones'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($h['diagnostico']): ?>
                        <div>
                            <p class="text-sm text-slate-500">Diagnóstico</p>
                            <p class="text-slate-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($h['diagnostico'])) ?></p>
                        </div>
                    <?php endif; ?>

                </div>

            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php
echo "</main></div></body></html>";
?>