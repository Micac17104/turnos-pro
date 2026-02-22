<?php
// --- FIX DEFINITIVO PARA RAILWAY ---
$path = __DIR__ . '/../sessions';

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if (!is_writable($path)) {
    chmod($path, 0777);
}

session_save_path($path);
session_start();
// -----------------------------------

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Agenda';
$current    = 'agenda';

// Parámetros
$view = $_GET['view'] ?? 'day';
$hoy  = $_GET['fecha'] ?? date('Y-m-d');

// Helpers de fechas
function startOfWeek($date) {
    return date('Y-m-d', strtotime('monday this week', strtotime($date)));
}
function endOfWeek($date) {
    return date('Y-m-d', strtotime('sunday this week', strtotime($date)));
}
function startOfMonth($date) {
    return date('Y-m-01', strtotime($date));
}
function endOfMonth($date) {
    return date('Y-m-t', strtotime($date));
}

// Nombres
function nombreDia($fecha) {
    $dias = [
        'Monday'    => 'Lunes',
        'Tuesday'   => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday'  => 'Jueves',
        'Friday'    => 'Viernes',
        'Saturday'  => 'Sábado',
        'Sunday'    => 'Domingo'
    ];
    return $dias[date('l', strtotime($fecha))];
}
function nombreMes($fecha) {
    $meses = [
        'January'   => 'enero',
        'February'  => 'febrero',
        'March'     => 'marzo',
        'April'     => 'abril',
        'May'       => 'mayo',
        'June'      => 'junio',
        'July'      => 'julio',
        'August'    => 'agosto',
        'September' => 'septiembre',
        'October'   => 'octubre',
        'November'  => 'noviembre',
        'December'  => 'diciembre'
    ];
    return $meses[date('F', strtotime($fecha))];
}

