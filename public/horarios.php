<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../pro/includes/helpers.php';

$pro_id = $_GET['pro'] ?? null;
$date   = $_GET['date'] ?? null;

if (!$pro_id || !$date) {
    echo "<p class='text-red-600'>Faltan datos para mostrar los horarios.</p>";
    echo "</main></div></body></html>";
    exit;
}

// Profesional
$stmt = $pdo->prepare("SELECT id, name, profession FROM users WHERE id = ?");
$stmt->execute([$pro_id]);
$pro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pro) {
    echo "<p class='text-red-600'>Profesional no encontrado.</p>";
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
");
$stmt->execute([$pro_id, $date]);
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
    Horarios disponibles
</h1>

<p class="text-slate-600 mb-6">
    Turnos para <strong><?= h($pro['name']) ?></strong> el día
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
                    <!-- RUTA CORRECTA -->
                    <a href="paciente-confirmar-turno.php?user_id=<?= $pro_id ?>&fecha=<?= $date ?>&hora=<?= $hora ?>"
                       class="px-4 py-3 bg-slate-900 text-white rounded-lg text-center hover:bg-slate-800 transition">
                        <?= $hora ?>
                    </a>
                <?php endif; ?>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <!-- RUTA CORRECTA -->
    <a href="reservar.php?pro=<?= $pro_id ?>"
       class="block mt-8 text-slate-600 hover:text-slate-900 text-sm">
        ← Elegir otra fecha
    </a>

</div>

<?php
// CIERRE DEL LAYOUT
echo "</main></div></body></html>";
?>