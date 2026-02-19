<?php
session_start();
require __DIR__ . '/../../config.php';

$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// =========================
// PARÁMETROS BÁSICOS
// =========================
$view = $_GET['view'] ?? 'day'; // vista diaria por defecto
$hoy  = $_GET['fecha'] ?? date('Y-m-d');

// =========================
// FUNCIONES DE FECHAS
// =========================
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

// =========================
// NOMBRES DE DÍAS Y MESES (SIN LOCALE)
// =========================
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

    $ingles = date('l', strtotime($fecha));
    return $dias[$ingles] ?? $ingles;
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

    $ingles = date('F', strtotime($fecha));
    return $meses[$ingles] ?? $ingles;
}

// =========================
// VISTA DIARIA
// =========================
$turnosDia = [];
$tareasDia = [];

if ($view === 'day') {
    $stmt = $pdo->prepare("
        SELECT a.*, c.name AS paciente
        FROM appointments a
        JOIN clients c ON a.client_id = c.id
        WHERE a.user_id = ?
        AND a.date = ?
        AND a.status != 'cancelled'
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

// =========================
// VISTA SEMANAL
// =========================
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
        JOIN clients c ON a.client_id = c.id
        WHERE a.user_id = ?
        AND a.date BETWEEN ? AND ?
        AND a.status != 'cancelled'
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

// =========================
// VISTA MENSUAL
// =========================
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
        JOIN clients c ON a.client_id = c.id
        WHERE a.user_id = ?
        AND a.date BETWEEN ? AND ?
        AND a.status != 'cancelled'
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

// =========================
// PACIENTES PARA MODALES
// =========================
$stmt = $pdo->prepare("SELECT id, name FROM clients WHERE user_id = ? ORDER BY name ASC");
$stmt->execute([$user_id]);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// FUNCIONES DE ESTILO POR ESTADO
// =========================
function claseTurnoEstado($status) {
    switch ($status) {
        case 'confirmed':
            return 'border-green-200 bg-green-50';
        case 'pending':
            return 'border-amber-200 bg-amber-50';
        case 'cancelled':
            return 'border-gray-200 bg-gray-100 text-gray-400 line-through';
        case 'attended':
            return 'border-blue-200 bg-blue-50';
        default:
            return 'border-gray-200 bg-gray-50';
    }
}

function etiquetaEstado($status) {
    switch ($status) {
        case 'confirmed':
            return 'Confirmado';
        case 'pending':
            return 'Pendiente';
        case 'cancelled':
            return 'Cancelado';
        case 'attended':
            return 'Atendido';
        default:
            return ucfirst($status);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-6xl mx-auto py-10">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Agenda</h1>

        <a href="/turnos-pro/profiles/<?= $user_id ?>/dashboard.php"
           class="text-gray-600 hover:text-gray-800">
            ← Volver al panel
        </a>
    </div>

    <!-- NAV VISTAS -->
    <div class="flex gap-3 mb-6">
        <a href="?view=day&fecha=<?= $hoy ?>"
           class="px-4 py-2 rounded-lg border <?= $view==='day'?'bg-blue-600 text-white border-blue-600':'bg-white text-gray-700 border-gray-300' ?>">
            Vista diaria
        </a>

        <a href="?view=week&fecha=<?= $hoy ?>"
           class="px-4 py-2 rounded-lg border <?= $view==='week'?'bg-blue-600 text-white border-blue-600':'bg-white text-gray-700 border-gray-300' ?>">
            Vista semanal
        </a>

        <a href="?view=month&fecha=<?= $hoy ?>"
           class="px-4 py-2 rounded-lg border <?= $view==='month'?'bg-blue-600 text-white border-blue-600':'bg-white text-gray-700 border-gray-300' ?>">
            Vista mensual
        </a>
    </div>

    <!-- ACCIONES SUPERIORES -->
    <div class="flex justify-between items-center mb-6">
        <!-- Navegación por fecha -->
        <div class="flex items-center gap-3">
            <?php
            $fechaAnterior  = date('Y-m-d', strtotime($hoy . ' -1 day'));
            $fechaSiguiente = date('Y-m-d', strtotime($hoy . ' +1 day'));
            ?>

            <?php if ($view === 'day'): ?>
                <a href="?view=day&fecha=<?= $fechaAnterior ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-gray-700 border-gray-300 text-sm">
                    ← Día anterior
                </a>

                <p class="font-semibold text-gray-800 text-lg">
                    <?= nombreDia($hoy) . ' ' . date('j', strtotime($hoy)) . ' de ' . nombreMes($hoy) . ' de ' . date('Y', strtotime($hoy)) ?>
                </p>

                <a href="?view=day&fecha=<?= $fechaSiguiente ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-gray-700 border-gray-300 text-sm">
                    Día siguiente →
                </a>
            <?php elseif ($view === 'week'): ?>
                <?php
                $inicioSemana   = startOfWeek($hoy);
                $finSemana      = endOfWeek($hoy);
                $semanaAnterior = date('Y-m-d', strtotime($inicioSemana . ' -7 days'));
                $semanaSiguiente= date('Y-m-d', strtotime($inicioSemana . ' +7 days'));
                ?>
                <a href="?view=week&fecha=<?= $semanaAnterior ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-gray-700 border-gray-300 text-sm">
                    ← Semana anterior
                </a>

                <p class="font-semibold text-gray-800 text-lg">
                    Semana del <?= date('d/m', strtotime($inicioSemana)) ?> al <?= date('d/m', strtotime($finSemana)) ?>
                </p>

                <a href="?view=week&fecha=<?= $semanaSiguiente ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-gray-700 border-gray-300 text-sm">
                    Semana siguiente →
                </a>
            <?php elseif ($view === 'month'): ?>
                <?php
                $inicioMes    = startOfMonth($hoy);
                $mesAnterior  = date('Y-m-d', strtotime($inicioMes . ' -1 month'));
                $mesSiguiente = date('Y-m-d', strtotime($inicioMes . ' +1 month'));
                ?>
                <a href="?view=month&fecha=<?= $mesAnterior ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-gray-700 border-gray-300 text-sm">
                    ← Mes anterior
                </a>

                <p class="font-semibold text-gray-800 text-lg">
                    <?= nombreMes($inicioMes) . ' de ' . date('Y', strtotime($inicioMes)) ?>
                </p>

                <a href="?view=month&fecha=<?= $mesSiguiente ?>"
                   class="px-3 py-2 rounded-lg border bg-white text-gray-700 border-gray-300 text-sm">
                    Mes siguiente →
                </a>
            <?php endif; ?>
        </div>

        <!-- Botones de acción -->
        <div class="flex gap-3">
            <button onclick="document.getElementById('modalTurno').classList.remove('hidden')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700">
                + Nuevo turno
            </button>

            <button onclick="document.getElementById('modalTarea').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">
                + Nueva tarea
            </button>
        </div>
    </div>

    <!-- =========================
         VISTA DIARIA (LISTA)
         ========================= -->
    <?php if ($view === 'day'): ?>

        <div class="bg-white rounded-xl shadow p-4">
            <?php if (count($turnosDia) === 0 && count($tareasDia) === 0): ?>
                <p class="text-gray-500 text-sm">
                    No tenés turnos ni tareas para este día.
                </p>
            <?php endif; ?>

            <div class="space-y-3">
                <!-- TURNOS DEL DÍA -->
                <?php foreach ($turnosDia as $t): ?>
                    <?php $clase = claseTurnoEstado($t['status']); ?>
                    <div class="flex justify-between items-center p-3 border rounded-lg <?= $clase ?>">
                        <div>
                            <p class="font-semibold text-gray-800">
                                <?= substr($t['time'], 0, 5) ?> hs — <?= htmlspecialchars($t['paciente']) ?>
                            </p>
                            <p class="text-xs text-gray-600 mt-1">
                                Estado: <?= etiquetaEstado($t['status']) ?>
                            </p>
                        </div>

                        <div class="flex flex-col items-end text-xs">
                            <button
                                class="text-blue-600 hover:underline"
                                onclick="abrirEditarTurno(
                                    <?= $t['id'] ?>,
                                    '<?= $t['date'] ?>',
                                    '<?= $t['time'] ?>',
                                    '<?= htmlspecialchars($t['paciente'], ENT_QUOTES) ?>',
                                    '<?= $t['client_id'] ?>',
                                    '<?= $t['status'] ?>'
                                )">
                                Editar
                            </button>

                            <a href="/turnos-pro/profiles/<?= $user_id ?>/cancelar-turno.php?id=<?= $t['id'] ?>&fecha=<?= $hoy ?>"
                               class="text-red-600 hover:underline mt-1">
                                Cancelar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- TAREAS DEL DÍA -->
                <?php if (count($tareasDia) > 0): ?>
                    <div class="mt-4">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Tareas</p>

                        <?php foreach ($tareasDia as $task): ?>
                            <div class="flex justify-between items-start p-3 border rounded-lg bg-blue-50">
                                <div>
                                    <p class="font-medium text-blue-800">
                                        <?= htmlspecialchars($task['title']) ?>
                                    </p>
                                    <?php if ($task['time']): ?>
                                        <p class="text-xs text-blue-600 mt-1">
                                            <?= substr($task['time'], 0, 5) ?> hs
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <button
                                    class="text-blue-700 text-xs hover:underline ml-2"
                                    onclick="abrirEditarTarea(
                                        <?= $task['id'] ?>,
                                        '<?= htmlspecialchars($task['title'], ENT_QUOTES) ?>',
                                        '<?= $task['date'] ?>',
                                        '<?= $task['time'] ?>',
                                        `<?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES) ?>`
                                    )">
                                    Editar
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>

    <!-- =========================
         VISTA SEMANAL
         ========================= -->
    <?php if ($view === 'week'): ?>

        <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-stretch">

            <?php foreach ($diasSemana as $dia): ?>
                <?php
                $turnosDiaSemana = array_filter($turnos, fn($t) => $t['date'] === $dia);
                $tareasDiaSemana = array_filter($tareas, fn($t) => $t['date'] === $dia);
                ?>

                <div class="bg-white p-4 rounded-xl shadow min-h-[220px] flex flex-col">

                    <p class="font-semibold text-gray-800 mb-3">
                        <?= nombreDia($dia) . ' ' . date('j', strtotime($dia)) ?>
                    </p>

                    <div class="flex-1 space-y-2">

                        <!-- TURNOS -->
                        <?php foreach ($turnosDiaSemana as $t): ?>
                            <?php $clase = claseTurnoEstado($t['status']); ?>
                            <div class="p-2 border rounded-lg flex justify-between items-center text-sm <?= $clase ?>">

                                <div>
                                    <p class="font-medium text-gray-800">
                                        <?= substr($t['time'], 0, 5) ?> hs
                                    </p>
                                    <p class="text-gray-600 text-xs">
                                        <?= htmlspecialchars($t['paciente']) ?>
                                    </p>
                                </div>

                                <div class="flex flex-col items-end text-xs">
                                    <button
                                        class="text-blue-600 hover:underline"
                                        onclick="abrirEditarTurno(
                                            <?= $t['id'] ?>,
                                            '<?= $t['date'] ?>',
                                            '<?= $t['time'] ?>',
                                            '<?= htmlspecialchars($t['paciente'], ENT_QUOTES) ?>',
                                            '<?= $t['client_id'] ?>',
                                            '<?= $t['status'] ?>'
                                        )">
                                        Editar
                                    </button>

                                    <a href="/turnos-pro/profiles/<?= $user_id ?>/cancelar-turno.php?id=<?= $t['id'] ?>&fecha=<?= $dia ?>"
                                       class="text-red-600 hover:underline mt-1">
                                        Cancelar
                                    </a>
                                </div>

                            </div>
                        <?php endforeach; ?>

                        <!-- TAREAS -->
                        <?php foreach ($tareasDiaSemana as $task): ?>
                            <div class="p-2 border rounded-lg bg-blue-50 flex justify-between items-start text-xs">

                                <div>
                                    <p class="font-medium text-blue-800">
                                        <?= htmlspecialchars($task['title']) ?>
                                    </p>
                                    <?php if ($task['time']): ?>
                                        <p class="text-blue-600 mt-1">
                                            <?= substr($task['time'], 0, 5) ?> hs
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <button
                                    class="text-blue-700 hover:underline ml-2"
                                    onclick="abrirEditarTarea(
                                        <?= $task['id'] ?>,
                                        '<?= htmlspecialchars($task['title'], ENT_QUOTES) ?>',
                                        '<?= $task['date'] ?>',
                                        '<?= $task['time'] ?>',
                                        `<?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES) ?>`
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

    <!-- =========================
         VISTA MENSUAL
         ========================= -->
    <?php if ($view === 'month'): ?>

        <div class="grid grid-cols-7 gap-3 mb-2">
            <div class="text-center font-semibold text-gray-700">Lun</div>
            <div class="text-center font-semibold text-gray-700">Mar</div>
            <div class="text-center font-semibold text-gray-700">Mié</div>
            <div class="text-center font-semibold text-gray-700">Jue</div>
            <div class="text-center font-semibold text-gray-700">Vie</div>
            <div class="text-center font-semibold text-gray-700">Sáb</div>
            <div class="text-center font-semibold text-gray-700">Dom</div>
        </div>

        <div class="grid grid-cols-7 gap-3">
            <?php foreach ($grid as $dia): ?>
                <div class="bg-white p-3 rounded-xl shadow min-h-[120px]">
                    <?php if ($dia): ?>
                        <p class="font-semibold text-gray-800 mb-2">
                            <?= date('j', strtotime($dia)) ?>
                        </p>

                        <?php
                        $turnosDiaMes = array_filter($turnosMes, fn($t) => $t['date'] === $dia);
                        $tareasDiaMes = array_filter($tareasMes, fn($t) => $t['date'] === $dia);
                        ?>

                        <?php foreach ($turnosDiaMes as $t): ?>
                            <?php $clase = claseTurnoEstado($t['status']); ?>
                            <div class="flex justify-between items-center text-xs text-gray-700 mb-1 border rounded px-1 <?= $clase ?>">
                                <span><?= substr($t['time'], 0, 5) ?> — <?= htmlspecialchars($t['paciente']) ?></span>

                                <a href="/turnos-pro/profiles/<?= $user_id ?>/cancelar-turno.php?id=<?= $t['id'] ?>&fecha=<?= $dia ?>"
                                   class="text-red-600 hover:underline">
                                    Cancelar
                                </a>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($tareasDiaMes as $task): ?>
                            <p class="text-xs text-blue-700">
                                • <?= htmlspecialchars($task['title']) ?>
                            </p>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<!-- MODAL NUEVA TAREA -->
<div id="modalTarea" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-xl shadow-lg w-96">

        <h3 class="text-xl font-semibold mb-4">Nueva tarea</h3>

        <form method="POST" action="/turnos-pro/profiles/<?= $user_id ?>/agenda-guardar.php">

            <input type="hidden" name="date" value="<?= $hoy ?>">

            <label class="text-sm text-gray-700">Título</label>
            <input type="text" name="title" required
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Hora (opcional)</label>
            <input type="time" name="time"
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Descripción (opcional)</label>
            <textarea name="description"
                      class="w-full p-3 border rounded-lg bg-gray-50 mb-3"></textarea>

            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modalTarea').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 rounded-lg">
                    Cancelar
                </button>

                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Guardar
                </button>
            </div>

        </form>

    </div>
</div>

<!-- MODAL NUEVO TURNO -->
<div id="modalTurno" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-xl shadow-lg w-96">

        <h3 class="text-xl font-semibold mb-4">Nuevo turno</h3>

        <form method="POST" action="/turnos-pro/profiles/<?= $user_id ?>/guardar-turno.php">

            <label class="text-sm text-gray-700">Paciente</label>
            <select name="client_id" required
                    class="w-full p-3 border rounded-lg bg-gray-50 mb-3">
                <option value="">Seleccionar...</option>
                <?php foreach ($pacientes as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label class="text-sm text-gray-700">Fecha</label>
            <input type="date" name="date" value="<?= $hoy ?>" required
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Hora</label>
            <input type="time" name="time" required
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Estado</label>
            <select name="status"
                    class="w-full p-3 border rounded-lg bg-gray-50 mb-3">
                <option value="pending">Pendiente</option>
                <option value="confirmed">Confirmado</option>
                <option value="cancelled">Cancelado</option>
            </select>

            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modalTurno').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 rounded-lg">
                    Cancelar
                </button>

                <button class="px-4 py-2 bg-green-600 text-white rounded-lg">
                    Guardar turno
                </button>
            </div>

        </form>

    </div>
</div>

<!-- MODAL EDITAR TURNO -->
<div id="modalEditarTurno" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-xl shadow-lg w-96">

        <h3 class="text-xl font-semibold mb-4">Editar turno</h3>

        <form method="POST" action="/turnos-pro/profiles/<?= $user_id ?>/editar-turno.php">

            <input type="hidden" name="id" id="edit_id">

            <label class="text-sm text-gray-700">Paciente</label>
            <select name="client_id" id="edit_client_id"
                    class="w-full p-3 border rounded-lg bg-gray-50 mb-3">
                <?php foreach ($pacientes as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label class="text-sm text-gray-700">Fecha</label>
            <input type="date" name="date" id="edit_date"
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Hora</label>
            <input type="time" name="time" id="edit_time"
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Estado</label>
            <select name="status" id="edit_status"
                    class="w-full p-3 border rounded-lg bg-gray-50 mb-3">
                <option value="pending">Pendiente</option>
                <option value="confirmed">Confirmado</option>
                <option value="cancelled">Cancelado</option>
                <option value="attended">Atendido</option>
            </select>

            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modalEditarTurno').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 rounded-lg">
                    Cerrar
                </button>

                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Guardar cambios
                </button>
            </div>

        </form>

    </div>
</div>

<script>
function abrirEditarTurno(id, fecha, hora, paciente, clientId, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_date').value = fecha;
    document.getElementById('edit_time').value = hora;
    document.getElementById('edit_client_id').value = clientId;
    document.getElementById('edit_status').value = status;

    document.getElementById('modalEditarTurno').classList.remove('hidden');
}
</script>

<!-- MODAL EDITAR TAREA -->
<div id="modalEditarTarea" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-xl shadow-lg w-96">

        <h3 class="text-xl font-semibold mb-4">Editar tarea</h3>

        <form method="POST" action="/turnos-pro/profiles/<?= $user_id ?>/editar-tarea.php">

            <input type="hidden" name="id" id="task_id">

            <label class="text-sm text-gray-700">Título</label>
            <input type="text" name="title" id="task_title"
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Fecha</label>
            <input type="date" name="date" id="task_date"
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Hora (opcional)</label>
            <input type="time" name="time" id="task_time"
                   class="w-full p-3 border rounded-lg bg-gray-50 mb-3">

            <label class="text-sm text-gray-700">Descripción</label>
            <textarea name="description" id="task_description"
                      class="w-full p-3 border rounded-lg bg-gray-50 mb-3"></textarea>

            <div class="flex justify-between items-center">

                <a id="task_delete_link"
                   class="text-red-600 text-sm hover:underline">
                    Eliminar tarea
                </a>

                <div class="flex gap-3">
                    <button type="button"
                            onclick="document.getElementById('modalEditarTarea').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 rounded-lg">
                        Cerrar
                    </button>

                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                        Guardar cambios
                    </button>
                </div>

            </div>

        </form>

    </div>
</div>

<script>
function abrirEditarTarea(id, title, date, time, description) {
    document.getElementById('task_id').value = id;
    document.getElementById('task_title').value = title;
    document.getElementById('task_date').value = date;
    document.getElementById('task_time').value = time || '';
    document.getElementById('task_description').value = description || '';

    document.getElementById('task_delete_link').href =
        "/turnos-pro/profiles/<?= $user_id ?>/eliminar-tarea.php?id=" + id;

    document.getElementById('modalEditarTarea').classList.remove('hidden');
}
</script>

</body>
</html>