// Vista diaria
$turnosDia = [];
$tareasDia = [];
if ($view === 'day') {
    $stmt = $pdo->prepare("
        SELECT a.*, c.name AS paciente
        FROM appointments a
        LEFT JOIN clients c ON a.client_id = c.id
        WHERE a.user_id = ?
          AND a.date = ?
          AND a.status IN ('pending','confirmed','attended')
        ORDER BY a.time
    ");
    $stmt->execute([$user_id, $hoy]);
    $turnosDia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT *
        FROM professional_tasks
        WHERE user_id = ?
          AND date = ?
        ORDER BY time IS NULL, time
    ");
    $stmt->execute([$user_id, $hoy]);
    $tareasDia = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Vista semanal
if ($view === 'week') {
    $inicio = startOfWeek($hoy);
    $fin    = endOfWeek($hoy);

    $diasSemana = [];
    for ($i = 0; $i < 7; $i++) {
        $diasSemana[] = date('Y-m-d', strtotime("$inicio +$i day"));
    }

    $stmt = $pdo->prepare("
        SELECT a.*, c.name AS paciente
        FROM appointments a
        LEFT JOIN clients c ON a.client_id = c.id
        WHERE a.user_id = ?
          AND a.date BETWEEN ? AND ?
          AND a.status IN ('pending','confirmed','attended')
        ORDER BY a.date, a.time
    ");
    $stmt->execute([$user_id, $inicio, $fin]);
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT *
        FROM professional_tasks
        WHERE user_id = ?
          AND date BETWEEN ? AND ?
        ORDER BY date, time IS NULL, time
    ");
    $stmt->execute([$user_id, $inicio, $fin]);
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Vista mensual
if ($view === 'month') {
    $inicioMes = startOfMonth($hoy);
    $finMes    = endOfMonth($hoy);

    $primerDiaSemana = date('N', strtotime($inicioMes));
    $diasMes         = date('t', strtotime($hoy));

    $grid = [];
    for ($i = 1; $i < $primerDiaSemana; $i++) {
        $grid[] = null;
    }
    for ($d = 1; $d <= $diasMes; $d++) {
        $grid[] = date('Y-m-d', strtotime("$inicioMes +".($d-1)." day"));
    }

    $stmt = $pdo->prepare("
        SELECT a.*, c.name AS paciente
        FROM appointments a
        LEFT JOIN clients c ON a.client_id = c.id
        WHERE a.user_id = ?
          AND a.date BETWEEN ? AND ?
          AND a.status IN ('pending','confirmed','attended')
        ORDER BY a.date, a.time
    ");
    $stmt->execute([$user_id, $inicioMes, $finMes]);
    $turnosMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT *
        FROM professional_tasks
        WHERE user_id = ?
          AND date BETWEEN ? AND ?
        ORDER BY date, time IS NULL, time
    ");
    $stmt->execute([$user_id, $inicioMes, $finMes]);
    $tareasMes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pacientes para modales
$stmt = $pdo->prepare("SELECT id, name FROM clients WHERE user_id = ? ORDER BY name ASC");
$stmt->execute([$user_id]);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estilos
function claseTurnoEstado($status) {
    return match ($status) {
        'confirmed' => 'border-green-200 bg-green-50',
        'pending'   => 'border-amber-200 bg-amber-50',
        'attended'  => 'border-blue-200 bg-blue-50',
        'cancelled' => 'border-gray-200 bg-gray-100 text-gray-400 line-through',
        default     => 'border-gray-200 bg-gray-50',
    };
}
function etiquetaEstado($status) {
    return match ($status) {
        'confirmed' => 'Confirmado',
        'pending'   => 'Pendiente',
        'attended'  => 'Atendido',
        'cancelled' => 'Cancelado',
        default     => ucfirst($status),
    };
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8 bg-slate-50">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">Agenda</h1>

        <a href="dashboard.php"
           class="text-sm text-slate-600 hover:text-slate-900">
            ← Volver al panel
        </a>
    </div>

    <!-- NAV VISTAS -->
    <div class="flex gap-3 mb-6">
        <a href="?view=day&fecha=<?= h($hoy) ?>"
           class="px-4 py-2 rounded-lg border text-sm <?= $view==='day'?'bg-slate-900 text-white border-slate-900':'bg-white text-slate-700 border-slate-300' ?>">
            Vista diaria
        </a>
        <a href="?view=week&fecha=<?= h($hoy) ?>"
           class="px-4 py-2 rounded-lg border text-sm <?= $view==='week'?'bg-slate-900 text-white border-slate-900':'bg-white text-slate-700 border-slate-300' ?>">
            Vista semanal
        </a>
        <a href="?view=month&fecha=<?= h($hoy) ?>"
           class="px-4 py-2 rounded-lg border text-sm <?= $view==='month'?'bg-slate-900 text-white border-slate-900':'bg-white text-slate-700 border-slate-300' ?>">
            Vista mensual
        </a>
    </div>

    <!-- ACCIONES SUPERIORES -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3 text-sm">
            <?php
            $fechaAnterior  = date('Y-m-d', strtotime($hoy . ' -1 day'));
            $fechaSiguiente = date('Y-m-d', strtotime($hoy . ' +1 day'));
            ?>

            <?php if ($view === 'day'): ?>
                <a href="?view=day&fecha=<?= h($fechaAnterior) ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-slate-700 border-slate-300">
                    ← Día anterior
                </a>

                <p class="font-semibold text-slate-900 text-base">
                    <?= nombreDia($hoy) . ' ' . date('j', strtotime($hoy)) . ' de ' . nombreMes($hoy) . ' de ' . date('Y', strtotime($hoy)) ?>
                </p>

                <a href="?view=day&fecha=<?= h($fechaSiguiente) ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-slate-700 border-slate-300">
                    Día siguiente →
                </a>

            <?php elseif ($view === 'week'): ?>
                <?php
                $inicioSemana   = startOfWeek($hoy);
                $finSemana      = endOfWeek($hoy);
                $semanaAnterior = date('Y-m-d', strtotime($inicioSemana . ' -7 days'));
                $semanaSiguiente= date('Y-m-d', strtotime($inicioSemana . ' +7 days'));
                ?>

                <a href="?view=week&fecha=<?= h($semanaAnterior) ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-slate-700 border-slate-300">
                    ← Semana anterior
                </a>

                <p class="font-semibold text-slate-900 text-base">
                    Semana del <?= date('d/m', strtotime($inicioSemana)) ?> al <?= date('d/m', strtotime($finSemana)) ?>
                </p>

                <a href="?view=week&fecha=<?= h($semanaSiguiente) ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-slate-700 border-slate-300">
                    Semana siguiente →
                </a>

            <?php elseif ($view === 'month'): ?>
                <?php
                $inicioMes    = startOfMonth($hoy);
                $mesAnterior  = date('Y-m-d', strtotime($inicioMes . ' -1 month'));
                $mesSiguiente = date('Y-m-d', strtotime($inicioMes . ' +1 month'));
                ?>

                <a href="?view=month&fecha=<?= h($mesAnterior) ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-slate-700 border-slate-300">
                    ← Mes anterior
                </a>

                <p class="font-semibold text-slate-900 text-base">
                    <?= nombreMes($inicioMes) . ' de ' . date('Y', strtotime($inicioMes)) ?>
                </p>

                <a href="?view=month&fecha=<?= h($mesSiguiente) ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-slate-700 border-slate-300">
                    Mes siguiente →
                </a>
            <?php endif; ?>
        </div>

        <div class="flex gap-3">
            <button type="button"
                    onclick="document.getElementById('modalTurno').classList.remove('hidden')"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm shadow hover:bg-emerald-700">
                + Nuevo turno
            </button>

            <button type="button"
                    onclick="document.getElementById('modalTarea').classList.remove('hidden')"
                    class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm shadow hover:bg-slate-800">
                + Nueva tarea
            </button>
        </div>
    </div>

    <!-- VISTA DIARIA -->
    <?php if ($view === 'day'): ?>

    <div class="grid grid-cols-3 gap-6">

        <!-- COLUMNA PRINCIPAL -->
        <div class="col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-4">

            <?php if (count($turnosDia) === 0 && count($tareasDia) === 0): ?>
                <p class="text-slate-500 text-sm">
                    No tenés turnos ni tareas para este día.
                </p>
            <?php endif; ?>

            <div class="space-y-3">
                <?php foreach ($turnosDia as $t): ?>

                    <?php 
                        $clase = claseTurnoEstado($t['status']);
                        $nombre = $t['paciente'] ?: $t['name'];
                    ?>

                    <div class="flex justify-between items-center p-3 border rounded-lg text-sm <?= $clase ?>">
                        <div>
                            <p class="font-semibold text-slate-900">
                                <?= substr($t['time'], 0, 5) ?> hs — <?= h($nombre) ?>
                            </p>

                            <p class="text-xs text-slate-600 mt-1">
                                Estado: <?= etiquetaEstado($t['status']) ?>
                            </p>
                        </div>

                        <div class="flex flex-col items-end text-xs">
                            <button
                                class="text-slate-900 hover:underline"
                                onclick="abrirEditarTurno(
                                    <?= (int)$t['id'] ?>,
                                    '<?= h($t['date']) ?>',
                                    '<?= h($t['time']) ?>',
                                    '<?= h($nombre, ENT_QUOTES) ?>',
                                    '<?= (int)$t['client_id'] ?>',
                                    '<?= h($t['status']) ?>'
                                )">
                                Editar
                            </button>

                            <a href="turno-cancelar.php?id=<?= (int)$t['id'] ?>"
                               class="text-red-600 hover:underline mt-1">
                                Cancelar
                            </a>
                        </div>
                    </div>

                <?php endforeach; ?>

                <?php if (count($tareasDia) > 0): ?>
                    <div class="mt-4">
                        <p class="text-sm font-semibold text-slate-800 mb-2">Tareas</p>

                        <?php foreach ($tareasDia as $task): ?>
                            <div class="flex justify-between items-start p-3 border rounded-lg bg-slate-50 text-sm">
                                <div>
                                    <p class="font-medium text-slate-900">
                                        <?= h($task['title']) ?>
                                    </p>
                                    <?php if ($task['time']): ?>
                                        <p class="text-xs text-slate-600 mt-1">
                                            <?= substr($task['time'], 0, 5) ?> hs
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <button
                                    class="text-slate-900 text-xs hover:underline ml-2"
                                    onclick="abrirEditarTarea(
                                        <?= (int)$task['id'] ?>,
                                        '<?= h($task['title'], ENT_QUOTES) ?>',
                                        '<?= h($task['date']) ?>',
                                        '<?= h($task['time']) ?>',
                                        `<?= h($task['description'] ?? '', ENT_QUOTES) ?>`
                                    )">
                                    Editar
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SIDEBAR DERECHA -->
        <div class="col-span-1 bg-white rounded-xl shadow-sm border border-slate-200 p-4">

            <h3 class="text-lg font-semibold text-slate-900 mb-4">Turnos del día</h3>

            <?php if (count($turnosDia) === 0): ?>
                <p class="text-slate-500 text-sm">No hay turnos para hoy.</p>
            <?php endif; ?>

            <div class="space-y-4">
                <?php foreach ($turnosDia as $t): ?>

                    <?php 
                        $nombre = $t['paciente'] ?: $t['name'];
                    ?>

                    <div class="border rounded-lg p-3 bg-slate-50">
                        <p class="font-semibold text-slate-900 text-sm">
                            <?= substr($t['time'], 0, 5) ?> hs — <?= h($nombre) ?>
                        </p>

                        <?php if ($t['phone']): ?>
                            <p class="text-xs text-slate-700 mt-1">
                                Tel: <?= h($t['phone']) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($t['email']): ?>
                            <p class="text-xs text-slate-700">
                                Email: <?= h($t['email']) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                <?php endforeach; ?>
            </div>

        </div>

    </div>

    <?php endif; ?>

    <!-- VISTA SEMANAL -->
    <?php if ($view === 'week'): ?>
        <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-stretch">
            <?php foreach ($diasSemana as $dia): ?>
                <?php
                $turnosDiaSemana = array_filter($turnos, fn($t) => $t['date'] === $dia);
                $tareasDiaSemana = array_filter($tareas, fn($t) => $t['date'] === $dia);
                ?>
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 min-h-[220px] flex flex-col">
                    <p class="font-semibold text-slate-900 mb-3 text-sm">
                        <?= nombreDia($dia) . ' ' . date('j', strtotime($dia)) ?>
                    </p>

                    <div class="flex-1 space-y-2">
                        <?php foreach ($turnosDiaSemana as $t): ?>
                            <?php $clase = claseTurnoEstado($t['status']); ?>
                            <div class="p-2 border rounded-lg flex justify-between items-center text-xs <?= $clase ?>">
                                <div>
                                    <p class="font-medium text-slate-900">
                                        <?= substr($t['time'], 0, 5) ?> hs
                                    </p>
                                    <p class="text-slate-600 text-[11px]">
                                        <?= h($t['paciente'] ?? 'Paciente sin registrar') ?>
                                    </p>
                                </div>

                                <div class="flex flex-col items-end text-[11px]">
                                    <button
                                        class="text-slate-900 hover:underline"
                                        onclick="abrirEditarTurno(
                                            <?= (int)$t['id'] ?>,
                                            '<?= h($t['date']) ?>',
                                            '<?= h($t['time']) ?>',
                                            '<?= h($t['paciente'] ?? '', ENT_QUOTES) ?>',
                                            '<?= (int)$t['client_id'] ?>',
                                            '<?= h($t['status']) ?>'
                                        )">
                                        Editar
                                    </button>

                                    <a href="turno-cancelar.php?id=<?= (int)$t['id'] ?>"
                                       class="text-red-600 hover:underline mt-1">
                                        Cancelar
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($tareasDiaSemana as $task): ?>
                            <div class="p-2 border rounded-lg bg-slate-50 flex justify-between items-start text-[11px]">
                                <div>
                                    <p class="font-medium text-slate-900">
                                        <?= h($task['title']) ?>
                                    </p>
                                    <?php if ($task['time']): ?>
                                        <p class="text-slate-600 mt-1">
                                            <?= substr($task['time'], 0, 5) ?> hs
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <button
                                    class="text-slate-900 hover:underline ml-2"
                                    onclick="abrirEditarTarea(
                                        <?= (int)$task['id'] ?>,
                                        '<?= h($task['title'], ENT_QUOTES) ?>',
                                        '<?= h($task['date']) ?>',
                                        '<?= h($task['time']) ?>',
                                        `<?= h($task['description'] ?? '', ENT_QUOTES) ?>`
                                    )">
                                    Editar
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- VISTA MENSUAL -->
    <?php if ($view === 'month'): ?>
        <div class="grid grid-cols-7 gap-3 mb-2 text-xs">
            <div class="text-center font-semibold text-slate-700">Lun</div>
            <div class="text-center font-semibold text-slate-700">Mar</div>
            <div class="text-center font-semibold text-slate-700">Mié</div>
            <div class="text-center font-semibold text-slate-700">Jue</div>
            <div class="text-center font-semibold text-slate-700">Vie</div>
            <div class="text-center font-semibold text-slate-700">Sáb</div>
            <div class="text-center font-semibold text-slate-700">Dom</div>
        </div>

        <div class="grid grid-cols-7 gap-3 text-xs">
            <?php foreach ($grid as $dia): ?>
                <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-200 min-h-[120px]">
                    <?php if ($dia): ?>
                        <p class="font-semibold text-slate-900 mb-2">
                            <?= date('j', strtotime($dia)) ?>
                        </p>

                        <?php
                        $turnosDiaMes = array_filter($turnosMes, fn($t) => $t['date'] === $dia);
                        $tareasDiaMes = array_filter($tareasMes, fn($t) => $t['date'] === $dia);
                        ?>

                        <?php foreach ($turnosDiaMes as $t): ?>
                            <?php $clase = claseTurnoEstado($t['status']); ?>
                            <div class="flex justify-between items-center mb-1 border rounded px-1 py-[2px] <?= $clase ?>">
                                <span class="text-[11px]">
                                    <?= substr($t['time'], 0, 5) ?> — <?= h($t['paciente'] ?? 'Paciente sin registrar') ?>
                                </span>
                                <a href="turno-cancelar.php?id=<?= (int)$t['id'] ?>"
                                   class="text-red-600 hover:underline text-[11px]">
                                    Cancelar
                                </a>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($tareasDiaMes as $task): ?>
                            <p class="text-[11px] text-slate-800">
                                • <?= h($task['title']) ?>
                            </p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- MODAL NUEVA TAREA -->
    <div id="modalTarea" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl shadow-lg w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4 text-slate-900">Nueva tarea</h3>

            <form method="POST" action="tarea-guardar.php">
                <input type="hidden" name="date" value="<?= h($hoy) ?>">

                <label class="text-xs font-medium text-slate-700">Título</label>
                <input type="text" name="title" required
                       class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm">

                <label class="text-xs font-medium text-slate-700">Hora (opcional)</label>
                <input type="time" name="time"
                       class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm">

                <label class="text-xs font-medium text-slate-700">Descripción (opcional)</label>
                <textarea name="description"
                          class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-4 text-sm"></textarea>

                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="document.getElementById('modalTarea').classList.add('hidden')"
                            class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm">
                        Cancelar
                    </button>

                    <button class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL NUEVO TURNO -->
    <div id="modalTurno" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl shadow-lg w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4 text-slate-900">Nuevo turno</h3>

            <form method="POST" action="turno-guardar.php">
                <label class="text-xs font-medium text-slate-700">Paciente</label>
                <select name="client_id" required
                        class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($pacientes as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= h($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="text-xs font-medium text-slate-700">Fecha</label>
                <input type="date" name="date" value="<?= h($hoy) ?>" required
                       class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm">

                <label class="text-xs font-medium text-slate-700">Hora</label>
                <input type="time" name="time" required
                       class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm">

                <label class="text-xs font-medium text-slate-700">Estado</label>
                <select name="status"
                        class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-4 text-sm">
                    <option value="pending">Pendiente</option>
                    <option value="confirmed">Confirmado</option>
                    <option value="cancelled">Cancelado</option>
                </select>

                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="document.getElementById('modalTurno').classList.add('hidden')"
                            class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm">
                        Cancelar
                    </button>

                    <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">
                        Guardar turno
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR TURNO -->
    <div id="modalEditarTurno" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl shadow-lg w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4 text-slate-900">Editar turno</h3>

            <form method="POST" action="turno-guardar-agenda.php">
                <input type="hidden" name="turno_id" id="edit_id">

                <label class="text-xs font-medium text-slate-700">Paciente</label>
                <select name="client_id" id="edit_client_id"
                        class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm">
                    <option value="">Paciente no registrado</option>
                    <?php foreach ($pacientes as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= h($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="text-xs font-medium text-slate-700">Fecha</label>
                <input type="date" name="date" id="edit_date"
                       class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm">

                <label class="text-xs font-medium text-slate-700">Hora</label>
                <input type="text" name="time" id="edit_time"
                       class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm"
                       placeholder="HH:MM" maxlength="5" pattern="[0-9]{2}:[0-9]{2}">

                <label class="text-xs font-medium text-slate-700">Estado</label>
                <select name="status" id="edit_status"
                        class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-4 text-sm">
                    <option value="pending">Pendiente</option>
                    <option value="confirmed">Confirmado</option>
                    <option value="attended">Atendido</option>
                    <option value="cancelled">Cancelado</option>
                </select>

                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="document.getElementById('modalEditarTurno').classList.add('hidden')"
                            class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm">
                        Cerrar
                    </button>

                    <button class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR TAREA -->
    <div id="modalEditarTarea" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-xl shadow-lg w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4 text-slate-900">Editar tarea</h3>

            <form method="POST" action="tarea-editar.php">
                <input type="hidden" name="id" id="task_id">

                <label class="text-xs font-medium text-slate-700">Título</label>
                <input type="text" name="title" id="task_title"
                       class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm">

                <label class="text-xs font-medium text-slate-700">Fecha</label>
                <input type="date" name="date" id="task_date"
                       class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm">

                <label class="text-xs font-medium text-slate-700">Hora (opcional)</label>
                <input type="time" name="time" id="task_time"
                       class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-3 text-sm">

                <label class="text-xs font-medium text-slate-700">Descripción</label>
                <textarea name="description" id="task_description"
                          class="w-full px-3 py-2 border rounded-lg bg-slate-50 mb-4 text-sm"></textarea>

                <div class="flex justify-between items-center">
                    <a id="task_delete_link"
                       class="text-red-600 text-xs hover:underline">
                        Eliminar tarea
                    </a>

                    <div class="flex gap-3">
                        <button type="button"
                                onclick="document.getElementById('modalEditarTarea').classList.add('hidden')"
                                class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm">
                            Cerrar
                        </button>

                        <button class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm">
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</main>

<script>
// Permitir escribir la hora libremente en el campo de edición
document.querySelectorAll('#edit_time').forEach(input => {
    input.addEventListener('input', e => {
        let v = e.target.value.replace(/[^0-9]/g, '');
        if (v.length >= 3) v = v.slice(0,2) + ':' + v.slice(2,4);
        e.target.value = v;
    });
});

function abrirEditarTurno(id, fecha, hora, paciente, clientId, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_date').value = fecha;
    document.getElementById('edit_time').value = hora;
    document.getElementById('edit_client_id').value = clientId;
    document.getElementById('edit_status').value = status;
    document.getElementById('modalEditarTurno').classList.remove('hidden');
}

function abrirEditarTarea(id, title, date, time, description) {
    document.getElementById('task_id').value = id;
    document.getElementById('task_title').value = title;
    document.getElementById('task_date').value = date;
    document.getElementById('task_time').value = time || '';
    document.getElementById('task_description').value = description || '';

    document.getElementById('task_delete_link').href =
        "tarea-eliminar.php?id=" + id;

    document.getElementById('modalEditarTarea').classList.remove('hidden');
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>