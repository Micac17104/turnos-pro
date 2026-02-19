<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Horarios';
$current    = 'horarios';

$dias = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    0 => 'Domingo'
];

// Obtener horarios actuales desde professional_schedule
$stmt = $pdo->prepare("SELECT * FROM professional_schedule WHERE user_id = ?");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$horarios = [];
foreach ($rows as $r) {
    $horarios[$r['day_of_week']] = $r;
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Horarios de atención</h1>

    <form method="post" action="/turnos-pro/pro/horarios-guardar.php" class="space-y-10">

        <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Disponibilidad semanal</h2>

            <div class="space-y-6">

                <?php foreach ($dias as $dow => $label): 
                    $h = $horarios[$dow] ?? [
                        'start_time'      => '09:00',
                        'end_time'        => '17:00',
                        'interval_minutes'=> 30
                    ];
                ?>

                <div class="flex items-center justify-between border-b border-slate-200 pb-4">

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="enabled[<?= $dow ?>]"
                               <?= isset($horarios[$dow]) ? 'checked' : '' ?>
                               class="w-4 h-4">
                        <span class="text-sm text-slate-700"><?= $label ?></span>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="time" name="start[<?= $dow ?>]"
                               value="<?= h($h['start_time']) ?>"
                               class="px-3 py-2 rounded-lg border border-slate-300 text-sm">

                        <span class="text-slate-500">a</span>

                        <input type="time" name="end[<?= $dow ?>]"
                               value="<?= h($h['end_time']) ?>"
                               class="px-3 py-2 rounded-lg border border-slate-300 text-sm">

                        <select name="interval[<?= $dow ?>]"
                                class="px-3 py-2 rounded-lg border border-slate-300 text-sm">
                            <?php foreach ([15,20,30,45,60] as $i): ?>
                                <option value="<?= $i ?>" <?= (int)$h['interval_minutes'] === $i ? 'selected' : '' ?>>
                                    <?= $i ?> min
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <?php endforeach; ?>

            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="/turnos-pro/pro/agenda.php"
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