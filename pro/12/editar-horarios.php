<?php
session_start();
require __DIR__ . '/../../config.php';

// Validar login
if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Detectar tenant real
$dir = basename(__DIR__);
$user_id = is_numeric($dir) ? $dir : $_SESSION['user_id'];

$mensaje = "";

// Guardar horario nuevo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $day = intval($_POST['day']);
    $start = $_POST['start'];
    $end = $_POST['end'];
    $duration = intval($_POST['duration']);

    $stmt = $pdo->prepare("
        INSERT INTO schedules (user_id, day_of_week, start_time, end_time, slot_duration)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $day, $start, $end, $duration]);

    $mensaje = "Horario agregado correctamente.";
}

// Obtener horarios existentes
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE user_id = ? ORDER BY day_of_week, start_time");
$stmt->execute([$user_id]);
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dias = [
    1 => "Lunes",
    2 => "Martes",
    3 => "Miércoles",
    4 => "Jueves",
    5 => "Viernes",
    6 => "Sábado",
    7 => "Domingo"
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configurar horarios</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-xl mx-auto mt-12">

    <a href="/turnos-pro/profiles/<?= $user_id ?>/dashboard.php"
       class="inline-block mb-6 text-gray-600 hover:text-gray-800">
        ← Volver al panel
    </a>

    <h2 class="text-3xl font-bold text-gray-800 mb-6">Configurar horarios</h2>

    <?php if ($mensaje): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg border border-green-300 text-sm">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <!-- AGREGAR HORARIO -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Agregar horario</h3>

        <form method="post" class="space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Día</label>
                <select name="day" required
                        class="w-full p-3 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400">
                    <?php foreach ($dias as $num => $nombre): ?>
                        <option value="<?= $num ?>"><?= $nombre ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hora inicio</label>
                <input type="time" name="start" required
                       class="w-full p-3 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hora fin</label>
                <input type="time" name="end" required
                       class="w-full p-3 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Duración del turno (minutos)</label>
                <input type="number" name="duration" value="30" required
                       class="w-full p-3 border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-400">
            </div>

            <button type="submit"
                    class="w-full py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
                Agregar horario
            </button>

        </form>
    </div>

    <!-- HORARIOS CARGADOS -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Horarios cargados</h3>

        <?php if (empty($horarios)): ?>
            <p class="text-gray-500">No hay horarios configurados.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($horarios as $h): ?>
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <p class="font-semibold text-gray-800"><?= $dias[$h['day_of_week']] ?></p>
                        <p class="text-gray-600 text-sm">
                            <?= substr($h['start_time'], 0, 5) ?> a <?= substr($h['end_time'], 0, 5) ?>
                        </p>
                        <p class="text-gray-600 text-sm">
                            Turnos de <?= $h['slot_duration'] ?> min
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>