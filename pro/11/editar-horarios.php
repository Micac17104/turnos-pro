<?php
session_start();
require __DIR__ . '/../../config.php';

// Validar login del profesional
if (!isset($_SESSION['user_id'])) {
    header("Location: /turnos-pro/index.php");
    exit;
}

// Detectar si estoy en _template o en un tenant real
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

    <link rel="stylesheet" href="/turnos-pro/assets/style.css">

    <style>
        .page-box {
            max-width: 600px;
            margin: 40px auto;
        }

        h2 {
            color: #0f172a;
            margin-bottom: 15px;
        }

        h3 {
            color: #0f172a;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        .card {
            background: #ffffff;
            padding: 20px;
            border-radius: 14px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .horario-item {
            padding: 12px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            margin-bottom: 10px;
        }

        label {
            font-size: 14px;
            color: #334155;
            margin-bottom: 6px;
            display: block;
        }

        select,
        input[type="time"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            margin-bottom: 12px;
            background: #f9fafb;
            transition: 0.2s ease;
        }

        select:focus,
        input:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 1px rgba(14, 165, 233, 0.35);
            background: #ffffff;
        }

        .alert-success {
            background: #ecfdf3;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 15px;
            color: #166534;
            font-size: 13px;
            border: 1px solid #bbf7d0;
        }
    </style>
</head>
<body>

<div class="page-box">

    <h2>Configurar horarios</h2>

    <?php if ($mensaje): ?>
        <div class="alert-success"><?= $mensaje ?></div>
    <?php endif; ?>

    <div class="card">
        <h3>Agregar horario</h3>

        <form method="post">

            <label>Día:</label>
            <select name="day" required>
                <?php foreach ($dias as $num => $nombre): ?>
                    <option value="<?= $num ?>"><?= $nombre ?></option>
                <?php endforeach; ?>
            </select>

            <label>Hora inicio:</label>
            <input type="time" name="start" required>

            <label>Hora fin:</label>
            <input type="time" name="end" required>

            <label>Duración del turno (minutos):</label>
            <input type="number" name="duration" value="30" required>

            <button type="submit" class="btn-big" style="margin-top:10px;">
                Agregar horario
            </button>
        </form>
    </div>

    <div class="card">
        <h3>Horarios cargados</h3>

        <?php foreach ($horarios as $h): ?>
            <div class="horario-item">
                <strong><?= $dias[$h['day_of_week']] ?></strong><br>
                <?= substr($h['start_time'], 0, 5) ?> a <?= substr($h['end_time'], 0, 5) ?><br>
                Turnos de <?= $h['slot_duration'] ?> min
            </div>
        <?php endforeach; ?>
    </div>

</div>

</body>
</html>