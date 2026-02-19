<?php
require __DIR__ . '/../../pro/includes/db.php';

$user_id = $_GET['user_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$user_id || !$date) {
    echo json_encode(["available" => [], "occupied" => []]);
    exit;
}

/* No permitir días pasados */
if (strtotime($date) < strtotime(date("Y-m-d"))) {
    echo json_encode(["available" => [], "occupied" => []]);
    exit;
}

$dayOfWeek = date('w', strtotime($date));

/* 1) Obtener horarios base del profesional */
$stmt = $pdo->prepare("
    SELECT start_time, end_time, interval_minutes
    FROM professional_schedule
    WHERE user_id = ? AND day_of_week = ?
");
$stmt->execute([$user_id, $dayOfWeek]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Si el profesional NO atiende ese día → devolver vacío */
if (!$schedules) {
    echo json_encode(["available" => [], "occupied" => []]);
    exit;
}

/* 2) Obtener excepciones */
$stmt = $pdo->prepare("
    SELECT * FROM professional_exceptions 
    WHERE user_id = ? AND date = ?
");
$stmt->execute([$user_id, $date]);
$exceptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Si el día está completamente bloqueado */
foreach ($exceptions as $ex) {
    if ($ex['type'] === 'block' && !$ex['start_time']) {
        echo json_encode(["available" => [], "occupied" => []]);
        exit;
    }
}

/* 3) Obtener turnos ocupados */
$stmt = $pdo->prepare("
    SELECT time FROM appointments 
    WHERE user_id = ? AND date = ? AND status != 'cancelled'
");
$stmt->execute([$user_id, $date]);
$occupied = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'time');

/* 4) Generar horarios disponibles y ocupados */
$available = [];
$occupiedList = [];

foreach ($schedules as $sch) {
    $start = strtotime($sch['start_time']);
    $end = strtotime($sch['end_time']);
    $interval = $sch['interval_minutes'] * 60;

    for ($t = $start; $t < $end; $t += $interval) {
        $time = date('H:i', $t);

        /* Si está ocupado → agregar a ocupados */
        if (in_array($time . ":00", $occupied)) {
            $occupiedList[] = $time;
            continue;
        }

        /* Aplicar excepciones */
        $blocked = false;
        foreach ($exceptions as $ex) {
            if ($ex['type'] === 'block' && $ex['start_time']) {
                $ts = strtotime($ex['start_time']);
                $te = strtotime($ex['end_time']);
                if ($t >= $ts && $t < $te) {
                    $blocked = true;
                    break;
                }
            }
        }

        if ($blocked) {
            $occupiedList[] = $time;
            continue;
        }

        $available[] = $time;
    }
}

echo json_encode([
    "available" => $available,
    "occupied" => $occupiedList
]);