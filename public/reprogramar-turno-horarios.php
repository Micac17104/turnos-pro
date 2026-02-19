<?php
require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

$turno_id = $_GET['turno_id'] ?? null;
$pro_id   = $_GET['pro'] ?? null;
$date     = $_GET['date'] ?? null;

if (!$turno_id || !$pro_id || !$date) {
    echo "<p class='text-red-600'>Faltan datos para mostrar los horarios.</p>";
    echo "</main></div></body></html>";
    exit;
}

// Obtener turno original
$stmt = $pdo->prepare("
    SELECT a.*, u.name AS profesional, u.profession
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$turno_id]);
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    echo "<p class='text-red-600'>Turno no encontrado.</p>";
    echo "</main></div></body></html>";
    exit;
}

// Horarios configurados
$stmt = $pdo->prepare("
    SELECT *
    FROM schedules
    WHERE user_id = ?
      AND day_of_week = ?
    ORDER BY start_time
");
$stmt->execute([$pro_id, date("N", strtotime($date))]);
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Turnos reservados
$stmt = $pdo->prepare("
    SELECT time
    FROM appointments
    WHERE user_id = ?
      AND date = ?
      AND status IN ('confirmed', 'pending')
      AND id != ?  -- excluir el turno actual
");
$stmt->execute([$pro_id, $date, $turno_id]);
$reservados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$reservados_map = [];
foreach ($reservados as $r) {
    $reservados_map[substr($r['time'], 0, 5)] = true;
}

// Generar turnos
function generarTurnos($horarios, $fecha) {
    $turnos = [];

    foreach ($horarios as $h) {
        $inicio = strtotime("$fecha {$h['start_time']}");
        $fin    = strtotime("$fecha {$h['end_time']}");
        $dur    = $h['slot_duration'] * 60;

        for ($t = $inicio; $t < $fin; $t += $dur) {
            $turnos[] = date("H:i", $t);
        }
    }

    return $turnos;
}

$turnos = generarTurnos($horarios, $date);
?>

<h1 class="text-2xl font-bold text-slate-900 mb-2">
    Elegí un nuevo horario
</h1>

<p class="text-slate-600 mb-6">
    Reprogramando turno con <strong><?= h($turno['profesional']) ?></strong> para el día
    <strong><?= date("d/m/Y", strtotime($date)) ?></strong>.
</p>

<div class="bg-white p-8 rounded-xl shadow border max-w-2xl">

    <?php if (empty($turnos)): ?>
        <p class="text-slate-500">Este profesional no tiene horarios configurados para este día.</p>
    <?php else: ?>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

            <?php foreach ($turnos as $hora): ?>
                <?php $ocupado = isset($reservados_map[$hora]); ?>

                <?php if ($ocupado): ?>
                    <div class="px-4 py-3 bg-slate-200 text-slate-500 rounded-lg text-center line-through">
                        <?= $hora ?>
                    </div>
                <?php else: ?>
                    <a href="/turnos-pro/public/reprogramar-turno-confirmar.php?turno_id=<?= $turno_id ?>&fecha=<?= $date ?>&hora=<?= $hora ?>"
                       class="px-4 py-3 bg-slate-900 text-white rounded-lg text-center hover:bg-slate-800 transition">
                        <?= $hora ?>
                    </a>
                <?php endif; ?>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <a href="/turnos-pro/public/reprogramar-turno.php?id=<?= $turno_id ?>"
       class="block mt-8 text-slate-600 hover:text-slate-900 text-sm">
        ← Elegir otra fecha
    </a>

</div>

<?php
// CIERRE DEL LAYOUT
echo "</main></div></body></html>";
?>